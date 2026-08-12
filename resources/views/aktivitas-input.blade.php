<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktifitas Input - SIPENA</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; background: #f5f8ff; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        body { color: #102a43; }
        .topbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 2rem; background: #ffffff; box-shadow: 0 1px 12px rgba(15, 23, 42, 0.08); position: sticky; top: 0; z-index: 20; }
        .brand { display: flex; align-items: center; gap: 0.85rem; text-decoration: none; }
        .brand img { width: 48px; height: 48px; object-fit: contain; }
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
        .page-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.75rem; }
        .page-title { margin: 0; font-size: 1.75rem; font-weight: 800; }
        .page-subtitle { margin: 0.5rem 0 0; color: #64748b; }
        .filter-panel { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1.75rem; padding: 1.5rem; background: #ffffff; border-radius: 1.5rem; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.05); }
        .filter-panel input,
        .filter-panel select,
        .filter-panel button { width: 100%; min-height: 48px; border: 1px solid #dbe4ef; border-radius: 0.85rem; background: #ffffff; color: #0f172a; padding: 0 1rem; font-size: 0.95rem; }
        .filter-panel input { box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.05); }
        .filter-panel button { border: none; background: #ffffff; color: #1d4ed8; font-weight: 700; cursor: pointer; transition: transform 0.2s ease, background-color 0.2s ease; }
        .filter-panel button:hover { background: #eff6ff; transform: translateY(-1px); }
        .table-card { background: #edf4ff; border-radius: 1.5rem; overflow: hidden; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08); }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 1100px; }
        th, td { padding: 1rem 1.2rem; text-align: left; vertical-align: middle; }
        th { background: #1d4ed8; color: #ffffff; font-size: 0.95rem; font-weight: 700; }
        tr { border-bottom: 1px solid rgba(15, 23, 42, 0.08); }
        tr:nth-child(even) { background: #e5edff; }
        tr:nth-child(odd) { background: #f8fbff; }
        td { color: #102a43; font-size: 0.95rem; }
        .status-pill { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.4rem 0.8rem; border-radius: 999px; font-size: 0.85rem; font-weight: 700; }
        .status-new { background: #dcf0ff; color: #0b4da8; }
        .empty-state { padding: 3rem 1rem; text-align: center; color: #64748b; font-size: 1rem; }
        @media (max-width: 1120px) {
            .filter-panel { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 768px) {
            .topbar { flex-wrap: wrap; justify-content: center; }
            .nav-links { width: 100%; justify-content: center; flex-wrap: wrap; }
            .page-header { flex-direction: column; align-items: flex-start; }
            .filter-panel { grid-template-columns: 1fr; }
            .table-card { padding: 0.75rem; }
            table { min-width: 900px; }
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
            <a class="nav-link active" href="{{ route('aktivitas.input') }}">Aktifitas Input</a>
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
        <header class="page-header">
            <div>
                <h1 class="page-title">Aktifitas Input</h1>
                <p class="page-subtitle">Rekaman aktivitas input akan ditampilkan di sini setelah pengguna mengisi data.</p>
            </div>
        </header>

        <section class="filter-panel">
            <input type="search" placeholder="Cari NISN / Nama Siswa">
            <select>
                <option>Semua Operator</option>
            </select>
            <input type="date">
            <button type="button"><i class="fas fa-rotate-right"></i> Reset Filter</button>
        </section>

        <section class="table-card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Operator</th>
                            <th>NISN</th>
                            <th>Nama Siswa</th>
                            <th>Tempat, Tanggal Lahir</th>
                            <th>L/P</th>
                            <th>Alamat</th>
                            <th>Waktu Input</th>
                            <th>Status</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="10" class="empty-state">Belum ada data aktivitas input. Data akan muncul setelah pengguna melakukan input.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
