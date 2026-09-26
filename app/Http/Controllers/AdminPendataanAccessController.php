<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminPendataanAccessController extends Controller
{
    /**
     * Display the access configuration page for Pendataan feature.
     */
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $roleFilter = $request->input('role', 'Staff');

        $query = User::query();

        if ($roleFilter !== 'all') {
            $query->where('jabatan', $roleFilter);
        } else {
            $query->whereNotIn('jabatan', ['Superadmin']);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('nama', 'asc')->get();

        $allStaff = User::where('jabatan', 'Staff')->get();
        $totalStaff = $allStaff->count();
        $staffWithAccess = $allStaff->where('has_pendataan_access', true)->count();
        $staffWithoutAccess = $totalStaff - $staffWithAccess;

        return view('admin.pendataan.access', [
            'title' => 'Pengaturan Akses Fitur Pendataan',
            'menuPendataanAccess' => 'active',
            'users' => $users,
            'totalStaff' => $totalStaff,
            'staffWithAccess' => $staffWithAccess,
            'staffWithoutAccess' => $staffWithoutAccess,
            'currentSearch' => $search,
            'currentRole' => $roleFilter,
        ]);
    }

    /**
     * AJAX toggle for granting or revoking pendataan access.
     */
    public function toggle(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($request->has('state')) {
            $user->has_pendataan_access = $request->boolean('state');
        } else {
            $user->has_pendataan_access = !$user->has_pendataan_access;
        }
        $user->save();

        return response()->json([
            'success' => true,
            'has_access' => (bool) $user->has_pendataan_access,
            'message' => 'Akses fitur Pendataan untuk ' . $user->nama . ($user->has_pendataan_access ? ' telah diaktifkan.' : ' telah dinonaktifkan.'),
        ]);
    }

    /**
     * Batch update access permissions.
     */
    public function batchUpdate(Request $request)
    {
        $selectedIds = (array) $request->input('accessible_user_ids', []);

        // Update all users who are not superadmin
        $targetUsers = User::whereNotIn('jabatan', ['Superadmin'])->get();

        foreach ($targetUsers as $staff) {
            $hasAccess = in_array((string) $staff->id, $selectedIds, true) || in_array((int) $staff->id, $selectedIds, true);
            $staff->has_pendataan_access = $hasAccess;
            $staff->save();
        }

        return redirect()->route('admin.pendataan.access')->with('success', 'Pengaturan akses fitur Pendataan berhasil disimpan!');
    }
}
