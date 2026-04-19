<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\Composition;
use App\Models\StockMovement;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with('user')->latest()->get();
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $products = Product::with('category')->get();
        return view('pos.index', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $totalAmount = 0;
            $saleItems = [];

            // 1. First Pass: Validate stock for ALL items before doing anything
            $neededCompositions = [];
            foreach ($request->items as $item) {
                $product = Product::with('recipes')->find($item['product_id']);
                $subtotal = $product->price * $item['qty'];
                $totalAmount += $subtotal;

                foreach ($product->recipes as $recipe) {
                    $compId = $recipe->composition_id;
                    $neededQty = $recipe->quantity_used * $item['qty'];
                    $neededCompositions[$compId] = ($neededCompositions[$compId] ?? 0) + $neededQty;
                }

                $saleItems[] = [
                    'product_id' => $product->id,
                    'qty' => $item['qty'],
                    'price' => $product->price,
                    'subtotal' => $subtotal
                ];
            }

            // Check if every composition is sufficient
            foreach ($neededCompositions as $compId => $totalNeeded) {
                $comp = Composition::find($compId);
                if ($comp->current_stock < $totalNeeded) {
                    return back()->withErrors(['error' => "Stok bahan '{$comp->name}' tidak mencukupi."])->withInput();
                }
            }

            if ($request->paid_amount < $totalAmount) {
                return back()->withErrors(['error' => "Jumlah bayar kurang."])->withInput();
            }

            // 2. Create Sale Record
            $sale = Sale::create([
                'invoice_number' => 'INV-' . date('YmdHis'),
                'user_id' => Auth::id(),
                'sale_date' => now(),
                'total_amount' => $totalAmount,
                'paid_amount' => $request->paid_amount,
                'change_amount' => $request->paid_amount - $totalAmount,
            ]);

            // 3. Create Details and Reduce Stock
            foreach ($saleItems as $detail) {
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $detail['product_id'],
                    'qty' => $detail['qty'],
                    'price' => $detail['price'],
                    'subtotal' => $detail['subtotal'],
                ]);

                // Reduce stock based on recipe
                $product = Product::with('recipes.composition')->find($detail['product_id']);
                foreach ($product->recipes as $recipe) {
                    $comp = $recipe->composition;
                    $reduceQty = $recipe->quantity_used * $detail['qty'];

                    $stockBefore = $comp->current_stock;
                    $stockAfter = $stockBefore - $reduceQty;

                    $comp->update(['current_stock' => $stockAfter]);

                    // Record Stock Movement
                    StockMovement::create([
                        'composition_id' => $comp->id,
                        'type' => 'out',
                        'reference_type' => Sale::class,
                        'reference_id' => $sale->id,
                        'qty' => $reduceQty,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'movement_date' => now(),
                        'note' => "Penjualan: {$sale->invoice_number} ({$product->name})",
                    ]);

                    // 4. Low Stock Warning Notification
                    if ($stockAfter <= $comp->minimum_stock) {
                        $type = $stockAfter <= 0 ? 'out_of_stock' : 'low_stock';
                        $title = $type == 'out_of_stock' ? "Stok Habis: {$comp->name}" : "Stok Menipis: {$comp->name}";
                        
                        Notification::create([
                            'title' => $title,
                            'message' => "Stok bahan {$comp->name} saat ini adalah {$stockAfter} {$comp->unit}.",
                            'type' => $type,
                        ]);
                    }
                }
            }

            return redirect()->route('sales.show', $sale->id)->with('success', 'Transaksi berhasil disimpan.');
        });
    }

    public function show(Sale $sale)
    {
        $sale->load(['details.product', 'user']);
        return view('sales.show', compact('sale'));
    }
}
