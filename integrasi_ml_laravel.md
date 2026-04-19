# Integrasi Machine Learning Python ke Laravel untuk Prediksi Restock

Kamu adalah software engineer senior dan data scientist yang membantu saya mengintegrasikan modul machine learning Python ke aplikasi Laravel yang sudah selesai dibuat.

## Kondisi Saat Ini
Saya sudah memiliki aplikasi POS parfum berbasis Laravel yang sudah berjalan. Fitur yang sudah ada:
- login multi role
- master produk
- master komposisi / bahan racikan
- resep produk
- stok masuk
- transaksi penjualan
- pengurangan stok otomatis berdasarkan resep
- mutasi stok
- warning stok minimum
- dashboard

Jangan membangun ulang modul POS dari awal. Fokus hanya pada integrasi machine learning ke project yang sudah ada.

## Tujuan Integrasi
Saya ingin menambahkan modul machine learning berbasis Python menggunakan Random Forest untuk menghasilkan rekomendasi prioritas restock bahan komposisi.

Integrasi yang diinginkan:
1. Python membaca data dari database Laravel
2. Python membangun dataset dari data transaksi dan stok
3. Python melatih model Random Forest
4. Python melakukan prediksi prioritas restock
5. Python menyimpan hasil prediksi ke database Laravel
6. Laravel membaca hasil prediksi dan menampilkannya
7. Laravel dapat menjalankan script Python lewat tombol "Generate Prediksi"

## Lokasi Pengerjaan
Saya menjalankan kamu dari root project Laravel, bukan dari folder `ml/`.

Artinya:
- kamu boleh membaca struktur Laravel yang sudah ada
- kamu harus menyesuaikan implementasi dengan project Laravel yang sudah ada
- semua file Python harus diletakkan di folder `ml/`
- jangan merusak modul POS yang sudah berjalan

## Struktur Folder yang Diinginkan
Semua file machine learning diletakkan di folder `ml/` di dalam project Laravel.

Contoh struktur:

project_laravel/
├── app/
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
├── routes/
├── storage/
├── ml/
│   ├── config.py
│   ├── database.py
│   ├── build_dataset.py
│   ├── train_model.py
│   ├── predict_restock.py
│   ├── requirements.txt
│   ├── models/
│   │   └── random_forest_restock.pkl
│   └── outputs/
│       ├── dataset.csv
│       ├── predictions.csv
│       └── evaluation.json
├── .env
└── composer.json

## Arsitektur Integrasi
Gunakan pola berikut:
- Laravel tetap menjadi aplikasi utama
- Python hanya menangani machine learning
- database MySQL Laravel menjadi penghubung antara Laravel dan Python
- Laravel tidak menjalankan logika machine learning secara langsung
- Laravel hanya:
  - memanggil script Python
  - membaca tabel hasil prediksi
  - menampilkan hasil prediksi

## Tabel Laravel yang Digunakan
Gunakan tabel yang sudah ada di project Laravel jika nama dan strukturnya sesuai. Jika berbeda, sesuaikan dengan struktur project yang ada.

Tabel utama yang kemungkinan dipakai:
- sales
- sale_details
- products
- product_recipes
- compositions
- stock_movements

Gunakan tabel hasil prediksi:
- restock_predictions

## Struktur Tabel Hasil Prediksi
Jika tabel `restock_predictions` belum ada, buat migration Laravel dengan kolom berikut:
- id
- composition_id
- period
- predicted_label
- probability
- recommendation_score
- notes
- created_at
- updated_at

## Tujuan Model
Gunakan klasifikasi biner:
- 1 = prioritas restock
- 0 = tidak prioritas restock

## Unit Data
Satu baris data mewakili:
- satu bahan komposisi
- dalam satu periode bulanan

Contoh:
- Alkohol pada Januari 2026
- Alkohol pada Februari 2026
- Vanilla Essence pada Januari 2026

## Sumber Data Machine Learning
Bangun dataset dari data Laravel berikut:
1. data penjualan
2. detail produk yang terjual
3. resep produk
4. data stok bahan
5. mutasi stok bahan

## Logika Pembentukan Dataset
Gunakan logika berikut:
1. ambil data penjualan dari sales dan sale_details
2. join dengan product_recipes untuk mengetahui bahan yang dipakai oleh setiap produk
3. hitung total pemakaian bahan:
   total_usage = qty_produk_terjual x quantity_used
4. agregasikan total pemakaian per bahan per bulan
5. gabungkan dengan data stok saat ini, stok minimum, stok masuk, stok keluar, dan histori pemakaian

