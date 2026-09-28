<?php

namespace App\Http\Controllers;

use App\Exports\PendataanExport;
use App\Models\Pendataan;
use App\Models\PendataanStaff;
use App\Services\PendataanParserService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class PendataanController extends Controller
{
    /**
     * Ensure current user has permission to access Pendataan.
     */
    private function checkAccess()
    {
        $user = auth()->user();
        if (!$user || !$user->canAccessPendataan()) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses fitur Pendataan.');
        }
    }

    /**
     * Enforce user permission and strict 8-hour shift staff session.
     * Even 0.1s past the 8-hour limit will immediately terminate the session and redirect.
     */
    private function enforceStaffSession()
    {
        $this->checkAccess();

        // If no staff session
        if (!session()->has('pendataan_staff_nama')) {
            if (auth()->user()->jabatan === 'Superadmin') {
                $now = microtime(true);
                $superadminNama = auth()->user()->nama ?? 'Super Admin';
                $baseSuperadminNama = preg_replace('/\s*\(\s*Shift\s*\d+\s*\)\s*$/i', '', $superadminNama);
                session([
                    'pendataan_staff_nama' => "{$baseSuperadminNama} ( Shift 1 )",
                    'pendataan_staff_raw_nama' => $baseSuperadminNama,
                    'pendataan_staff_shift' => 'Shift 1',
                    'pendataan_staff_username' => 'superadmin',
                    'pendataan_staff_login_at' => $now,
                    'pendataan_staff_expires_at' => $now + (8 * 3600),
                ]);
                return;
            }

            if (request()->wantsJson() || request()->ajax()) {
                throw new \Illuminate\Http\Exceptions\HttpResponseException(
                    response()->json([
                        'success' => false,
                        'expired' => true,
                        'message' => 'Sesi staf belum aktif. Silakan buka fitur Pendataan terlebih dahulu.',
                        'redirect' => route('pendataan.unlock'),
                    ], 401)
                );
            }

            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()->route('pendataan.unlock')->with('error', 'Silakan buka fitur Pendataan dengan akun staf Anda.')
            );
        }

        // Check 8-hour shift expiration with sub-second microtime precision
        $expiresAt = (float) session('pendataan_staff_expires_at', 0);
        $currentTime = microtime(true);

        // If expires_at is not set for existing session, initialize from login_at or now
        if ($expiresAt <= 0) {
            $loginAt = (float) session('pendataan_staff_login_at', $currentTime);
            $expiresAt = $loginAt + (8 * 3600);
            session(['pendataan_staff_expires_at' => $expiresAt]);
        }

        if ($currentTime >= $expiresAt) {
            $staffNama = session('pendataan_staff_nama', 'Staf');
            session()->forget([
                'pendataan_staff_id',
                'pendataan_staff_nama',
                'pendataan_staff_raw_nama',
                'pendataan_staff_shift',
                'pendataan_staff_username',
                'pendataan_staff_login_at',
                'pendataan_staff_expires_at',
            ]);

            if (request()->wantsJson() || request()->ajax()) {
                throw new \Illuminate\Http\Exceptions\HttpResponseException(
                    response()->json([
                        'success' => false,
                        'expired' => true,
                        'message' => 'Sesi shift 8 jam Anda telah berakhir. Anda telah otomatis logout.',
                        'redirect' => route('pendataan.unlock'),
                    ], 401)
                );
            }

            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()->route('pendataan.unlock')->with('error', "Sesi shift 8 jam untuk staf {$staffNama} telah berakhir. Sistem telah otomatis logout. Silakan login kembali untuk shift berikutnya.")
            );
        }
    }

    /**
     * Show unlock login screen for staff before accessing Pendataan.
     */
    public function showUnlock()
    {
        $this->checkAccess();

        if (session()->has('pendataan_staff_nama')) {
            $expiresAt = (float) session('pendataan_staff_expires_at', 0);
            if ($expiresAt > 0 && microtime(true) >= $expiresAt) {
                session()->forget([
                    'pendataan_staff_id',
                    'pendataan_staff_nama',
                    'pendataan_staff_raw_nama',
                    'pendataan_staff_shift',
                    'pendataan_staff_username',
                    'pendataan_staff_login_at',
                    'pendataan_staff_expires_at',
                ]);
            } else {
                return redirect()->route('pendataan.index');
            }
        }

        return view('pendataan.unlock', [
            'title' => 'Buka Fitur Pendataan',
        ]);
    }

    /**
     * Process staff unlock authentication.
     */
    public function unlock(Request $request)
    {
        $this->checkAccess();

        $now = microtime(true);
        $expiresAt = $now + (8 * 3600); // exactly 8 hours shift (28,800 seconds)

        // Helper to normalize shift input to 'Shift 1', 'Shift 2', or 'Shift 3'
        $normalizeShift = function ($shiftInput) {
            $shiftStr = trim((string) $shiftInput);
            if (in_array($shiftStr, ['2', 'Shift 2', 'shift 2', 'SHIFT 2'])) {
                return 'Shift 2';
            }
            if (in_array($shiftStr, ['3', 'Shift 3', 'shift 3', 'SHIFT 3'])) {
                return 'Shift 3';
            }
            return 'Shift 1';
        };

        // Superadmin bypass option
        if ($request->boolean('superadmin_bypass') && auth()->user()->jabatan === 'Superadmin') {
            $shiftLabel = $normalizeShift($request->input('shift', 'Shift 1'));
            $baseNama = auth()->user()->nama ?? 'Super Admin';
            $baseNama = preg_replace('/\s*\(\s*Shift\s*\d+\s*\)\s*$/i', '', $baseNama);
            $namaWithShift = "{$baseNama} ( {$shiftLabel} )";

            session([
                'pendataan_staff_id' => null,
                'pendataan_staff_nama' => $namaWithShift,
                'pendataan_staff_raw_nama' => $baseNama,
                'pendataan_staff_shift' => $shiftLabel,
                'pendataan_staff_username' => 'superadmin',
                'pendataan_staff_login_at' => $now,
                'pendataan_staff_expires_at' => $expiresAt,
            ]);
            return redirect()->route('pendataan.index')->with('success', "Berhasil masuk sebagai {$namaWithShift}. Sesi shift 8 jam dimulai.");
        }

        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'shift' => 'required|in:1,2,3,Shift 1,Shift 2,Shift 3',
        ], [
            'username.required' => 'Username staf wajib diisi.',
            'password.required' => 'Password staf (5 huruf) wajib diisi.',
            'shift.required' => 'Pilihan shift (Shift 1 / 2 / 3) wajib dipilih.',
            'shift.in' => 'Pilihan shift harus Shift 1, Shift 2, atau Shift 3.',
        ]);

        $inputUsername = strtolower(trim($request->input('username')));
        $inputPassword = trim($request->input('password'));

        $staff = PendataanStaff::where('is_active', true)
            ->where(function ($q) use ($inputUsername) {
                $q->whereRaw('LOWER(username) = ?', [$inputUsername])
                  ->orWhereRaw('LOWER(nama) = ?', [$inputUsername]);
            })
            ->first();

        if (!$staff || strcasecmp($staff->password, $inputPassword) !== 0) {
            return back()->withInput()->with('error', 'Username atau Password staf (5 huruf) tidak sesuai.');
        }

        $shiftLabel = $normalizeShift($request->input('shift', 'Shift 1'));

        // Clean staff base name in case it already contains "( Shift ... )"
        $baseStaffNama = preg_replace('/\s*\(\s*Shift\s*\d+\s*\)\s*$/i', '', $staff->nama);
        $namaWithShift = "{$baseStaffNama} ( {$shiftLabel} )";

        session([
            'pendataan_staff_id' => $staff->id,
            'pendataan_staff_nama' => $namaWithShift,
            'pendataan_staff_raw_nama' => $baseStaffNama,
            'pendataan_staff_shift' => $shiftLabel,
            'pendataan_staff_username' => $staff->username,
            'pendataan_staff_login_at' => $now,
            'pendataan_staff_expires_at' => $expiresAt,
        ]);

        return redirect()->route('pendataan.index')->with('success', "Akses berhasil dibuka! Anda aktif sebagai staf {$namaWithShift} (Sesi shift 8 jam dimulai).");
    }

    /**
     * Lock the pendataan feature / switch active staff.
     */
    public function lock(Request $request)
    {
        session()->forget([
            'pendataan_staff_id',
            'pendataan_staff_nama',
            'pendataan_staff_raw_nama',
            'pendataan_staff_shift',
            'pendataan_staff_username',
            'pendataan_staff_login_at',
            'pendataan_staff_expires_at',
        ]);

        if ($request->boolean('expired')) {
            return redirect()->route('pendataan.unlock')->with('error', 'Sesi shift 8 jam Anda telah berakhir. Sistem telah otomatis logout.');
        }

        return redirect()->route('pendataan.unlock')->with('success', 'Berhasil logout dari fitur Pendataan. Akun utama Anda tetap aktif.');
    }

    /**
     * Build shared Eloquent query with active filters.
     */
    private function buildQuery(Request $request)
    {
        $query = Pendataan::with('user')->latest('created_at');

        // Filter: Tanggal (Start Date & End Date)
        if ($request->filled('start_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $query->where('created_at', '>=', $startDate);
        }

        if ($request->filled('end_date')) {
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->where('created_at', '<=', $endDate);
        }

        // Filter: Shift (All Shift, Shift 1, Shift 2, Shift 3)
        if ($request->filled('shift')) {
            $shift = trim((string) $request->shift);
            if (!in_array($shift, ['All Shift', 'all', 'All', ''])) {
                if (in_array($shift, ['1', 'Shift 1', 'shift 1', 'SHIFT 1'])) {
                    $query->where('nama', 'like', '%Shift 1%');
                } elseif (in_array($shift, ['2', 'Shift 2', 'shift 2', 'SHIFT 2'])) {
                    $query->where('nama', 'like', '%Shift 2%');
                } elseif (in_array($shift, ['3', 'Shift 3', 'shift 3', 'SHIFT 3'])) {
                    $query->where('nama', 'like', '%Shift 3%');
                }
            }
        }

        // Filter: Nama
        if ($request->filled('nama')) {
            $namaFilter = trim($request->nama);
            $query->where('nama', 'like', "%{$namaFilter}%");
        }

        // Filter: Nama Produk
        if ($request->filled('nama_produk')) {
            $produkFilter = trim($request->nama_produk);
            $query->where('nama_produk', 'like', "%{$produkFilter}%");
        }

        return $query;
    }

    /**
     * Display a listing of pendataan with filters and summary.
     */
    public function index(Request $request)
    {
        $this->enforceStaffSession();

        $activeStaffNama = session('pendataan_staff_nama') ?? auth()->user()->nama;

        $query = $this->buildQuery($request);

        // Summary calculations based on filtered data
        $summaryQuery = clone $query;
        $totalTransaksi = $summaryQuery->count();
        $totalQty = (int) $summaryQuery->sum('qty');
        $totalNominal = (float) $summaryQuery->sum('total_harga');

        // Paginated list
        $pendataans = $query->paginate(25)->withQueryString();

        // Get predefined names and any additional existing names from DB (clean base names for staff filter dropdown)
        $dbRawNames = Pendataan::select('nama')->distinct()->whereNotNull('nama')->pluck('nama')->toArray();
        $cleanedDbNames = array_map(function ($n) {
            return trim(preg_replace('/\s*\(\s*Shift\s*\d+\s*\)\s*$/i', '', $n));
        }, $dbRawNames);

        $daftarNama = array_values(array_filter(array_unique(array_merge(Pendataan::DAFTAR_NAMA, $cleanedDbNames))));
        sort($daftarNama, SORT_NATURAL | SORT_FLAG_CASE);

        $uniqueProducts = Pendataan::select('nama_produk')->distinct()->whereNotNull('nama_produk')->pluck('nama_produk');

        return view('pendataan.index', [
            'title' => 'Pendataan',
            'menuPendataan' => 'active',
            'pendataans' => $pendataans,
            'totalTransaksi' => $totalTransaksi,
            'totalQty' => $totalQty,
            'totalNominal' => $totalNominal,
            'daftarNama' => $daftarNama,
            'uniqueProducts' => $uniqueProducts,
            'activeStaffNama' => $activeStaffNama,
            'shiftLoginAt' => session('pendataan_staff_login_at'),
            'shiftExpiresAt' => session('pendataan_staff_expires_at'),
            'filters' => [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'shift' => $request->shift ?: 'All Shift',
                'nama' => $request->nama,
                'nama_produk' => $request->nama_produk,
            ],
        ]);
    }

    /**
     * Export pendataan records to Excel (.xlsx).
     */
    public function exportExcel(Request $request)
    {
        $this->enforceStaffSession();

        $items = $this->buildQuery($request)->get();

        $activeShift = $request->input('shift', 'All Shift');
        $shiftSuffix = (!empty($activeShift) && !in_array($activeShift, ['all', 'All', 'All Shift']))
            ? '-' . str_replace(' ', '', $activeShift)
            : '';

        $filename = 'Laporan-Pendataan' . $shiftSuffix . '-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(
            new PendataanExport($items, [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'shift' => $activeShift,
                'nama' => $request->nama,
                'nama_produk' => $request->nama_produk,
            ]),
            $filename
        );
    }

    /**
     * Export pendataan records to PDF (.pdf).
     */
    public function exportPdf(Request $request)
    {
        $this->enforceStaffSession();

        $items = $this->buildQuery($request)->get();
        $totalTransaksi = $items->count();
        $totalQty = (int) $items->sum('qty');
        $totalNominal = (float) $items->sum('total_harga');

        $activeShift = $request->input('shift', 'All Shift');
        $filters = [
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'shift' => $activeShift,
            'nama' => $request->nama,
            'nama_produk' => $request->nama_produk,
        ];

        $shiftSuffix = (!empty($activeShift) && !in_array($activeShift, ['all', 'All', 'All Shift']))
            ? '-' . str_replace(' ', '', $activeShift)
            : '';

        $pdf = Pdf::loadView('pendataan.pdf', compact('items', 'filters', 'totalTransaksi', 'totalQty', 'totalNominal'))
            ->setPaper('a4', 'landscape');

        $filename = 'Laporan-Pendataan' . $shiftSuffix . '-' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * AJAX endpoint to parse text on demand.
     */
    public function parseText(Request $request)
    {
        $this->enforceStaffSession();

        $text = (string) $request->input('text', '');
        $parsed = PendataanParserService::parse($text);

        return response()->json([
            'success' => true,
            'data' => $parsed,
        ]);
    }

    /**
     * Store a newly created pendataan record.
     */
    public function store(Request $request)
    {
        $this->enforceStaffSession();

        // ----------------------------------------------------------------
        // Server-side idempotency guard: block duplicate submissions
        // submitted within 10 seconds with the same token.
        // ----------------------------------------------------------------
        $token = (string) $request->input('_idempotency_token', '');
        if ($token !== '') {
            $cacheKey = 'pendataan_submit_' . auth()->id() . '_' . $token;
            if (cache()->has($cacheKey)) {
                return redirect()->route('pendataan.index')
                    ->with('warning', 'Data sudah berhasil disimpan sebelumnya (duplikasi dicegah).');
            }
            // Store the token for 10 seconds to block retries
            cache()->put($cacheKey, true, now()->addSeconds(10));
        }

        $request->validate([
            'deskripsi' => 'nullable|string',
            'nama_produk' => 'nullable|string|max:255',
            'harga_qty' => 'nullable',
            'total_harga' => 'nullable',
            'qty' => 'nullable|integer|min:1',
            'gambar' => 'nullable|file|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'gambar_base64' => 'nullable|string',
        ], [
            'gambar.mimes' => 'Format gambar harus jpeg, png, jpg, webp, atau gif.',
            'gambar.max' => 'Ukuran gambar maksimal 10MB.',
        ]);

        $deskripsi = $request->input('deskripsi', '');
        $parsed = PendataanParserService::parse($deskripsi);

        // Resolve product name (use input if provided, otherwise fallback to parsed)
        $namaProduk = trim((string) $request->input('nama_produk'));
        if ($namaProduk === '') {
            $namaProduk = $parsed['nama_produk'] ?: 'Produk Tanpa Nama';
        }

        // Resolve unit price
        $hargaQty = $request->filled('harga_qty')
            ? PendataanParserService::cleanPrice((string) $request->input('harga_qty'))
            : $parsed['harga_qty'];

        // Resolve qty
        $qty = $request->filled('qty') ? (int) $request->input('qty') : $parsed['qty'];
        if ($qty <= 0) {
            $qty = 1;
        }

        // Resolve total price
        $totalHarga = $request->filled('total_harga')
            ? PendataanParserService::cleanPrice((string) $request->input('total_harga'))
            : $parsed['total_harga'];

        if ($totalHarga <= 0 && $hargaQty > 0) {
            $totalHarga = $hargaQty * $qty;
        }

        if ($hargaQty <= 0 && $totalHarga > 0 && $qty > 0) {
            $hargaQty = round($totalHarga / $qty, 2);
        }

        // Handle Image upload or pasted base64 image
        $gambarPath = null;
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('pendataan', 'public');
        } elseif ($request->filled('gambar_base64')) {
            $gambarPath = $this->saveBase64Image($request->input('gambar_base64'));
        }

        // Strictly lock nama to authenticated staff session
        $nama = session('pendataan_staff_nama') ?: (auth()->user()->nama ?: 'Staff');

        Pendataan::create([
            'user_id' => auth()->id(),
            'nama' => $nama,
            'deskripsi' => $deskripsi,
            'nama_produk' => $namaProduk,
            'harga_qty' => $hargaQty,
            'total_harga' => $totalHarga,
            'qty' => $qty,
            'gambar' => $gambarPath,
        ]);

        return redirect()->route('pendataan.index')->with('success', 'Data Pendataan berhasil disimpan atas nama ' . $nama . '!');
    }

    /**
     * Show single record detail (for modal or AJAX).
     */
    public function show($id)
    {
        $this->enforceStaffSession();

        $pendataan = Pendataan::with('user')->findOrFail($id);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $pendataan->id,
                    'nama' => $pendataan->nama,
                    'nama_produk' => $pendataan->nama_produk,
                    'harga_qty' => $pendataan->harga_qty,
                    'formatted_harga_qty' => $pendataan->formatted_harga_qty,
                    'total_harga' => $pendataan->total_harga,
                    'formatted_total_harga' => $pendataan->formatted_total_harga,
                    'qty' => $pendataan->qty,
                    'deskripsi' => $pendataan->deskripsi,
                    'alasan_edit' => $pendataan->alasan_edit,
                    'gambar_url' => $pendataan->gambar_url,
                    'created_at' => $pendataan->created_at->format('d/m/Y H:i'),
                    'user_nama' => $pendataan->user?->nama ?? '-',
                ],
            ]);
        }

        return redirect()->route('pendataan.index');
    }

    /**
     * Update an existing pendataan record.
     */
    public function update(Request $request, $id)
    {
        $this->enforceStaffSession();

        $pendataan = Pendataan::findOrFail($id);

        $request->validate([
            'deskripsi' => 'nullable|string',
            'nama_produk' => 'required|string|max:255',
            'harga_qty' => 'required',
            'total_harga' => 'required',
            'qty' => 'required|integer|min:1',
            'alasan_edit' => 'required|string|min:3|max:1000',
            'gambar' => 'nullable|file|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'gambar_base64' => 'nullable|string',
            'hapus_gambar' => 'nullable|boolean',
        ], [
            'alasan_edit.required' => 'Alasan edit wajib diisi!',
            'alasan_edit.min' => 'Alasan edit minimal 3 karakter.',
        ]);

        $hargaQty = PendataanParserService::cleanPrice((string) $request->input('harga_qty'));
        $qty = (int) $request->input('qty', 1);
        if ($qty <= 0) {
            $qty = 1;
        }
        $totalHarga = PendataanParserService::cleanPrice((string) $request->input('total_harga'));
        if ($totalHarga <= 0 && $hargaQty > 0) {
            $totalHarga = $hargaQty * $qty;
        }

        // Handle image update or removal
        $gambarPath = $pendataan->gambar;

        if ($request->boolean('hapus_gambar')) {
            if ($gambarPath && Storage::disk('public')->exists($gambarPath)) {
                Storage::disk('public')->delete($gambarPath);
            }
            $gambarPath = null;
        }

        if ($request->hasFile('gambar')) {
            if ($gambarPath && Storage::disk('public')->exists($gambarPath)) {
                Storage::disk('public')->delete($gambarPath);
            }
            $gambarPath = $request->file('gambar')->store('pendataan', 'public');
        } elseif ($request->filled('gambar_base64')) {
            if ($gambarPath && Storage::disk('public')->exists($gambarPath)) {
                Storage::disk('public')->delete($gambarPath);
            }
            $gambarPath = $this->saveBase64Image($request->input('gambar_base64'));
        }

        // Audit-trail edit reason with timestamp and staff name
        $editor = session('pendataan_staff_nama') ?: (auth()->user()->nama ?: 'Staff');
        $inputAlasan = trim($request->input('alasan_edit'));
        $entryAlasan = '[' . date('d/m/Y H:i') . ' - ' . $editor . ']: ' . $inputAlasan;
        $alasanFinal = $pendataan->alasan_edit
            ? $pendataan->alasan_edit . "\n" . $entryAlasan
            : $entryAlasan;

        // Preserve original nama - cannot be altered
        $pendataan->update([
            'nama' => $pendataan->nama,
            'deskripsi' => $request->input('deskripsi', ''),
            'nama_produk' => trim($request->input('nama_produk')),
            'harga_qty' => $hargaQty,
            'total_harga' => $totalHarga,
            'qty' => $qty,
            'gambar' => $gambarPath,
            'alasan_edit' => $alasanFinal,
        ]);

        return redirect()->route('pendataan.index')->with('success', 'Data Pendataan berhasil diperbarui!');
    }

    /**
     * Delete a pendataan record.
     */
    public function destroy($id)
    {
        $this->enforceStaffSession();

        $pendataan = Pendataan::findOrFail($id);

        if ($pendataan->gambar && Storage::disk('public')->exists($pendataan->gambar)) {
            Storage::disk('public')->delete($pendataan->gambar);
        }

        $pendataan->delete();

        return redirect()->route('pendataan.index')->with('success', 'Data Pendataan berhasil dihapus!');
    }

    /**
     * Save base64 image data to public storage.
     */
    private function saveBase64Image(string $base64String): ?string
    {
        if (!preg_match('/^data:image\/(\w+);base64,/', $base64String, $matches)) {
            return null;
        }

        $imageType = strtolower($matches[1]);
        if (!in_array($imageType, ['jpeg', 'jpg', 'png', 'webp', 'gif'])) {
            $imageType = 'png';
        }

        $imageData = substr($base64String, strpos($base64String, ',') + 1);
        $decodedData = base64_decode($imageData);

        if ($decodedData === false) {
            return null;
        }

        $fileName = 'pendataan/' . Str::uuid() . '.' . $imageType;
        Storage::disk('public')->put($fileName, $decodedData);

        return $fileName;
    }
}
