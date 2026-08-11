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
     * Generator Data Leaderboard Peringkat Karyawan (1 - 90)
     * 1 Task Submission Selesai / Ter-upload = 1 Poin
     */
    public static function getLeaderboardData(): array
    {
        $karyawanList = SosmedPublicFormController::getKaryawanList();
        $submissions = SosmedSubmission::all();

        $leaderboard = [];

        foreach ($karyawanList as $k) {
            $empName = trim($k['nama']);
            $empDiv = trim($k['divisi']);

            $firstWordName = strtolower(SosmedSubmission::extractFirstWord($empName));

            // Matching submission user by name or first word
            $userSubs = $submissions->filter(function ($sub) use ($empName, $firstWordName) {
                $subNama = strtolower(trim($sub->nama));
                $subFirstWord = strtolower(trim($sub->nama_first_word));
                
                return $subNama === strtolower($empName)
                    || ($subFirstWord !== '' && $subFirstWord === $firstWordName);
            });

            $totalSubmissions = $userSubs->count();
            $accCount = $userSubs->where('status', 'acc')->count();
            $pendingCount = $userSubs->where('status', 'pending')->count();
            $rejectedCount = $userSubs->where('status', 'ditolak')->count();

            // 1 Task completed / uploaded = 1 Poin
            $totalPoints = $totalSubmissions;

            $totalFee = $userSubs->where('status', 'acc')->sum('fee_amount');

            $usernames = $userSubs->pluck('username_sosmed')->filter()->unique()->values()->toArray();
            $tasksCompleted = $userSubs->pluck('pilihan_tugas')->filter()->unique()->values()->toArray();

            $igCount = $userSubs->where('sosmed_platform', 'Instagram')->count();
            $tiktokCount = $userSubs->where('sosmed_platform', 'TikTok')->count();
            $fbCount = $userSubs->where('sosmed_platform', 'Facebook')->count();
            $ytCount = $userSubs->where('sosmed_platform', 'YouTube')->count();

            $leaderboard[] = [
                'nama' => $empName,
                'divisi' => $empDiv,
                'username_sosmed' => !empty($usernames) ? implode(', ', $usernames) : '-',
                'total_points' => $totalPoints,
                'total_submissions' => $totalSubmissions,
                'acc_count' => $accCount,
                'pending_count' => $pendingCount,
                'rejected_count' => $rejectedCount,
                'total_fee' => $totalFee,
                'tasks_completed' => $tasksCompleted,
                'ig_count' => $igCount,
                'tiktok_count' => $tiktokCount,
                'fb_count' => $fbCount,
                'yt_count' => $ytCount,
                'submissions' => $userSubs->sortByDesc('created_at')->values(),
            ];
        }

        // Urutkan dari poin terbesar ke terkecil (Terbesar -> Terkecil)
        usort($leaderboard, function ($a, $b) {
            if ($b['total_points'] !== $a['total_points']) {
                return $b['total_points'] <=> $a['total_points'];
            }
            if ($b['total_fee'] !== $a['total_fee']) {
                return $b['total_fee'] <=> $a['total_fee'];
            }
            return strcmp($a['nama'], $b['nama']);
        });

        // Set Nomor Peringkat (Rank 1 - 90)
        foreach ($leaderboard as $index => &$item) {
            $item['rank'] = $index + 1;
        }

        return $leaderboard;
    }

    /**
     * Dashboard khusus Admin Sosmed
     */
    public function dashboard()
    {
        $totalSubmissions = SosmedSubmission::count();
        $pendingCount = SosmedSubmission::where('status', 'pending')->count();
        $accCount = SosmedSubmission::where('status', 'acc')->count();
        $rejectedCount = SosmedSubmission::where('status', 'ditolak')->count();
        $totalFeeAccrued = SosmedSubmission::where('status', 'acc')->sum('fee_amount');

        // Traffic per Platform
        $platformCounts = [
            'Instagram' => SosmedSubmission::where('sosmed_platform', 'Instagram')->count(),
            'TikTok'    => SosmedSubmission::where('sosmed_platform', 'TikTok')->count(),
            'Facebook'  => SosmedSubmission::where('sosmed_platform', 'Facebook')->count(),
            'YouTube'   => SosmedSubmission::where('sosmed_platform', 'YouTube')->count(),
        ];

        // Ambil data Peringkat Karyawan (Urut 1 - 90)
        $leaderboardAll = static::getLeaderboardData();

        // Ambil Top 5 untuk Grafik Peringkat Linechart (Rank 1 s/d Rank 5)
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
        $recentSubmissions = SosmedSubmission::orderBy('created_at', 'desc')->limit(5)->get();

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
     * Moderasi dan Riwayat Submission User
     */
    public function submissions(Request $request)
    {
        $query = SosmedSubmission::with('processor')->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
     * Hapus Data Submission
     */
    public function destroySubmission($id)
    {
        $submission = SosmedSubmission::findOrFail($id);

        if (!empty($submission->photos) && is_array($submission->photos)) {
            foreach ($submission->photos as $photoPath) {
                $cleanPath = str_replace('storage/', '', $photoPath);
                if (Storage::disk('public')->exists($cleanPath)) {
                    Storage::disk('public')->delete($cleanPath);
                }
            }
        }

        $submission->delete();
        return back()->with('success', 'Data submission berhasil dihapus.');
    }

    /**
     * Rekap Leaderboard & Fee Terkumpul 90 Karyawan
     */
    public function userFees(Request $request)
    {
        $leaderboardAll = static::getLeaderboardData();

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
     * Halaman Pengaturan Fee & Form Status
     */
    public function settings()
    {
        $feePerSubmission = (float) SosmedSetting::getByKey('fee_per_submission', 5000);
        $isFormActive = SosmedSetting::getByKey('form_is_active', '1');
        $publicFormUrl = route('sosmed.form.show');

        $rawTaskLinks = SosmedSetting::getByKey('task_links', '[]');
        $taskLinksArray = json_decode($rawTaskLinks, true) ?? [];
        $taskLinksText = is_array($taskLinksArray) ? implode("\n", $taskLinksArray) : '';

        return view('admin.sosmed.settings', compact('feePerSubmission', 'isFormActive', 'publicFormUrl', 'taskLinksText'));
    }

    /**
     * Update Pengaturan Fee, Form Status & Link Tugas
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'fee_per_submission' => 'required|numeric|min:0',
            'form_is_active' => 'required|in:0,1',
            'task_links' => 'nullable|string',
        ]);

        $taskLinksInput = $request->task_links ?? '';
        $links = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "", $taskLinksInput)))));

        SosmedSetting::setByKey('fee_per_submission', $request->fee_per_submission);
        SosmedSetting::setByKey('form_is_active', $request->form_is_active);
        SosmedSetting::setByKey('task_links', json_encode($links));

        return back()->with('success', 'Pengaturan fee, status form, dan link tugas sosmed berhasil diperbarui.');
    }
}
