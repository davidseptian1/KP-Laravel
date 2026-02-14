<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminDepositController extends Controller
{
    public function form()
    {
        return view('admin.deposit.form', [
            'title' => 'Form Deposit',
            'menuAdminDepositForm' => 'active',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'bank' => 'required|string|max:100',
            'server' => 'required|string|max:100',
            'no_rek' => 'required|string|max:100',
            'nama_rekening' => 'required|string|max:255',
            'reply_penambahan' => 'nullable|string',
            'jam' => 'required|date_format:H:i',
        ]);

        Deposit::create([
            'user_id' => Auth::id(),
            'nama_supplier' => $validated['nama_supplier'],
            'nominal' => $validated['nominal'],
            'bank' => $validated['bank'],
            'server' => $validated['server'],
            'no_rek' => $validated['no_rek'],
            'nama_rekening' => $validated['nama_rekening'],
            'reply_penambahan' => $validated['reply_penambahan'] ?? null,
            'jam' => $validated['jam'],
        ]);

        return redirect()->route('admin.deposit.form')->with('success', 'Deposit berhasil disimpan');
    }

    public function monitoring(Request $request)
    {
        $items = Deposit::orderByDesc('created_at')->paginate(15);

        return view('admin.deposit.monitoring', [
            'title' => 'Monitoring Deposit',
            'menuAdminDepositMonitoring' => 'active',
            'items' => $items,
        ]);
    }

    public function analysis(Request $request)
    {
        $user = Auth::user();
        if (!$user || !in_array($user->jabatan, ['Admin', 'HRD'], true)) {
            abort(403, 'Akses ditolak');
        }

        $summary = Deposit::selectRaw('COUNT(*) as total, SUM(nominal) as total_nominal, AVG(nominal) as avg_nominal, MIN(nominal) as min_nominal, MAX(nominal) as max_nominal')
            ->first();

        $byBank = Deposit::selectRaw('bank, COUNT(*) as jumlah, SUM(nominal) as total')
            ->groupBy('bank')
            ->orderByDesc('total')
            ->get();

        $byServer = Deposit::selectRaw('server, COUNT(*) as jumlah, SUM(nominal) as total')
            ->groupBy('server')
            ->orderByDesc('total')
            ->get();

        $bySupplier = Deposit::selectRaw('nama_supplier, COUNT(*) as jumlah, SUM(nominal) as total')
            ->groupBy('nama_supplier')
            ->orderByDesc('total')
            ->get();

        return view('admin.deposit.analysis', [
            'title' => 'Analisis Deposit',
            'menuDepositAnalysis' => 'active',
            'summary' => $summary,
            'byBank' => $byBank,
            'byServer' => $byServer,
            'bySupplier' => $bySupplier,
        ]);
    }
}
