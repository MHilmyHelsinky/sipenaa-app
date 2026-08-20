<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WordDownloadController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');
Route::get('/aktivitas-input', [DashboardController::class, 'aktivitasInput'])->middleware(['auth'])->name('aktivitas.input');
Route::get('/manajemen-pengguna', [DashboardController::class, 'manageUsers'])->middleware(['auth'])->name('manajemen.pengguna');
Route::get('/pembuatan-kartu-baru', [DashboardController::class, 'pembuatanKartu'])->middleware(['auth'])->name('pembuatan.kartu');
Route::post('/pembuatan-kartu-baru', [DashboardController::class, 'storeCard'])->middleware(['auth'])->name('pembuatan.kartu.store');

Route::get('/preview-kartu/{card}', [DashboardController::class, 'previewKartu'])->middleware(['auth'])->name('preview.kartu');
Route::get('/preview-kartu/{card}/pdf-preview', [DashboardController::class, 'previewPdf'])->middleware(['auth'])->name('preview.kartu.pdf');
Route::get('/preview-kartu/{card}/download-pdf', [DashboardController::class, 'downloadPdf'])->middleware(['auth'])->name('preview.kartu.download.pdf');
Route::get('/preview-kartu/{card}/download-word', [WordDownloadController::class, 'download'])->middleware(['auth'])->name('preview.kartu.download.word');

Route::post('/cetak-massal', [DashboardController::class, 'printBatch'])->middleware(['auth'])->name('cetak.massal');
Route::get('/data-kartu', [DashboardController::class, 'dataKartu'])->middleware(['auth'])->name('data.kartu');
Route::get('/laporan', [DashboardController::class, 'laporan'])->middleware(['auth'])->name('laporan');
Route::get('/laporan/export', [DashboardController::class, 'exportLaporan'])->middleware(['auth'])->name('laporan.export');

Route::post('/manajemen-pengguna', [DashboardController::class, 'storeUser'])->middleware(['auth'])->name('manajemen.pengguna.store');
Route::patch('/manajemen-pengguna/{user}/toggle', [DashboardController::class, 'toggleUser'])->middleware(['auth'])->name('manajemen.pengguna.toggle');
Route::delete('/manajemen-pengguna/{user}', [DashboardController::class, 'destroyUser'])->middleware(['auth'])->name('manajemen.pengguna.destroy');

require __DIR__.'/auth.php';
