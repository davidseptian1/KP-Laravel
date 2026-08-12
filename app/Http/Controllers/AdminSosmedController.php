<?php

namespace App\Http\Controllers;

use App\Models\SosmedSetting;
use App\Models\SosmedSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminSosmedController extends Controller
{
    /**
     * Generator Data Leaderboard & Analisa Detail 90 Karyawan
     * (Platform Gemar, Kelengkapan 3 Foto, Keaktifan Harian, Hari Bolos/Kosong)
     * * Hanya menghitung submission yang TIDAK dihapus (whereNull deleted_at)
     */
    public static function getUserDetailedAnalysis($targetNama = null): array
    {
        $karyawanList = SosmedPublicFormController::getKaryawanList();
        $submissions = SosmedSubmission::whereNull('deleted_at')->get();

        // Hitung frekuensi nama depan untuk mendeteksi nama umum (seperti Muhammad, Nur, Syifa, dll)
        $firstWordCounts = [];
        foreach ($karyawanList as $k) {
            $fw = strtolower(SosmedSubmission::extractFirstWord($k['nama']));
            if ($fw !== '') {
                $firstWordCounts[$fw] = ($firstWordCounts[$fw] ?? 0) + 1;
            }
        }

        $analysisData = [];

        foreach ($karyawanList as $k) {
            $empName = trim($k['nama']);
            $empDiv = trim($k['divisi']);

            if ($targetNama && strtolower(trim($targetNama)) !== strtolower($empName)) {
                // skip if filtering for single target
            }

            $empNameLower = strtolower($empName);
            $firstWordName = strtolower(SosmedSubmission::extractFirstWord($empName));
            $isFirstWordUnique = isset($firstWordCounts[$firstWordName]) && $firstWordCounts[$firstWordName] === 1;

            // Matching submission user persis berdasarkan Nama Lengkap / Nama Depan Unik
            $userSubs = $submissions->filter(function ($sub) use ($empNameLower, $firstWordName, $isFirstWordUnique) {
                $subNama = strtolower(trim($sub->nama));
                
                // 1. Cocokkan Nama Lengkap (Presisi Utama)
                if ($subNama === $empNameLower) {
                    return true;
                }

                // 2. Cocokkan Nama Depan HANYA jika Nama Depan tersebut unik (TIDAK dimiliki karyawan lain)
                if ($isFirstWordUnique && $subNama === $firstWordName) {
                    return true;
                }

                return false;
            });

            $totalSubmissions = $userSubs->count();
            $accCount = $userSubs->where('status', 'acc')->count();
            $pendingCount = $userSubs->where('status', 'pending')->count();
            $rejectedCount = $userSubs->where('status', 'ditolak')->count();

            // Poin hanya bertambah jika task sudah disetujui (ACC) oleh Admin: 1 Task ACC = 1 Poin
            $totalPoints = $accCount;
            $totalFee = $userSubs->where('status', 'acc')->sum('fee_amount');

            // 1. Analisa Platform Gemar Apa (Instagram, TikTok, Facebook, YouTube)
            $igCount = $userSubs->where('sosmed_platform', 'Instagram')->count();
            $tiktokCount = $userSubs->where('sosmed_platform', 'TikTok')->count();
            $fbCount = $userSubs->where('sosmed_platform', 'Facebook')->count();
            $ytCount = $userSubs->where('sosmed_platform', 'YouTube')->count();

            $platformBreakdown = [
                'Instagram' => $igCount,
                'TikTok'    => $tiktokCount,
                'Facebook'  => $fbCount,
                'YouTube'   => $ytCount,
            ];
            arsort($platformBreakdown);
            $favoritePlatform = $totalSubmissions > 0 ? array_key_first($platformBreakdown) : 'Belum Ada';

            // 2. Analisa Kelengkapan 3 Gambar (Apakah lengkap 3 gambar tiap mengirim)
            $threePhotoCount = 0;
            $totalPhotosUploaded = 0;

            foreach ($userSubs as $sub) {
                $photoArray = is_array($sub->photos) ? $sub->photos : [];
                $count = count($photoArray);
                $totalPhotosUploaded += $count;
                if ($count >= 3) {
                    $threePhotoCount++;
                }
            }

            $photoCompletenessRate = $totalSubmissions > 0 
                ? round(($threePhotoCount / $totalSubmissions) * 100) 
                : 0;

            $avgPhotosPerSubmission = $totalSubmissions > 0 
                ? round($totalPhotosUploaded / $totalSubmissions, 1) 
                : 0;

            $isAlwaysThreePhotos = ($photoCompletenessRate >= 100 && $totalSubmissions > 0);

            // 3. Analisa Keaktifan Harian (Rajin tiap hari atau ada hari yang tidak menyelesaikan task)
            $activeDates = $userSubs->pluck('created_at')->map(function ($date) {
                return $date->format('Y-m-d');
            })->unique();

            $activeDaysCount = $activeDates->count();

            $firstSub = $userSubs->sortBy('created_at')->first();
            $daysSpan = $firstSub ? max(1, (int) $firstSub->created_at->diffInDays(now()) + 1) : 1;
            $inactiveDaysCount = max(0, $daysSpan - $activeDaysCount);

            $activityPercentage = min(100, round(($activeDaysCount / $daysSpan) * 100));

            if ($totalSubmissions === 0) {
                $activityStatus = 'Belum Aktif 😴';
                $activityBadge = 'bg-secondary';
            } elseif ($activityPercentage >= 80 || $activeDaysCount >= 5) {
                $activityStatus = 'Sangat Rajin 🔥 (Setiap Hari)';
                $activityBadge = 'bg-success';
            } elseif ($activityPercentage >= 40 || $activeDaysCount >= 2) {
                $activityStatus = 'Cukup Rajin ⚡';
                $activityBadge = 'bg-info';
            } else {
                $activityStatus = 'Jarang Kirim ⚠️ (Ada Hari Bolos)';
                $activityBadge = 'bg-warning text-dark';
            }

            // Submissions hari ini
            $submittedToday = $userSubs->filter(function($s) {
                return $s->created_at->isToday();
            })->count() > 0;

            $usernamesRaw = $userSubs->pluck('username_sosmed')->filter()->toArray();
            $usernames = [];
            foreach ($usernamesRaw as $uRaw) {
                $splitParts = preg_split('/[,\/]+/', $uRaw);
                foreach ($splitParts as $sp) {
                    $cSp = trim($sp);
                    if ($cSp !== '' && !in_array($cSp, $usernames)) {
                        $usernames[] = $cSp;
                    }
                }
            }
            $tasksCompleted = $userSubs->pluck('pilihan_tugas')->filter()->unique()->values()->toArray();

            $analysisData[] = [
                'nama' => $empName,
                'divisi' => $empDiv,
                'username_sosmed' => !empty($usernames) ? implode(', ', $usernames) : '-',
                'total_points' => $totalPoints,
                'total_submissions' => $totalSubmissions,
                'acc_count' => $accCount,
                'pending_count' => $pendingCount,
                'rejected_count' => $rejectedCount,
                'total_fee' => $totalFee,
                'favorite_platform' => $favoritePlatform,
                'platform_breakdown' => $platformBreakdown,
                'three_photo_count' => $threePhotoCount,
                'photo_completeness_rate' => $photoCompletenessRate,
                'avg_photos' => $avgPhotosPerSubmission,
                'is_always_three' => $isAlwaysThreePhotos,
                'active_days_count' => $activeDaysCount,
                'inactive_days_count' => $inactiveDaysCount,
                'activity_percentage' => $activityPercentage,
                'activity_status' => $activityStatus,
                'activity_badge' => $activityBadge,
                'submitted_today' => $submittedToday,
                'tasks_completed' => $tasksCompleted,
                'ig_count' => $igCount,
                'tiktok_count' => $tiktokCount,
                'fb_count' => $fbCount,
                'yt_count' => $ytCount,
                'submissions' => $userSubs->sortByDesc('created_at')->values(),
            ];
        }

        // Urutkan default berdasarkan Total Points DESC, lalu Active Days DESC
        usort($analysisData, function ($a, $b) {
            if ($b['total_points'] !== $a['total_points']) {
                return $b['total_points'] <=> $a['total_points'];
            }
            if ($b['active_days_count'] !== $a['active_days_count']) {
                return $b['active_days_count'] <=> $a['active_days_count'];
            }
            return strcmp($a['nama'], $b['nama']);
        });

        // Set Rank 1 - 90
        foreach ($analysisData as $index => &$item) {
            $item['rank'] = $index + 1;
        }

        return $analysisData;
    }

    /**
     * Dashboard khusus Admin Sosmed
     */
    public function dashboard()
    {
        $totalSubmissions = SosmedSubmission::whereNull('deleted_at')->count();
        $pendingCount = SosmedSubmission::whereNull('deleted_at')->where('status', 'pending')->count();
        $accCount = SosmedSubmission::whereNull('deleted_at')->where('status', 'acc')->count();
        $rejectedCount = SosmedSubmission::whereNull('deleted_at')->where('status', 'ditolak')->count();
        $totalFeeAccrued = SosmedSubmission::whereNull('deleted_at')->where('status', 'acc')->sum('fee_amount');

        // Traffic per Platform
        $platformCounts = [
            'Instagram' => SosmedSubmission::whereNull('deleted_at')->where('sosmed_platform', 'Instagram')->count(),
            'TikTok'    => SosmedSubmission::whereNull('deleted_at')->where('sosmed_platform', 'TikTok')->count(),
            'Facebook'  => SosmedSubmission::whereNull('deleted_at')->where('sosmed_platform', 'Facebook')->count(),
            'YouTube'   => SosmedSubmission::whereNull('deleted_at')->where('sosmed_platform', 'YouTube')->count(),
        ];

        // Ambil data Peringkat & Analisa Karyawan (Urut 1 - 90)
        $leaderboardAll = static::getUserDetailedAnalysis();

        // Ambil Top 5 untuk Grafik Peringkat Horizontal Bar (Rank 1 s/d Rank 5)
        $top5Users = array_slice($leaderboardAll, 0, 5);

        $top5Labels = [];
        $top5Points = [];
        $top5Divisions = [];
        $top5Usernames = [];

        foreach ($top5Users as $user) {
            $top5Labels[] = "#" . $user['rank'] . " " . $user['nama'];
            $top5Points[] = $user['total_points'];
            $top5Divisions[] = $user['divisi'];
            $top5Usernames[] = $user['username_sosmed'];
        }

        // Recent 5 Submissions
        $recentSubmissions = SosmedSubmission::whereNull('deleted_at')->orderBy('created_at', 'desc')->limit(5)->get();

        return view('admin.sosmed.dashboard', compact(
            'totalSubmissions',
            'pendingCount',
            'accCount',
            'rejectedCount',
            'totalFeeAccrued',
            'platformCounts',
            'leaderboardAll',
            'top5Labels',
            'top5Points',
            'top5Divisions',
            'top5Usernames',
            'recentSubmissions'
        ));
    }

    /**
     * Dashboard Analisa Rincian User & Keaktifan Harian (Admin)
     */
    public function userAnalysis(Request $request)
    {
        $analysisData = static::getUserDetailedAnalysis();

        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $analysisData = array_filter($analysisData, function ($user) use ($search) {
                return str_contains(strtolower($user['nama']), $search)
                    || str_contains(strtolower($user['username_sosmed']), $search)
                    || str_contains(strtolower($user['divisi']), $search)
                    || str_contains(strtolower($user['favorite_platform']), $search);
            });
        }

        return view('admin.sosmed.user_analysis', compact('analysisData'));
    }

    /**
     * Moderasi dan Riwayat Submission User (Dengan Audit Penghapusan Admin)
     */
    public function submissions(Request $request)
    {
        $query = SosmedSubmission::with(['processor', 'deleter'])->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            if ($request->status === 'terhapus') {
                $query->whereNotNull('deleted_at');
            } else {
                $query->whereNull('deleted_at')->where('status', $request->status);
            }
        }

        if ($request->filled('platform')) {
            $query->where('sosmed_platform', $request->platform);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('username_sosmed', 'like', "%{$search}%")
                  ->orWhere('nama_first_word', 'like', "%{$search}%")
                  ->orWhere('divisi', 'like', "%{$search}%");
            });
        }

        $submissions = $query->paginate(15)->withQueryString();
        $defaultFee = (float) SosmedSetting::getByKey('fee_per_submission', 5000);

        return view('admin.sosmed.submissions', compact('submissions', 'defaultFee'));
    }

    /**
     * Update Status Submission (ACC / Ditolak) + Catatan
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,acc,ditolak',
            'catatan' => 'nullable|string|max:1000',
            'fee_amount' => 'nullable|numeric|min:0',
        ]);

        $submission = SosmedSubmission::findOrFail($id);
        $submission->status = $request->status;
        $submission->catatan = $request->catatan;
        $submission->processed_by = Auth::id();
        $submission->processed_at = now();

        if ($request->filled('fee_amount')) {
            $submission->fee_amount = (float) $request->fee_amount;
        } elseif ($request->status === 'acc' && $submission->fee_amount <= 0) {
            $submission->fee_amount = (float) SosmedSetting::getByKey('fee_per_submission', 5000);
        }

        $submission->save();

        $statusText = $request->status === 'acc' ? 'disetujui (ACC)' : ($request->status === 'ditolak' ? 'ditolak' : 'diubah ke pending');
        return back()->with('success', "Status submission #{$submission->id} berhasil {$statusText}.");
    }

    /**
     * Tandai Hapus Submission + Catat Admin Sosmed & Waktu Penghapusan Audit
     */
    public function destroySubmission($id)
    {
        $submission = SosmedSubmission::findOrFail($id);

        $submission->deleted_by = Auth::id();
        $submission->deleted_at = now();
        $submission->save();

        $deleterName = Auth::user()->name ?? 'Admin Sosmed';
        $timeStr = $submission->deleted_at->format('d/m/Y H:i') . ' WIB';

        return back()->with('success', "Data submission #{$submission->id} berhasil ditandai terhapus oleh {$deleterName} pada {$timeStr}. Data tercatat dalam riwayat audit.");
    }

    /**
     * Pulihkan Submission yang Terhapus
     */
    public function restoreSubmission($id)
    {
        $submission = SosmedSubmission::findOrFail($id);

        $submission->deleted_by = null;
        $submission->deleted_at = null;
        $submission->save();

        return back()->with('success', "Data submission #{$submission->id} berhasil dipulihkan kembali.");
    }

    /**
     * Rekap Leaderboard & Fee Terkumpul 90 Karyawan
     */
    public function userFees(Request $request)
    {
        $leaderboardAll = static::getUserDetailedAnalysis();

        if ($request->filled('search')) {
            $search = strtolower(trim($request->search));
            $leaderboardAll = array_filter($leaderboardAll, function ($user) use ($search) {
                return str_contains(strtolower($user['nama']), $search)
                    || str_contains(strtolower($user['username_sosmed']), $search)
                    || str_contains(strtolower($user['divisi']), $search);
            });
        }

        return view('admin.sosmed.user_fees', compact('leaderboardAll'));
    }

    /**
     * Halaman Pengaturan Fee, Form Status & Kelola Penugasan Sosmed
     */
    public function settings()
    {
        $feePerSubmission = (float) SosmedSetting::getByKey('fee_per_submission', 5000);
        $isFormActive = SosmedSetting::getByKey('form_is_active', '1');
        $publicFormUrl = route('sosmed.form.show');

        $rawTaskLinks = SosmedSetting::getByKey('task_links', '[]');
        $taskItemsRaw = json_decode($rawTaskLinks, true) ?? [];
        if (!is_array($taskItemsRaw)) {
            $taskItemsRaw = [];
        }

        $taskItems = [];
        foreach ($taskItemsRaw as $index => $item) {
            if (is_array($item)) {
                $taskItems[] = [
                    'judul' => $item['judul'] ?? ('Tugas ' . ($index + 1)),
                    'platform' => $item['platform'] ?? 'Semua Platform',
                    'link' => $item['link'] ?? '',
                    'tanggal_start' => $item['tanggal_start'] ?? date('Y-m-d'),
                ];
            } else {
                $taskItems[] = [
                    'judul' => 'Tugas ' . ($index + 1),
                    'platform' => 'Semua Platform',
                    'link' => (string) $item,
                    'tanggal_start' => date('Y-m-d'),
                ];
            }
        }

        $platformOptions = [
            'Semua Platform',
            'TikTok',
            'Instagram',
            'Facebook',
            'YouTube',
            'Twitter / X',
            'Threads'
        ];

        return view('admin.sosmed.settings', compact('feePerSubmission', 'isFormActive', 'publicFormUrl', 'taskItems', 'platformOptions'));
    }

    /**
     * Update Pengaturan Fee, Form Status & Daftar Penugasan (Judul, Platform, Link, Tanggal Start)
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'fee_per_submission' => 'required|numeric|min:0',
            'form_is_active' => 'required|in:0,1',
            'tasks' => 'nullable|array',
            'tasks.*.judul' => 'nullable|string|max:255',
            'tasks.*.platform' => 'nullable|string|max:255',
            'tasks.*.link' => 'nullable|string|max:1000',
            'tasks.*.tanggal_start' => 'nullable|date',
        ]);

        $savedTasks = [];
        if (!empty($request->tasks) && is_array($request->tasks)) {
            foreach ($request->tasks as $index => $t) {
                $judul = trim($t['judul'] ?? '');
                $platform = trim($t['platform'] ?? 'Semua Platform');
                $link = trim($t['link'] ?? '');
                $tanggalStart = trim($t['tanggal_start'] ?? date('Y-m-d'));

                if ($judul !== '' || $link !== '') {
                    $savedTasks[] = [
                        'judul' => $judul !== '' ? $judul : ('Tugas ' . ($index + 1)),
                        'platform' => $platform !== '' ? $platform : 'Semua Platform',
                        'link' => $link,
                        'tanggal_start' => $tanggalStart !== '' ? $tanggalStart : date('Y-m-d'),
                    ];
                }
            }
        }

        SosmedSetting::setByKey('fee_per_submission', $request->fee_per_submission);
        SosmedSetting::setByKey('form_is_active', $request->form_is_active);
        SosmedSetting::setByKey('task_links', json_encode($savedTasks));

        return back()->with('success', 'Pengaturan fee, status form, dan kelola penugasan sosmed berhasil diperbarui.');
    }
}
