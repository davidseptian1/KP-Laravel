<?php

namespace App\Http\Controllers\LoanRequest;

use App\Http\Controllers\Controller;
use App\Models\LoanRequestForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminLoanRequestFormController extends Controller
{
    public function index()
    {
        $items = LoanRequestForm::with('creator')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.loan-request.forms', [
            'title' => 'Peminjaman Barang Form',
            'menuAdminLoanRequestForm' => 'active',
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'expires_at' => 'nullable|date',
        ]);

        $kodeForm = $this->generateKodeForm();
        $expiresAt = null;
        if (!empty($validated['expires_at'])) {
            $expiresAt = Carbon::parse($validated['expires_at'])->endOfDay();
        }

        LoanRequestForm::create([
            'kode_form' => $kodeForm,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'token' => Str::random(32),
            'is_active' => true,
            'expires_at' => $expiresAt,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.loan-request.forms')->with('success', 'Form peminjaman barang berhasil dibuat');
    }

    public function toggle(int $id)
    {
        $form = LoanRequestForm::findOrFail($id);
        $form->is_active = !$form->is_active;
        $form->save();

        return redirect()->route('admin.loan-request.forms')->with('success', 'Status form diperbarui');
    }

    private function generateKodeForm(): string
    {
        $datePart = now()->format('Ymd');
        $prefix = "PB-{$datePart}-";

        $last = LoanRequestForm::where('kode_form', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $nextNumber = 1;
        if ($last) {
            $lastNumber = (int) Str::after($last->kode_form, $prefix);
            $nextNumber = $lastNumber + 1;
        }

        return $prefix . str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
