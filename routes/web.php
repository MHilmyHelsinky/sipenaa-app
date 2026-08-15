<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// 1. Mengubah halaman awal agar langsung dialihkan (redirect) ke halaman login
Route::redirect('/', '/login');

// 2. Rute dashboard untuk Super Admin dan User dengan data riil dari tabel users
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::get('/aktivitas-input', [DashboardController::class, 'aktivitasInput'])
    ->middleware(['auth'])
    ->name('aktivitas.input');

Route::get('/manajemen-pengguna', [DashboardController::class, 'manageUsers'])
    ->middleware(['auth'])
    ->name('manajemen.pengguna');

Route::get('/pembuatan-kartu-baru', [DashboardController::class, 'pembuatanKartu'])
    ->middleware(['auth'])
    ->name('pembuatan.kartu');

Route::post('/pembuatan-kartu-baru', [DashboardController::class, 'storeCard'])
    ->middleware(['auth'])
    ->name('pembuatan.kartu.store');

Route::get('/preview-kartu/{card}', [DashboardController::class, 'previewKartu'])
    ->middleware(['auth'])
    ->name('preview.kartu');

Route::get('/preview-kartu/{card}/download-pdf', [DashboardController::class, 'downloadPdf'])
    ->middleware(['auth'])
    ->name('preview.kartu.download.pdf');

Route::get('/preview-kartu/{card}/download-word', [DashboardController::class, 'downloadWord'])
    ->middleware(['auth'])
    ->name('preview.kartu.download.word');

Route::get('/data-kartu', [DashboardController::class, 'dataKartu'])
    ->middleware(['auth'])
    ->name('data.kartu');

Route::get('/laporan', [DashboardController::class, 'laporan'])
    ->middleware(['auth'])
    ->name('laporan');

Route::post('/manajemen-pengguna', [DashboardController::class, 'storeUser'])
    ->middleware(['auth'])
    ->name('manajemen.pengguna.store');

Route::patch('/manajemen-pengguna/{user}/toggle', [DashboardController::class, 'toggleUser'])
    ->middleware(['auth'])
    ->name('manajemen.pengguna.toggle');

Route::delete('/manajemen-pengguna/{user}', [DashboardController::class, 'destroyUser'])
    ->middleware(['auth'])
    ->name('manajemen.pengguna.destroy');

// 3. Memanggil rute otentikasi (login, logout, dll)
require __DIR__.'/auth.php';