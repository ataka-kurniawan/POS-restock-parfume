@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-7">
        <div class="card shadow">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Pilih Produk</h5>
                <input type="text" id="productSearch" class="form-control form-control-sm w-50" placeholder="Cari produk...">
            </div>
            <div class="card-body" style="height: 500px; overflow-y: auto;">
                <div class="row" id="productList">
                    @foreach($products as $product)
                    <div class="col-md-4 mb-3 product-item" data-name="{{ strtolower($product->name) }}" data-code="{{ strtolower($product->product_code) }}">
                        <div class="card h-100 btn-outline-primary cursor-pointer border shadow-sm" onclick="addToCart({{ $product->id }}, '{{ $product->name }}', {{ $product->price }})">
                            <div class="card-body text-center p-2">
                                <h6 class="mb-1">{{ $product->name }}</h6>
                                <small class="text-muted">{{ $product->product_code }}</small>
                                <div class="text-primary fw-bold mt-2">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <form action="{{ route('pos.store') }}" method="POST">
            @csrf
            <div class="card shadow">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Keranjang Belanja</h5>
                </div>
                <div class="card-body p-0" style="height: 350px; overflow-y: auto;">
                    <table class="table mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Produk</th>
                                <th width="100">Qty</th>
                                <th>Subtotal</th>
                                <th width="40"></th>
                            </tr>
                        </thead>
                        <tbody id="cartTable">
                            <!-- Items added via JS -->
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between mb-2">
                        <h5>Total:</h5>
                        <h5 id="totalDisplay">Rp 0</h5>
                        <input type="hidden" name="total_amount" id="totalInput" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bayar (Rp)</label>
                        <input type="number" class="form-control form-control-lg" name="paid_amount" id="paidInput" required oninput="calculateChange()">
                    </div>
                    <div class="d-flex justify-content-between mb-3 text-secondary">
                        <span>Kembalian:</span>
                        <span id="changeDisplay">Rp 0</span>
                    </div>
                    <button type="submit" class="btn btn-success w-100 btn-lg">
                        <i class="fas fa-check-circle"></i> Selesaikan Transaksi
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    let cart = [];

    function addToCart(id, name, price) {
        let existing = cart.find(item => item.product_id === id);
        if (existing) {
            existing.qty++;
        } else {
            cart.push({ product_id: id, name: name, price: price, qty: 1 });
        }
        renderCart();
    }

    function updateQty(id, qty) {
        let item = cart.find(item => item.product_id === id);
        if (item) {
            item.qty = parseInt(qty);
            if (item.qty <= 0) {
                removeFromCart(id);
            }
        }
        renderCart();
    }

    function removeFromCart(id) {
        cart = cart.filter(item => item.product_id !== id);
        renderCart();
    }

    function renderCart() {
        let html = '';
        let total = 0;
        cart.forEach(item => {
            let subtotal = item.price * item.qty;
            total += subtotal;
            html += `
                <tr>
                    <td><small>${item.name}</small></td>
                    <td>
                        <input type="number" class="form-control form-control-sm" value="${item.qty}" onchange="updateQty(${item.product_id}, this.value)">
                        <input type="hidden" name="items[${item.product_id}][product_id]" value="${item.product_id}">
                        <input type="hidden" name="items[${item.product_id}][qty]" value="${item.qty}">
                    </td>
                    <td>Rp ${subtotal.toLocaleString('id-ID')}</td>
                    <td>
                        <button type="button" class="btn btn-sm text-danger" onclick="removeFromCart(${item.product_id})">
                            <i class="fas fa-times"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
        document.getElementById('cartTable').innerHTML = html;
        document.getElementById('totalDisplay').innerText = 'Rp ' + total.toLocaleString('id-ID');
        document.getElementById('totalInput').value = total;
        calculateChange();
    }

    function calculateChange() {
        let total = parseInt(document.getElementById('totalInput').value);
        let paid = parseInt(document.getElementById('paidInput').value) || 0;
        let change = paid - total;
        document.getElementById('changeDisplay').innerText = 'Rp ' + (change > 0 ? change.toLocaleString('id-ID') : 0);
    }

    // Search functionality
    document.getElementById('productSearch').addEventListener('input', function(e) {
        let search = e.target.value.toLowerCase();
        document.querySelectorAll('.product-item').forEach(item => {
            if (item.dataset.name.includes(search) || item.dataset.code.includes(search)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
</script>
<style>
    .cursor-pointer { cursor: pointer; }
    .cursor-pointer:hover { background-color: #e9ecef !important; }
</style>
@endpush
@endsection