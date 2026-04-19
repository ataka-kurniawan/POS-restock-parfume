# Project Brief: POS Parfum + Manajemen Stok + Rekomendasi Restock

Kamu adalah software engineer senior yang bertugas membantu membangun aplikasi web berbasis Laravel untuk skripsi saya.

## Konteks Proyek
Saya sedang membuat aplikasi Point of Sale (POS) berbasis web untuk Scentart Atelier, sebuah usaha parfum racik. Sistem ini harus mendukung:
- pencatatan transaksi penjualan parfum
- pengelolaan stok komposisi/bahan racikan
- pengurangan stok bahan otomatis berdasarkan resep produk
- warning stok minimum
- dashboard penjualan dan stok
- rekomendasi prioritas restock berbasis data historis

## Tujuan Sistem
Sistem dibangun untuk mengatasi masalah berikut:
- stok bahan sering baru diketahui habis saat proses peracikan
- pencatatan penjualan belum terhubung dengan pemakaian bahan
- restock masih reaktif
- belum ada early warning stok minimum
- owner sulit menentukan bahan mana yang harus diprioritaskan untuk restock

## Stack yang Harus Digunakan
- Laravel 12 atau versi stabil terbaru yang kompatibel
- MySQL
- Blade Template
- Bootstrap atau AdminLTE
- Eloquent ORM
- Laravel Migration, Seeder, Form Request, Middleware
- Python hanya untuk modul machine learning Random Forest, bukan untuk core aplikasi Laravel

## Aktor Sistem
1. Admin
2. Kasir
3. Owner

## Hak Akses
### Admin
- mengelola kategori
- mengelola produk
- mengelola komposisi
- mengelola supplier
- mengelola resep produk
- mencatat stok masuk
- melihat mutasi stok
- melihat dashboard
- melihat laporan

### Kasir
- melakukan transaksi penjualan
- melihat daftar produk
- mencetak nota

### Owner
- melihat dashboard
- melihat laporan
- melihat stok menipis/habis
- melihat rekomendasi prioritas restock

## Fitur Utama yang Wajib Ada
### 1. Autentikasi dan Role
- login
- logout
- middleware role admin, kasir, owner

### 2. Master Data
- kategori produk
- produk parfum
- komposisi atau bahan racikan
- supplier
- resep produk

### 3. Manajemen Stok
- stok masuk bahan
- stok terkini
- stok minimum
- mutasi stok
- adjustment stok manual
- warning stok menipis dan habis

### 4. POS
- transaksi penjualan
- keranjang belanja
- total, bayar, kembalian
- nomor invoice otomatis
- simpan transaksi ke sales dan sale_details
- cetak nota

### 5. Integrasi Resep dengan Penjualan
Saat produk terjual:
- ambil resep produk
- hitung total penggunaan setiap bahan berdasarkan qty terjual
- kurangi stok komposisi otomatis
- simpan ke tabel mutasi stok sebagai stok keluar
- cek apakah stok <= minimum_stock
- jika ya, buat notifikasi warning

### 6. Dashboard
- total penjualan hari ini
- jumlah transaksi
- produk terlaris
- stok menipis
- stok habis
- daftar bahan stok terendah
- daftar prioritas restock

### 7. Laporan
- laporan penjualan harian
- laporan penjualan bulanan
- laporan produk terlaris
- laporan stok komposisi
- laporan mutasi stok

## Struktur Database yang Diinginkan
Buat migration, model, relasi, controller, validation, dan blade views untuk tabel berikut:

### users
- id
- name
- email
- password
- role
- timestamps

### categories
- id
- name
- timestamps

### products
- id
- category_id
- product_code
- name
- price
- is_best_seller
- timestamps

### compositions
- id
- composition_code
- name
- unit
- current_stock
- minimum_stock
- timestamps

### product_recipes
- id
- product_id
- composition_id
- quantity_used
- timestamps

### suppliers
- id
- name
- phone
- address
- timestamps

### stock_ins
- id
- composition_id
- supplier_id
- qty
- date
- note
- timestamps

### stock_movements
- id
- composition_id
- type
- reference_type
- reference_id
- qty
- stock_before
- stock_after
- movement_date
- note
- timestamps

### sales
- id
- invoice_number
- user_id
- sale_date
- total_amount
- paid_amount
- change_amount
- timestamps

### sale_details
- id
- sale_id
- product_id
- qty
- price
- subtotal
- timestamps

### notifications
- id
- title
- message
- type
- is_read
- timestamps

### restock_predictions
- id
- composition_id
- period
- predicted_label
- probability
- recommendation_score
- notes
- timestamps

## Relasi yang Harus Dibuat
- Category hasMany Product
- Product belongsTo Category
- Product hasMany ProductRecipe
- Composition hasMany ProductRecipe
- Composition hasMany StockIn
- Composition hasMany StockMovement
- Composition hasMany RestockPrediction
- Sale belongsTo User
- Sale hasMany SaleDetail
- Product hasMany SaleDetail

## Aturan Bisnis Penting
1. Transaksi penjualan harus menggunakan database transaction.
2. Jika stok bahan tidak cukup, transaksi harus ditolak dengan pesan validasi.
3. Saat stok masuk, current_stock bertambah dan riwayat mutasi dicatat.
4. Saat penjualan berhasil, current_stock bahan berkurang sesuai resep.
5. Jika current_stock <= minimum_stock, tampilkan status stok menipis.
6. Jika current_stock = 0, tampilkan status stok habis.
7. Semua form harus menggunakan validasi Laravel Form Request.
8. Kode invoice harus otomatis dan unik.

## Standar Kode
- gunakan clean code
- gunakan resource controller bila cocok
- pisahkan business logic ke service class jika prosesnya kompleks
- gunakan eager loading untuk query relasi
- gunakan migration, seeder, dan factory
- tambahkan komentar pada bagian kode yang penting
- gunakan penamaan file dan method yang konsisten

## Output yang Saya Inginkan
Kerjakan bertahap dan terstruktur.

### Tahap 1
Buatkan:
- struktur folder yang disarankan
- daftar route web.php
- migration semua tabel
- model beserta relasinya

### Tahap 2
Buatkan:
- authentication dan role middleware
- CRUD kategori
- CRUD produk
- CRUD komposisi
- CRUD supplier
- CRUD resep produk

### Tahap 3
Buatkan:
- modul stok masuk
- mutasi stok
- warning stok
- transaksi penjualan
- logika pengurangan stok otomatis berdasarkan resep

### Tahap 4
Buatkan:
- dashboard
- laporan
- tampilan notifikasi stok minimum
- halaman prioritas restock

## Cara Menjawab
Saat memberi output:
1. jelaskan dulu apa yang akan dibuat
2. tampilkan struktur file jika perlu
3. berikan kode lengkap per file
4. beri nama file di atas setiap potongan kode
5. jangan melompat terlalu jauh
6. fokus pada kode yang bisa langsung dipakai
7. jika ada asumsi, tuliskan asumsi tersebut secara eksplisit

## Permintaan Awal
Mulailah dari Tahap 1:
- buatkan struktur folder yang disarankan
- buat migration semua tabel
- buat model Eloquent dan relasinya
- buat route awal untuk autentikasi, dashboard, dan master data