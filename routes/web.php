<?php

use App\Http\Controllers\LogbookController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ==========================================
// HALAMAN UTAMA
// ==========================================
Route::get('/', function () {
    return view('welcome');
});

// ==========================================
// ROUTE AUTENTIKASI BREEZE
// ==========================================
if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}

// ==========================================
// ROUTE SETELAH LOGIN
// ==========================================
Route::middleware(['auth', 'verified'])->group(function () {

    // DASHBOARD SEMUA ROLE
    Route::get('/dashboard', [LogbookController::class, 'index'])
        ->name('dashboard');

    // ======================================
    // PROFILE SISWA, GURU, DAN ADMIN
    // ======================================
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // ======================================
    // KHUSUS SISWA
    // ======================================
    Route::middleware(['role:siswa'])->group(function () {

        // Menambahkan logbook
        Route::post('/logbook', [LogbookController::class, 'store'])
            ->name('logbook.store');

        // Menghapus logbook
        Route::delete('/logbook/{id}', [LogbookController::class, 'destroy'])
            ->name('logbook.destroy');
    });

    // ======================================
    // KHUSUS GURU DAN ADMIN
    // ======================================
    Route::middleware(['role:guru,admin'])->group(function () {

        // Mengubah status / verifikasi logbook
        Route::put('/logbook/{id}/status', [LogbookController::class, 'updateStatus'])
            ->name('logbook.status');
    });

});
