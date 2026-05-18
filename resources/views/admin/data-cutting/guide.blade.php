@extends('layouts.app')

@section('content')
<div class="container-xl">
    <div class="page-header d-print-none mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h2 class="page-title">
                    <i class="ti ti-help me-2"></i>Panduan Potong Data
                </h2>
                <div class="text-muted mt-1">Dokumentasi lengkap fitur Potong Data untuk Super Admin</div>
            </div>
        </div>
    </div>
</div>

<div class="page-wrapper">
    <div class="container-xl">
        <div class="row">
            <div class="col-lg-10 offset-lg-1">
                
                <!-- Quick Start -->
                <div class="card mb-3">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0">
                            <i class="ti ti-rocket me-2"></i>Quick Start (Mulai Cepat)
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>📋 5 Langkah Mudah:</h5>
                                <ol>
                                    <li>Klik menu <strong>"Potong Data"</strong></li>
                                    <li>Klik <strong>"Potong Data Baru"</strong></li>
                                    <li>Pilih <strong>tanggal potong</strong> (default: 2 bulan lalu)</li>
                                    <li><strong>✅ Centang backup</strong> (sangat disarankan!)</li>
                                    <li>Klik <strong>"Proses Potong Data"</strong></li>
                                </ol>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-warning">
                                    <strong>⚠️ Penting!</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>Data akan <strong>DIHAPUS PERMANEN</strong></li>
                                        <li>Tidak bisa dibatalkan setelah dikonfirmasi</li>
                                        <li>Selalu buat <strong>backup terlebih dahulu</strong></li>
                                        <li>Preview data sebelum proses</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- What is Data Cutting -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-database me-2"></i>Apa itu Potong Data?
                        </h3>
                    </div>
                    <div class="card-body">
                        <p>
                            <strong>Potong Data</strong> adalah fitur untuk menghapus data lama dari database secara aman. Fitur ini membantu:
                        </p>
                        <ul>
                            <li><strong>Optimasi Performa:</strong> Database lebih ringan = aplikasi lebih cepat</li>
                            <li><strong>Hemat Storage:</strong> Mengurangi ukuran file database</li>
                            <li><strong>Keamanan Data:</strong> Data lama dapat diarsipkan/dihapus sesuai kebijakan</li>
                            <li><strong>Maintenance:</strong> Membersihkan data yang tidak lagi diperlukan</li>
                        </ul>
                        <div class="alert alert-info mt-3">
                            <strong>💡 Strategi yang Disarankan:</strong><br>
                            Simpan data 1-2 bulan terakhir saja di database aktif. Data lama diarsipkan di backup file.
                        </div>
                    </div>
                </div>

                <!-- Backup Feature -->
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title mb-0">
                            <i class="ti ti-backup me-2"></i>Fitur Backup
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Apa itu Backup?</h5>
                                <p>
                                    Backup adalah salinan lengkap database Anda sebelum data dihapus.
                                    File backup bisa digunakan untuk restore jika terjadi masalah.
                                </p>
                                <h5 class="mt-3">Lokasi Penyimpanan</h5>
                                <p><code>storage/app/backups/backup_YYYY-MM-DD_HHMMSS.sql</code></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Keuntungan Backup</h5>
                                <ul>
                                    <li>✅ Aman sebelum penghapusan data</li>
                                    <li>✅ Bisa diunduh kapan saja</li>
                                    <li>✅ Bisa di-restore jika diperlukan</li>
                                    <li>✅ Menyimpan riwayat lengkap</li>
                                    <li>✅ Trace audit trail</li>
                                </ul>
                            </div>
                        </div>

                        <div class="alert alert-danger mt-3">
                            <strong>🔴 Peringatan!</strong> Jangan lupa backup. Tanpa backup, data yang dihapus tidak bisa dikembalikan!
                        </div>
                    </div>
                </div>

                <!-- Tables Affected -->
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="ti ti-table me-2"></i>Tabel yang Terpengaruh
                        </h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Data dari tabel berikut akan dihapus jika lebih lama dari tanggal potong:</p>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th width="30%">Tabel</th>
                                        <th>Deskripsi</th>
                                        <th width="20%">Field Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Transaksi</strong></td>
                                        <td>Riwayat transaksi penjualan</td>
                                        <td><code>tgl_entri</code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Deposit</strong></td>
                                        <td>Data deposit/setoran</td>
                                        <td><code>created_at</code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Reimburse</strong></td>
                                        <td>Pengajuan penggantian biaya</td>
                                        <td><code>created_at</code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Minusan</strong></td>
                                        <td>Data minusan/potongan</td>
                                        <td><code>created_at</code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Import</strong></td>
                                        <td>Log file import</td>
                                        <td><code>created_at</code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tag Nomor Pasca Bayar</strong></td>
                                        <td>Data tagihan nomor</td>
                                        <td><code>created_at</code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tag PLN Internet</strong></td>
                                        <td>Data tagihan PLN/Internet</td>
                                        <td><code>created_at</code></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tag Lainnya</strong></td>
                                        <td>Data tagihan lainnya</td>
                                        <td><code>created_at</code></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- How to Use -->
                <div class="card mb-3">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title mb-0">
                            <i class="ti ti-player-play me-2"></i>Panduan Langkah demi Langkah
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="tutorialAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#step1">
                                        Step 1: Akses Menu Potong Data
                                    </button>
                                </h2>
                                <div id="step1" class="accordion-collapse collapse show" data-bs-parent="#tutorialAccordion">
                                    <div class="accordion-body">
                                        <ol>
                                            <li>Login sebagai <strong>Superadmin</strong></li>
                                            <li>Lihat sidebar menu di sebelah kiri</li>
                                            <li>Cari menu <strong>"Potong Data"</strong> (dengan icon database off)</li>
                                            <li>Klik untuk membuka halaman riwayat</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step2">
                                        Step 2: Buka Form Potong Data Baru
                                    </button>
                                </h2>
                                <div id="step2" class="accordion-collapse collapse" data-bs-parent="#tutorialAccordion">
                                    <div class="accordion-body">
                                        <ol>
                                            <li>Di halaman Potong Data, klik tombol hijau <strong>"Potong Data Baru"</strong></li>
                                            <li>Anda akan dibawa ke form untuk membuat proses potong data baru</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step3">
                                        Step 3: Pilih Tanggal Potong
                                    </button>
                                </h2>
                                <div id="step3" class="accordion-collapse collapse" data-bs-parent="#tutorialAccordion">
                                    <div class="accordion-body">
                                        <ol>
                                            <li>Pada field <strong>"Tanggal Potong Data"</strong>, pilih tanggal</li>
                                            <li>Data SEBELUM tanggal ini akan dihapus</li>
                                            <li>Default: 2 bulan yang lalu (ini adalah default yang bagus)</li>
                                            <li>Ubah jika Anda ingin menghapus data yang lebih baru atau lebih lama</li>
                                        </ol>
                                        <div class="alert alert-warning mt-2">
                                            <strong>💡 Tips:</strong> Jika hari ini 17 Mei 2026, tanggal potong default adalah 17 Maret 2026. 
                                            Data sebelum 17 Maret akan dihapus.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step4">
                                        Step 4: Lihat Preview Data
                                    </button>
                                </h2>
                                <div id="step4" class="accordion-collapse collapse" data-bs-parent="#tutorialAccordion">
                                    <div class="accordion-body">
                                        <ol>
                                            <li>Setelah Anda memilih tanggal, preview akan otomatis dimuat</li>
                                            <li>Preview menampilkan:
                                                <ul>
                                                    <li>Tanggal cutoff yang dipilih</li>
                                                    <li>Total jumlah record yang akan dihapus</li>
                                                    <li>Breakdown per tabel (berapa record dari masing-masing tabel)</li>
                                                </ul>
                                            </li>
                                            <li>Periksa dengan cermat sebelum lanjut</li>
                                            <li>Jika tidak yakin, ubah tanggal dan preview akan update otomatis</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step5">
                                        Step 5: Aktifkan Backup Database
                                    </button>
                                </h2>
                                <div id="step5" class="accordion-collapse collapse" data-bs-parent="#tutorialAccordion">
                                    <div class="accordion-body">
                                        <ol>
                                            <li>Lihat checkbox <strong>"Buat Backup Database Sebelum Menghapus"</strong></li>
                                            <li>✅ <strong>WAJIB CENTANG!</strong> (Disarankan sekali)</li>
                                            <li>Backup akan dibuat otomatis sebelum data dihapus</li>
                                            <li>File backup bisa diunduh nanti jika diperlukan</li>
                                        </ol>
                                        <div class="alert alert-danger mt-2">
                                            <strong>⚠️ JANGAN lupa centang backup!</strong> Ini adalah safety measure yang sangat penting!
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step6">
                                        Step 6: Isi Catatan (Opsional)
                                    </button>
                                </h2>
                                <div id="step6" class="accordion-collapse collapse" data-bs-parent="#tutorialAccordion">
                                    <div class="accordion-body">
                                        <ol>
                                            <li>Di field <strong>"Catatan"</strong>, Anda bisa menulis alasan/keterangan</li>
                                            <li>Contoh:
                                                <ul>
                                                    <li>"Potong data rutin bulanan - maintenance performa"</li>
                                                    <li>"Backup sebelum upgrade sistem"</li>
                                                    <li>"Cleanup data 3 bulan terakhir"</li>
                                                </ul>
                                            </li>
                                            <li>Catatan ini akan disimpan di riwayat untuk audit trail</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step7">
                                        Step 7: Konfirmasi & Proses
                                    </button>
                                </h2>
                                <div id="step7" class="accordion-collapse collapse" data-bs-parent="#tutorialAccordion">
                                    <div class="accordion-body">
                                        <ol>
                                            <li>Baca peringatan dengan seksama</li>
                                            <li>Centang checkbox: <strong>"Saya memahami bahwa data akan DIHAPUS PERMANEN..."</strong></li>
                                            <li>Tombol <strong>"Proses Potong Data"</strong> akan aktif</li>
                                            <li>Klik tombol tersebut untuk mulai proses</li>
                                            <li>Proses akan berjalan (bisa beberapa menit):</li>
                                                <ul>
                                                    <li>Status "backing_up" = sedang membuat backup</li>
                                                    <li>Status "deleting" = sedang menghapus data</li>
                                                    <li>Status "completed" = selesai!</li>
                                                </ul>
                                            </li>
                                        </ol>
                                        <div class="alert alert-danger mt-2">
                                            <strong>🔴 PERHATIAN!</strong> Setelah Anda klik "Proses Potong Data", tidak ada lagi cara untuk membatalkan. 
                                            Pastikan semua sudah benar!
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#step8">
                                        Step 8: Monitor Riwayat
                                    </button>
                                </h2>
                                <div id="step8" class="accordion-collapse collapse" data-bs-parent="#tutorialAccordion">
                                    <div class="accordion-body">
                                        <ol>
                                            <li>Setelah proses selesai, Anda akan kembali ke halaman riwayat</li>
                                            <li>Lihat proses yang baru dibuat dengan status "Selesai" atau "Gagal"</li>
                                            <li>Klik icon mata untuk melihat detail lebih lanjut</li>
                                            <li>Jika ada backup, klik icon download untuk mengunduh file backup</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Troubleshooting -->
                <div class="card mb-3">
                    <div class="card-header bg-danger text-white">
                        <h3 class="card-title mb-0">
                            <i class="ti ti-alert-circle me-2"></i>Troubleshooting
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="accordion" id="troubleAccordion">
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#trouble1">
                                        ❌ Error: mysqldump tidak ditemukan
                                    </button>
                                </h2>
                                <div id="trouble1" class="accordion-collapse collapse show" data-bs-parent="#troubleAccordion">
                                    <div class="accordion-body">
                                        <p><strong>Penyebab:</strong> MySQL tidak terinstall atau path tidak benar</p>
                                        <p><strong>Solusi:</strong></p>
                                        <ol>
                                            <li>Pastikan MySQL sudah terinstall (biasanya di XAMPP atau Laragon)</li>
                                            <li>Tambahkan path ke system PATH:
                                                <ul>
                                                    <li>XAMPP: <code>C:\xampp\mysql\bin</code></li>
                                                    <li>Laragon: <code>C:\laragon\bin\mysql\mysql-X.X.XX-winxxx\bin</code></li>
                                                </ul>
                                            </li>
                                            <li>Restart aplikasi</li>
                                            <li>Coba lagi tanpa backup terlebih dahulu untuk bypass backup</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#trouble2">
                                        ❌ Error: Disk space tidak cukup
                                    </button>
                                </h2>
                                <div id="trouble2" class="accordion-collapse collapse" data-bs-parent="#troubleAccordion">
                                    <div class="accordion-body">
                                        <p><strong>Penyebab:</strong> Backup file terlalu besar, disk penuh</p>
                                        <p><strong>Solusi:</strong></p>
                                        <ol>
                                            <li>Cek space disk Anda (harus minimal 2x ukuran database untuk backup)</li>
                                            <li>Hapus backup file yang lama (di folder storage/app/backups)</li>
                                            <li>Bersihkan file temporary lainnya</li>
                                            <li>Coba lagi</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#trouble3">
                                        ⚠️ Proses sangat lambat
                                    </button>
                                </h2>
                                <div id="trouble3" class="accordion-collapse collapse" data-bs-parent="#troubleAccordion">
                                    <div class="accordion-body">
                                        <p><strong>Penyebab:</strong> Normal jika data sangat banyak (jutaan records, GB size)</p>
                                        <p><strong>Solusi & Tips:</strong></p>
                                        <ol>
                                            <li>Tunggu saja, jangan force close browser</li>
                                            <li>Jalankan pada jam sepi (malam/weekend) agar tidak ganggu pengguna lain</li>
                                            <li>Gunakan dua fase:
                                                <ul>
                                                    <li>Phase 1 (Hari 1): Buat backup saja (centang backup, tapi jangan delete)</li>
                                                    <li>Phase 2 (Hari 2): Jalankan proses delete (tanpa backup)</li>
                                                </ul>
                                            </li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#trouble4">
                                        ❌ Access Denied / Forbidden
                                    </button>
                                </h2>
                                <div id="trouble4" class="accordion-collapse collapse" data-bs-parent="#troubleAccordion">
                                    <div class="accordion-body">
                                        <p><strong>Penyebab:</strong> Hanya Superadmin yang bisa akses fitur ini</p>
                                        <p><strong>Solusi:</strong></p>
                                        <ol>
                                            <li>Login ulang dengan akun Superadmin</li>
                                            <li>Pastikan akun Anda memiliki role "Superadmin"</li>
                                            <li>Hubungi admin jika perlu ditingkatkan ke Superadmin</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Best Practices -->
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h3 class="card-title mb-0">
                            <i class="ti ti-bulb me-2"></i>Best Practices (Praktik Terbaik)
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>✅ Harus Dilakukan</h5>
                                <ul>
                                    <li>Selalu buat backup sebelum delete</li>
                                    <li>Preview data sebelum process</li>
                                    <li>Jalankan pada jam sepi/malam</li>
                                    <li>Catat tanggal & hasil di dokumentasi</li>
                                    <li>Simpan backup di lokasi aman (cloud/external drive)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h5>❌ Jangan Dilakukan</h5>
                                <ul>
                                    <li>Jangan skip backup checkbox</li>
                                    <li>Jangan langsung delete tanpa preview</li>
                                    <li>Jangan jalankan saat jam kerja ramai</li>
                                    <li>Jangan hapus backup file terlalu cepat</li>
                                    <li>Jangan gunakan akun non-superadmin</li>
                                </ul>
                            </div>
                        </div>

                        <hr>

                        <h5>📅 Jadwal yang Disarankan</h5>
                        <div class="alert alert-info">
                            <strong>Jalankan setiap 1 bulan sekali:</strong><br>
                            - Hari: Akhir bulan (tgl 25-28)<br>
                            - Waktu: 22:00 - 02:00 (jam sepi)<br>
                            - Tanggal Potong: 2 bulan sebelumnya<br>
                            <br>
                            <strong>Contoh:</strong> Tgl 27 Mei potong data sampai 27 Maret
                        </div>
                    </div>
                </div>

                <!-- Support -->
                <div class="card">
                    <div class="card-body text-center">
                        <h5>❓ Butuh Bantuan?</h5>
                        <p class="text-muted mb-3">Jika Anda mengalami masalah atau ada pertanyaan, silakan hubungi IT Support dengan informasi:</p>
                        <ul class="text-start" style="max-width: 500px; margin: 0 auto;">
                            <li>Tanggal & waktu kejadian</li>
                            <li>Error message (jika ada)</li>
                            <li>Screenshot halaman yang bermasalah</li>
                            <li>Apa yang sedang Anda coba lakukan</li>
                        </ul>
                        <div class="mt-3">
                            <a href="{{ route('data-cutting.index') }}" class="btn btn-primary">
                                <i class="ti ti-arrow-left me-2"></i>Kembali ke Potong Data
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
