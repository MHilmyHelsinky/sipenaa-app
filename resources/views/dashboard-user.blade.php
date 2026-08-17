<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - SIPENA</title>
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
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .hero { position: relative; background: linear-gradient(90deg, #1c64f2 0%, #0b53a3 100%); border-radius: 1.5rem; color: #ffffff; padding: 1.5rem; overflow: hidden; }
        .hero::before { content: ''; position: absolute; width: 180px; height: 180px; border-radius: 50%; background: rgba(255,255,255,0.18); top: -20px; right: 20px; }
        .hero h1 { margin: 0 0 0.75rem; font-size: 1.9rem; line-height: 1.1; }
        .hero p { margin: 0; color: rgba(255,255,255,0.92); font-size: 1rem; max-width: 38rem; line-height: 1.5; }
        .hero-details { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .hero-meta { display: inline-flex; align-items: center; gap: 0.75rem; background: rgba(255,255,255,0.18); padding: 0.85rem 1rem; border-radius: 1rem; font-size: 0.95rem; min-width: 210px; }
        .stats-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1.25rem; margin-top: 1.75rem; }
        .stat-card { background: #ffffff; border-radius: 1.5rem; padding: 1.5rem; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08); min-height: 140px; display: flex; flex-direction: column; justify-content: space-between; }
        .stat-card .icon { width: 52px; height: 52px; border-radius: 18px; display: grid; place-items: center; color: #1d4ed8; background: rgba(59,130,246,0.14); margin-bottom: 1rem; }
        .stat-card .label { font-size: 0.95rem; color: #64748b; margin-bottom: 0.65rem; }
        .stat-card .value { font-size: 2rem; font-weight: 800; color: #102a43; }
        .stat-card small { display: block; margin-top: 0.5rem; color: #94a3b8; }
        .section { margin-top: 2rem; }
        .section-card { background: #ffffff; border-radius: 1.5rem; padding: 1.75rem; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08); }
        .section-card h2 { margin: 0; font-size: 1.2rem; color: #102a43; }
        .section-card-header { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem; }
        .section-card-summary { background: #eff6ff; color: #0f4cd1; padding: 0.85rem 1rem; border-radius: 999px; font-weight: 700; white-space: nowrap; }
        .section-card-subtitle { margin: 0.35rem 0 0; color: #64748b; font-size: 0.95rem; }
        .chart-card { overflow: hidden; }
        .chart-head { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1rem; }
        .chart-badge { display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.55rem 0.8rem; border-radius: 999px; background: #f8fbff; border: 1px solid #e2e8f0; color: #475569; font-size: 0.82rem; font-weight: 700; }
        .chart-stage { position: relative; height: 340px; border-radius: 1.25rem; background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%); border: 1px solid #e2e8f0; padding: 1.25rem 1.25rem 1rem; overflow: hidden; }
        .chart-grid { position: absolute; inset: 1.5rem 1.25rem 3.4rem 3.6rem; display: flex; flex-direction: column; justify-content: space-between; pointer-events: none; }
        .grid-line { border-top: 1px dashed #dbe4ef; width: 100%; }
        .chart-ylabels { position: absolute; left: 0.65rem; top: 1.5rem; bottom: 3.4rem; width: 2.2rem; display: flex; flex-direction: column; justify-content: space-between; align-items: flex-end; color: #94a3b8; font-size: 0.72rem; font-weight: 700; }
        .bars { position: absolute; left: 3.6rem; right: 1.25rem; bottom: 3.35rem; top: 1.2rem; display: grid; grid-template-columns: repeat(6, 1fr); gap: 1rem; align-items: end; }
        .bar-col { height: 100%; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; gap: 0.55rem; min-width: 0; }
        .bar-value { color: #0b53a3; font-size: 0.82rem; font-weight: 800; }
        .bar-track { width: min(52px, 65%); height: calc(100% - 2.2rem); border-radius: 16px 16px 7px 7px; background: linear-gradient(180deg, #e5eefb, #f5f8fd); display: flex; align-items: flex-end; overflow: hidden; box-shadow: inset 0 1px 2px rgba(15,23,42,0.04); }
        .bar-fill { width: 100%; min-height: 6px; border-radius: 16px 16px 7px 7px; background: linear-gradient(180deg, #60a5fa 0%, #1d4ed8 100%); box-shadow: 0 8px 18px rgba(29,78,216,0.2); transition: height 0.35s ease; }
        .bar-label { color: #64748b; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; }
        .chart-foot { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.85rem; color: #94a3b8; font-size: 0.8rem; }
        .table-wrap { overflow-x: auto; }
        .section-card table { width: 100%; border-collapse: collapse; min-width: 760px; }
        .section-card th, .section-card td { padding: 0.95rem 1rem; border-bottom: 1px solid #e2e8f0; text-align: left; }
        .section-card th { color: #334155; font-weight: 700; background: #f8fbff; }
        .section-card tbody tr:hover { background: #f8fafc; }
        .empty-state { text-align: center; color: #64748b; padding: 1.25rem 0; }
        .input-time { font-variant-numeric: tabular-nums; white-space: nowrap; font-weight: 700; color: #334155; }
        @media (max-width: 1024px) { .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 720px) { .topbar { flex-wrap: wrap; justify-content: center; } .hero-details { flex-direction: column; align-items: flex-start; } .stats-grid { grid-template-columns: 1fr; } .chart-stage { height: 300px; } .bars { gap: 0.4rem; left: 3rem; } .chart-grid { left: 3rem; } .chart-ylabels { width: 1.7rem; } }
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
            <a class="nav-link" href="{{ route('pembuatan.kartu') }}">Pembuatan Kartu Baru</a>
            <a class="nav-link" href="{{ route('data.kartu') }}">Data Kartu</a>
            <a class="nav-link" href="{{ route('laporan') }}">Laporan</a>
        </div>
        <details class="profile-dropdown">
            <summary class="profile-summary">
                <div class="profile-name">{{ Auth::user()->name }}</div>
                <div class="profile-circle">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
            </summary>
            <div class="profile-menu">
                <a href="{{ route('profile.edit') }}">Profil</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form>
            </div>
        </details>
    </nav>

    <main class="container">
        <section class="hero">
            <div class="hero-details">
                <div><h1>Selamat Datang</h1><p>Kelola Data NISN Siswa dengan mudah, akurat, dan terintegrasi.</p></div>
                <div class="hero-meta"><i class="fas fa-calendar-days"></i><div><div>{{ $currentDate }}</div><div style="font-weight:700">{{ $currentTime }} WIB</div></div></div>
            </div>
        </section>

        <section class="stats-grid">
            <div class="stat-card"><div class="icon"><i class="fas fa-id-card"></i></div><div><div class="label">NISN Terdaftar</div><div class="value">{{ number_format($nisnTerdaftar, 0, ',', '.') }}</div><small>Total seluruh NISN terdaftar</small></div></div>
            <div class="stat-card"><div class="icon"><i class="fas fa-print"></i></div><div><div class="label">Sudah Cetak</div><div class="value">{{ number_format($sudahCetak, 0, ',', '.') }}</div><small>Siswa dengan NISN tercetak</small></div></div>
            <div class="stat-card"><div class="icon"><i class="fas fa-file-circle-xmark"></i></div><div><div class="label">Belum Cetak</div><div class="value">{{ number_format($belumCetak, 0, ',', '.') }}</div><small>NISN yang belum tercetak</small></div></div>
            <div class="stat-card"><div class="icon"><i class="fas fa-clock"></i></div><div><div class="label">Cetak Hari Ini</div><div class="value">{{ number_format($cetakHariIni, 0, ',', '.') }}</div><small>Kartu dicetak hari ini</small></div></div>
        </section>

        <section class="section">
            <div class="section-card chart-card">
                <div class="chart-head"><div><h2>Aktivitas Percetakan</h2><p class="section-card-subtitle">Jumlah kartu yang benar-benar dicetak dalam 6 bulan terakhir.</p></div><div class="chart-badge"><i class="fas fa-chart-column"></i> 6 bulan</div></div>
                <div class="chart-stage">
                    @php($maxPrint = max(1, (int) ($printChart->max('count') ?? 0)))
                    @php($ticks = [4, 3, 2, 1, 0])
                    <div class="chart-grid">@foreach($ticks as $tick)<div class="grid-line"></div>@endforeach</div>
                    <div class="chart-ylabels">@foreach($ticks as $tick)<span>{{ $tick === 0 ? 0 : (int) ceil($maxPrint * ($tick / 4)) }}</span>@endforeach</div>
                    <div class="bars">
                        @foreach($printChart as $item)
                            <div class="bar-col" title="{{ $item['full_label'] ?? $item['label'] }}: {{ $item['count'] }} kartu">
                                <div class="bar-value">{{ $item['count'] }}</div>
                                <div class="bar-track"><div class="bar-fill" style="height: {{ max(3, ($item['count'] / $maxPrint) * 100) }}%"></div></div>
                                <div class="bar-label">{{ $item['label'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="chart-foot"><i class="fas fa-circle-info"></i><span>Angka mengikuti riwayat download/cetak yang tersimpan di sistem.</span></div>
            </div>
        </section>

        <section class="section">
            <div class="section-card">
                <div class="section-card-header"><div><h2>Data Input Hari Ini</h2><p class="section-card-subtitle">Data kartu siswa yang masuk ke database hari ini.</p></div><div class="section-card-summary">{{ $inputTodayCount }} input hari ini</div></div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>No</th><th>NISN</th><th>Nama Lengkap</th><th>Tempat Lahir</th><th>Tanggal Lahir</th><th>Jenis Kelamin</th><th>Waktu Input</th></tr></thead>
                        <tbody>
                            @forelse($inputTodayData as $index => $input)
                                <tr>
                                    <td>{{ $index + 1 }}</td><td>{{ $input['nisn'] }}</td><td>{{ $input['nama_lengkap'] }}</td><td>{{ $input['tempat_lahir'] }}</td><td>{{ $input['tanggal_lahir'] }}</td><td>{{ $input['jenis_kelamin'] }}</td><td class="input-time"><i class="far fa-clock" style="margin-right:.35rem;color:#64748b"></i>{{ $input['waktu_input'] }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="empty-state">Belum ada data input hari ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
