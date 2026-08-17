<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kartu - SIPENA</title>
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
        .profile-circle { width: 46px; height: 46px; border-radius: 50%; background: #1d4ed8; color: #ffffff; display: grid; place-items: center; font-size: 1rem; font-weight: 800; }
        .profile-menu { position: absolute; right: 0; top: calc(100% + 0.75rem); min-width: 180px; background: #ffffff; border: 1px solid rgba(15,23,42,0.12); box-shadow: 0 18px 45px rgba(15,23,42,0.12); border-radius: 1rem; overflow: hidden; opacity: 0; visibility: hidden; transform: translateY(-5px); transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease; z-index: 30; }
        .profile-dropdown[open] .profile-menu { opacity: 1; visibility: visible; transform: translateY(0); }
        .profile-menu a, .profile-menu button { display: block; width: 100%; padding: 0.85rem 1rem; background: transparent; border: none; text-align: left; color: #0f172a; font-size: 0.95rem; cursor: pointer; text-decoration: none; }
        .profile-menu a:hover, .profile-menu button:hover { background: #eff6ff; }
        .profile-menu form { margin: 0; }
        .container { max-width: 1400px; margin: 0 auto; padding: 2rem; }
        .page-header { margin-bottom: 1.5rem; }
        .page-title { margin: 0; font-size: 1.75rem; font-weight: 800; }
        .page-subtitle { margin: 0.5rem 0 0; color: #64748b; }
        .page-card { background: #ffffff; border-radius: 1.5rem; padding: 1.75rem; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08); }
        .filter-section { display: grid; grid-template-columns: 1fr auto auto auto; gap: 1rem; margin-bottom: 1.5rem; align-items: flex-end; }
        .form-group { display: flex; flex-direction: column; gap: 0.25rem; }
        .form-label { font-size: 0.875rem; font-weight: 600; color: #475569; }
        .form-input, .form-select { padding: 0.625rem 0.875rem; border: 1px solid #d5dde7; border-radius: 0.75rem; font-size: 0.95rem; font-family: inherit; }
        .form-input:focus, .form-select:focus { outline: none; border-color: #1d4ed8; box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1); }
        .btn { padding: 0.625rem 1rem; border-radius: 0.75rem; border: none; font-weight: 600; cursor: pointer; font-size: 0.95rem; transition: background 0.2s ease; }
        .btn-reset { background: #e0e9ff; color: #1d4ed8; border: 1px solid #1d4ed8; }
        .btn-reset:hover { background: #1d4ed8; color: #ffffff; }
        .table-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }
        .table-head span { color: #64748b; font-weight: 700; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th, td { padding: 1rem 1.25rem; text-align: left; }
        th { background: #7fb0d5; color: #ffffff; font-weight: 700; }
        tr { border-bottom: 1px solid rgba(15, 23, 42, 0.08); }
        tr:nth-child(even) { background: #f8fafc; }
        .empty-state { text-align: center; color: #64748b; padding: 1.5rem 0; }
        @media (max-width: 1024px) {
            .filter-section { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 720px) {
            .topbar { flex-wrap: wrap; justify-content: center; }
            .nav-links { width: 100%; justify-content: center; flex-wrap: wrap; }
            .filter-section { grid-template-columns: 1fr; }
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
            <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
            <a class="nav-link" href="{{ route('pembuatan.kartu') }}">Pembuatan Kartu Baru</a>
            <a class="nav-link active" href="{{ route('data.kartu') }}">Data Kartu</a>
            <a class="nav-link" href="{{ route('laporan') }}">Laporan</a>
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
            <h1 class="page-title">Data Kartu</h1>
            <p class="page-subtitle">Lihat dan kelola data kartu siswa Anda sendiri.</p>
        </section>
        <section class="page-card">
            <div class="filter-section">
                <form method="GET" action="{{ route('data.kartu') }}" style="display: contents;">
                    <div class="form-group">
                        <label class="form-label" for="search">Cari NISN atau Nama Siswa</label>
                        <input 
                            type="text" 
                            id="search" 
                            name="search" 
                            class="form-input" 
                            placeholder="Cari..." 
                            value="{{ $search }}"
                        >
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="waktu_input">Waktu Input</label>
                        <input 
                            type="date" 
                            id="waktu_input" 
                            name="waktu_input" 
                            class="form-input" 
                            value="{{ $waktuInput }}"
                        >
                    </div>
                    <button type="submit" class="btn btn-reset">Filter</button>
                    <a href="{{ route('data.kartu') }}" class="btn btn-reset">Reset Filter</a>
                </form>
            </div>
            <div class="table-head">
                <div>
                    <h2>Daftar Kartu Siswa yang Sudah Ada</h2>
                    <p class="page-subtitle">Lihat ringkasan kartu siswa yang sudah tersimpan.</p>
                </div>
                <span>{{ $cards->count() }} kartu tersimpan</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NISN</th>
                            <th>Nama Lengkap</th>
                            <th>Tempat Lahir</th>
                            <th>Tanggal Lahir</th>
                            <th>L/P</th>
                            <th>Alamat</th>
                            <th>Waktu Input</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cards as $index => $card)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $card->nisn }}</td>
                                <td>{{ $card->nama_lengkap }}</td>
                                <td>{{ $card->tempat_lahir }}</td>
                                <td>{{ optional($card->tanggal_lahir)->format('d/m/Y') }}</td>
                                <td>{{ $card->jenis_kelamin }}</td>
                                <td>{{ trim(($card->desa ? $card->desa . ', ' : '') . ($card->kecamatan ? $card->kecamatan . ', ' : '') . ($card->kabupaten ?? '')) }}</td>
                                <td>{{ optional($card->created_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('preview.kartu', ['card' => $card->id]) }}" style="color: #1d4ed8; text-decoration: none; font-weight: 600;">Cetak</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="empty-state">Belum ada data kartu tersimpan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
