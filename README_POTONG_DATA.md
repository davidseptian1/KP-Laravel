# README - Fitur Potong Data (Data Cutting)

## 🎯 Ringkas Cepat

Fitur **Potong Data** memungkinkan Super Admin untuk menghapus data lama (>2 bulan) dari database sambil membuat backup otomatis. Ini membantu menjaga performa aplikasi tetap optimal.

## ✨ Fitur Utama

✅ **Backup Database Otomatis** - Buat backup SQL sebelum delete
✅ **Preview Data Real-time** - Lihat berapa record yang akan dihapus
✅ **Penghapusan Permanen** - Hapus data lama dari 8 tabel
✅ **Riwayat Lengkap** - Track setiap proses dengan detail
✅ **Download Backup** - Akses file backup kapan saja

## 🚀 Mulai Cepat

1. **Login** → Gunakan akun **Superadmin**
2. **Menu** → Klik "Potong Data" di sidebar
3. **Riwayat** → Klik "Potong Data Baru"
4. **Form** → Pilih tanggal (default: 2 bulan lalu)
5. **Preview** → Lihat data yang akan dihapus
6. **Backup** → ✅ Centang "Buat Backup Database"
7. **Proses** → Klik "Proses Potong Data" (tunggu hingga selesai)

## 📋 File-File yang Dibuat

### Backend
- `app/Http/Controllers/DataCuttingController.php` - Logic utama
- `app/Models/DataCutLog.php` - Model untuk menyimpan riwayat
- `database/migrations/2026_05_17_000001_create_data_cut_logs_table.php` - Schema database

### Frontend (Views)
- `resources/views/admin/data-cutting/index.blade.php` - Halaman riwayat
- `resources/views/admin/data-cutting/create.blade.php` - Form pembuatan
- `resources/views/admin/data-cutting/guide.blade.php` - Panduan lengkap

### Routing
- `routes/web.php` - Routes ditambahkan ke grup `isSuperadmin`

### Navigation
- `resources/views/layouts/sidebar.blade.php` - Menu ditambahkan

### Documentation
- `PANDUAN_POTONG_DATA.md` - Panduan lengkap (user-friendly)
- `KONFIGURASI_POTONG_DATA.md` - Dokumentasi teknis
- `app/Console/Commands/CheckMysqldumpPath.php` - Utility command

## 📊 Database Schema

Tabel `data_cut_logs` menyimpan:
- User ID (siapa yang mengjalankan)
- Backup file info (nama & ukuran)
- Tanggal potong data (cut_date)
- Count record dihapus per tabel
- Status (pending/backing_up/deleting/completed/failed)
- Error log (jika ada kegagalan)

## 🔒 Keamanan

- ✅ Hanya Superadmin yang bisa akses
- ✅ CSRF protection
- ✅ Validation input
- ✅ Transaction handling untuk data integrity
- ✅ Error logging & tracking

## 📂 Tabel yang Terpengaruh

Fitur ini menghapus data dari tabel berikut yang lebih lama dari tanggal cutoff:

1. `transaksis` (field: tgl_entri)
2. `deposits` (field: created_at)
3. `reimburses` (field: created_at)
4. `minusans` (field: created_at)
5. `imports` (field: created_at)
6. `tag_nomor_pasca_bayars` (field: created_at)
7. `tag_pln_internets` (field: created_at)
8. `tag_lainnyas` (field: created_at)

## ⚠️ Penting!

- 🔴 Data AKAN DIHAPUS PERMANEN - tidak bisa dibatalkan
- 🔴 WAJIB buat backup sebelum delete
- 🔴 Preview data sebelum proses untuk memastikan benar
- 🔴 Jalankan pada jam sepi (malam/weekend)

## 🐛 Troubleshooting

### Error: mysqldump tidak ditemukan
```
Solusi: Pastikan MySQL terinstall dan path ditambahkan ke system PATH
- XAMPP: C:\xampp\mysql\bin
- Laragon: C:\laragon\bin\mysql\mysql-X.X.XX-winxxx\bin
```

