@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Daftar Komposisi (Bahan)</h3>
    <a href="{{ route('compositions.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Bahan
    </a>
</div>

<div class="card shadow">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Kode</th>
                    <th>Nama Bahan</th>
                    <th>Stok Saat Ini</th>
                    <th>Min. Stok</th>
                    <th>Satuan</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($compositions as $comp)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><code>{{ $comp->composition_code }}</code></td>
                    <td>{{ $comp->name }}</td>
                    <td class="{{ $comp->current_stock <= $comp->minimum_stock ? 'text-danger fw-bold' : '' }}">
                        {{ $comp->current_stock }}
                    </td>
                    <td>{{ $comp->minimum_stock }}</td>
                    <td>{{ $comp->unit }}</td>
                    <td class="text-center">
                        <a href="{{ route('compositions.edit', $comp->id) }}" class="btn btn-sm btn-info text-white">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <form action="{{ route('compositions.destroy', $comp->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus bahan ini?')">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">Belum ada data komposisi.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection