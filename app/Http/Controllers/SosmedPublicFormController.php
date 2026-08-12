<?php

namespace App\Http\Controllers;

use App\Models\SosmedSetting;
use App\Models\SosmedSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SosmedPublicFormController extends Controller
{
    /**
     * Helper Masking Nama untuk Privasi Publik (contoh: Defit Septian -> De*** Se***)
     */
    public static function maskName(string $name): string
    {
        $words = array_filter(explode(' ', trim($name)));
        $maskedWords = [];

        foreach ($words as $w) {
            $len = mb_strlen($w);
            if ($len <= 2) {
                $maskedWords[] = mb_substr($w, 0, 1) . '*';
            } elseif ($len <= 4) {
                $maskedWords[] = mb_substr($w, 0, 1) . '***';
            } else {
                $maskedWords[] = mb_substr($w, 0, 2) . '***';
            }
        }

        return implode(' ', $maskedWords);
    }

    /**
     * Helper Masking Username Sosmed (Mendukung multiple username dipisah koma, contoh: @defit, @dasda, @asda)
     */
    public static function maskUsername(string $username): string
    {
        $trimmed = trim($username);
        if (empty($trimmed) || $trimmed === '-') return '-';

        // Split jika ada multiple username dipisah koma atau garis miring
        $parts = preg_split('/[,\/]+/', $trimmed);
        $maskedParts = [];

        foreach ($parts as $part) {
            $cleanPart = trim($part);
            if (empty($cleanPart)) continue;

            $hasAt = str_starts_with($cleanPart, '@');
            $raw = $hasAt ? substr($cleanPart, 1) : $cleanPart;
            $len = strlen($raw);

            if ($len <= 2) {
                $masked = substr($raw, 0, 1) . '*';
            } elseif ($len <= 4) {
                $masked = substr($raw, 0, 1) . '***';
            } else {
                $masked = substr($raw, 0, 2) . '***' . substr($raw, -1);
            }

            $maskedParts[] = ($hasAt ? '@' : '') . $masked;
        }

        return !empty($maskedParts) ? implode(', ', $maskedParts) : '-';
    }

    /**
     * Master Data Karyawan & Divisi dari Sistem Monitoring Transaksi
     */
    public static function getKaryawanList(): array
    {
        return [
            ['nama' => 'Muhammad Haikal', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Sulaiman', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Ninda Ruminda', 'divisi' => 'Accounting & Finance'],
            ['nama' => 'Muhammad Bayu Anggoro', 'divisi' => 'Sales & Marketing Toko Cabang'],
            ['nama' => 'Yudin', 'divisi' => 'Gudang'],
            ['nama' => 'Atika Farah Zahidah', 'divisi' => 'Sales & Marketing Toko Cabang'],
            ['nama' => 'Rudy Nur Ramdani', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Rafi Salam Nur Pratama', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Nike Adelia', 'divisi' => 'Operasional ACT'],
            ['nama' => 'Nur Azizah', 'divisi' => 'Admin & Kasir Pusat'],
            ['nama' => 'Agus Sofyan', 'divisi' => 'Sales & Marketing Toko Cabang'],
            ['nama' => 'Bobby Noermansyah', 'divisi' => 'Sales & Marketing'],
            ['nama' => 'Arista Widya', 'divisi' => 'Gudang'],
            ['nama' => 'Maulana Malik', 'divisi' => 'Sales & Marketing'],
            ['nama' => 'Noval Fahrul Akbar', 'divisi' => 'Sales & Marketing Toko Cabang'],
            ['nama' => 'Al Imron', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Depina Nurmala Putri', 'divisi' => 'Operasional ACT'],
            ['nama' => 'Muhammad Filah Pramudia', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Diah Nur Aisyah', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Dinda Bunga Faulina', 'divisi' => 'Gudang'],
            ['nama' => 'Muhammad Febriyansyah', 'divisi' => 'Gudang'],
            ['nama' => 'Ikhsan Ashafi', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Rahmat Nur Prayogo', 'divisi' => 'Sales & Marketing'],
            ['nama' => 'Savitri', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Asmad Sugianto', 'divisi' => 'Sales & Marketing'],
            ['nama' => 'Ikhsan Fadli Lubis', 'divisi' => 'Sales & Marketing'],
            ['nama' => 'Firda Ratna Ningrum', 'divisi' => 'Accounting & Finance'],
            ['nama' => 'Maharani Putri Mulya', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Kevin Dwitama', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Andira Rizky', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Khodam Fauzi', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Jaenal Abidin', 'divisi' => 'Sales & Marketing Toko Cabang'],
            ['nama' => 'Ginta Rahmadia', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Yogi Pratama Surya', 'divisi' => 'Sales & Marketing'],
            ['nama' => 'Sinta Dwi Utami', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Sandra Devi', 'divisi' => 'HRD'],
            ['nama' => 'Salma Saleha', 'divisi' => 'Sales & Marketing Toko Cabang'],
            ['nama' => 'Anwar Sadi', 'divisi' => 'Sales & Marketing Toko Cabang'],
            ['nama' => 'Siswati Dwi Haryanti', 'divisi' => 'Accounting & Finance'],
            ['nama' => 'Dhini Rahmawati', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Rayant Maxnadier', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Siti Halimah Sadiah', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Farsya Salabila', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Usman Pangestu', 'divisi' => 'Sales & Marketing Toko Cabang'],
            ['nama' => 'Defit Septian Firmansyah', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Lia', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Eka Muhammad Guntur', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Annisa Salsabilla', 'divisi' => 'Sales & Marketing Toko Cabang'],
            ['nama' => 'Jenih', 'divisi' => 'Kebersihan dan Lingkungan'],
            ['nama' => 'Dedy Junaedi', 'divisi' => 'Sales & Marketing'],
            ['nama' => 'Mahmur', 'divisi' => 'Kebersihan dan Lingkungan'],
            ['nama' => 'Mutiara Ayu Nuramanah', 'divisi' => 'Gudang'],
            ['nama' => 'Ichsan Cahya Adi', 'divisi' => 'Sales & Marketing'],
            ['nama' => 'Adryansa Fahrezi', 'divisi' => 'Sales & Marketing Toko Cabang'],
            ['nama' => 'Sartika Dewi', 'divisi' => 'Chika Coffee'],
            ['nama' => 'Cahyo adi winoto', 'divisi' => 'Sales & Marketing'],
            ['nama' => 'Azka Aulia', 'divisi' => 'Gudang'],
            ['nama' => 'Davina Zahwa', 'divisi' => 'Gudang'],
            ['nama' => 'Muhammad Nur Jalil', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Rossdatul Maula Wiyyah', 'divisi' => 'Gudang'],
            ['nama' => 'Hasan Kurnia Sandi', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Syifa Ar Rahmah Nurhasanah', 'divisi' => 'Fresh Mart'],
            ['nama' => 'Keisya Ayunda', 'divisi' => 'Gudang'],
            ['nama' => 'Syafira Nur Alifa', 'divisi' => 'Gudang'],
            ['nama' => 'Elok nurviana Tri Rahayu', 'divisi' => 'HRD'],
            ['nama' => 'Rita Larassati', 'divisi' => 'Accounting & Finance'],
            ['nama' => 'Diana Syifa', 'divisi' => 'Gudang'],
            ['nama' => 'Nurul Zakiyah', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Trimujianto', 'divisi' => 'Chika Coffee'],
            ['nama' => 'Shareen Wulandary', 'divisi' => 'Digital Marketing'],
            ['nama' => 'Fajar Prasetiyo', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Lady Fatimah', 'divisi' => 'Digital Marketing'],
            ['nama' => 'Kanti Mutmainah', 'divisi' => 'Digital Marketing'],
            ['nama' => 'Cahaya Apriyanti', 'divisi' => 'Digital Marketing'],
            ['nama' => 'Fatwa Alzidane', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Syifa Nurhabibah', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Mahendra Yudhistira Siswandryo', 'divisi' => 'PKL/MAGANG'],
            ['nama' => 'Almer Aqila Raaph', 'divisi' => 'PKL/MAGANG'],
            ['nama' => 'Muhammad Iqbal Maulana Umar', 'divisi' => 'PKL/MAGANG'],
            ['nama' => 'Fadly Jaya Subarsyah', 'divisi' => 'PKL/MAGANG'],
            ['nama' => 'Satria Jaya Laksana', 'divisi' => 'PKL/MAGANG'],
            ['nama' => 'Zahra Suci Ramadhani', 'divisi' => 'Gudang'],
            ['nama' => 'Muhammad Naufal', 'divisi' => 'Sales & Marketing Toko Cabang'],
            ['nama' => 'Anggi Ramadhan', 'divisi' => 'Sales & Marketing Toko Cabang'],
            ['nama' => 'Reno Offianda', 'divisi' => 'Operasional Server Pusat'],
            ['nama' => 'Shiami Qalbu Silmi', 'divisi' => 'Chika Coffee'],
            ['nama' => 'Erik Erdiansyah', 'divisi' => 'Kebersihan dan Lingkungan'],
            ['nama' => 'Alikka Aureliya Azzahra', 'divisi' => 'Chika Coffee'],
            ['nama' => 'Erriza Nabila Putri', 'divisi' => 'Chika Coffee'],
            ['nama' => 'Michell Nelson Nicky', 'divisi' => 'Accounting & Finance'],
        ];
    }

    public function index()
    {
        $isFormActive = SosmedSetting::getByKey('form_is_active', '1');

        $karyawanList = static::getKaryawanList();

        // Extract daftar divisi unik dari master data karyawan
        $divisiList = array_values(array_unique(array_column($karyawanList, 'divisi')));
        sort($divisiList);

        $sosmedPlatforms = [
            'Instagram',
            'TikTok',
            'Facebook',
            'YouTube',
            'Twitter / X',
            'Threads'
        ];

        $rawTaskLinks = SosmedSetting::getByKey('task_links', '[]');
        $taskLinksRaw = json_decode($rawTaskLinks, true) ?? [];
        if (!is_array($taskLinksRaw)) {
            $taskLinksRaw = [];
        }

        $todayStr = now()->format('Y-m-d');
        $formattedTasks = [];
        $activePlatformCounts = [];

        foreach ($sosmedPlatforms as $p) {
            $activePlatformCounts[$p] = 0;
        }

        foreach ($taskLinksRaw as $index => $item) {
            $num = $index + 1;
            if (is_array($item)) {
                $judul = trim($item['judul'] ?? '');
                $platform = trim($item['platform'] ?? 'Semua Platform');
                $link = trim($item['link'] ?? '');
                $tanggalStart = trim($item['tanggal_start'] ?? $todayStr);

                // Task HANYA TAMPIL di user jika Tanggal Start === HARI INI (otomatis HILANG pada jam 00:00)
                if ($tanggalStart === $todayStr) {
                    $formattedTasks[] = [
                        'id' => 'tugas_' . $num,
                        'title' => $judul !== '' ? $judul : ('Tugas ' . $num),
                        'platform' => $platform,
                        'link' => $link,
                        'tanggal_start' => $tanggalStart,
                    ];

                    if ($platform === 'Semua Platform') {
                        foreach ($sosmedPlatforms as $p) {
                            $activePlatformCounts[$p]++;
                        }
                    } elseif (isset($activePlatformCounts[$platform])) {
                        $activePlatformCounts[$platform]++;
                    }
                }
            } else {
                // Legacy format string URL
                $formattedTasks[] = [
                    'id' => 'tugas_' . $num,
                    'title' => 'Tugas ' . $num,
                    'platform' => 'Semua Platform',
                    'link' => trim((string) $item),
                    'tanggal_start' => $todayStr,
                ];

                foreach ($sosmedPlatforms as $p) {
                    $activePlatformCounts[$p]++;
                }
            }
        }

        // Ambil data task yang SUDAH dikerjakan HARI INI per user & platform untuk mengunci dropdown
        $todaySubmissions = SosmedSubmission::whereDate('created_at', today())
            ->whereNull('deleted_at')
            ->get(['nama', 'nama_first_word', 'sosmed_platform', 'pilihan_tugas']);
        
        $todayTaskMap = [];
        foreach ($todaySubmissions as $sub) {
            $namaKey = strtolower(trim($sub->nama));
            $platformKey = strtolower(trim($sub->sosmed_platform));
            $tugas = trim($sub->pilihan_tugas);

            if ($tugas !== '') {
                $itemKey = $platformKey . '|' . strtolower($tugas);

                if (!isset($todayTaskMap[$namaKey])) {
                    $todayTaskMap[$namaKey] = [];
                }
                if (!in_array($itemKey, $todayTaskMap[$namaKey])) {
                    $todayTaskMap[$namaKey][] = $itemKey;
                }
            }
        }

        // Generator Klasemen Poin Realtime untuk Public Form (Dengan Masking Nama ***)
        $rawLeaderboard = AdminSosmedController::getUserDetailedAnalysis();
        $publicLeaderboard = [];

        foreach ($rawLeaderboard as $user) {
            $publicLeaderboard[] = [
                'rank' => $user['rank'],
                'nama_raw' => $user['nama'],
                'nama_masked' => static::maskName($user['nama']),
                'username_masked' => static::maskUsername($user['username_sosmed']),
                'divisi' => $user['divisi'],
                'total_points' => $user['total_points'],
                'submitted_today' => $user['submitted_today'],
            ];
        }

        return view('sosmed.public_form', compact(
            'isFormActive',
            'karyawanList',
            'divisiList',
            'sosmedPlatforms',
            'formattedTasks',
            'activePlatformCounts',
            'todayTaskMap',
            'publicLeaderboard'
        ));
    }

    public function store(Request $request)
    {
        $isFormActive = SosmedSetting::getByKey('form_is_active', '1');
        if ($isFormActive === '0' || $isFormActive === 0 || $isFormActive === false) {
            return back()->with('error', 'Mohon maaf, form penginputan sosmed saat ini sedang dinonaktifkan oleh Admin.')->withInput();
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'username_sosmed' => 'required|string|max:255',
            'divisi' => 'required|string|max:255',
            'sosmed_platform' => 'required|string|max:255',
            'pilihan_tugas' => 'required|string|max:255',
            'tugas_link' => 'nullable|string|max:1000',
            'photos' => 'required|array|min:1',
            'photos.*' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:10240',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'username_sosmed.required' => 'Username Sosmed wajib diisi.',
            'divisi.required' => 'Divisi wajib diisi / dipilih.',
            'sosmed_platform.required' => 'Platform Sosmed wajib dipilih.',
            'pilihan_tugas.required' => 'Pilihan Tugas Sosmed wajib dipilih.',
            'photos.required' => 'Foto bukti posting minimal 1 file (disarankan 3 foto: Like, Komen, Share).',
            'photos.*.image' => 'File yang diupload harus berupa gambar.',
            'photos.*.max' => 'Ukuran foto maksimal 10MB per file.',
        ]);

        $namaTrimmed = trim($request->nama);
        $namaFirstWord = SosmedSubmission::extractFirstWord($namaTrimmed);
        $platform = trim($request->sosmed_platform);
        $pilihanTugas = trim($request->pilihan_tugas);

        // Kunci penugasan: Pengguna tidak boleh menyelesaikan task yang sama pada platform yang sama 2x dalam 1 hari
        $alreadyCompletedToday = SosmedSubmission::whereDate('created_at', today())
            ->whereNull('deleted_at')
            ->whereRaw('LOWER(nama) = ?', [strtolower($namaTrimmed)])
            ->whereRaw('LOWER(sosmed_platform) = ?', [strtolower($platform)])
            ->whereRaw('LOWER(pilihan_tugas) = ?', [strtolower($pilihanTugas)])
            ->exists();

        if ($alreadyCompletedToday) {
            return back()->with('error', "Mohon maaf, Anda sudah menyelesaikan '" . $pilihanTugas . "' untuk platform " . $platform . " hari ini! Silakan selesaikan tugas platform lainnya.")->withInput();
        }

        $uploadedPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('sosmed_photos', 'public');
                    $uploadedPaths[] = 'storage/' . $path;
                }
            }
        }

        if (empty($uploadedPaths)) {
            return back()->with('error', 'Gagal memproses file foto yang diupload. Silakan coba lagi.')->withInput();
        }

        $currentFeeSetting = (float) SosmedSetting::getByKey('fee_per_submission', 5000);

        SosmedSubmission::create([
            'nama' => $namaTrimmed,
            'username_sosmed' => trim($request->username_sosmed),
            'nama_first_word' => $namaFirstWord,
            'divisi' => $request->divisi,
            'sosmed_platform' => $request->sosmed_platform,
            'pilihan_tugas' => $pilihanTugas,
            'tugas_link' => $request->tugas_link,
            'photos' => $uploadedPaths,
            'status' => 'pending',
            'fee_amount' => $currentFeeSetting,
        ]);

        return back()->with('success', 'Form bukti posting ' . $pilihanTugas . ' berhasil dikirim! Data Anda akan ditinjau oleh Admin Sosmed.');
    }
}
