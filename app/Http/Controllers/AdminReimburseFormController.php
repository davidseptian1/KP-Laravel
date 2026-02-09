<?php

namespace App\Http\Controllers;

use App\Models\ReimburseForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminReimburseFormController extends Controller
{
    public function index()
    {
        $items = ReimburseForm::with('creator')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.reimburse.forms', [
            'title' => 'Reimburse Form',
            'menuAdminReimburseForm' => 'active',
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
            $expiresAt = \Carbon\Carbon::parse($validated['expires_at'])->endOfDay();
        }

        ReimburseForm::create([
            'kode_form' => $kodeForm,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'token' => Str::random(32),
            'is_active' => true,
            'expires_at' => $expiresAt,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.reimburse.forms')->with('success', 'Form reimburse berhasil dibuat');
    }

    public function toggle(int $id)
    {
        $form = ReimburseForm::findOrFail($id);
        $form->is_active = !$form->is_active;
        $form->save();

        return redirect()->route('admin.reimburse.forms')->with('success', 'Status form diperbarui');
    }

    private function generateKodeForm(): string
    {
        $datePart = now()->format('Ymd');
        $prefix = "RF-{$datePart}-";

        $last = ReimburseForm::where('kode_form', 'like', $prefix . '%')
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
