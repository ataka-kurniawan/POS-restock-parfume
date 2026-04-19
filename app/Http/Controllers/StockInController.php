<?php

namespace App\Http\Controllers;

use App\Models\StockIn;
use App\Models\Composition;
use App\Models\Supplier;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockInController extends Controller
{
    public function index()
    {
        $stockIns = StockIn::with(['composition', 'supplier'])->latest()->get();
        return view('stock_ins.index', compact('stockIns'));
    }

    public function create()
    {
        $compositions = Composition::all();
        $suppliers = Supplier::all();
        return view('stock_ins.create', compact('compositions', 'suppliers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'composition_id' => 'required|exists:compositions,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'qty' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'note' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $composition = Composition::lockForUpdate()->find($request->composition_id);
            
            $stockBefore = $composition->current_stock;
            $stockAfter = $stockBefore + $request->qty;

            // 1. Save Stock In
            $stockIn = StockIn::create($request->all());

            // 2. Update Composition Stock
            $composition->update(['current_stock' => $stockAfter]);

            // 3. Record Stock Movement
            StockMovement::create([
                'composition_id' => $composition->id,
                'type' => 'in',
                'reference_type' => StockIn::class,
                'reference_id' => $stockIn->id,
                'qty' => $request->qty,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'movement_date' => now(),
                'note' => 'Stock In: ' . ($request->note ?? 'N/A'),
            ]);
        });

        return redirect()->route('stock-ins.index')->with('success', 'Stok masuk berhasil dicatat.');
    }
}
