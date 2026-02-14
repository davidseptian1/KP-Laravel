<?php

namespace App\Http\Controllers;

use App\Models\DepositForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminDepositFormController extends Controller
{
    public function index()
    {
        $items = DepositForm::orderByDesc('created_at')->paginate(10);

        return view('admin.deposit.forms', [
            'title' => 'Form Deposit',
            'menuAdminDepositForm' => 'active',
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

        $kode = $this->generateKodeForm();

        DepositForm::create([
            'kode_form' => $kode,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'token' => Str::random(40),
            'is_active' => true,
            'expires_at' => $validated['expires_at'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.deposit.forms')->with('success', 'Form deposit berhasil dibuat');
    }

    public function toggle(int $id)
    {
        $form = DepositForm::findOrFail($id);
        $form->is_active = !$form->is_active;
        $form->save();

        return redirect()->route('admin.deposit.forms')->with('success', 'Status form berhasil diperbarui');
    }

    private function generateKodeForm(): string
    {
        $datePart = now()->format('Ymd');
        $prefix = "DP-{$datePart}-";

        $last = DepositForm::where('kode_form', 'like', $prefix . '%')
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
