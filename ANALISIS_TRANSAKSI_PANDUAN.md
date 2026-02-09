# Panduan Lengkap: Fitur Analisis Transaksi CSV

Sistem analisis transaksi telah berhasil ditambahkan ke aplikasi e-Sistem Monitoring Transaksi. Fitur ini memungkinkan Anda untuk:

1. ✅ **Upload data transaksi** dari file CSV
2. ✅ **Analisis Kecepatan Transaksi** - Distribusi dan rata-rata waktu proses
3. ✅ **Rekomendasi Produk** - Produk mana yang paling menguntungkan untuk dijual
4. ✅ **Analisis Performa Reseller** - Top 10 reseller dengan pendapatan terbaik
5. ✅ **Export Laporan** - Download hasil analisis ke CSV

---

## 🔧 File yang Telah Dibuat

### 1. **Database Migration**
- **File**: `database/migrations/2026_01_29_000001_create_transaksis_table.php`
- **Tabel**: `transaksis`
- **Kolom utama**:
  - `trx_id` - ID transaksi (Unique)
  - `tgl_entri` - Tanggal dan waktu entry
  - `kode_produk` - Kode produk
  - `nomor_tujuan` - Nomor tujuan transaksi
  - `status` - Status (Gagal, Sukses, Proses)
  - `harga_beli`, `harga_jual`, `laba` - Data finansial (DECIMAL(12,2))
  - `durasi_detik` - Kecepatan transaksi dalam detik
  - dan kolom-kolom lainnya untuk reseller info

### 2. **Model**
- **File**: `app/Models/Transaksi.php`
- **Fitur**:
  - Scope untuk filtering (by status, produk, reseller, tanggal)
  - Type casting untuk kolom numerik

### 3. **Import Class**
- **File**: `app/Imports/TransaksiImport.php`
- **Fitur**:
  - Parse CSV dengan format tanggal d/m/Y H:i
  - Validasi data
  - Error handling

### 4. **Controller**
- **File**: `app/Http/Controllers/TransaksiController.php`
- **Methods**:
  - `uploadForm()` - Menampilkan form upload
  - `importCsv()` - Proses import file CSV
  - `analisis()` - Dashboard analisis lengkap dengan filter
  - `analisisKecepatan()` - Analisis kecepatan transaksi
  - `analisisRekomendasiProduk()` - Rekomendasi produk
  - `analisisReseller()` - Performa reseller
  - `exportAnalisis()` - Export hasil analisis
  - `clearData()` - Hapus semua data

### 5. **Views**
- **Upload Form**: `resources/views/admin/transaksi/upload.blade.php`
- **Analisis Dashboard**: `resources/views/admin/transaksi/analisis.blade.php`

### 6. **Routes**
- `GET /transaksi/upload` → Upload form
- `POST /transaksi/import` → Import CSV
- `GET /transaksi/analisis` → Dashboard analisis
- `GET /transaksi/export` → Export laporan
- `DELETE /transaksi/clear` → Hapus data

---

## 📊 Fitur Analisis yang Tersedia

### 1. **Kecepatan Transaksi**
Menampilkan:
- Rata-rata kecepatan transaksi (dalam detik)
- Distribusi kategori: Cepat (< 10s), Normal (10-30s), Lambat (> 30s)
- Kecepatan per produk dengan min-max

### 2. **Rekomendasi Produk**
Menggunakan formula skor:
```
Skor = (Profit Margin × 40%) + (Success Rate × 30%) + (Volume × 30%)
```

Kriteria Status (disarankan untuk implementasi default):
- 🟢 **Highly Recommended**: Skor ≥ 70
- 🔵 **Recommended**: Skor ≥ 50
- ⚠️ **Monitor**: Skor < 50

### 3. **Performa Reseller**
Top 10 reseller terbaik berdasarkan:
- Total penjualan
- Total laba
- Rata-rata laba per transaksi

### 4. **Statistik Umum**
- Total transaksi
- Success rate
- Total pendapatan
- Total laba
- Profit margin

---

## 📁 Format File CSV yang Didukung

File CSV harus memiliki header dengan kolom berikut:

```
trx_id,tgl_entri,kode_produk,nomor_tujuan,status,sn,kode_reseller,nama_reseller,modul,harga_beli,harga_jual,laba,durasi_detik
```

### Contoh Data CSV:

```csv
trx_id,tgl_entri,kode_produk,nomor_tujuan,status,sn,kode_reseller,nama_reseller,modul,harga_beli,harga_jual,laba,durasi_detik
TRX001,28/01/2026 23:42,PROD001,087750646966,Sukses,SN001,RES001,Reseller A,PLUSLINK,6905.00,6985.00,80.00,5
TRX002,28/01/2026 23:40,PROD002,081234567890,Sukses,SN002,RES002,Reseller B,DIGIFLAZZ,1414.00,1415.00,1.00,8
```

### Penjelasan Kolom:

