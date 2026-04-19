@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md">
        <div class="card text-white bg-primary mb-3 shadow">
            <div class="card-body">
                <h5 class="card-title text-truncate small"><i class="fas fa-money-bill-wave"></i> Hari Ini</h5>
                <h4 class="mb-0">Rp {{ number_format($totalSalesToday, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md">
        <div class="card text-white bg-info mb-3 shadow">
            <div class="card-body">
                <h5 class="card-title text-truncate small"><i class="fas fa-calendar-alt"></i> Bulan Ini</h5>
                <h4 class="mb-0">Rp {{ number_format($totalSalesMonth, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md text-center">
        <div class="card text-white bg-success mb-3 shadow">
            <div class="card-body">
                <h5 class="card-title text-truncate small"><i class="fas fa-shopping-basket"></i> Trx Hari Ini</h5>
                <h4 class="mb-0">{{ $transactionCountToday }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md text-center">
        <div class="card text-white bg-warning mb-3 shadow">
            <div class="card-body">
                <h5 class="card-title text-truncate small"><i class="fas fa-exclamation-triangle"></i> Stok Menipis</h5>
                <h4 class="mb-0">{{ $lowStockItems->count() }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md text-center">
        <div class="card text-white bg-danger mb-3 shadow">
            <div class="card-body">
                <h5 class="card-title text-truncate small"><i class="fas fa-times-circle"></i> Stok Habis</h5>
                <h4 class="mb-0">{{ $outOfStockItems->count() }}</h4>
            </div>
        </div>
    </div>
</div>

<!-- Monthly Sales Chart Row -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="mb-0">Statistik Penjualan Bulan Ini</h5>
            </div>
            <div class="card-body" style="position: relative; height: 250px; width: 100%;"> <!-- Adjusted height for smaller chart -->
                <canvas id="monthlySalesChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Stock Kritis, Produk Terlaris, and Restock Priority Row -->
<div class="row mt-4">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="mb-0">Daftar Stok Kritis</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Bahan</th>
                            <th>Kode</th>
                            <th>Stok Saat Ini</th>
                            <th>Min. Stok</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($outOfStockItems as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td><code>{{ $item->composition_code }}</code></td>
                            <td class="text-danger fw-bold">{{ $item->current_stock }} {{ $item->unit }}</td>
                            <td>{{ $item->minimum_stock }}</td>
                            <td><span class="badge bg-danger">Habis</span></td>
                        </tr>
                        @empty
                        @endforelse

                        @forelse($lowStockItems as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td><code>{{ $item->composition_code }}</code></td>
                            <td class="text-warning fw-bold">{{ $item->current_stock }} {{ $item->unit }}</td>
                            <td>{{ $item->minimum_stock }}</td>
                            <td><span class="badge bg-warning">Menipis</span></td>
                        </tr>
                        @empty
                        @endforelse

                        @if($outOfStockItems->isEmpty() && $lowStockItems->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">Semua stok dalam kondisi aman.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header bg-white">
                <h5 class="mb-0">Produk Terlaris</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @forelse($bestSellers as $product)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        {{ $product->name }}
                        <span class="badge bg-primary rounded-pill">{{ $product->sale_details_count }} terjual</span>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted">Belum ada data penjualan.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        @if(auth()->user()->role === 'owner')
        <div class="card shadow mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Prioritas Restock</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @forelse($topRestockPriorities as $priority)
                    <li class="list-group-item">
                        <div class="fw-bold">{{ $priority->composition->name }}</div>
                        <small class="text-muted">Skor: {{ $priority->recommendation_score }} | Label: {{ $priority->predicted_label }}</small>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted">Belum ada data prediksi.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Monthly Sales Chart
        const ctxSales = document.getElementById('monthlySalesChart').getContext('2d');
        new Chart(ctxSales, {
            type: 'line', // Tipe chart: bisa 'bar', 'line', dll.
            data: {
                labels: {!! json_encode($chartLabels) !!}, // Label sumbu X (hari)
                datasets: [{
                    label: 'Total Penjualan (Rp)',
                    data: {!! json_encode($chartData) !!}, // Data sumbu Y (jumlah penjualan)
                    borderColor: 'rgb(75, 192, 192)', // Warna garis
                    tension: 0.1,
                    fill: false
                }]
            },
            options: {
                maintainAspectRatio: false, // Allow controlling aspect ratio
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value, index, values) {
                                // Format Rupiah
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });

        // Restock Prediction Chart (Bar Chart)
        const ctxPredictions = document.getElementById('restockPredictionChart');
        if (ctxPredictions) {
            new Chart(ctxPredictions, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($predictionLabels) !!},
                    datasets: [{
                        label: 'Jumlah Bahan',
                        data: {!! json_encode($predictionData) !!},
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.6)', // High Priority (Red)
                            'rgba(255, 206, 86, 0.6)',  // Medium Priority (Yellow)
                            'rgba(75, 192, 192, 0.6)', // Low Priority (Green)
                            'rgba(153, 102, 255, 0.6)'  // Unknown (Purple)
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1 // Ensure whole numbers for counts
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Distribusi Prioritas Restock Bulan Ini'
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
@endsection