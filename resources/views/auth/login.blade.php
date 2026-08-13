<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIPENA Disdikbud Banda Aceh</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }
        html, body {
            margin: 0;
            min-height: 100%;
            height: 100%;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #ffffff;
        }
        body {
            overflow-x: hidden;
        }
        .page {
            display: grid;
            grid-template-columns: minmax(0, 62%) minmax(0, 38%);
            min-height: 100vh;
            width: 100%;
        }
        .left-panel {
            position: relative;
            overflow: hidden;
            background: linear-gradient(180deg, #1c79e1 0%, #0b53a3 100%);
            color: #ffffff;
            padding: 4rem 4rem 3.5rem 4rem;
            -webkit-clip-path: circle(60% at 26% 50%);
            clip-path: circle(60% at 26% 50%);
        }
        .left-panel::before {
            content: '';
            position: absolute;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.08);
            top: 50%;
            right: -120px;
            transform: translateY(-50%);
        }
        .left-panel::after {
            content: '';
            position: absolute;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
            bottom: -160px;
            left: -120px;
        }
        .left-panel .header {
            position: absolute;
            top: 2rem;
            left: 3rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            z-index: 2;
        }
        .left-panel .header .logo-box {
            width: 4.8rem;
            min-width: 4.8rem;
            height: 4.8rem;
            background: transparent;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        .left-panel .header .logo-box img {
            width: 90%;
            height: 90%;
            object-fit: contain;
            filter: none;
        }
        .left-panel .header .header-text {
            font-size: 0.78rem;
            line-height: 1.45;
            letter-spacing: 0.02em;
            font-weight: 700;
            opacity: 0.95;
        }
        .left-panel .content {
            position: relative;
            z-index: 2;
            max-width: 42rem;
            margin-top: 5rem;
        }
        .left-panel h1 {
            margin: 0;
            font-size: 4rem;
            font-weight: 900;
            letter-spacing: 0.12em;
            line-height: 0.95;
        }
        .left-panel .subtitle {
            font-size: 1.2rem;
            font-weight: 400;
            margin: 1rem 0 1.4rem;
            opacity: 0.95;
        }
        .left-panel .description {
            font-size: 1rem;
            font-weight: 500;
            line-height: 1.9;
            max-width: 30rem;
            margin-bottom: 3rem;
            opacity: 0.95;
        }
        .features {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }
        .feature {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.7rem;
            text-align: center;
            font-size: 0.95rem;
            font-weight: 600;
        }
        .feature i {
            font-size: 2.1rem;
            opacity: 0.95;
        }
        .feature span {
            line-height: 1.3;
        }
        .right-panel {
            position: relative;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 3rem;
            overflow: hidden;
        }
        .right-panel::after {
            content: '';
            position: absolute;
            width: 340px;
            height: 340px;
            border-radius: 50%;
            background: #0b53a3;
            bottom: -180px;
            right: -150px;
            z-index: 0;
        }
        .card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            padding: 3rem 2.5rem;
            background: #ffffff;
            border-radius: 2.5rem;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
        }
        .card h2 {
            margin: 0 0 0.5rem;
            font-size: 2rem;
            font-weight: 800;
            color: #111827;
            text-align: center;
        }
        .card .note {
            margin: 0 0 2rem;
            text-align: center;
            color: #6b7280;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .field {
            position: relative;
            margin-bottom: 1.4rem;
        }
        .field input {
            width: 100%;
            border: none;
            border-radius: 999px;
            background: #f1f3f7;
            padding: 1.2rem 1.35rem 1.2rem 4.25rem;
            font-size: 0.98rem;
            color: #1f2937;
            outline: none;
            transition: box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .field input::placeholder {
            color: #6b7280;
        }
        .field input:focus {
            border-color: transparent;
            box-shadow: 0 0 0 5px rgba(11, 83, 163, 0.08);
            background: #f8faff;
        }
        .field .icon {
            position: absolute;
            top: 50%;
            left: 1.2rem;
            transform: translateY(-50%);
            color: #475569;
            font-size: 1rem;
        }
        .field .toggle {
            position: absolute;
            top: 50%;
            right: 1.2rem;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #475569;
            cursor: pointer;
            font-size: 1.05rem;
            padding: 0;
        }
        .forgot {
            display: block;
            text-align: right;
            margin-bottom: 1.6rem;
            font-size: 0.95rem;
            font-weight: 700;
            color: #0b53a3;
            text-decoration: none;
            opacity: 0.9;
        }
        .forgot:hover {
            opacity: 1;
            color: #074a8b;
        }
        .submit {
            width: 100%;
            border: none;
            border-radius: 999px;
            padding: 1.25rem;
            font-size: 1rem;
            font-weight: 800;
            background: linear-gradient(90deg, #1f72d7 0%, #0b53a3 100%);
            color: #ffffff;
            cursor: pointer;
            box-shadow: 0 18px 40px rgba(11, 83, 163, 0.18);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 22px 48px rgba(11, 83, 163, 0.22);
        }
        .signup {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.95rem;
            color: #475569;
        }
        .signup a {
            color: #0b53a3;
            font-weight: 700;
            text-decoration: none;
        }
        .signup a:hover {
            color: #074a8b;
        }
        .floating-logo {
            position: absolute;
            left: 52%;
            top: 52%;
            transform: translate(-50%, -50%);
            width: 240px;
            height: 240px;
            border-radius: 50%;
            background: linear-gradient(180deg, #1e79df 0%, #0b53a3 100%);
            border: 12px solid #ffffff;
            box-shadow: 0 28px 68px rgba(0,0,0,0.22);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 3;
        }
        .floating-logo img {
            width: 78%;
            height: 78%;
            object-fit: contain;
            filter: none;
        }
        @media (max-width: 1200px) {
            .page {
                grid-template-columns: 55% 45%;
            }
            .left-panel {
                padding: 3rem 3rem 2.5rem 3rem;
            }
            .right-panel {
                padding: 3rem 2rem;
            }
            .floating-logo {
                width: 200px;
                height: 200px;
                left: 54%;
            }
        }
        @media (max-width: 960px) {
            .page {
                display: block;
            }
            .left-panel, .right-panel {
                width: 100%;
                min-height: auto;
            }
            .left-panel {
                padding: 3rem 2rem 2rem;
                clip-path: none;
            }
            .right-panel {
                padding: 3rem 2rem 4rem;
            }
            .floating-logo {
                left: 50%;
                top: 38%;
                width: 200px;
                height: 200px;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="left-panel">
            <div class="header">
                <div class="logo-box">
                    <img src="{{ asset('images/pemko.png') }}" alt="Logo Pemko">
                </div>
                <div class="header-text">
                    <p>Dinas Pendidikan dan Kebudayaan Kota Banda Aceh</p>
                    <p>UPT Tekkomdik</p>
                </div>
            </div>
            <div class="content">
                <h1>SELAMAT DATANG</h1>
                <p class="subtitle">Di SIPENA Disdikbud Banda Aceh</p>
                <p class="description">Sistem Informasi Pendataan dan<br>Pecetakan NISN Siswa</p>
                <div class="features">
                    <div class="feature">
                        <i class="fas fa-users"></i>
                        <span>Pendataan<br>Siswa</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-print"></i>
                        <span>Cetak kartu</span>
                    </div>
                    <div class="feature">
                        <i class="fas fa-chart-bar"></i>
                        <span>Laporan<br></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="right-panel">
            <div class="card">
                <h2>Login</h2>
                <p class="note">Masuk dengan akun admin. Akun user akan dibuat oleh admin setelah login.</p>
                @if ($errors->any())
                    <div class="mb-4 text-red-500 text-sm text-center font-semibold">
                        {{ $errors->first() }}
                    </div>
                @endif
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="field">
                        <i class="fas fa-user icon"></i>
                        <input type="text" name="username" placeholder="Username" required>
                    </div>
                    <div class="field">
                        <i class="fas fa-lock icon"></i>
                        <input type="password" name="password" id="password" placeholder="Password" required>
                        <button type="button" class="toggle" onclick="togglePassword()">
                            <i class="fas fa-eye-slash" id="toggleIcon"></i>
                        </button>
                    </div>
                    <a href="{{ route('password.request') }}" class="forgot">Forgot Password ?</a>
                    <button type="submit" class="submit">Login</button>
                </form>
                <p class="signup">
                    Silakan masuk menggunakan akun admin.
                </p>
            </div>
        </div>

        <div class="floating-logo">
            <img src="{{ asset('images/logo-sipena.png.png') }}" alt="Logo SIPENA">
        </div>
    </div>
    <script>
        function togglePassword() {
            const pw = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (pw.type === 'password') {
                pw.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                pw.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>
</html>
