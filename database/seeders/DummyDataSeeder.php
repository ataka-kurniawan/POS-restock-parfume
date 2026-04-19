<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\Composition;
use App\Models\Notification;
use App\Models\RestockPrediction;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Import DB facade

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Dummy untuk Login
        // Use firstOrCreate to ensure users are not duplicated on re-run
        User::firstOrCreate(['email' => 'admin@gmail.com'], [
            'name' => 'Admin Scentart',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::firstOrCreate(['email' => 'kasir@gmail.com'], [
            'name' => 'Kasir Scentart',
            'password' => Hash::make('password'),
            'role' => 'kasir',
        ]);

        User::firstOrCreate(['email' => 'owner@gmail.com'], [
            'name' => 'Owner Scentart',
            'password' => Hash::make('password'),
            'role' => 'owner',
        ]);

        // Ensure MasterDataSeeder's data exists or create essential ones
        // We need a category for products and compositions for restock predictions
        $category = \App\Models\Category::firstOrCreate(['name' => 'Default Category']);

        // 2. Tambah Produk Tambahan
        $prod2 = Product::firstOrCreate(['product_code' => 'P002'], [
            'category_id' => $category->id,
            'name' => 'Rose Garden 30ml',
            'price' => 85000,
        ]);

        // 3. Tambah Komposisi Tambahan (Bahan Baku)
        $comp4 = Composition::firstOrCreate(['composition_code' => 'B004'], [
            'name' => 'Bibit Rose Essential',
            'unit' => 'ml',
            'current_stock' => 50, // Stok menipis
            'minimum_stock' => 100,
        ]);

        // 4. Tambah Resep untuk Produk P002
        // Use firstOrCreate for ProductRecipe to avoid duplication
        \App\Models\ProductRecipe::firstOrCreate(
            ['product_id' => $prod2->id, 'composition_id' => $comp4->id],
            ['quantity_used' => 15]
        );

        // 5. Data Penjualan Historis (100 records)
        $allProducts = Product::all();
        // Ensure there are products available to sell, fallback if none exist
        if ($allProducts->isEmpty()) {
             $sampleProduct = Product::firstOrCreate(['product_code' => 'P999'], [
                'category_id' => $category->id,
                'name' => 'Sample Product for Seeding',
                'price' => 10000,
             ]);
             $allProducts = Product::all(); // Re-fetch to include the sample product if it was just created
        }

        // Get a kasir user ID for sales, fallback to admin if no kasir is found
        $kasirUserId = User::where('role', 'kasir')->value('id') ?? User::where('role', 'admin')->value('id');

        for ($i = 0; $i < 100; $i++) {
            $date = Carbon::now()->subDays(rand(0, 7));
            $product = $allProducts->random();
            $qty = rand(1, 3);
            $total = $product->price * $qty;

            // Use firstOrCreate for Sale to avoid duplicate invoice numbers if seeder runs multiple times
            // Invoice number format changed slightly for better uniqueness
            $sale = Sale::firstOrCreate(
                ['invoice_number' => 'INV-' . $date->format('YmdHis') . '-' . $i], 
                [
                    'user_id' => $kasirUserId, 
                    'sale_date' => $date,
                    'total_amount' => $total,
                    'paid_amount' => $total + 5000,
                    'change_amount' => 5000,
                ]
            );

            // Use firstOrCreate for SaleDetail to avoid duplicates
            SaleDetail::firstOrCreate(
                ['sale_id' => $sale->id, 'product_id' => $product->id],
                ['qty' => $qty, 'price' => $product->price, 'subtotal' => $total]
            );
        }

        // 6. Notifikasi Dummy (Low Stock)
        $compB004 = Composition::where('composition_code', 'B004')->first();
        if ($compB004) {
            Notification::firstOrCreate(['title' => 'Stok Menipis: Bibit Rose Essential'], [
                'message' => 'Stok saat ini hanya ' . $compB004->current_stock . ' ' . $compB004->unit . ', segera lakukan restock.',
                'type' => 'low_stock',
            ]);
        }

        // 7. Prediksi Restock Dummy (Modul ML)
        RestockPrediction::firstOrCreate(
            ['composition_id' => $comp4->id, 'period' => Carbon::now()->format('Y-m')],
            [
                'predicted_label' => 'High Priority',
                'probability' => 0.89,
                'recommendation_score' => 95.5,
                'notes' => 'Permintaan produk Rose Garden meningkat drastis minggu ini.',
            ]
        );

        $comp1 = Composition::where('composition_code', 'B001')->first();
        if ($comp1) {
            RestockPrediction::firstOrCreate(
                ['composition_id' => $comp1->id, 'period' => Carbon::now()->format('Y-m')],
                [
                    'predicted_label' => 'Medium Priority',
                    'probability' => 0.65,
                    'recommendation_score' => 70.2,
                    'notes' => 'Stok masih cukup tapi trend penjualan Vanilla stabil.',
                ]
            );
        }
    }
}