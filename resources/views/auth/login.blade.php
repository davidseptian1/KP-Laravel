<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta-name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — E-SLM</title>

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html {
            width: 100%; height: 100%;
            font-family: "Segoe UI", sans-serif;
            overflow: hidden;
        }

        /* VIDEO BACKGROUND */
        .video-bg {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            object-fit: cover;
            z-index: -1;
            filter: brightness(0.55);
        }

        /* CENTER WRAPPER */
        .login-wrapper {
            height: 100vh;
            display: flex; justify-content: center; align-items: center;
            padding: 20px;
        }

        /* CARD */
        .login-card {
            width: 100%; max-width: 400px;
            background: rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(12px);
            padding: 35px; border-radius: 16px;
            box-shadow: 0 0 25px rgba(0,0,0,0.45);
            color: white;
            animation: fadeIn 0.9s ease-in-out;
            text-align: center;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(25px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* LOGO */
        .logo {
            width: 80px;
            margin: 0 auto 12px auto;
        }

        h2.title { font-size: 26px; font-weight: bold; margin-bottom: 5px; }
        p.subtitle { font-size: 14px; opacity: 0.9; margin-bottom: 20px; }

        /* INPUT LABEL + BOX */
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
            width: 100%; padding: 12px;
            background: #2d6cdf;
            border: none; border-radius: 8px;
            font-size: 15px; font-weight: 600;
            color: white; cursor: pointer;
            transition: 0.25s; letter-spacing: 0.3px;
        }

        .btn-login:hover { background: #1f53b8; }

        /* OTHER TEXT */
        .forgot-area {
            margin-top: 15px;
            font-size: 14px;
        }

        .forgot-area a {
            color: #fff; text-decoration: none; 
            opacity: 0.8; margin: 0 5px;
        }

        .footer {
            margin-top: 15px;
            font-size: 12px;
            opacity: 0.7;
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

            <!-- LOGO -->
            <img src="{{ asset('sbadmin2/img/logo_chika.png') }}" class="logo" alt="Logo">

            <h2 class="title">Login</h2>
            <p class="subtitle">E-SLM — Sistem Monitoring Transaksi</p>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="input-group">
                    <label class="input-label">Email</label>
                    <input type="email" name="email" placeholder="Masukkan email Anda" required>
                </div>

                <!-- Password -->
                <div class="input-group">
                    <label class="input-label">Password</label>
                    <input type="password" name="password" id="password" placeholder="Masukkan password" required>
                </div>

                <button type="submit" class="btn-login">Masuk ke Sistem</button>
            </form>

            <!-- LUPA PASSWORD -->
            <div class="forgot-area">
                <a href="https://wa.me/6287719952225?text=Halo%20Admin,%20saya%20lupa%20password%20E-SLM">
                    Butuh Bantuan?
                </a>
            </div>

            <div class="footer">
                © 2025 PT Chika — All Rights Reserved
            </div>
        </div>
    </div>

</body>
</html>
