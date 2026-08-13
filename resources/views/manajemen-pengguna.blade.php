<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - SIPENA</title>
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
        .page-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }
        .page-title { margin: 0; font-size: 1.75rem; font-weight: 800; }
        .page-subtitle { margin: 0.5rem 0 0; color: #64748b; }
        .card { background: #ffffff; border-radius: 1.5rem; padding: 1.75rem; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08); margin-bottom: 2rem; }
        .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
        .field { display: grid; gap: 0.45rem; }
        .field label { font-size: 0.95rem; color: #334155; font-weight: 700; }
        .field input { width: 100%; min-height: 48px; border: 1px solid #d1d5db; border-radius: 0.85rem; padding: 0 1rem; font-size: 0.95rem; color: #0f172a; background: #f8fafc; }
        .field input:focus { outline: none; border-color: #93c5fd; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.12); background: #ffffff; }
        .actions { display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem; }
        .button { min-height: 48px; border: none; border-radius: 999px; padding: 0 1.5rem; font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: background 0.2s ease, transform 0.2s ease; }
        .button.primary { background: #1d4ed8; color: #ffffff; }
        .button.primary:hover { background: #1e40af; transform: translateY(-1px); }
        .button.secondary { background: #e2e8f0; color: #0f172a; }
        .button.secondary:hover { background: #cbd5e1; }
        .message { margin-bottom: 1rem; padding: 1rem 1.25rem; border-radius: 1rem; font-weight: 600; }
        .message.success { background: #dcfce7; color: #166534; }
        .message.error { background: #fee2e2; color: #991b1b; }
        .table-card { background: #edf4ff; border-radius: 1.5rem; overflow: hidden; box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08); }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th, td { padding: 1rem 1.2rem; text-align: left; vertical-align: middle; }
        th { background: #1d4ed8; color: #ffffff; font-size: 0.95rem; font-weight: 700; }
        tr { border-bottom: 1px solid rgba(15, 23, 42, 0.08); }
        tr:nth-child(even) { background: #e5edff; }
        tr:nth-child(odd) { background: #f8fbff; }
        td { color: #102a43; font-size: 0.95rem; }
        .status-pill { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.4rem 0.85rem; border-radius: 999px; font-size: 0.85rem; font-weight: 700; }
        .status-active { background: #dcf0ff; color: #0b4da8; }
        .status-inactive { background: #fee2e2; color: #b91c1c; }
        .row-actions { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
        .row-actions form { margin: 0; }
        .row-actions button { min-width: 120px; }
        @media (max-width: 960px) {
            .form-grid { grid-template-columns: 1fr; }
            .actions { flex-direction: column; align-items: stretch; }
            .page-header { flex-direction: column; align-items: flex-start; }
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
            <a class="nav-link" href="{{ route('aktivitas.input') }}">Aktifitas Input</a>
            <a class="nav-link active" href="{{ route('manajemen.pengguna') }}">Manajemen pengguna</a>
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
                <h1 class="page-title">Manajemen Pengguna</h1>
                <p class="page-subtitle">Buat akun admin baru dan kelola status akun yang sudah terdaftar.</p>
            </div>
        </header>

        <section class="card">
            @if(session('success'))
                <div class="message success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="message error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('manajemen.pengguna.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="field">
                        <label for="name">Nama Pengguna</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" placeholder="Nama lengkap" required>
                    </div>
                    <div class="field">
                        <label for="role">Tipe Akun</label>
                        <select id="role" name="role" required>
                            <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="user" {{ old('role', 'user') === 'user' ? 'selected' : '' }}>User Biasa</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="password">Kata Sandi</label>
                        <input id="password" name="password" type="password" placeholder="Minimal 8 karakter" required>
                    </div>
                    <div class="field">
                        <label for="password_confirmation">Konfirmasi Sandi</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Ulangi kata sandi" required>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="button primary">Buat Pengguna</button>
                    <button type="reset" class="button secondary">Bersihkan</button>
                </div>
            </form>
        </section>

        <section class="table-card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Pengguna</th>
                            <th>Username</th>
                                    <th>Jenis Akun</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->username }}</td>
                                <td>{{ $user->role === 'super_admin' ? 'Super Admin' : 'User Biasa' }}</td>
                                <td>
                                    <span class="status-pill {{ $user->is_active ? 'status-active' : 'status-inactive' }}">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="row-actions">
                                        <form method="POST" action="{{ route('manajemen.pengguna.toggle', $user) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="button secondary">
                                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('manajemen.pengguna.destroy', $user) }}" onsubmit="return confirm('Hapus akun {{ addslashes($user->name) }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="button secondary" style="background: #fee2e2; color: #b91c1c;">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 2rem; color: #64748b;">Belum ada akun admin yang dibuat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
