@extends('layouts.app')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0">Filter Laporan Penjualan</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('reports.sales') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Tanggal Selesai</label>
                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Riwayat Penjualan</h5>
                <span class="badge bg-success">Total Pendapatan: Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Tanggal</th>
                            <th>Kasir</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td><a href="{{ route('sales.show', $sale->id) }}"><code>{{ $sale->invoice_number }}</code></a></td>
                            <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y H:i') }}</td>
                            <td>{{ $sale->user->name }}</td>
                            <td class="text-end">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4">Tidak ada data untuk periode ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="mb-0">Produk Terlaris (Periode Ini)</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @forelse($bestSellers as $product)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $product->name }}
                        <span class="badge bg-primary rounded-pill">{{ $product->sale_details_count }} terjual</span>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted">Belum ada data.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection