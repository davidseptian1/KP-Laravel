<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <!-- FIX META -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | e-SMT</title>

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

        /* VIDEO BACKGROUND */
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

        /* CENTER WRAPPER */
        .login-wrapper {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* CARD */
        .login-card {
            width: 100%;
            max-width: 400px;
            background: rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(12px);
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.45);
            color: white;
            animation: fadeIn 0.9s ease-in-out;
            text-align: center;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(25px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* LOGO */
        .logo {
            width: 80px;
            margin: 0 auto 12px auto;
        }

        h2.title {
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        p.subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 20px;
        }

        /* INPUT */
        .input-group {
            margin-bottom: 18px;
            text-align: left;
        }

        .input-label {
            display: block;
            font-size: 13px;
            margin-bottom: 6px;
            color: #f1f1f1;
            opacity: 0.9;
            font-weight: 500;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: none;
            font-size: 14px;
            outline: none;
        }

        .input-group input:focus {
            box-shadow: 0 0 0 2px #4a90e2;
        }

        /* BUTTON */
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #2d6cdf;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            color: white;
            cursor: pointer;
            transition: 0.25s;
        }

        .btn-login:hover {
            background: #1f53b8;
        }

        /* OTHER */
        .forgot-area {
            margin-top: 15px;
            font-size: 14px;
        }

        .forgot-area a {
            color: #fff;
            text-decoration: none;
            opacity: 0.8;
        }

        .footer {
            margin-top: 15px;
            font-size: 12px;
            opacity: 0.7;
        }

        /* ===================== */
        /* TOAST LOGIN - FIX */
        /* ===================== */
        .toast {
            position: fixed;
            bottom: 25px;
            right: 25px;
            min-width: 300px;
            max-width: 360px;

            /* WARNA AMAN */
            background: rgba(127, 127, 127, 0.88);

            /* Glass effect (aman) */
            backdrop-filter: blur(6px);

            color: #ffffff;
            border-radius: 14px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.45);

            display: flex;
            gap: 14px;
            padding: 14px 16px;

            animation: slideUp 0.4s ease;
            z-index: 99999;
        }

        /* ERROR */
        .toast-error {
            border-left: 5px solid #4a90e2;
        }

        /* SUCCESS */
        .toast-success {
            border-left: 5px solid #6fcf97;
        }

        .toast-icon {
            font-size: 20px;
            line-height: 1;
            margin-top: 2px;
            color: #9fc5ff;
        }

        .toast-success .toast-icon {
            color: #6fcf97;
        }

        .toast-content {
            flex: 1;
        }

        .toast-title {
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.4px;
            margin-bottom: 3px;
        }

        .toast-text {
            font-size: 13px;
            opacity: 0.95;
        }

        /* ANIMATION */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>

    <!-- VIDEO BACKGROUND -->
    <video autoplay muted loop class="video-bg">
        <source src="{{ asset('sbadmin2/img/vidio.mp4') }}" type="video/mp4">
    </video>

    <div class="login-wrapper">
        <div class="login-card">

            <img src="{{ asset('sbadmin2/img/logo_chika.png') }}" class="logo" alt="Logo">

            <h2 class="title">Login</h2>
            <p class="subtitle">e-SMT — Sistem Monitoring Transaksi</p>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="input-group">
                    <label class="input-label">Email</label>
                    <input type="email" name="email" placeholder="Masukkan email Anda" required>
                </div>

                <div class="input-group">
                    <label class="input-label">Password</label>
                    <input type="password" name="password" placeholder="Masukkan password" required>
                </div>

                <button type="submit" class="btn-login">Masuk ke Sistem</button>
            </form>

            <div class="forgot-area">
                <a href="https://wa.me/6287719952225?text=Halo%20Admin,%20saya%20lupa%20password%20e-SMT">
                    Butuh Bantuan?
                </a>
            </div>

            <div class="footer">
                © 2025 PT Chika — All Rights Reserved
            </div>
        </div>
    </div>

    <!-- TOAST ERROR -->
    @if (session('error'))
    <div class="toast toast-error" id="toast">
        <div class="toast-icon">⛔</div>
        <div class="toast-content">
            <div class="toast-title">ERROR</div>
            <div class="toast-text">
                {{ session('error') }}
            </div>
        </div>
    </div>
    @endif


    <!-- TOAST SUCCESS -->
    @if (session('success'))
    <div class="toast toast-success" id="toast">
        <div class="toast-icon">✅</div>
        <div class="toast-content">
            <div class="toast-title">SUCCESS</div>
            <div class="toast-text">
                {{ session('success') }}
            </div>
        </div>
    </div>
    @endif

    <!-- AUTO HIDE -->
    <script>
        setTimeout(() => {
            const toast = document.getElementById('toast');
            if (toast) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(20px)';
                setTimeout(() => toast.remove(), 300);
            }
        }, 3500);
    </script>

</body>

</html>