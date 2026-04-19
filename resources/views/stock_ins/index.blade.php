@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Riwayat Stok Masuk</h3>
    <a href="{{ route('stock-ins.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Stok Masuk
    </a>
</div>

<div class="card shadow">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Tanggal</th>
                    <th>Bahan</th>
                    <th>Supplier</th>
                    <th>Qty</th>
                    <th>Satuan</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockIns as $si)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($si->date)->format('d/m/Y') }}</td>
                    <td>{{ $si->composition->name }}</td>
                    <td>{{ $si->supplier->name ?? '-' }}</td>
                    <td>{{ $si->qty }}</td>
                    <td>{{ $si->composition->unit }}</td>
                    <td><small>{{ $si->note ?? '-' }}</small></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4">Belum ada riwayat stok masuk.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection