<?php

namespace App\Http\Controllers;

use App\Models\SosmedSetting;
use App\Models\SosmedSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SosmedPublicFormController extends Controller
{
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

        $formattedTasks = [];
        foreach ($taskLinksRaw as $index => $link) {
            $num = $index + 1;
            $formattedTasks[] = [
                'id' => 'tugas_' . $num,
                'title' => 'Tugas ' . $num,
                'link' => trim($link),
            ];
        }

        // Ambil data task yang SUDAH dikerjakan HARI INI per user untuk mengunci dropdown
        $todaySubmissions = SosmedSubmission::whereDate('created_at', today())->get(['nama', 'nama_first_word', 'pilihan_tugas']);
        
        $todayTaskMap = [];
        foreach ($todaySubmissions as $sub) {
            $namaKey = strtolower(trim($sub->nama));
            $firstWordKey = strtolower(trim($sub->nama_first_word));
            $tugas = trim($sub->pilihan_tugas);

            if ($tugas !== '') {
                if (!isset($todayTaskMap[$namaKey])) {
                    $todayTaskMap[$namaKey] = [];
                }
                if (!in_array($tugas, $todayTaskMap[$namaKey])) {
                    $todayTaskMap[$namaKey][] = $tugas;
                }

                if ($firstWordKey !== '') {
                    if (!isset($todayTaskMap[$firstWordKey])) {
                        $todayTaskMap[$firstWordKey] = [];
                    }
                    if (!in_array($tugas, $todayTaskMap[$firstWordKey])) {
                        $todayTaskMap[$firstWordKey][] = $tugas;
                    }
                }
            }
        }

        return view('sosmed.public_form', compact(
            'isFormActive',
            'karyawanList',
            'divisiList',
            'sosmedPlatforms',
            'formattedTasks',
            'todayTaskMap'
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
        $pilihanTugas = trim($request->pilihan_tugas);

        // Kunci penugasan: Pengguna tidak boleh menyelesaikan task yang sama 2x dalam 1 hari
        $alreadyCompletedToday = SosmedSubmission::whereDate('created_at', today())
            ->where(function ($q) use ($namaTrimmed, $namaFirstWord) {
                $q->where(function ($subQ) use ($namaTrimmed) {
                    $subQ->whereRaw('LOWER(nama) = ?', [strtolower($namaTrimmed)]);
                })->orWhere(function ($subQ) use ($namaFirstWord) {
                    if ($namaFirstWord !== '') {
                        $subQ->whereRaw('LOWER(nama_first_word) = ?', [strtolower($namaFirstWord)]);
                    }
                });
            })
            ->whereRaw('LOWER(pilihan_tugas) = ?', [strtolower($pilihanTugas)])
            ->exists();

        if ($alreadyCompletedToday) {
            return back()->with('error', "Mohon maaf, Anda sudah menyelesaikan '" . $pilihanTugas . "' untuk hari ini! Silakan pilih tugas lain yang belum diselesaikan.")->withInput();
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