## Fitur untuk Model
Gunakan fitur numerik seperti:
- current_stock
- minimum_stock
- stock_gap
- total_usage_month
- incoming_stock_month
- outgoing_stock_month
- usage_last_month
- avg_usage_3_month
- related_best_seller_sales

Jika perlu, tambahkan fitur turunan lain yang masuk akal dan jelaskan alasannya.

## Label Target
Gunakan label klasifikasi biner.

Contoh logika label:
- label = 1 jika stok mendekati atau di bawah minimum dan pemakaian tinggi
- label = 0 jika stok masih aman

Jelaskan secara eksplisit aturan label yang dipakai.

## Model Machine Learning
Gunakan:
- Python 3
- pandas
- numpy
- scikit-learn
- sqlalchemy
- pymysql
- joblib

Model yang digunakan:
- RandomForestClassifier

Evaluasi model dengan:
- accuracy
- precision
- recall
- F1-score
- confusion matrix

Jika data tidak seimbang, pertimbangkan `class_weight="balanced"` dan jelaskan alasannya.

## Integrasi Laravel
Laravel harus memiliki:
1. migration tabel `restock_predictions` jika belum ada
2. model `RestockPrediction`
3. relasi `RestockPrediction` ke `Composition`
4. halaman untuk menampilkan hasil prediksi
5. controller untuk menjalankan script Python
6. route untuk:
   - halaman hasil prediksi
   - tombol generate prediksi

## Cara Kerja yang Diinginkan
Alur integrasi harus seperti ini:

1. User klik tombol "Generate Prediksi" di Laravel
2. Laravel menjalankan script Python
3. Python membaca database Laravel
4. Python membangun dataset atau mengambil data terbaru
5. Python load model atau training model jika diperlukan
6. Python melakukan prediksi prioritas restock
7. Python menyimpan hasil ke tabel `restock_predictions`
8. Laravel membaca hasil dari tabel tersebut
9. Laravel menampilkan hasil di halaman prediksi atau dashboard

## Tahapan Pengerjaan
Kerjakan bertahap. Jangan langsung membuat semuanya sekaligus.

### Tahap 1
Analisis project Laravel yang sudah ada lalu buat:
- cek apakah tabel `restock_predictions` sudah ada
- jika belum, buat migration
- buat model Laravel `RestockPrediction`
- buat relasi ke model `Composition`
- buat controller untuk menampilkan hasil prediksi
- buat route web untuk halaman prediksi

### Tahap 2
Buat:
- controller Laravel untuk tombol "Generate Prediksi"
- method yang menjalankan script Python
- penjelasan command Python yang dipakai
- penanganan output/error sederhana

### Tahap 3
Buat folder `ml/` dan file:
- requirements.txt
- config.py
- database.py

Pastikan Python bisa membaca koneksi database Laravel secara aman dan realistis.

### Tahap 4
Buat:
- build_dataset.py
- query dan logika pembentukan dataset per bahan per bulan
- export dataset ke `ml/outputs/dataset.csv`

### Tahap 5
Buat:
- train_model.py
- preprocessing
- feature engineering
- training RandomForestClassifier
- evaluasi model
- simpan model ke `ml/models/random_forest_restock.pkl`
- simpan hasil evaluasi ke file JSON

### Tahap 6
Buat:
- predict_restock.py
- load model
- ambil data terbaru
- prediksi prioritas restock
- hitung probability dan recommendation_score
- simpan hasil ke tabel `restock_predictions`

## Aturan Penting
- jangan membangun ulang project Laravel dari nol
- jangan mengubah logic POS yang sudah berjalan kecuali memang perlu untuk integrasi
- semua file Python harus diletakkan di folder `ml/`
- gunakan kode yang realistis dan bisa dipakai untuk skripsi
- hindari pseudo-code
- tampilkan nama file di atas setiap kode
- berikan kode lengkap
- jika ada asumsi terhadap nama tabel, kolom, atau model Laravel, jelaskan asumsi tersebut

## Format Jawaban
Saat menjawab:
1. fokus hanya pada tahap yang saya minta
2. tampilkan nama file
3. berikan kode lengkap
4. jelaskan singkat fungsi tiap file
5. jangan lompat ke tahap berikutnya jika belum diminta

## Permintaan Awal
Mulai dari Tahap 1.

Tolong:
- analisis struktur project Laravel yang ada
- cek apakah tabel `restock_predictions` sudah ada
- jika belum, buat migration
- buat model `RestockPrediction`
- buat controller untuk menampilkan hasil prediksi
- buat route web untuk halaman prediksi
- sesuaikan implementasi dengan struktur project Laravel yang sudah ada