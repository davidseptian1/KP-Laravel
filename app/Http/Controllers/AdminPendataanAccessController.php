<?php

namespace App\Http\Controllers;

use App\Models\PendataanStaff;
use App\Models\SosmedSetting;
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

        // 13+ Staff Credentials for Pendataan
        $staffAccounts = PendataanStaff::orderBy('nama', 'asc')->get();

        // Users who currently have access or can be selected to create staff credentials
        $usersWithAccess = User::whereNotIn('jabatan', ['Superadmin'])
            ->orderBy('has_pendataan_access', 'desc')
            ->orderBy('nama', 'asc')
            ->get();

        $botToken = TelegramPendataanController::getBotToken();
        $botUsername = config('services.telegram_pendataan.bot_username', 'intel_awgbot');
        $checkLimit = TelegramPendataanController::getCheckLimit();
        $webhookUrl = url('/api/telegram/pendataan-webhook');

        $latestBotLogs = class_exists(\App\Models\TelegramPendataanLog::class)
            ? \App\Models\TelegramPendataanLog::with('pendataan')->latest('id')->take(6)->get()
            : collect();

        return view('admin.pendataan.access', [
            'title' => 'Pengaturan Akses Fitur Pendataan',
            'menuPendataanAccess' => 'active',
            'users' => $users,
            'totalStaff' => $totalStaff,
            'staffWithAccess' => $staffWithAccess,
            'staffWithoutAccess' => $staffWithoutAccess,
            'staffAccounts' => $staffAccounts,
            'usersWithAccess' => $usersWithAccess,
            'currentSearch' => $search,
            'currentRole' => $roleFilter,
            'botToken' => $botToken,
            'botUsername' => $botUsername,
            'checkLimit' => $checkLimit,
            'webhookUrl' => $webhookUrl,
            'latestBotLogs' => $latestBotLogs,
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

    /**
     * Store a newly created PendataanStaff account.
     */
    public function storeStaff(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:pendataan_staff,nama',
            'username' => 'required|string|alpha_dash|max:50|unique:pendataan_staff,username',
            'password' => 'required|string|min:3|max:20',
            'user_id' => 'nullable|exists:users,id',
            'is_active' => 'nullable',
        ], [
            'nama.required' => 'Nama staf wajib diisi.',
            'nama.unique' => 'Nama staf sudah terdaftar.',
            'username.required' => 'Username staf wajib diisi.',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, strip, dan underscore.',
            'username.unique' => 'Username ini sudah digunakan, silakan pilih username lain.',
            'password.required' => 'Password staf wajib diisi.',
            'password.min' => 'Password minimal 3 karakter.',
        ]);

        $nama = trim($request->nama);
        $username = strtolower(trim($request->username));
        $password = trim($request->password);
        $isActive = $request->boolean('is_active', true);

        PendataanStaff::create([
            'nama' => $nama,
            'username' => $username,
            'password' => $password,
            'is_active' => $isActive,
        ]);

        // If an existing User account was selected, ensure has_pendataan_access is activated
        if ($request->filled('user_id')) {
            User::where('id', $request->user_id)->update(['has_pendataan_access' => true]);
        }

        cache()->forget('pendataan_filter_staff_names');

        return redirect()->route('admin.pendataan.access')
            ->with('success', "Akun staf '{$nama}' (Username: {$username}) berhasil ditambahkan untuk login fitur Pendataan!");
    }

    /**
     * Update an existing PendataanStaff account (Name, Username, Password, Active status).
     */
    public function updateStaff(Request $request, $id)
    {
        $staff = PendataanStaff::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:100|unique:pendataan_staff,nama,' . $id,
            'username' => 'required|string|alpha_dash|max:50|unique:pendataan_staff,username,' . $id,
            'password' => 'required|string|min:3|max:20',
            'is_active' => 'nullable',
        ], [
            'nama.required' => 'Nama staf wajib diisi.',
            'nama.unique' => 'Nama staf sudah digunakan oleh staf lain.',
            'username.required' => 'Username staf wajib diisi.',
            'username.alpha_dash' => 'Username hanya boleh huruf, angka, strip, dan underscore.',
            'username.unique' => 'Username ini sudah digunakan.',
            'password.required' => 'Password staf wajib diisi.',
            'password.min' => 'Password minimal 3 karakter.',
        ]);

        $staff->update([
            'nama' => trim($request->nama),
            'username' => strtolower(trim($request->username)),
            'password' => trim($request->password),
            'is_active' => $request->boolean('is_active'),
        ]);

        cache()->forget('pendataan_filter_staff_names');

        return redirect()->route('admin.pendataan.access')
            ->with('success', "Data akun staf '{$staff->nama}' berhasil diperbarui!");
    }

    /**
     * Delete a PendataanStaff account.
     */
    public function destroyStaff($id)
    {
        $staff = PendataanStaff::findOrFail($id);
        $nama = $staff->nama;
        $staff->delete();

        cache()->forget('pendataan_filter_staff_names');

        return redirect()->route('admin.pendataan.access')
            ->with('success', "Akun staf '{$nama}' berhasil dihapus dari daftar login fitur Pendataan.");
    }

    /**
     * Toggle active status of a PendataanStaff account via AJAX.
     */
    public function toggleStaffStatus(Request $request, $id)
    {
        $staff = PendataanStaff::findOrFail($id);
        $staff->is_active = $request->has('state') ? $request->boolean('state') : !$staff->is_active;
        $staff->save();

        return response()->json([
            'success' => true,
            'is_active' => $staff->is_active,
            'message' => "Status akun {$staff->nama} berhasil diubah menjadi " . ($staff->is_active ? 'Aktif' : 'Nonaktif'),
        ]);
    }

    /**
     * Update password for a PendataanStaff account (legacy helper).
     */
    public function updateStaffPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|string|min:3|max:20',
        ], [
            'password.required' => 'Password staf wajib diisi.',
            'password.min' => 'Password minimal 3 karakter.',
        ]);

        $staff = PendataanStaff::findOrFail($id);
        $staff->password = trim($request->password);
        $staff->save();

        return redirect()->back()->with('success', "Password untuk staf {$staff->nama} berhasil diperbarui menjadi '{$staff->password}'!");
    }

    /**
     * Update Telegram Bot configuration for Pendataan.
     */
    public function updateBotSettings(Request $request)
    {
        $request->validate([
            'bot_token' => 'nullable|string',
            'check_limit' => 'required|integer|min:1|max:50',
        ], [
            'check_limit.required' => 'Jumlah pesan yang dicek wajib diisi.',
            'check_limit.min' => 'Jumlah pesan yang dicek minimal 1.',
            'check_limit.max' => 'Jumlah pesan yang dicek maksimal 50.',
        ]);

        if ($request->filled('bot_token')) {
            SosmedSetting::setByKey('pendataan_telegram_bot_token', trim($request->bot_token));
        }

        SosmedSetting::setByKey('pendataan_telegram_check_limit', (int) $request->check_limit);

        return redirect()->route('admin.pendataan.access')
            ->with('success', 'Pengaturan Bot Telegram Pendataan (Scraping limit & Bot Token) berhasil disimpan!');
    }

    /**
     * Display the Telegram Bot activity logs page.
     */
    public function botLogs(Request $request)
    {
        if (!auth()->user() || !auth()->user()->canAccessPendataan()) {
            abort(403, 'Anda tidak memiliki hak akses ke fitur Pendataan.');
        }

        $statusFilter = strtolower(trim((string) $request->input('status', 'all')));
        $search = trim((string) $request->input('search', ''));

        $query = \App\Models\TelegramPendataanLog::with('pendataan');

        if ($statusFilter !== 'all' && in_array($statusFilter, [
            \App\Models\TelegramPendataanLog::STATUS_MATCHED,
            \App\Models\TelegramPendataanLog::STATUS_UNMATCHED,
            \App\Models\TelegramPendataanLog::STATUS_GENERAL_CHAT,
            \App\Models\TelegramPendataanLog::STATUS_INVALID_FORMAT,
            \App\Models\TelegramPendataanLog::STATUS_ERROR,
        ])) {
            if ($statusFilter === 'general_chat' || $statusFilter === 'invalid_format') {
                $query->whereIn('status', [\App\Models\TelegramPendataanLog::STATUS_GENERAL_CHAT, \App\Models\TelegramPendataanLog::STATUS_INVALID_FORMAT]);
            } else {
                $query->where('status', $statusFilter);
            }
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('raw_message', 'like', "%{$search}%")
                  ->orWhere('parsed_product', 'like', "%{$search}%")
                  ->orWhere('chat_title', 'like', "%{$search}%")
                  ->orWhere('sender_name', 'like', "%{$search}%")
                  ->orWhere('action_note', 'like', "%{$search}%");
            });
        }

        $logs = $query->latest('id')->paginate(20)->withQueryString();

        // Summary Statistics
        $totalLogs = \App\Models\TelegramPendataanLog::count();
        $matchedLogs = \App\Models\TelegramPendataanLog::where('status', \App\Models\TelegramPendataanLog::STATUS_MATCHED)->count();
        $unmatchedLogs = \App\Models\TelegramPendataanLog::where('status', \App\Models\TelegramPendataanLog::STATUS_UNMATCHED)->count();
        $generalChatLogs = \App\Models\TelegramPendataanLog::whereIn('status', [
            \App\Models\TelegramPendataanLog::STATUS_GENERAL_CHAT,
            \App\Models\TelegramPendataanLog::STATUS_INVALID_FORMAT,
        ])->count();

        // Webhook Raw Logs
        $activeTab = $request->input('tab', 'transactions');
        $rawLogs = \App\Models\TelegramWebhookRawLog::latest('id')->paginate(20, ['*'], 'raw_page')->withQueryString();
        $totalRawLogs = \App\Models\TelegramWebhookRawLog::count();

        if ($request->ajax() || $request->wantsJson()) {
            return view('admin.pendataan.partials.bot_logs_table', compact('logs'))->render();
        }

        return view('admin.pendataan.bot_logs', [
            'title' => 'Riwayat Logs Aktivitas Bot Telegram',
            'menuSuperadminLogs' => 'active',
            'logs' => $logs,
            'totalLogs' => $totalLogs,
            'matchedLogs' => $matchedLogs,
            'unmatchedLogs' => $unmatchedLogs,
            'invalidFormatLogs' => $generalChatLogs,
            'generalChatLogs' => $generalChatLogs,
            'currentStatus' => $statusFilter,
            'currentSearch' => $search,
            'botUsername' => config('services.telegram_pendataan.bot_username', 'intel_awgbot'),
            'checkLimit' => TelegramPendataanController::getCheckLimit(),
            'activeTab' => $activeTab,
            'rawLogs' => $rawLogs,
            'totalRawLogs' => $totalRawLogs,
        ]);
    }

    /**
     * Clear all bot activity logs.
     */
    public function clearBotLogs(Request $request)
    {
        if (strtolower(trim(auth()->user()->jabatan ?? '')) !== 'superadmin') {
            abort(403, 'Hanya Superadmin yang memiliki izin untuk membersihkan riwayat log.');
        }

        \App\Models\TelegramPendataanLog::truncate();

        return redirect()->route('admin.pendataan.bot-logs', ['tab' => 'transactions'])
            ->with('success', 'Semua riwayat log aktivitas bot Telegram berhasil dibersihkan!');
    }

    /**
     * Clear all raw webhook logs.
     */
    public function clearWebhookRawLogs(Request $request)
    {
        if (strtolower(trim(auth()->user()->jabatan ?? '')) !== 'superadmin') {
            abort(403, 'Hanya Superadmin yang memiliki izin untuk membersihkan raw log.');
        }

        \App\Models\TelegramWebhookRawLog::truncate();

        return redirect()->route('admin.pendataan.bot-logs', ['tab' => 'webhook'])
            ->with('success', 'Semua riwayat Raw Webhook Logs berhasil dibersihkan!');
    }

    /**
     * Test / simulate webhook ping to verify webhook logging is operational.
     */
    public function testWebhookPing(Request $request)
    {
        if (!auth()->user() || !auth()->user()->canAccessPendataan()) {
            abort(403, 'Unauthorized');
        }

        \App\Models\TelegramWebhookRawLog::create([
            'source' => 'simulated_test',
            'ip_address' => $request->ip(),
            'http_method' => 'POST',
            'update_id' => rand(100000000, 999999999),
            'update_type' => 'test_ping',
            'chat_id' => '-100test',
            'chat_title' => 'Simulasi Tes Webhook',
            'sender_name' => auth()->user()->nama ?? 'Admin',
            'summary' => 'Simulasi pengujian webhook logger dari tombol UI Admin.',
            'raw_payload' => json_encode([
                'test' => true,
                'sender' => auth()->user()->nama ?? 'Admin',
                'timestamp' => now()->toIso8601String(),
                'message' => 'Tes simulasi payload webhook berhasil masuk ke database!',
                'note' => 'Logger webhook berfungsi 100% normal dan siap menerima panggilan dari Telegram/SMS Forwarder.',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            'status' => 'received',
            'notes' => 'Simulasi sukses! Webhook logger siap merekam semua payload masuk.',
        ]);

        return redirect()->route('admin.pendataan.bot-logs', ['tab' => 'webhook'])
            ->with('success', 'Simulasi webhook berhasil dicatat! Webhook logger terbukti berfungsi.');
    }

    /**
     * Show single raw webhook log JSON detail.
     */
    public function showWebhookRawLog($id)
    {
        if (!auth()->user() || !auth()->user()->canAccessPendataan()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $rawLog = \App\Models\TelegramWebhookRawLog::findOrFail($id);

        // Format JSON payload nicely
        $payloadStr = $rawLog->raw_payload;
        $decoded = json_decode($rawLog->raw_payload, true);
        if ($decoded !== null) {
            $payloadStr = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $rawLog->id,
                'source' => $rawLog->source,
                'ip_address' => $rawLog->ip_address ?: '-',
                'http_method' => $rawLog->http_method,
                'update_id' => $rawLog->update_id ?: '-',
                'update_type' => $rawLog->update_type ?: '-',
                'chat_id' => $rawLog->chat_id ?: '-',
                'chat_title' => $rawLog->chat_title ?: '-',
                'sender_name' => $rawLog->sender_name ?: '-',
                'summary' => $rawLog->summary ?: '-',
                'status' => $rawLog->status,
                'status_badge_html' => $rawLog->status_badge_html,
                'notes' => $rawLog->notes ?: '-',
                'raw_payload' => $payloadStr,
                'created_at' => $rawLog->created_at?->format('d/m/Y H:i:s') ?: '-',
            ],
        ]);
    }

    /**
     * Show single log detail as JSON for modal.
     */
    public function showBotLog($id)
    {
        if (!auth()->user() || !auth()->user()->canAccessPendataan()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $log = \App\Models\TelegramPendataanLog::with('pendataan')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $log->id,
                'chat_id' => $log->chat_id,
                'chat_title' => $log->chat_title ?: '-',
                'message_id' => $log->message_id,
                'sender_name' => $log->sender_name ?: '-',
                'sender_username' => $log->sender_username ? '@' . $log->sender_username : '-',
                'raw_message' => $log->raw_message,
                'parsed_product' => $log->parsed_product ?: '-',
                'parsed_nominal' => $log->formatted_nominal,
                'raw_nominal' => $log->raw_nominal ?: '-',
                'status' => $log->status,
                'status_label' => $log->status_label,
                'status_badge_html' => $log->status_badge_html,
                'pendataan_id' => $log->pendataan_id,
                'pendataan_product' => $log->pendataan?->nama_produk,
                'pendataan_nominal' => $log->pendataan ? 'Rp ' . number_format($log->pendataan->total_harga, 0, ',', '.') : null,
                'pendataan_nama' => $log->pendataan?->nama,
                'action_note' => $log->action_note,
                'bot_replied' => (bool) $log->bot_replied,
                'bot_reply_text' => $log->bot_reply_text,
                'created_at' => $log->formatted_created_at,
            ],
        ]);
    }
}
