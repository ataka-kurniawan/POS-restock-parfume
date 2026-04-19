@extends('layouts.app')

@section('content')
<div class="mb-4">
    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card shadow">
    <div class="card-header bg-white">
        <h5 class="mb-0">Edit Kategori</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('categories.update', $category->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Nama Kategori</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ $category->name }}" required autofocus>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Perbarui Kategori
            </button>
        </form>
    </div>
</div>
@endsection