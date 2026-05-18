# Fitur Potong Data (Data Cutting)

## Deskripsi
Fitur "Potong Data" memungkinkan super admin untuk mengelola ukuran database dengan menghapus data yang lebih lama dari tanggal tertentu. Fitur ini dirancang untuk menjaga performa aplikasi dengan menyimpan hanya data terbaru (1-2 bulan terakhir).

## Fitur Utama

### 1. **Backup Database Otomatis**
- Sebelum menghapus data, sistem dapat membuat backup database lengkap
- File backup disimpan di `storage/app/backups/`
- Dapat diunduh kapan saja untuk keperluan restore
- Format: `backup_YYYY-MM-DD_HHMMSS.sql`

### 2. **Preview Data**
- Lihat preview jumlah data yang akan dihapus per tabel
- Data dipisahkan per jenis (Transaksi, Deposit, Reimburse, dll)
- Bisa menyesuaikan tanggal potong sebelum proses dimulai

### 3. **Penghapusan Data Permanen**
- Menghapus data yang lebih lama dari tanggal yang ditentukan
- Tabel yang terpengaruh:
  - Transaksi
  - Deposit
  - Reimburse
  - Minusan
  - Import
  - Tag Nomor Pasca Bayar
  - Tag PLN Internet
  - Tag Lainnya

### 4. **Riwayat Lengkap**
- Mencatat setiap proses potong data
- Menyimpan detail backup, jumlah record dihapus, status
- Dapat melihat log error jika ada kegagalan

## Cara Menggunakan

### Step 1: Akses Menu
1. Login sebagai **Superadmin**
2. Buka sidebar menu
3. Klik **"Potong Data"** di bagian admin management

### Step 2: Buat Potong Data Baru
1. Klik tombol **"Potong Data Baru"** (warna hijau)
2. Halaman form akan terbuka

### Step 3: Isi Form
1. **Tanggal Potong Data**: Pilih tanggal cutoff
   - Data SEBELUM tanggal ini akan dihapus
   - Default: 2 bulan yang lalu

2. **Preview Data**: 
   - Sistem otomatis menampilkan preview
   - Lihat berapa record yang akan dihapus per tabel
   - Ubah tanggal jika perlu

3. **Backup Database**:
   - ✅ Centang untuk membuat backup
   - ⚠️ Sangat disarankan untuk selalu membuat backup
   - File akan disimpan dan bisa diunduh

4. **Catatan** (Opsional):
   - Tuliskan alasan atau catatan penting
   - Berguna untuk dokumentasi

5. **Konfirmasi**:
   - Centang checkbox "Saya memahami bahwa data akan DIHAPUS PERMANEN..."
   - Tombol submit akan aktif setelah konfirmasi

### Step 4: Proses Eksekusi
1. Klik **"Proses Potong Data"**
2. Sistem akan:
   - Membuat backup (jika diaktifkan) - Proses: backing_up
   - Menghapus data lama - Proses: deleting
   - Menandai sebagai selesai - Status: completed

3. Tunggu hingga selesai (bisa beberapa menit tergantung jumlah data)

### Step 5: Verifikasi
1. Kembali ke halaman riwayat "Potong Data"
2. Lihat riwayat dengan status "Selesai"
3. Klik ikon mata untuk melihat detail
4. Download backup jika diperlukan

## Riwayat & Monitoring

### Tab Riwayat
- Lihat semua proses potong data yang pernah dilakukan
- Filter berdasarkan status, tanggal, atau user
- Paginated untuk kemudahan navigasi

### Informasi yang Ditampilkan
- **Tanggal Potong**: Tanggal cutoff data
- **Dibuat Oleh**: User (superadmin) yang menjalankan proses
- **Total Record Dihapus**: Jumlah total record dari semua tabel
- **Backup**: Ukuran file backup (jika ada)
- **Status**: Pending, Backing up, Deleting, Completed, Failed
- **Waktu**: Kapan proses dilakukan

## Troubleshooting

### ❌ Error: mysqldump tidak ditemukan
**Solusi:**
1. Pastikan MySQL sudah terinstall
2. Tambahkan path MySQL ke system PATH
   - Untuk XAMPP: `C:\xampp\mysql\bin`
   - Untuk Laragon: `C:\laragon\bin\mysql\mysql-X.X.XX-winxxx\bin`
3. Restart aplikasi

### ❌ Backup gagal dibuat
**Kemungkinan penyebab:**
- Disk space tidak cukup
- Permission folder `storage/app/backups` tidak valid
- Koneksi database bermasalah

**Solusi:**
1. Cek disk space
2. Cek folder permissions: `chmod 755 storage/app/backups`
3. Test koneksi database

### ❌ Proses penghapusan lambat
**Ini NORMAL jika:**
- Data sangat banyak (jutaan records)
- Database size besar (GB)

**Tips mengoptimalkan:**
- Jalankan pada jam sepi (malam/weekend)
- Gunakan BACKUP terlebih dahulu
- Pisahkan proses: backup hari pertama, delete hari kedua

### ⚠️ Akses Ditolak (Forbidden)
**Solusi:**
- Hanya Superadmin yang bisa akses fitur ini
- Login ulang dengan akun Superadmin

## Best Practices

1. **Selalu Backup Dulu**
   - ✅ Aktifkan checkbox backup sebelum delete
   - Simpan backup di lokasi aman (external drive/cloud)

2. **Test Preview Dulu**
   - Lihat preview data yang akan dihapus
   - Pastikan tanggal cutoff sudah benar

3. **Dokumentasi**
   - Isi catatan/notes untuk setiap proses
   - Berguna untuk audit trail

4. **Jadwalkan Rutin**
   - Jalankan setiap bulan (misalnya: hari 1 setiap bulan)
   - Konsisten dengan kebijakan retensi data

5. **Monitor Status**
   - Cek status di tab riwayat
   - Pastikan tidak ada error

## Recovery (Jika Diperlukan)

### Restore dari Backup
Jika perlu mengembalikan data yang sudah dihapus:

1. Download file backup dari riwayat "Potong Data"
2. Jalankan query di MySQL:
   ```sql
   mysql -u root -p database_name < backup_file.sql
   ```
3. Atau gunakan MySQL GUI tool (phpMyAdmin, Workbench)

## FAQ

**Q: Berapa sering harus potong data?**
A: Tergantung jumlah data yang masuk. Rekomendasi: setiap 1-2 bulan.

**Q: Apakah bisa batal setelah klik "Proses Potong Data"?**
A: Tidak. Pastikan semua data sudah benar sebelum submit.

**Q: Berapa lama proses backup+delete?**
A: Bisa 5 menit hingga 1 jam tergantung ukuran data.

**Q: Apakah backup file otomatis dihapus?**
A: Tidak. File backup tetap tersimpan. Anda perlu manually menghapus jika ingin.

**Q: Bagaimana jika gagal ditengah proses?**
A: Status akan berubah menjadi "Failed" dan error log akan disimpan. Cek error log untuk debugging.

## Support

Jika ada masalah atau pertanyaan, silakan hubungi IT Support dengan menyertakan:
- Riwayat potong data (tangkapan layar)
- Error log (jika ada)
- Waktu kejadian
- Action yang sedang dilakukan
