<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductRecipe;
use App\Models\Composition;
use Illuminate\Http\Request;

class ProductRecipeController extends Controller
{
    public function index(Product $product)
    {
        $recipes = $product->recipes()->with('composition')->get();
        return view('product_recipes.index', compact('product', 'recipes'));
    }

    public function create(Product $product)
    {
        $compositions = Composition::all();
        return view('product_recipes.create', compact('product', 'compositions'));
    }

    public function store(Request $request, Product $product)
    {
        $request->validate([
            'composition_id' => 'required|exists:compositions,id',
            'quantity_used' => 'required|numeric|min:0.01',
        ]);

        $product->recipes()->create($request->all());
        return redirect()->route('products.recipes.index', $product->id)->with('success', 'Bahan berhasil ditambahkan ke resep.');
    }

    public function destroy(Product $product, ProductRecipe $recipe)
    {
        $recipe->delete();
        return redirect()->route('products.recipes.index', $product->id)->with('success', 'Bahan berhasil dihapus dari resep.');
    }
}
