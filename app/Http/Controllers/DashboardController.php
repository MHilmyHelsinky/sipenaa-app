<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DashboardController extends Controller
{
    protected function authorizeSuperAdmin(): void
    {
        abort_if(Auth::user()?->role !== 'super_admin', 403);
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->role === 'user') {
            return view('dashboard-user', [
                'nisnTerdaftar' => 0,
                'sudahCetak' => 0,
                'belumCetak' => 0,
                'cetakHariIni' => 0,
                'inputTodayCount' => 0,
                'inputTodayData' => [],
                'currentDate' => now()->locale('id')->isoFormat('dddd, D MMMM YYYY'),
                'currentTime' => now()->format('H:i'),
            ]);
        }

        $today = now();

        $totalPetugas = User::where('role', 'user')->count();
        $inputToday = User::where('role', 'user')
            ->whereDate('created_at', $today)
            ->count();
        $activeToday = User::where('role', 'user')
            ->where('is_active', true)
            ->whereDate('last_login_at', $today)
            ->count();

        $activeUsers = User::where('role', 'user')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('dashboard', [
            'totalPetugas' => $totalPetugas,
            'inputToday' => $inputToday,
            'activeToday' => $activeToday,
            'activeUsers' => $activeUsers,
        ]);
    }

    public function aktivitasInput(): View
    {
        return view('aktivitas-input');
    }

    public function pembuatanKartu(): View
    {
        return view('pembuatan-kartu');
    }

    public function storeCard(Request $request)
    {
        $validated = $request->validate([
            'nisn' => ['required', 'string', 'max:50', 'unique:cards,nisn'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'jenis_kelamin' => ['required', 'in:Laki-laki,Perempuan'],
            'desa' => ['nullable', 'string', 'max:255'],
            'kecamatan' => ['nullable', 'string', 'max:255'],
            'kabupaten' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = Storage::disk('public')->putFile('card_photos', $request->file('foto'));
        }

        Card::create([
            'nisn' => $validated['nisn'],
            'nama_lengkap' => $validated['nama_lengkap'],
            'tempat_lahir' => $validated['tempat_lahir'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'desa' => $validated['desa'] ?? null,
            'kecamatan' => $validated['kecamatan'] ?? null,
            'kabupaten' => $validated['kabupaten'] ?? null,
            'foto_path' => $fotoPath,
        ]);

        return back()->with('success', 'Kartu siswa berhasil disimpan ke database.');
    }

    public function dataKartu(): View
    {
        return view('data-kartu', [
            'cards' => Card::latest()->get(),
        ]);
    }

    public function laporan(): View
    {
        return view('laporan');
    }

    public function manageUsers(Request $request): View
    {
        $this->authorizeSuperAdmin();

        $users = User::orderByRaw("role = 'super_admin' desc")
            ->orderBy('name')
            ->get();

        return view('manajemen-pengguna', [
            'users' => $users,
        ]);
    }

    protected function generateUniqueUsername(string $name): string
    {
        $base = Str::slug($name, '_');
        $username = $base ?: 'user';
        $candidate = $username;
        $count = 1;

        while (User::where('username', $candidate)->exists()) {
            $candidate = $username . $count;
            $count++;
        }

        return $candidate;
    }

    public function storeUser(Request $request)
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['required', 'in:super_admin,user'],
        ]);

        $username = $this->generateUniqueUsername($validated['name']);

        User::create([
            'name' => $validated['name'],
            'username' => $username,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Akun baru berhasil dibuat. Username otomatis: ' . $username);
    }

    public function toggleUser(User $user)
    {
        $this->authorizeSuperAdmin();

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', 'Status akun berhasil diperbarui.');
    }

    public function destroyUser(User $user)
    {
        $this->authorizeSuperAdmin();

        $user->delete();

        return back()->with('success', 'Akun berhasil dihapus.');
    }
}
