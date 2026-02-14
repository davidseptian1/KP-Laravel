<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\DepositForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepositFormController extends Controller
{
    public function show(string $token)
    {
        $form = DepositForm::where('token', $token)->firstOrFail();

        if (!$form->is_active) {
            abort(404, 'Form tidak aktif');
        }

        if ($form->expires_at && now()->greaterThan($form->expires_at->copy()->endOfDay())) {
            abort(404, 'Form sudah kedaluwarsa');
        }

        return view('staff.deposit.form', [
            'title' => 'Form Deposit',
            'menuDeposit' => 'active',
            'form' => $form,
        ]);
    }

    public function submit(Request $request, string $token)
    {
        $form = DepositForm::where('token', $token)->firstOrFail();

        if (!$form->is_active) {
            abort(404, 'Form tidak aktif');
        }

        if ($form->expires_at && now()->greaterThan($form->expires_at->copy()->endOfDay())) {
            abort(404, 'Form sudah kedaluwarsa');
        }

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
            'form_id' => $form->id,
            'nama_supplier' => $validated['nama_supplier'],
            'nominal' => $validated['nominal'],
            'bank' => $validated['bank'],
            'server' => $validated['server'],
            'no_rek' => $validated['no_rek'],
            'nama_rekening' => $validated['nama_rekening'],
            'reply_penambahan' => $validated['reply_penambahan'] ?? null,
            'jam' => $validated['jam'],
        ]);

        return redirect()->back()->with('success', 'Deposit berhasil dikirim');
    }
}
