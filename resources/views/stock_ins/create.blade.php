@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('stock-ins.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card shadow">
    <div class="card-header bg-white">
        <h5 class="mb-0">Tambah Stok Masuk</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('stock-ins.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="composition_id" class="form-label">Bahan (Komposisi)</label>
                    <select class="form-select" id="composition_id" name="composition_id" required>
                        <option value="">Pilih Bahan</option>
                        @foreach($compositions as $comp)
                        <option value="{{ $comp->id }}">{{ $comp->name }} (Stok: {{ $comp->current_stock }} {{ $comp->unit }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="supplier_id" class="form-label">Supplier (Opsional)</label>
                    <select class="form-select" id="supplier_id" name="supplier_id">
                        <option value="">Pilih Supplier</option>
                        @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="qty" class="form-label">Jumlah Masuk</label>
                    <input type="number" step="0.01" class="form-control" id="qty" name="qty" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="date" class="form-label">Tanggal Masuk</label>
                    <input type="date" class="form-control" id="date" name="date" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-12 mb-3">
                    <label for="note" class="form-label">Catatan</label>
                    <textarea class="form-control" id="note" name="note" rows="2"></textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Simpan Stok Masuk
            </button>
        </form>
    </div>
</div>
@endsection