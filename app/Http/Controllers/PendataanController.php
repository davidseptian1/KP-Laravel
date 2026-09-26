<?php

namespace App\Http\Controllers;

use App\Models\Pendataan;
use App\Services\PendataanParserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
     * Display a listing of pendataan with filters and summary.
     */
    public function index(Request $request)
    {
        $this->checkAccess();

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

        // Summary calculations based on filtered data
        $summaryQuery = clone $query;
        $totalTransaksi = $summaryQuery->count();
        $totalQty = (int) $summaryQuery->sum('qty');
        $totalNominal = (float) $summaryQuery->sum('total_harga');

        // Paginated list or get all for DataTables
        $pendataans = $query->paginate(25)->withQueryString();

        // Get distinct names and products for filter dropdowns/suggestions
        $uniqueNames = Pendataan::select('nama')->distinct()->whereNotNull('nama')->pluck('nama');
        $uniqueProducts = Pendataan::select('nama_produk')->distinct()->whereNotNull('nama_produk')->pluck('nama_produk');

        return view('pendataan.index', [
            'title' => 'Pendataan',
            'menuPendataan' => 'active',
            'pendataans' => $pendataans,
            'totalTransaksi' => $totalTransaksi,
            'totalQty' => $totalQty,
            'totalNominal' => $totalNominal,
            'uniqueNames' => $uniqueNames,
            'uniqueProducts' => $uniqueProducts,
            'filters' => [
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'nama' => $request->nama,
                'nama_produk' => $request->nama_produk,
            ],
        ]);
    }

    /**
     * AJAX endpoint to parse text on demand.
     */
    public function parseText(Request $request)
    {
        $this->checkAccess();

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
        $this->checkAccess();

        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'nama_produk' => 'nullable|string|max:255',
            'harga_qty' => 'nullable',
            'total_harga' => 'nullable',
            'qty' => 'nullable|integer|min:1',
            'gambar' => 'nullable|file|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'gambar_base64' => 'nullable|string',
        ], [
            'nama.required' => 'Field Nama wajib diisi.',
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

        Pendataan::create([
            'user_id' => auth()->id(),
            'nama' => trim($request->input('nama')),
            'deskripsi' => $deskripsi,
            'nama_produk' => $namaProduk,
            'harga_qty' => $hargaQty,
            'total_harga' => $totalHarga,
            'qty' => $qty,
            'gambar' => $gambarPath,
        ]);

        return redirect()->route('pendataan.index')->with('success', 'Data Pendataan berhasil disimpan!');
    }

    /**
     * Show single record detail (for modal or AJAX).
     */
    public function show($id)
    {
        $this->checkAccess();

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
        $this->checkAccess();

        $pendataan = Pendataan::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'nama_produk' => 'required|string|max:255',
            'harga_qty' => 'required',
            'total_harga' => 'required',
            'qty' => 'required|integer|min:1',
            'gambar' => 'nullable|file|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'gambar_base64' => 'nullable|string',
            'hapus_gambar' => 'nullable|boolean',
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

        $pendataan->update([
            'nama' => trim($request->input('nama')),
            'deskripsi' => $request->input('deskripsi', ''),
            'nama_produk' => trim($request->input('nama_produk')),
            'harga_qty' => $hargaQty,
            'total_harga' => $totalHarga,
            'qty' => $qty,
            'gambar' => $gambarPath,
        ]);

        return redirect()->route('pendataan.index')->with('success', 'Data Pendataan berhasil diperbarui!');
    }

    /**
     * Delete a pendataan record.
     */
    public function destroy($id)
    {
        $this->checkAccess();

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
