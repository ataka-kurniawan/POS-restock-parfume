<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Categories
        $cat1 = \App\Models\Category::create(['name' => 'Signature Series']);
        $cat2 = \App\Models\Category::create(['name' => 'Floral Bliss']);

        // 2. Compositions (Bahan Baku)
        $comp1 = \App\Models\Composition::create([
            'composition_code' => 'B001',
            'name' => 'Bibit Parfum Vanilla',
            'unit' => 'ml',
            'current_stock' => 1000,
            'minimum_stock' => 100,
        ]);
        $comp2 = \App\Models\Composition::create([
            'composition_code' => 'B002',
            'name' => 'Alkohol 96%',
            'unit' => 'ml',
            'current_stock' => 5000,
            'minimum_stock' => 500,
        ]);
        $comp3 = \App\Models\Composition::create([
            'composition_code' => 'B003',
            'name' => 'Fixative',
            'unit' => 'ml',
            'current_stock' => 500,
            'minimum_stock' => 50,
        ]);

        // 3. Products
        $prod1 = \App\Models\Product::create([
            'category_id' => $cat1->id,
            'product_code' => 'P001',
            'name' => 'Vanilla Sky 50ml',
            'price' => 150000,
        ]);

        // 4. Recipes
        \App\Models\ProductRecipe::create([
            'product_id' => $prod1->id,
            'composition_id' => $comp1->id,
            'quantity_used' => 25, // 25ml bibit
        ]);
        \App\Models\ProductRecipe::create([
            'product_id' => $prod1->id,
            'composition_id' => $comp2->id,
            'quantity_used' => 20, // 20ml alkohol
        ]);
        \App\Models\ProductRecipe::create([
            'product_id' => $prod1->id,
            'composition_id' => $comp3->id,
            'quantity_used' => 5, // 5ml fixative
        ]);

        // 5. Suppliers
        \App\Models\Supplier::create([
            'name' => 'Toko Wangi Makmur',
            'phone' => '08123456789',
            'address' => 'Jl. Aroma Harum No. 12',
        ]);
    }
}
