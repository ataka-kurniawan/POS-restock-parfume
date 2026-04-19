@extends('layouts.app')

@section('content')
<div class="mb-4 d-flex justify-content-between">
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Kembali ke Produk
    </a>
    <h4 class="mb-0">Resep: {{ $product->name }}</h4>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="mb-0">Tambah Bahan ke Resep</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('products.recipes.store', $product->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="composition_id" class="form-label">Bahan (Komposisi)</label>
                        <select class="form-select" id="composition_id" name="composition_id" required>
                            <option value="">Pilih Bahan</option>
                            @foreach(\App\Models\Composition::all() as $comp)
                            <option value="{{ $comp->id }}">{{ $comp->name }} ({{ $comp->unit }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="quantity_used" class="form-label">Jumlah Digunakan</label>
                        <input type="number" step="0.01" class="form-control" id="quantity_used" name="quantity_used" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-plus"></i> Tambah ke Resep
                    </button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="mb-0">Daftar Bahan Saat Ini</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Bahan</th>
                            <th>Qty</th>
                            <th>Unit</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recipes as $recipe)
                        <tr>
                            <td>{{ $recipe->composition->name }}</td>
                            <td>{{ $recipe->quantity_used }}</td>
                            <td>{{ $recipe->composition->unit }}</td>
                            <td class="text-center">
                                <form action="{{ route('products.recipes.destroy', [$product->id, $recipe->id]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus dari resep?')">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">Resep belum diatur.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection