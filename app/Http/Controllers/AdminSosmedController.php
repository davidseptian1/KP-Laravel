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
     * Dashboard khusus Admin Sosmed
     */
    public function dashboard()
    {
        $totalSubmissions = SosmedSubmission::count();
        $pendingCount = SosmedSubmission::where('status', 'pending')->count();
        $accCount = SosmedSubmission::where('status', 'acc')->count();
        $rejectedCount = SosmedSubmission::where('status', 'ditolak')->count();
        $totalFeeAccrued = SosmedSubmission::where('status', 'acc')->sum('fee_amount');

        // Traffic per Platform (IG, TikTok, Facebook)
        $platformCounts = [
            'Instagram' => SosmedSubmission::where('sosmed_platform', 'Instagram')->count(),
            'TikTok'    => SosmedSubmission::where('sosmed_platform', 'TikTok')->count(),
            'Facebook'  => SosmedSubmission::where('sosmed_platform', 'Facebook')->count(),
        ];

        // Top Most Active Users (Grafik Paling Rajin Simpan) - Grouped by First Word of Nama
        $topUsersData = SosmedSubmission::select('nama_first_word', DB::raw('COUNT(*) as total_submit'), DB::raw('SUM(CASE WHEN status = "acc" THEN fee_amount ELSE 0 END) as total_fee'))
            ->groupBy('nama_first_word')
            ->orderByDesc('total_submit')
            ->limit(10)
            ->get();

        $topUserLabels = $topUsersData->pluck('nama_first_word')->toArray();
        $topUserCounts = $topUsersData->pluck('total_submit')->toArray();

        // Recent 5 Submissions
        $recentSubmissions = SosmedSubmission::orderBy('created_at', 'desc')->limit(5)->get();

        return view('admin.sosmed.dashboard', compact(
            'totalSubmissions',
            'pendingCount',
            'accCount',
            'rejectedCount',
            'totalFeeAccrued',
            'platformCounts',
            'topUserLabels',
            'topUserCounts',
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
     * Rekap Fee Terkumpul Setiap User
     * Mengambil data kalimat/kata pertama dari field input nama user
     */
    public function userFees(Request $request)
    {
        $query = SosmedSubmission::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_first_word', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        // Group by nama_first_word
        $submissions = $query->get();

        $userFeesGrouped = $submissions->groupBy('nama_first_word')->map(function ($items, $firstWord) {
            $distinctNames = $items->pluck('nama')->unique()->values()->toArray();
            $totalSubmissions = $items->count();
            $accSubmissions = $items->where('status', 'acc')->count();
            $pendingSubmissions = $items->where('status', 'pending')->count();
            $rejectedSubmissions = $items->where('status', 'ditolak')->count();
            $totalFeeAccrued = $items->where('status', 'acc')->sum('fee_amount');

            $igCount = $items->where('sosmed_platform', 'Instagram')->count();
            $tiktokCount = $items->where('sosmed_platform', 'TikTok')->count();
            $fbCount = $items->where('sosmed_platform', 'Facebook')->count();

            return [
                'first_word'          => $firstWord,
                'variasi_nama'        => $distinctNames,
                'latest_divisi'       => $items->sortByDesc('created_at')->first()->divisi ?? '-',
                'total_submissions'   => $totalSubmissions,
                'acc_submissions'     => $accSubmissions,
                'pending_submissions' => $pendingSubmissions,
                'rejected_submissions'=> $rejectedSubmissions,
                'total_fee'           => $totalFeeAccrued,
                'ig_count'            => $igCount,
                'tiktok_count'        => $tiktokCount,
                'fb_count'            => $fbCount,
                'items'               => $items->sortByDesc('created_at'),
            ];
        })->sortByDesc('total_fee');

        return view('admin.sosmed.user_fees', compact('userFeesGrouped'));
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
