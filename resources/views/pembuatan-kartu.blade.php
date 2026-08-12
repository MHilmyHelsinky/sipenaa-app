<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembuatan Kartu Baru - SIPENA</title>
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
        .profile-menu a, .profile-menu button { display: block; width: 100%; padding: 0.85rem 1rem; background: transparent; border: none; text-align: left; color: #0f172a; font-size: 0.95rem; cursor: pointer; text-decoration: none; }
        .profile-menu a:hover, .profile-menu button:hover { background: #eff6ff; }
        .profile-menu form { margin: 0; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .page-header { margin-bottom: 1.5rem; }
        .page-title { margin: 0; font-size: 1.75rem; font-weight: 800; }
        .page-subtitle { margin: 0.5rem 0 0; color: #64748b; }
        .page-card { background: #ffffff; border-radius: 1.5rem; padding: 1.75rem; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08); }
        .alert { border-radius: 1rem; padding: 1rem 1.25rem; margin-bottom: 1.25rem; }
        .alert.success { background: #ecfdf5; color: #0f766e; border: 1px solid #6ee7b7; }
        .alert.error { background: #ffefef; color: #991b1b; border: 1px solid #fecaca; }
        .form-grid { display: grid; grid-template-columns: 1.4fr 0.85fr; gap: 1.75rem; }
        .form-fields { display: flex; flex-direction: column; gap: 1rem; }
        .field-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
        .field-row .field-group:last-child { min-width: 0; }
        .field-group { display: flex; flex-direction: column; gap: 0.5rem; }
        .field-group label { font-weight: 700; color: #334155; }
        .field-group input[type="text"], .field-group input[type="date"] { width: 100%; border: 1px solid #cbd5e1; border-radius: 0.85rem; padding: 0.95rem 1rem; font-size: 0.95rem; color: #0f172a; }
        .field-group input[type="file"] { width: 100%; }
        .radio-row { display: flex; gap: 1rem; flex-wrap: wrap; }
        .radio-option { display: inline-flex; align-items: center; gap: 0.5rem; background: #f8fafc; padding: 0.85rem 1rem; border-radius: 999px; border: 1px solid #cbd5e1; cursor: pointer; }
        .radio-option input { accent-color: #1d4ed8; }
        .button-row { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem; }
        .button { border-radius: 999px; border: none; cursor: pointer; font-weight: 700; padding: 0.95rem 1.5rem; }
        .button-primary { background: #1d4ed8; color: #ffffff; }
        .button-secondary { background: #f8fafc; color: #1d4ed8; border: 1px solid #cbd5e1; }
        .photo-panel { background: #f8fbff; border-radius: 1.25rem; padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem; justify-content: space-between; }
        .photo-panel-title { font-weight: 700; color: #102a43; }
        .photo-preview { width: 100%; min-height: 260px; border-radius: 1rem; background: #ffffff; border: 1px dashed #cbd5e1; display: grid; place-items: center; color: #64748b; text-align: center; padding: 1rem; }
        .photo-placeholder { max-width: 100%; }
        .upload-label { display: inline-flex; align-items: center; justify-content: center; width: 100%; padding: 0.95rem 1rem; border-radius: 999px; background: #eff6ff; color: #1d4ed8; text-align: center; cursor: pointer; }
        .photo-note { color: #64748b; font-size: 0.95rem; }
        @media (max-width: 980px) { .form-grid { grid-template-columns: 1fr; } .field-row { grid-template-columns: 1fr; } }
        @media (max-width: 720px) { .topbar { flex-wrap: wrap; justify-content: center; } .nav-links { width: 100%; justify-content: center; flex-wrap: wrap; } }
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
            <a class="nav-link active" href="{{ route('pembuatan.kartu') }}">Pembuatan Kartu Baru</a>
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
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Keluar</button>
                </form>
            </div>
        </details>
    </nav>

    <main class="container">
        <section class="page-header">
            <h1 class="page-title">Pembuatan Kartu Baru</h1>
            <p class="page-subtitle">Isi data siswa di bawah ini dan unggah foto untuk menyimpan kartu ke database.</p>
        </section>
        <section class="page-card">
            @if(session('success'))
                <div class="alert success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert error">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('pembuatan.kartu.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-grid">
                    <div class="form-fields">
                        <div class="field-group">
                            <label for="nisn">NISN</label>
                            <input type="text" id="nisn" name="nisn" value="{{ old('nisn') }}" placeholder="Masukkan NISN siswa" required>
                        </div>
                        <div class="field-group">
                            <label for="nama_lengkap">Nama Siswa</label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" placeholder="Masukkan nama lengkap" required>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label for="tempat_lahir">Tempat Lahir</label>
                                <input type="text" id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir') }}" placeholder="Tempat lahir" required>
                            </div>
                            <div class="field-group">
                                <label for="tanggal_lahir">Tanggal Lahir</label>
                                <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required>
                            </div>
                        </div>
                        <div class="field-group">
                            <label>Jenis Kelamin</label>
                            <div class="radio-row">
                                <label class="radio-option">
                                    <input type="radio" name="jenis_kelamin" value="Laki-laki" {{ old('jenis_kelamin') === 'Laki-laki' ? 'checked' : '' }} required>
                                    <span>Laki-laki</span>
                                </label>
                                <label class="radio-option">
                                    <input type="radio" name="jenis_kelamin" value="Perempuan" {{ old('jenis_kelamin') === 'Perempuan' ? 'checked' : '' }} required>
                                    <span>Perempuan</span>
                                </label>
                            </div>
                        </div>
                        <div class="field-row">
                            <div class="field-group">
                                <label for="desa">Desa</label>
                                <input type="text" id="desa" name="desa" value="{{ old('desa') }}" placeholder="Desa">
                            </div>
                            <div class="field-group">
                                <label for="kecamatan">Kecamatan</label>
                                <input type="text" id="kecamatan" name="kecamatan" value="{{ old('kecamatan') }}" placeholder="Kecamatan">
                            </div>
                            <div class="field-group">
                                <label for="kabupaten">Kabupaten</label>
                                <input type="text" id="kabupaten" name="kabupaten" value="{{ old('kabupaten') }}" placeholder="Kabupaten">
                            </div>
                        </div>
                        <div class="button-row">
                            <a href="{{ route('dashboard') }}" class="button button-secondary">Batal</a>
                            <button type="submit" class="button button-primary">Simpan</button>
                        </div>
                    </div>
                    <aside class="photo-panel">
                        <div class="photo-panel-title">Foto Siswa</div>
                        <div class="photo-preview">
                            <div class="photo-placeholder">Preview foto akan muncul setelah upload.</div>
                        </div>
                        <label class="upload-label" for="foto">Unggah Foto</label>
                        <input type="file" id="foto" name="foto" accept="image/*">
                        <p class="photo-note">Foto disimpan di database saat formulir disubmit.</p>
                    </aside>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
