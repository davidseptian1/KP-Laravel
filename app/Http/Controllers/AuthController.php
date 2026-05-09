<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth/login');
    }

    public function loginProses(Request $request)
    {
        $request->validate(
            [
                'email'    => 'required|email',
                'password' => 'required|min:8',
            ],
            [
                'email.required'    => 'Email wajib diisi',
                'password.required' => 'Password wajib diisi',
                'password.min'      => 'Password minimal 8 karakter',
            ],
        );

        $credentials = [
            'email'    => $request->email,
            'password' => $request->password,
        ];

        // Cek kredensial tanpa langsung login
        if (Auth::validate($credentials)) {
            $user = User::where('email', $request->email)->first();

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

            // Simpan user_id di session untuk verifikasi OTP
            session(['otp_user_id' => $user->id]);

            return redirect()->route('otp.index');
        } else {
            return redirect()->back()->with('error', 'Email Atau Password Salah');
        }
    }

    public function logout(){
        Auth::logout();

        return redirect()->route('login')->with('success','Anda Berhasil Logout');
    }
}
