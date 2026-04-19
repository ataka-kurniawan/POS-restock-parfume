@extends('layouts.app')

@section('content')
<div class="mb-4 d-flex justify-content-between">
    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Kembali ke Riwayat
    </a>
    <button onclick="window.print()" class="btn btn-primary d-print-none">
        <i class="fas fa-print"></i> Cetak Nota
    </a>
</div>

<div class="card shadow border-0" id="receipt">
    <div class="card-body">
        <div class="text-center mb-4">
            <h4 class="mb-0">Scentart Atelier</h4>
            <small>Jl. Aroma Harum No. 12, Kota Parfum</small><br>
            <small>Telp: 08123456789</small>
        </div>
        <hr>
        <div class="row mb-4">
            <div class="col-6">
                <table class="table table-borderless table-sm">
                    <tr><td>No. Invoice</td><td>: <strong>{{ $sale->invoice_number }}</strong></td></tr>
                    <tr><td>Tanggal</td><td>: {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/Y H:i') }}</td></tr>
                </table>
            </div>
            <div class="col-6">
                <table class="table table-borderless table-sm text-end">
                    <tr><td>Kasir</td><td>: {{ $sale->user->name }}</td></tr>
                </table>
            </div>
        </div>

        <table class="table">
            <thead class="table-light">
                <tr>
                    <th>Produk</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Harga</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->details as $detail)
                <tr>
                    <td>{{ $detail->product->name }}</td>
                    <td class="text-center">{{ $detail->qty }}</td>
                    <td class="text-end">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">TOTAL</th>
                    <th class="text-end">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</th>
                </tr>
                <tr>
                    <td colspan="3" class="text-end">BAYAR</td>
                    <td class="text-end">Rp {{ number_format($sale->paid_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-end">KEMBALIAN</td>
                    <td class="text-end">Rp {{ number_format($sale->change_amount, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
        
        <div class="text-center mt-5">
            <p>Terima kasih atas kunjungan Anda!</p>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar, .navbar, .d-print-none, .btn { display: none !important; }
    .col-md-10 { width: 100% !important; margin: 0 !important; }
    .card { border: none !important; box-shadow: none !important; }
}
</style>
@endsection