<?php

namespace App\Http\Controllers;

use App\Models\Composition;
use Illuminate\Http\Request;

class CompositionController extends Controller
{
    public function index()
    {
        $compositions = Composition::all();
        return view('compositions.index', compact('compositions'));
    }

    public function create()
    {
        return view('compositions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'composition_code' => 'required|string|unique:compositions,composition_code',
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'minimum_stock' => 'required|numeric|min:0',
        ]);

        Composition::create($request->all());
        return redirect()->route('compositions.index')->with('success', 'Komposisi berhasil ditambahkan.');
    }

    public function edit(Composition $composition)
    {
        return view('compositions.edit', compact('composition'));
    }

    public function update(Request $request, Composition $composition)
    {
        $request->validate([
            'composition_code' => 'required|string|unique:compositions,composition_code,' . $composition->id,
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:20',
            'minimum_stock' => 'required|numeric|min:0',
        ]);

        $composition->update($request->all());
        return redirect()->route('compositions.index')->with('success', 'Komposisi berhasil diperbarui.');
    }

    public function destroy(Composition $composition)
    {
        $composition->delete();
        return redirect()->route('compositions.index')->with('success', 'Komposisi berhasil dihapus.');
    }
}