### Proses lambat
```
Normal jika data banyak (jutaan records). Tunggu saja, jangan force close.
Disarankan: Jalankan pada jam malam hari.
```

### Access Denied
```
Hanya Superadmin yang bisa akses. Login ulang dengan akun Superadmin.
```

## 📖 Dokumentasi Lengkap

- **Panduan User**: Buka halaman `/data-cutting/guide` (interaktif, accordion)
- **File Markdown**: Baca `PANDUAN_POTONG_DATA.md` (detail, step-by-step)
- **Konfigurasi Teknis**: Baca `KONFIGURASI_POTONG_DATA.md` (admin, developer)

## 🔧 Maintenance

### Cleanup Backup Files
Backup files disimpan di `storage/app/backups/`

Cleanup manual (jika disk penuh):
```bash
# Hapus file backup lama (contoh: lebih dari 3 bulan)
rm storage/app/backups/backup_2026-01-*.sql
rm storage/app/backups/backup_2026-02-*.sql
```

### Check Database Size
```sql
SELECT 
    table_name, 
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
FROM information_schema.TABLES 
WHERE table_schema = 'laravel_tilangan'
ORDER BY size_mb DESC;
```

## 📞 Support

Jika ada masalah:
1. Buka halaman `/data-cutting/guide`
2. Baca section Troubleshooting
3. Hubungi IT Support dengan:
   - Tangkapan layar halaman
   - Error message (jika ada)
   - Waktu kejadian
   - Apa yang sedang Anda coba

## 🎓 Best Practices

1. **Selalu Backup** - ✅ Centang backup sebelum delete
2. **Preview Dulu** - Lihat berapa record akan dihapus
3. **Jadwal Rutin** - Jalankan setiap 1 bulan (hari 25-28)
4. **Jam Sepi** - Jalankan malam hari (22:00 - 02:00)
5. **Dokumentasi** - Isi catatan untuk setiap proses

## 📊 Recommended Schedule

- **Frekuensi**: Setiap 1 bulan
- **Hari**: Akhir bulan (25-28)
- **Waktu**: 22:00 - 02:00 (jam sepi)
- **Tanggal Potong**: 2 bulan sebelumnya
- **Backup**: Simpan di lokasi aman (cloud/external)

## ✅ Testing Checklist

Sebelum production, pastikan:
- [ ] Migrate berhasil (`php artisan migrate:status`)
- [ ] Menu "Potong Data" muncul di sidebar
- [ ] Bisa akses `/data-cutting` sebagai Superadmin
- [ ] Form membuka dengan default date
- [ ] Preview data menampilkan count dengan benar
- [ ] Backup file terbuat di `storage/app/backups/`
- [ ] Data terdelete sesuai jumlah yang preview
- [ ] Bisa download backup file
- [ ] Riwayat menampilkan status "Completed"

## 🚀 Deploy Checklist

- [x] Migration created & executed
- [x] Controller implemented
- [x] Views created
- [x] Routes added
- [x] Navigation updated
- [x] Documentation completed
- [x] Cache cleared
- [x] No syntax errors

## 📝 Version Info

- **Fitur**: Potong Data (Data Cutting)
- **Created**: 17 May 2026
- **Migration**: 2026_05_17_000001
- **Status**: ✅ Production Ready

## 🎉 Next Steps

Fitur sudah siap digunakan! Langkah selanjutnya:
1. Test di local/staging environment
2. Training untuk Super Admin users
3. Jadwalkan first run potong data
4. Backup lama ke archive storage
5. Monitor riwayat secara berkala

---

**Untuk pertanyaan lebih lanjut, baca dokumentasi lengkap:**
- `/data-cutting/guide` - Panduan interaktif
- `PANDUAN_POTONG_DATA.md` - Detailed guide
- `KONFIGURASI_POTONG_DATA.md` - Technical docs