| Kolom | Tipe | Contoh | Keterangan |
|-------|------|--------|-----------|
| trx_id | Text (Unique) | 117625695 | ID transaksi unik |
| tgl_entri | Tanggal (d/m/Y H:i) | 28/01/2026 23:42 | Tanggal dan waktu entry |
| kode_produk | Text | XDP1, MOBA5 | Kode produk |
| nomor_tujuan | Text | 087750646966 | Nomor tujuan (HP/rekening) |
| status | Gagal/Sukses/Proses | Sukses | Status transaksi |
| sn | Text | CMP5091 | Serial number (opsional) |
| kode_reseller | Text | CODE123 | Kode reseller (opsional) |
| nama_reseller | Text | SUMBER REJEKI | Nama reseller (opsional) |
| modul | Text | IP: >*PLUSLINK# | Modul/gateway (opsional) |
| harga_beli | Decimal(12,2) | 6905.00 | Harga beli / cost |
| harga_jual | Decimal(12,2) | 6985.00 | Harga jual / revenue |
| laba | Decimal(12,2) | 80.00 | Profit / margin (auto-hitung jika kosong) |
| durasi_detik | Integer | 5 | Kecepatan transaksi dalam detik |

---

## 🚀 Cara Menggunakan

### Step 1: Akses Halaman Upload
1. Login ke aplikasi
2. Buka menu: **Analisis Transaksi** → **Upload Data**
3. Atau akses langsung (development): `http://127.0.0.1:8000/transaksi/upload`

### Step 2: Upload File CSV
1. Klik tombol "Pilih File CSV"
2. Pilih file CSV Anda
3. Klik "Upload"

### Step 3: Lihat Analisis
1. Setelah upload berhasil, otomatis redirect ke halaman analisis
2. Atau klik tombol "Lihat Analisis"

### Step 4: Filter Data (Opsional)
Anda bisa memfilter data berdasarkan:
- Tanggal mulai & akhir (format tanggal yang sama dengan CSV: `d/m/Y` pada input)
- Kode produk
- Status transaksi

### Step 5: Export Laporan
1. Klik tombol "Export Rekomendasi"
2. File CSV dengan hasil analisis akan terunduh

---

## 🔍 Interpretasi Hasil Analisis

### Kecepatan Transaksi
- **Cepat**: Transaksi diproses dalam < 10 detik (Ideal)
- **Normal**: Transaksi diproses 10-30 detik (Standar)
- **Lambat**: Transaksi diproses > 30 detik (Perlu optimasi)

### Rekomendasi Produk
Gunakan urutan ranking untuk menentukan produk mana yang paling fokus dipromosikan:

**Ranking 1-5** ⭐⭐⭐⭐⭐
- Sangat menguntungkan
- Success rate tinggi
- Volume transaksi stabil
→ **Tingkatkan stok & promosi**

**Ranking 6-10** ⭐⭐⭐⭐
- Cukup menguntungkan
- Perlu monitoring
→ **Pertahankan & monitor**

**Ranking 11+** ⭐⭐⭐
- Profit rendah atau success rate rendah
→ **Kurangi atau evaluasi ulang**

### Performa Reseller
- Lihat reseller top untuk identifikasi key account
- Monitor reseller dengan volume rendah untuk sokongan tambahan
- Gunakan data untuk strategi marketing berbasis performa

---

## 🛠️ Fitur Tambahan

### Hapus Semua Data
Jika ingin memulai analisis baru, gunakan tombol "Hapus Semua Data" di halaman upload.
⚠️ **Peringatan**: Action ini tidak bisa dibatalkan!

### Auto-Calculate Laba
Jika kolom `laba` kosong di CSV, sistem akan otomatis menghitung:
```
laba = harga_jual - harga_beli
```

### Validasi CSV
Sistem akan memvalidasi:
- Kolom required: `trx_id`, `tgl_entri`, `kode_produk`, `status`, `harga_beli`, `harga_jual`
- Format tanggal harus `d/m/Y H:i` (detik opsional)
- Status harus salah satu: `Gagal`, `Sukses`, `Proses`
- Harga dan laba harus numerik (decimal)
- `trx_id` harus unik per record (duplikasi akan diabaikan atau dilaporkan tergantung implementasi importer)

---

## 📝 Catatan Penting

1. **Database**: Tabel `transaksis` sudah dibuat melalui migration
2. **Permission**: Pastikan user login dengan role admin untuk mengakses fitur ini
3. **File Size**: Maksimal upload 10MB per file (sesuaikan `php.ini` jika perlu)
4. **Format**: Pastikan format tanggal dalam CSV adalah `d/m/Y H:i` (contoh: 28/01/2026 23:42)
5. **Duplikasi**: Jika import file yang sama 2x, akan error karena trx_id duplikat
6. **Kolom Opsional**: sn, kode_reseller, nama_reseller, modul bisa kosong/tidak ada

---

## 🔗 Route Reference

```
Upload Form:     GET    /transaksi/upload      → transaksi.upload
Import CSV:      POST   /transaksi/import      → transaksi.import
Analisis:        GET    /transaksi/analisis    → transaksi.analisis
Export:          GET    /transaksi/export      → transaksi.export
Clear Data:      DELETE /transaksi/clear       → transaksi.clear
```

---

## ✅ Status Implementasi

- ✅ Migration tabel `transaksis` dibuat
- ✅ Model `Transaksi` dengan scope
- ✅ Import class untuk parsing CSV
- ✅ TransaksiController dengan 6 methods
- ✅ View upload form
- ✅ View analisis dashboard lengkap
- ✅ Routes terintegrasi
- ✅ Database migration dijalankan

---

**Sistem analisis transaksi siap digunakan! 🎉**
