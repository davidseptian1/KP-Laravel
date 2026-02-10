<?php

namespace App\Http\Controllers\DataRequest;

use App\Http\Controllers\Controller;
use App\Models\DataRequestForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminDataRequestFormController extends Controller
{
    public function index()
    {
        $items = DataRequestForm::with('creator')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.data-request.forms', [
            'title' => 'Pengajuan Data Form',
            'menuAdminDataRequestForm' => 'active',
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

        DataRequestForm::create([
            'kode_form' => $kodeForm,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'token' => Str::random(32),
            'is_active' => true,
            'expires_at' => $expiresAt,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.data-request.forms')->with('success', 'Form pengajuan data berhasil dibuat');
    }

    public function toggle(int $id)
    {
        $form = DataRequestForm::findOrFail($id);
        $form->is_active = !$form->is_active;
        $form->save();

        return redirect()->route('admin.data-request.forms')->with('success', 'Status form diperbarui');
    }

    private function generateKodeForm(): string
    {
        $datePart = now()->format('Ymd');
        $prefix = "DF-{$datePart}-";

        $last = DataRequestForm::where('kode_form', 'like', $prefix . '%')
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
