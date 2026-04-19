@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Prediksi Prioritas Restock</h3>
    <form action="{{ route('restock-predictions.generate') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-cogs"></i> Generate Prediksi
        </button>
    </form>
</div>

<div class="alert alert-light border shadow-sm">
    <i class="fas fa-info-circle text-info"></i> Halaman ini menampilkan rekomendasi bahan yang harus segera di-restock berdasarkan data historis penjualan dan mutasi stok.
</div>

<div class="card shadow">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Bahan</th>
                    <th>Periode</th>
                    <th>Probabilitas</th>
                    <th>Skor Rekomendasi</th>
                    <th>Label Prediksi</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($predictions as $p)
                <tr>
                    <td><strong>{{ $p->composition->name }}</strong></td>
                    <td>{{ $p->period }}</td>
                    <td>{{ number_format($p->probability * 100, 2) }}%</td>
                    <td>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $p->recommendation_score }}%"></div>
                        </div>
                        <small>{{ $p->recommendation_score }}</small>
                    </td>
                    <td>
                        @php
                            $badgeClass = match($p->predicted_label) {
                                'High Priority' => 'bg-danger',
                                'Medium Priority' => 'bg-warning text-dark',
                                'Low Priority' => 'bg-success',
                                default => 'bg-secondary'
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $p->predicted_label }}</span>
                    </td>
                    <td><small>{{ $p->notes ?? '-' }}</small></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">Belum ada data prediksi yang dihasilkan oleh modul ML.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection