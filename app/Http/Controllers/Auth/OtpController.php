<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Carbon\Carbon;

class OtpController extends Controller
{
    public function index()
    {
        if (!session('otp_user_id')) {
            return redirect()->route('login');
        }

        $user = User::find(session('otp_user_id'));
        return view('auth.otp', compact('user'));
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|array|size:6',
            'otp.*' => 'required|numeric|digits:1',
        ]);

        $otpCode = implode('', $request->otp);
        $userId = session('otp_user_id');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Sesi verifikasi berakhir.');
        }

        $user = User::find($userId);

        if ($user->otp_code == $otpCode && Carbon::now()->lt($user->otp_expires_at)) {
            // Bersihkan OTP
            $user->update([
                'otp_code' => null,
                'otp_expires_at' => null,
            ]);

            Auth::login($user);
            session()->forget('otp_user_id');

            return redirect()->route('dashboard')->with('success', 'Berhasil verifikasi dan login.');
        }

        return redirect()->back()->with('error', 'Kode OTP salah atau sudah kedaluwarsa.');
    }

    public function resend()
    {
        $userId = session('otp_user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::find($userId);
        
        $otp = rand(100000, 999999);
        $user->update([
            'otp_code' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new OtpMail($otp));

        return redirect()->back()->with('success', 'Kode OTP baru telah dikirim ke email Anda.');
    }
}
