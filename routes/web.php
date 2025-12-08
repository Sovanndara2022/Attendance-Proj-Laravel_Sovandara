<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Teacher\TimetableController;
use App\Http\Controllers\Teacher\AttendanceController;
use App\Http\Controllers\Teacher\GroupAttendanceController;
use App\Http\Controllers\Teacher\ReportsController;
use App\Http\Controllers\ProfileController;

Route::redirect('/', '/teacher/timetable');

Route::middleware('auth')->prefix('teacher')->group(function () {
    // Timetable
    Route::get('/timetable', [TimetableController::class, 'index'])->name('teacher.timetable');

    // Session attendance edit + save
    Route::get('/sessions/{session}/attendance', [AttendanceController::class, 'edit'])
        ->name('teacher.sessions.edit');
    Route::put('/sessions/{session}/attendance', [AttendanceController::class, 'update'])
        ->name('teacher.sessions.update');

    // Group attendance grid + CSV
    Route::get('/groups/{group}/attendance-grid', [GroupAttendanceController::class, 'grid'])
        ->name('teacher.group.grid');
    Route::get('/groups/{group}/attendance-grid.csv', [GroupAttendanceController::class, 'csv'])
        ->name('teacher.group.csv');

    // Reports + CSV
    Route::get('/reports', [ReportsController::class, 'index'])->name('teacher.reports');
    Route::get('/reports.csv', [ReportsController::class, 'csv'])->name('teacher.reports.csv');
});

// Profile (avatar, password)
Route::middleware('auth')->group(function () {
    Route::get('/profile',  [ProfileController::class,'index'])->name('profile.index');

    // must be PUT because the form uses @method('PUT')
    Route::put('/profile', [ProfileController::class,'update'])->name('profile.update');

    // password form uses POST
    Route::post('/profile/password', [ProfileController::class,'password'])->name('profile.password');
});
require __DIR__.'/auth.php';
