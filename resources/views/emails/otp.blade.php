<!DOCTYPE html>
<html>
<head>
    <title>Kode OTP</title>
</head>
<body style="font-family: sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">
        <h2 style="color: #2d6cdf; text-align: center;">Verifikasi Akun</h2>
        <p>Halo,</p>
        <p>Gunakan kode OTP berikut untuk melanjutkan proses login Anda:</p>
        <div style="text-align: center; margin: 30px 0;">
            <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; background: #f4f4f4; padding: 10px 20px; border-radius: 5px; border: 1px dashed #2d6cdf;">
                {{ $otp }}
            </span>
        </div>
        <p>Kode ini akan kedaluwarsa dalam 10 menit. Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini.</p>
        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 12px; color: #777; text-align: center;">
            © {{ date('Y') }} PT Chika — All Rights Reserved
        </p>
    </div>
</body>
</html>
