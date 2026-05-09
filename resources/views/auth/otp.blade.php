<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi 2 Langkah | e-SMT</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body,
        html {
            width: 100%;
            height: 100%;
            font-family: "Segoe UI", sans-serif;
            overflow: hidden;
        }

        .video-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: -1;
            filter: brightness(0.55);
        }

        .login-wrapper {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 450px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            color: #333;
            animation: fadeIn 0.6s ease-out;
            text-align: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2.title {
            font-size: 28px;
            font-weight: 800;
            color: #1e3a8a;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        p.subtitle {
            font-size: 15px;
            color: #64748b;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .info-box {
            background: #f0f7ff;
            border: 1px solid #e0efff;
            padding: 15px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
            text-align: left;
            margin-bottom: 25px;
        }

        .info-icon {
            color: #3b82f6;
            font-size: 24px;
        }

        .info-text {
            font-size: 13px;
            color: #1e40af;
            line-height: 1.4;
        }

        .otp-container {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 30px;
        }

        .otp-input {
            width: 55px;
            height: 65px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 24px;
            font-weight: 700;
            text-align: center;
            color: #1e3a8a;
            outline: none;
            transition: all 0.2s;
        }

        .otp-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .btn-verify {
            width: 100%;
            padding: 14px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }

        .btn-verify:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
        }

        .resend-area {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }

        .resend-text {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 10px;
        }

        .btn-resend {
            background: none;
            border: none;
            color: #2563eb;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            margin: 0 auto;
        }

        .back-link {
            margin-top: 25px;
            display: block;
            font-size: 14px;
            color: #64748b;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        /* TOAST - Reuse from login if needed, or simple alert */
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .alert-success { background: #dcfce7; color: #166534; }
    </style>
</head>

<body>
    <!-- VIDEO BACKGROUND -->
    <video autoplay muted loop class="video-bg">
        <source src="{{ asset('sbadmin2/img/vidio.mp4') }}" type="video/mp4">
    </video>

    <div class="login-wrapper">
        <div class="login-card">
            <h2 class="title">Verifikasi 2 Langkah 💬</h2>
            <p class="subtitle">Masukkan kode 6 digit yang kami kirimkan untuk melanjutkan login.</p>

            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="info-box">
                <div class="info-icon">✉️</div>
                <div class="info-text">
                    Kode OTP dikirim ke <strong>email</strong> terdaftar Anda. Periksa kotak masuk/spam.
                </div>
            </div>

            <form action="{{ route('otp.verify') }}" method="POST">
                @csrf
                <div class="otp-container">
                    <input type="text" name="otp[]" class="otp-input" maxlength="1" required autofocus>
                    <input type="text" name="otp[]" class="otp-input" maxlength="1" required>
                    <input type="text" name="otp[]" class="otp-input" maxlength="1" required>
                    <input type="text" name="otp[]" class="otp-input" maxlength="1" required>
                    <input type="text" name="otp[]" class="otp-input" maxlength="1" required>
                    <input type="text" name="otp[]" class="otp-input" maxlength="1" required>
                </div>

                <button type="submit" class="btn-verify">Verifikasi Akun Saya</button>
            </form>

            <div class="resend-area">
                <p class="resend-text">Tidak menerima kode OTP?</p>
                <form action="{{ route('otp.resend') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-resend">🔄 Kirim Ulang Kode</button>
                </form>
            </div>

            <a href="{{ route('login') }}" class="back-link">⬅️ Kembali ke Login</a>
        </div>
    </div>

    <script>
        const inputs = document.querySelectorAll('.otp-input');
        
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            // Hanya izinkan angka
            input.addEventListener('keypress', (e) => {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>

</html>
