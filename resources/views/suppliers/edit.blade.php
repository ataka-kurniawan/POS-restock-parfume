@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card shadow">
    <div class="card-header bg-white">
        <h5 class="mb-0">Edit Supplier</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Nama Supplier</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $supplier->name }}" required autofocus>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Nomor Telepon</label>
                <input type="text" class="form-control" id="phone" name="phone" value="{{ $supplier->phone }}">
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Alamat</label>
                <textarea class="form-control" id="address" name="address" rows="3">{{ $supplier->address }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Perbarui Supplier
            </button>
        </form>
    </div>
</div>
@endsection