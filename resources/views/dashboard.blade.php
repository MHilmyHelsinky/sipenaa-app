<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Super Admin - SIPENA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; background: #f5f8ff; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        body { color: #102a43; }
        .topbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 0.2rem 2rem; background: #ffffff; box-shadow: 0 1px 12px rgba(15, 23, 42, 0.08); position: sticky; top: 0; z-index: 20; height: 90px; }
        .brand { display: flex; align-items: center; gap: 0.5rem; text-decoration: none; }
        .brand img { width: 150px; height: 150px; object-fit: contain; }
        .brand-text { font-weight: 800; font-size: 1rem; letter-spacing: 0.02em; color: #102a43; }
        .nav-links { display: flex; align-items: center; gap: 0.75rem; }
        .nav-link { padding: 0.75rem 1rem; border-radius: 999px; color: #475569; text-decoration: none; font-weight: 700; transition: background 0.2s ease, color 0.2s ease; }
        .nav-link.active { background: #cfe2ff; color: #1d4ed8; }
        .nav-link:hover { background: #e8effe; color: #1d4ed8; }
        .profile-dropdown { position: relative; }
        .profile-summary { display: flex; align-items: center; gap: 0.85rem; cursor: pointer; border: none; background: transparent; color: inherit; font: inherit; }
        .profile-summary::-webkit-details-marker { display: none; }
        .profile-name { font-weight: 700; color: #102a43; }
        .profile-circle { width: 46px; height: 46px; border-radius: 50%; background: #0b53a3; color: #ffffff; display: grid; place-items: center; font-size: 1rem; font-weight: 800; }
        .profile-menu { position: absolute; right: 0; top: calc(100% + 0.75rem); min-width: 180px; background: #ffffff; border: 1px solid rgba(15,23,42,0.12); box-shadow: 0 18px 45px rgba(15,23,42,0.12); border-radius: 1rem; overflow: hidden; opacity: 0; visibility: hidden; transform: translateY(-5px); transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease; z-index: 30; }
        .profile-dropdown[open] .profile-menu { opacity: 1; visibility: visible; transform: translateY(0); }
        .profile-menu a,
        .profile-menu button { display: block; width: 100%; padding: 0.85rem 1rem; background: transparent; border: none; text-align: left; color: #0f172a; font-size: 0.95rem; cursor: pointer; text-decoration: none; }
        .profile-menu a:hover,
        .profile-menu button:hover { background: #eff6ff; }
        .profile-menu form { margin: 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .hero { background: linear-gradient(90deg, #1c64f2 0%, #0b53a3 100%); border-radius: 1.5rem; color: #ffffff; padding: 1.75rem 2rem; position: relative; overflow: hidden; }
        .hero::before { content: ''; position: absolute; width: 180px; height: 180px; border-radius: 50%; background: rgba(255,255,255,0.18); top: -30px; right: 30px; }
        .hero h1 { margin: 0; font-size: 1.95rem; line-height: 1.2; letter-spacing: -0.03em; }
        .hero .tag { display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(255,255,255,0.16); padding: 0.4rem 0.85rem; border-radius: 999px; font-weight: 700; font-size: 0.85rem; margin-bottom: 1rem; }
        .summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1.75rem; margin-top: 1.5rem; }
        .card { background: #ffffff; border-radius: 1.5rem; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08); padding: 2rem; display: flex; align-items: center; gap: 1.25rem; min-height: 150px; }
        .card .icon { width: 56px; height: 56px; border-radius: 18px; display: grid; place-items: center; font-size: 1.35rem; color: #1d4ed8; }
        .card.total .icon { background: rgba(59,130,246,0.14); }
        .card.input .icon { background: rgba(59,130,246,0.14); }
        .card.active .icon { background: rgba(59,130,246,0.14); }
        .card .label { font-size: 0.95rem; color: #64748b; margin-bottom: 0.45rem; }
        .card .value { font-size: 2rem; font-weight: 800; color: #102a43; }
        .card small { display: block; margin-top: 0.35rem; color: #94a3b8; }
        .table-section { margin-top: 3rem; }
        .table-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem; }
        .table-head h2 { margin: 0; font-size: 1.35rem; font-weight: 800; color: #102a43; }
        .table-head p { margin: 0; color: #64748b; }
        .table-card { background: #edf4ff; border-radius: 1.5rem; overflow: hidden; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08); padding: 1.25rem; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 700px; }
        th, td { padding: 1.25rem 1.4rem; text-align: left; }
        th { background: #1d4ed8; color: #ffffff; font-size: 0.95rem; font-weight: 700; }
        tr { border-bottom: 1px solid rgba(15, 23, 42, 0.08); }
        tr:nth-child(even) { background: #dbeafe; }
        tr:nth-child(odd) { background: #eff6ff; }
        td { color: #102a43; font-size: 0.95rem; }
        .status-pill { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.45rem 0.85rem; border-radius: 999px; font-size: 0.85rem; font-weight: 700; }
        .status-active { background: #dcf0ff; color: #0b4da8; }
        .status-inactive { background: #fee2e2; color: #b91c1c; }
        .footer-note { margin-top: 0.75rem; color: #64748b; font-size: 0.92rem; }
        @media (max-width: 960px) {
            .topbar { flex-wrap: wrap; justify-content: center; }
            .nav-links { width: 100%; justify-content: center; flex-wrap: wrap; }
            .summary-grid { grid-template-columns: 1fr; }
            .table-head { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <nav class="topbar">
        <a class="brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('images/'.rawurlencode('logo dalam sipena.png')) }}" alt="SIPENA Logo">
            <span class="brand-text">SIPENA</span>
        </a>
        <div class="nav-links">
            <a class="nav-link active" href="{{ route('dashboard') }}">Dashboard</a>
            <a class="nav-link" href="{{ route('aktivitas.input') }}">Aktifitas Input</a>
            <a class="nav-link" href="{{ route('manajemen.pengguna') }}">Manajemen pengguna</a>
        </div>
        <details class="profile-dropdown">
            <summary class="profile-summary">
                <div class="profile-name">{{ Auth::user()->name }}</div>
                <div class="profile-circle">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            </summary>
            <div class="profile-menu">
                <a href="{{ route('profile.edit') }}">Profil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Keluar</button>
                </form>
            </div>
        </details>
    </nav>

    <main class="container">
        <section class="hero">
            <div class="tag">Dashboard Super Admin</div>
            <h1>Pusat kendali persetujuan dan pantauan aktivitas sistem SIPENA</h1>
        </section>

        <section class="summary-grid">
            <div class="card total">
                <div class="icon"><i class="fas fa-users"></i></div>
                <div>
                    <div class="label">Total Petugas</div>
                    <div class="value">{{ $totalPetugas }}</div>
                    <small>Akun terdaftar</small>
                </div>
            </div>
            <div class="card input">
                <div class="icon"><i class="fas fa-clipboard-list"></i></div>
                <div>
                    <div class="label">Input Hari Ini</div>
                    <div class="value">{{ $inputToday }}</div>
                    <small>Data diinput di input</small>
                </div>
            </div>
            <div class="card active">
                <div class="icon"><i class="fas fa-user-check"></i></div>
                <div>
                    <div class="label">Petugas Aktif Hari Ini</div>
                    <div class="value">{{ $activeToday }}</div>
                    <small>Sedang Aktif</small>
                </div>
            </div>
        </section>

        <section class="table-section">
            <div class="table-head">
                <div>
                    <h2>Daftar Petugas Aktif</h2>
                    <p>Data diambil langsung dari akun pengguna yang terdaftar di sistem.</p>
                </div>
            </div>
            <div class="table-card">
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Petugas</th>
                                <th>Email</th>
                                <th>Jam Aktif</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeUsers as $index => $user)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->username }}</td>
                                    <td>{{ optional($user->last_login_at)->format('H:i') ?? '-' }}</td>
                                    <td>
                                        <span class="status-pill {{ $user->is_active ? 'status-active' : 'status-inactive' }}">
                                            {{ $user->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 2rem; color: #64748b;">Tidak ada petugas aktif saat ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <p class="footer-note">Semua angka di dashboard ini berasal langsung dari akun pengguna yang ada di sistem.</p>
        </section>
    </main>
</body>
</html>
