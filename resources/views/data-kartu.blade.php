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
        .topbar { display:flex; align-items:center; justify-content:space-between; gap:.75rem; padding:.25rem 2rem; max-height:90px; background:#fff; box-shadow:0 1px 12px rgba(15,23,42,.08); position:sticky; top:0; z-index:20; }
        .brand { display:flex; align-items:center; gap:.85rem; text-decoration:none; }
        .brand img { width:150px; height:auto; max-height:80px; object-fit:contain; }
        .brand-text { font-weight:800; font-size:1rem; color:#102a43; }
        .nav-links { display:flex; align-items:center; gap:.75rem; flex-wrap:wrap; justify-content:center; }
        .nav-link { padding:.75rem 1rem; border-radius:999px; color:#475569; text-decoration:none; font-weight:700; }
        .nav-link.active,.nav-link:hover { background:#cfe2ff; color:#1d4ed8; }
        .profile-dropdown{position:relative}.profile-summary{display:flex;align-items:center;gap:.85rem;cursor:pointer;border:none;background:transparent;color:inherit;font:inherit}.profile-summary::-webkit-details-marker{display:none}.profile-name{font-weight:700}.profile-circle{width:46px;height:46px;border-radius:50%;background:#1d4ed8;color:#fff;display:grid;place-items:center;font-weight:800}
        .profile-menu{position:absolute;right:0;top:calc(100% + .75rem);min-width:180px;background:#fff;border:1px solid rgba(15,23,42,.12);box-shadow:0 18px 45px rgba(15,23,42,.12);border-radius:1rem;overflow:hidden;opacity:0;visibility:hidden;transform:translateY(-5px);transition:.18s;z-index:30}.profile-dropdown[open] .profile-menu{opacity:1;visibility:visible;transform:translateY(0)}.profile-menu a,.profile-menu button{display:block;width:100%;padding:.85rem 1rem;background:transparent;border:none;text-align:left;color:#0f172a;font-size:.95rem;cursor:pointer;text-decoration:none}.profile-menu a:hover,.profile-menu button:hover{background:#eff6ff}.profile-menu form{margin:0}
        .container{max-width:1400px;margin:0 auto;padding:2rem}.page-header{margin-bottom:1.5rem}.page-title{margin:0;font-size:1.75rem;font-weight:800}.page-subtitle{margin:.5rem 0 0;color:#64748b}.page-card{background:#fff;border-radius:1.5rem;padding:1.75rem;box-shadow:0 20px 40px rgba(15,23,42,.08)}
        .toolbar{display:flex;align-items:end;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem}.filter-section{display:grid;grid-template-columns:minmax(220px,1fr) auto auto;gap:1rem;align-items:end;flex:1}.form-group{display:flex;flex-direction:column;gap:.25rem}.form-label{font-size:.875rem;font-weight:600;color:#475569}.form-input,.form-select{padding:.625rem .875rem;border:1px solid #d5dde7;border-radius:.75rem;font-size:.95rem;font-family:inherit}.btn{padding:.625rem 1rem;border-radius:.75rem;border:none;font-weight:700;cursor:pointer;font-size:.95rem}.btn-primary{background:#1d4ed8;color:#fff}.btn-green{background:#16a34a;color:#fff}.btn-light{background:#e0e9ff;color:#1d4ed8;border:1px solid #1d4ed8}
        .print-tools{display:flex;align-items:center;gap:.65rem;flex-wrap:wrap}.selected-count{font-size:.9rem;color:#64748b;font-weight:700}.layout-select{padding:.6rem .75rem;border:1px solid #d5dde7;border-radius:.7rem;background:#fff;font-weight:700}
        .table-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:1rem}.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse;min-width:1000px}th,td{padding:1rem 1.1rem;text-align:left}th{background:#7fb0d5;color:#fff;font-weight:700}tr{border-bottom:1px solid rgba(15,23,42,.08)}tr:nth-child(even){background:#f8fafc}.empty-state{text-align:center;color:#64748b;padding:1.5rem 0}.check-cell{width:50px;text-align:center}.row-check{width:18px;height:18px;cursor:pointer}.status{display:inline-flex;padding:.35rem .65rem;border-radius:999px;font-size:.8rem;font-weight:800}.status-done{background:#dcfce7;color:#15803d}.status-pending{background:#fef3c7;color:#a16207}
        @media(max-width:1024px){.topbar{flex-wrap:wrap;justify-content:center}.filter-section{grid-template-columns:1fr 1fr}.toolbar{align-items:stretch}}@media(max-width:720px){.filter-section{grid-template-columns:1fr}.print-tools{width:100%}.print-tools>*{flex:1}}
    </style>
</head>
<body>
<nav class="topbar">
    <a class="brand" href="{{ route('dashboard') }}"><img src="{{ asset('images/'.rawurlencode('logo dalam sipena.png')) }}" alt="SIPENA Logo"><span class="brand-text">SIPENA</span></a>
    <div class="nav-links"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a><a class="nav-link" href="{{ route('pembuatan.kartu') }}">Pembuatan Kartu Baru</a><a class="nav-link active" href="{{ route('data.kartu') }}">Data Kartu</a><a class="nav-link" href="{{ route('laporan') }}">Laporan</a></div>
    <details class="profile-dropdown"><summary class="profile-summary"><div class="profile-name">{{ Auth::user()->name }}</div><div class="profile-circle">{{ strtoupper(substr(Auth::user()->name,0,1)) }}</div></summary><div class="profile-menu"><a href="{{ route('profile.edit') }}">Profil</a><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form></div></details>
</nav>

<main class="container">
    <section class="page-header"><h1 class="page-title">Data Kartu</h1><p class="page-subtitle">Pilih beberapa kartu untuk dicetak sekaligus. Layout 4 kartu/lembar direkomendasikan agar hasil tetap terbaca.</p></section>
    <section class="page-card">
        <div class="toolbar">
            <form method="GET" action="{{ route('data.kartu') }}" class="filter-section">
                <div class="form-group"><label class="form-label" for="search">Cari NISN atau Nama Siswa</label><input type="text" id="search" name="search" class="form-input" placeholder="Cari..." value="{{ $search }}"></div>
                <div class="form-group"><label class="form-label" for="waktu_input">Waktu Input</label><input type="date" id="waktu_input" name="waktu_input" class="form-input" value="{{ $waktuInput }}"></div>
                <div style="display:flex;gap:.5rem"><button type="submit" class="btn btn-light">Filter</button><a href="{{ route('data.kartu') }}" class="btn btn-light" style="text-decoration:none">Reset</a></div>
            </form>
            <form method="POST" action="{{ route('cetak.massal') }}" class="print-tools" id="batchPrintForm">
                @csrf
                <span class="selected-count" id="selectedCount">0 terpilih</span>
                <select class="layout-select" name="per_page" aria-label="Layout cetak"><option value="4">4 kartu / lembar</option><option value="5">5 kartu / lembar</option></select>
                <button type="submit" class="btn btn-green"><i class="fa-solid fa-print"></i> Cetak Terpilih</button>
            </form>
        </div>

        <div class="table-head"><div><h2>Daftar Kartu Siswa</h2><p class="page-subtitle">Klik baris kartu untuk melihat preview. Gunakan checkbox untuk mencetak banyak kartu.</p></div><span>{{ $cards->count() }} kartu tersimpan</span></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th class="check-cell"><input id="selectAll" type="checkbox" class="row-check" title="Pilih semua"></th><th>No</th><th>NISN</th><th>Nama Lengkap</th><th>Tempat Lahir</th><th>Tanggal Lahir</th><th>L/P</th><th>Alamat</th><th>Waktu Input</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($cards as $index => $card)
                        <tr>
                            <td class="check-cell"><input form="batchPrintForm" type="checkbox" class="row-check card-check" name="card_ids[]" value="{{ $card->id }}"></td>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $card->nisn }}</td>
                            <td>{{ $card->nama_lengkap }}</td>
                            <td>{{ $card->tempat_lahir }}</td>
                            <td>{{ optional($card->tanggal_lahir)->format('d/m/Y') }}</td>
                            <td>{{ $card->jenis_kelamin }}</td>
                            <td>{{ trim(($card->desa ? $card->desa . ', ' : '') . ($card->kecamatan ? $card->kecamatan . ', ' : '') . ($card->kabupaten ?? '')) }}</td>
                            <td>{{ optional($card->created_at)->timezone('Asia/Jakarta')->format('d/m/Y H:i') }} WIB</td>
                            <td><span class="status {{ $card->printed_at ? 'status-done' : 'status-pending' }}">{{ $card->printed_at ? 'Sudah cetak' : 'Belum cetak' }}</span></td>
                            <td><a href="{{ route('preview.kartu', ['card' => $card->id]) }}" style="color:#1d4ed8;text-decoration:none;font-weight:700">Preview</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="empty-state">Belum ada data kartu tersimpan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>
const checks = () => [...document.querySelectorAll('.card-check')];
const selectedCount = document.getElementById('selectedCount');
const selectAll = document.getElementById('selectAll');
function refreshCount(){ const count = checks().filter(c => c.checked).length; selectedCount.textContent = `${count} terpilih`; if(selectAll) selectAll.checked = count > 0 && count === checks().length; }
checks().forEach(c => c.addEventListener('change', refreshCount));
selectAll?.addEventListener('change', e => { checks().forEach(c => c.checked = e.target.checked); refreshCount(); });
document.getElementById('batchPrintForm').addEventListener('submit', e => { if(checks().every(c => !c.checked)){ e.preventDefault(); alert('Pilih minimal satu kartu terlebih dahulu.'); } });
refreshCount();
</script>
</body>
</html>
