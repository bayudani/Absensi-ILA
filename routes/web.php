<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Redirect root ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// DASHBOARD (Semua Role)
Route::get('/dashboard', function () {
    return view('dashboard'); 
})->middleware(['auth', 'verified'])->name('dashboard');

// === GROUP ADMIN ===
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Master Data (Placeholder View dulu biar gak error)
    Route::get('/kelas', function() { return view('admin.kelas.index'); })->name('kelas.index');
    Route::get('/siswa', function() { return view('admin.siswa.index'); })->name('siswa.index');
    Route::get('/guru', function() { return view('admin.guru.index'); })->name('guru.index');
    Route::get('/mapel', function() { return view('admin.mapel.index'); })->name('mapel.index');
    // Route::get('/ortu', function() { return view('dashboard'); })->name('ortu.index');
    
    // Akademik
    Route::get('/jadwal', function() { return view('admin.jadwal.index'); })->name('jadwal.index');
    Route::get('/user', function() { return view('dashboard'); })->name('user.index');
    
    // Laporan
    Route::get('/rekap-total', function() { return view('admin.rekap.index'); })->name('rekap');
});

// === GROUP GURU ===
Route::middleware(['auth', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/input-absensi', function() { return view('dashboard'); })->name('absensi.create');
    Route::get('/jadwal-mengajar', function() { return view('dashboard'); })->name('jadwal');
    Route::get('/rekap-kelas', function() { return view('dashboard'); })->name('rekap');
});

// === GROUP WALI KELAS ===
Route::middleware(['auth', 'role:walas'])->prefix('walas')->name('walas.')->group(function () {
    Route::get('/siswa-binaan', function() { return view('dashboard'); })->name('siswa');
    Route::get('/laporan-absensi', function() { return view('dashboard'); })->name('laporan');
    Route::get('/monitoring-pr', function() { return view('dashboard'); })->name('monitoring');
});

// === GROUP KEPSEK ===
Route::middleware(['auth', 'role:kepsek'])->prefix('kepsek')->name('kepsek.')->group(function () {
    Route::get('/monitoring', function() { return view('dashboard'); })->name('monitoring');
});

// Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';