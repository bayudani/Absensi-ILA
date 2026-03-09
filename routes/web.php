<?php

use App\Http\Controllers\Api\FonnteWebhookController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Absensi Terpadu SMPN 3 Siak Kecil
|--------------------------------------------------------------------------
*/

// 1. Root & Dashboard
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard'); 
})->middleware(['auth', 'verified'])->name('dashboard');

/**
|--------------------------------------------------------------------------
| GROUP ADMIN & KEPSEK (Master Data)
|--------------------------------------------------------------------------
| LOGIC: Kita tambahkan 'kepsek' di sini agar mereka bisa akses URL-nya.
| Pembatasan "View Only" (tidak bisa edit/hapus) ditangani di level View (Blade).
*/
Route::middleware(['auth', 'role:admin,kepsek'])->prefix('admin')->name('admin.')->group(function () {
    
    // Master Data
    Route::get('/kelas', function() { return view('admin.kelas.index'); })->name('kelas.index');
    Route::get('/siswa', function() { return view('admin.siswa.index'); })->name('siswa.index');
    Route::get('/guru', function() { return view('admin.guru.index'); })->name('guru.index');
    Route::get('/mapel', function() { return view('admin.mapel.index'); })->name('mapel.index');
    
    // Administrasi & Akun
    Route::get('/jadwal', function() { return view('admin.jadwal.index'); })->name('jadwal.index');
    Route::get('/user-management', function() { return view('admin.user.index'); })->name('user.index');
    
    // Laporan Global
    Route::get('/rekap-total', function() { return view('admin.rekap.index'); })->name('rekap');
});

/**
|--------------------------------------------------------------------------
| GROUP GURU & WALI KELAS (Role: guru, walas)
|--------------------------------------------------------------------------
| Wali Kelas adalah Guru, jadi mereka berbagi akses ke fitur mengajar harian.
*/
Route::middleware(['auth', 'role:guru,walas'])->prefix('guru')->name('guru.')->group(function () {
    
    // Input Absensi: Mendukung shortcut dari tabel jadwal ({jadwal_id?})
    Route::get('/input-absensi/{jadwal_id?}', function ($jadwal_id = null) {
        return view('guru.absensi.index', ['jadwal_id' => $jadwal_id]);
    })->name('absensi.create');

    // Jadwal Mengajar Pribadi
    Route::get('/jadwal-mengajar', function () {
        return view('guru.jadwal.index');
    })->name('jadwal');

    // Rekap Performa Kelas per Mata Pelajaran
    Route::get('/rekap-absensi', function () {
        return view('guru.rekap.index');
    })->name('rekap');
});

/**
|--------------------------------------------------------------------------
| GROUP KHUSUS WALI KELAS (Role: walas)
|--------------------------------------------------------------------------
| Fitur tambahan khusus untuk pemantauan kelas binaan.
*/
Route::middleware(['auth', 'role:walas'])->prefix('walas')->name('walas.')->group(function () {
    
    // Data Siswa yang ada di kelas yang dibinanya
    Route::get('/siswa-binaan', function() { return view('walas.siswa.index'); })->name('siswa');
    
    // Laporan rekapitulasi kehadiran kelas secara total (semua mapel)
    Route::get('/laporan-absensi', function() { return view('walas.rekap.index'); })->name('laporan');
});

/**
|--------------------------------------------------------------------------
| GROUP KEPALA SEKOLAH (Role: kepsek)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:kepsek'])->prefix('kepsek')->name('kepsek.')->group(function () {
    
    // Monitoring kedisiplinan guru dan statistik sekolah
    Route::get('/monitoring', function() { return view('kepsek.monitoring.index'); })->name('monitoring');
});

/**
|--------------------------------------------------------------------------
| PROFILE & AUTH (Laravel Breeze Standard)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// foonte
Route::post('/fonnte-webhook', [FonnteWebhookController::class, 'handle']);
require __DIR__.'/auth.php';