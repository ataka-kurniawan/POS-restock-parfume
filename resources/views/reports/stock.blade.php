@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Laporan Stok Komposisi</h3>
    <button onclick="window.print()" class="btn btn-outline-primary d-print-none">
        <i class="fas fa-print"></i> Cetak Laporan
    </button>
</div>

<div class="card shadow">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Kode</th>
                    <th>Nama Bahan</th>
                    <th>Satuan</th>
                    <th>Min. Stok</th>
                    <th>Stok Saat Ini</th>
                    <th>Status</th>
                    <th>Total Mutasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($compositions as $comp)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td><code>{{ $comp->composition_code }}</code></td>
                    <td>{{ $comp->name }}</td>
                    <td>{{ $comp->unit }}</td>
                    <td>{{ $comp->minimum_stock }}</td>
                    <td class="fw-bold {{ $comp->current_stock <= $comp->minimum_stock ? 'text-danger' : '' }}">
                        {{ $comp->current_stock }}
                    </td>
                    <td>
                        @if($comp->current_stock <= 0)
                            <span class="badge bg-danger">Habis</span>
                        @elseif($comp->current_stock <= $comp->minimum_stock)
                            <span class="badge bg-warning text-dark">Menipis</span>
                        @else
                            <span class="badge bg-success">Aman</span>
                        @endif
                    </td>
                    <td>{{ $comp->stock_movements_count }} kali</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection