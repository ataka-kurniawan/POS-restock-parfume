<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'POS Parfum') }}</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background: #343a40; color: white; }
        .sidebar a { color: #c2c7d0; text-decoration: none; padding: 10px 20px; display: block; }
        .sidebar a:hover { background: #495057; color: white; }
        .sidebar a.active { background: #0d6efd; color: white; }
        .content { padding: 20px; }
        .navbar { background: white; border-bottom: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar shadow-sm">
                <div class="py-4 text-center border-bottom border-secondary mb-3">
                    <h4>Scentart POS</h4>
                </div>
                <div class="nav flex-column">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>

                    @if(in_array(auth()->user()->role, ['admin', 'owner']))
                    <div class="mt-3 px-3 small text-uppercase text-secondary">Master Data</div>
                    <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
                        <i class="fas fa-tags me-2"></i> Kategori
                    </a>
                    <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
                        <i class="fas fa-box me-2"></i> Produk
                    </a>
                    <a href="{{ route('compositions.index') }}" class="{{ request()->routeIs('compositions.*') ? 'active' : '' }}">
                        <i class="fas fa-flask me-2"></i> Komposisi
                    </a>
                    <a href="{{ route('suppliers.index') }}" class="{{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                        <i class="fas fa-truck me-2"></i> Supplier
                    </a>
                    @endif

                    @if(in_array(auth()->user()->role, ['admin', 'kasir']))
                    <div class="mt-3 px-3 small text-uppercase text-secondary">Transaksi</div>
                    <a href="{{ route('pos.index') }}" class="{{ request()->routeIs('pos.*') ? 'active' : '' }}">
                        <i class="fas fa-shopping-cart me-2"></i> POS (Kasir)
                    </a>
                    <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.*') ? 'active' : '' }}">
                        <i class="fas fa-history me-2"></i> Riwayat Penjualan
                    </a>
                    @endif

                    @if(in_array(auth()->user()->role, ['admin', 'owner']))
                    <div class="mt-3 px-3 small text-uppercase text-secondary">Stok</div>
                    <a href="{{ route('stock-ins.index') }}" class="{{ request()->routeIs('stock-ins.*') ? 'active' : '' }}">
                        <i class="fas fa-plus-circle me-2"></i> Stok Masuk
                    </a>
                    <a href="{{ route('stock-movements.index') }}" class="{{ request()->routeIs('stock-movements.*') ? 'active' : '' }}">
                        <i class="fas fa-exchange-alt me-2"></i> Mutasi Stok
                    </a>
                    @endif

                    <div class="mt-3 px-3 small text-uppercase text-secondary">Laporan & Info</div>
                    <a href="{{ route('reports.sales') }}" class="{{ request()->routeIs('reports.sales') ? 'active' : '' }}">
                        <i class="fas fa-chart-line me-2"></i> Lap. Penjualan
                    </a>
                    <a href="{{ route('reports.stock') }}" class="{{ request()->routeIs('reports.stock') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar me-2"></i> Lap. Stok
                    </a>
                    @if(auth()->user()->role === 'owner')
                    <a href="{{ route('restock-predictions.index') }}" class="{{ request()->routeIs('restock-predictions.*') ? 'active' : '' }}">
                        <i class="fas fa-magic me-2"></i> Prediksi Restock
                    </a>
                    @endif
                    <a href="{{ route('notifications.index') }}" class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                        <i class="fas fa-bell me-2"></i> Notifikasi
                    </a>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-0">
                <nav class="navbar navbar-expand-lg px-4 py-2">
                    <div class="container-fluid">
                        <span class="navbar-text fw-bold">
                            Halo, {{ auth()->user()->name }} ({{ ucfirst(auth()->user()->role) }})
                        </span>
                        <div class="d-flex">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="btn btn-outline-danger btn-sm" type="submit">
                                    <i class="fas fa-sign-out-alt"></i> Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </nav>

                <div class="content">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>