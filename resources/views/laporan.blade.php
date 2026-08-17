<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - SIPENA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; background: #f5f8ff; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        body { color: #102a43; }
        .topbar { display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; padding: 0.25rem 2rem; max-height: 90px; background: #ffffff; box-shadow: 0 1px 12px rgba(15, 23, 42, 0.08); position: sticky; top: 0; z-index: 20; }
        .brand { display: flex; align-items: center; gap: 0.85rem; text-decoration: none; }
        .brand img { width: 150px; height: auto; max-height: 80px; object-fit: contain; }
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
        .profile-menu a, .profile-menu button { display: block; width: 100%; padding: 0.85rem 1rem; background: transparent; border: none; text-align: left; color: #0f172a; font-size: 0.95rem; cursor: pointer; text-decoration: none; }
        .profile-menu a:hover, .profile-menu button:hover { background: #eff6ff; }
        .profile-menu form { margin: 0; }
        .container { max-width: 1280px; margin: 0 auto; padding: 2rem; }
        .page-header { margin-bottom: 1.5rem; }
        .page-title { margin: 0; font-size: 1.75rem; font-weight: 800; }
        .page-subtitle { margin: 0.5rem 0 0; color: #64748b; }
        .page-card { background: #ffffff; border-radius: 1.5rem; padding: 1.75rem; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08); }
        .toolbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem; }
        .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.1rem; border-radius: 0.875rem; text-decoration: none; font-weight: 700; cursor: pointer; border: none; font-family: inherit; }
        .btn-primary { background: linear-gradient(135deg, #1d4ed8, #2563eb); color: #ffffff; box-shadow: 0 12px 24px rgba(37, 99, 235, 0.2); }
        .btn-primary:hover { background: linear-gradient(135deg, #1e40af, #1d4ed8); }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 1100px; }
        th, td { padding: 0.9rem 0.9rem; text-align: left; }
        th { background: #7fb0d5; color: #ffffff; font-weight: 700; }
        tr { border-bottom: 1px solid rgba(15, 23, 42, 0.08); }
        tr:nth-child(even) { background: #f8fafc; }
        .empty-state { text-align: center; color: #64748b; padding: 1.5rem 0; }
        .status-badge { display: inline-flex; align-items: center; padding: 0.35rem 0.7rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; }
        .status-ok { background: #dcfce7; color: #166534; }
        .status-wait { background: #fee2e2; color: #b91c1c; }
        @media (max-width: 720px) { .topbar { flex-wrap: wrap; justify-content: center; } .nav-links { width: 100%; justify-content: center; flex-wrap: wrap; } .toolbar { flex-direction: column; align-items: stretch; } }
    </style>
</head>
<body>
    <nav class="topbar">
        <a class="brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('images/'.rawurlencode('logo dalam sipena.png')) }}" alt="SIPENA Logo">
            <span class="brand-text">SIPENA</span>
        </a>
        <div class="nav-links">
            <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
            <a class="nav-link" href="{{ route('pembuatan.kartu') }}">Pembuatan Kartu Baru</a>
            <a class="nav-link" href="{{ route('data.kartu') }}">Data Kartu</a>
            <a class="nav-link active" href="{{ route('laporan') }}">Laporan</a>
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
        <section class="page-header">
            <h1 class="page-title">Laporan</h1>
            <p class="page-subtitle">Data siswa yang tersedia untuk laporan dan ekspor Excel.</p>
        </section>
        <section class="page-card">
            <div class="toolbar">
                <div>
                    <strong>{{ $cards->count() }}</strong> data laporan
                </div>
                <a href="{{ route('laporan.export') }}" class="btn btn-primary">
                    <i class="fa-solid fa-file-excel"></i>
                    Unduh Excel
                </a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>NISN</th>
                            <th>Nama</th>
                            <th>Tempat Lahir</th>
                            <th>Tanggal Lahir</th>
                            <th>Alamat</th>
                            <th>Jenis Kelamin</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cards as $card)
                            <tr>
                                <td>{{ $card['nisn'] }}</td>
                                <td>{{ $card['nama_lengkap'] }}</td>
                                <td>{{ $card['tempat_lahir'] }}</td>
                                <td>{{ $card['tanggal_lahir'] }}</td>
                                <td>{{ $card['alamat'] }}</td>
                                <td>{{ $card['jenis_kelamin'] }}</td>
                                <td>
                                    @if($card['keterangan'] === 'Sudah cetak')
                                        <span class="status-badge status-ok">Sudah cetak</span>
                                    @else
                                        <span class="status-badge status-wait">Belum cetak</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="empty-state">Belum ada data laporan untuk ditampilkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
