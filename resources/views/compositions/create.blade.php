@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('compositions.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card shadow">
    <div class="card-header bg-white">
        <h5 class="mb-0">Tambah Bahan Baru</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('compositions.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="composition_code" class="form-label">Kode Bahan</label>
                    <input type="text" class="form-control" id="composition_code" name="composition_code" placeholder="B001" required autofocus>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nama Bahan</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Alkohol 96%" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="unit" class="form-label">Satuan</label>
                    <input type="text" class="form-control" id="unit" name="unit" placeholder="ml, gr, pcs" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="minimum_stock" class="form-label">Stok Minimum</label>
                    <input type="number" step="0.01" class="form-control" id="minimum_stock" name="minimum_stock" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Bahan
            </button>
        </form>
    </div>
</div>
@endsection