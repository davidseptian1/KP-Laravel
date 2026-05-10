<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Carbon\Carbon;
use Exception;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Jika user belum ada, bisa opsional buat user baru atau tolak
                // Untuk KP-Laravel, mungkin sebaiknya cek jika email terdaftar
                return redirect()->route('login')->with('error', 'Email tidak terdaftar di sistem.');
            }

            // Update google_id
            $user->update([
                'google_id' => $googleUser->getId(),
            ]);

            // Bypass OTP untuk superadmin
            if ($user->email === 'superadmin@example.com') {
                Auth::login($user);
                return redirect()->route('dashboard')->with('success', 'Selamat Datang, Superadmin!');
            }

            // Generate OTP
            $otp = rand(100000, 999999);
            $user->update([
                'otp_code' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(10),
            ]);

            // Kirim Email OTP
            Mail::to($user->email)->send(new OtpMail($otp));

            // Simpan user_id di session sementara untuk verifikasi OTP
            session(['otp_user_id' => $user->id]);

            return redirect()->route('otp.index');

        } catch (Exception $e) {
            return redirect()->route('login')->with('error', 'Terjadi kesalahan login: ' . $e->getMessage());
        }
    }
}
