@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Mutasi Stok Lengkap</h3>
</div>

<div class="card shadow">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Waktu</th>
                    <th>Bahan</th>
                    <th>Tipe</th>
                    <th>Qty</th>
                    <th>Sblm</th>
                    <th>Ssdh</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $m)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($m->movement_date)->format('d/m/Y H:i') }}</td>
                    <td>{{ $m->composition->name }}</td>
                    <td>
                        <span class="badge {{ $m->type == 'in' ? 'bg-success' : 'bg-danger' }}">
                            {{ strtoupper($m->type) }}
                        </span>
                    </td>
                    <td>{{ $m->qty }}</td>
                    <td>{{ $m->stock_before }}</td>
                    <td>{{ $m->stock_after }}</td>
                    <td><small>{{ $m->note }}</small></td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">Belum ada data mutasi stok.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection