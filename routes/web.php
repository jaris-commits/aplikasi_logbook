<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\LogbookController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WeeklyLogbookSummaryController;
use Illuminate\Support\Facades\Route;

// HALAMAN UTAMA
Route::get('/', function () {
    return redirect()->route('login');
});

// AUTENTIKASI BREEZE
if (file_exists(__DIR__.'/auth.php')) {
    require __DIR__.'/auth.php';
}

// SEMUA USER YANG LOGIN
Route::middleware(['auth', 'verified'])->group(function () {

    // DASHBOARD
    Route::get('/dashboard', [LogbookController::class, 'index'])
        ->name('dashboard');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->middleware('role:admin,guru,siswa')->name('attendance.index');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])
        ->middleware('role:siswa')->name('attendance.check-in');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])
        ->middleware('role:siswa')->name('attendance.check-out');
    Route::get('/attendance/{attendance}/photo', [AttendanceController::class, 'photo'])
        ->middleware('role:admin,guru,siswa')->name('attendance.photo');

    Route::get('/projects', [ProjectController::class, 'index'])
        ->middleware('role:admin,guru')->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])
        ->middleware('role:admin,guru')->name('projects.store');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])
        ->middleware('role:admin,guru')->name('projects.destroy');

    // PROFILE
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // KHUSUS ADMIN
    Route::middleware(['role:admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            // PENGELOLAAN AKUN
            Route::get('/users', [AdminController::class, 'index'])
                ->name('users.index');

            Route::get('/users/create', [AdminController::class, 'create'])
                ->name('users.create');

            Route::post('/users', [AdminController::class, 'store'])
                ->name('users.store');

            Route::get('/users/{id}/edit', [AdminController::class, 'edit'])
                ->name('users.edit');

            Route::put('/users/{id}', [AdminController::class, 'update'])
                ->name('users.update');

            Route::delete('/users/{id}', [AdminController::class, 'destroy'])
                ->name('users.destroy');
        });

    // KHUSUS SISWA
    Route::middleware(['role:siswa'])->group(function () {

        Route::post('/logbook', [LogbookController::class, 'store'])
            ->name('logbook.store');

        Route::get('/logbook/{id}/edit', [LogbookController::class, 'edit'])
            ->name('logbook.edit');

        Route::put('/logbook/{id}', [LogbookController::class, 'update'])
            ->name('logbook.update');

        Route::delete('/logbook/{id}', [LogbookController::class, 'destroy'])
            ->name('logbook.destroy');
    });

    // GURU DAN ADMIN
    Route::middleware(['role:guru,admin'])->group(function () {

        Route::post('/grades', [GradeController::class, 'store'])
            ->name('grades.store');

        Route::put('/logbook/{id}/status', [LogbookController::class, 'updateStatus'])
            ->name('logbook.status');
    });

    Route::middleware(['role:guru', 'throttle:5,1'])->post('/reports/weekly-summary', WeeklyLogbookSummaryController::class)
        ->name('report.weekly-summary');

    Route::middleware(['auth'])->group(function () {
        Route::get('/report/logbook-pdf', [ReportController::class, 'logbookPdf'])
            ->name('report.logbook.pdf');

        Route::get('/report/grade-pdf', [ReportController::class, 'gradePdf'])
            ->name('report.grade.pdf');
        Route::post('/report/signature', [ReportController::class, 'saveSignature'])
            ->name('report.signature');
    });
});
