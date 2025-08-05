<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

Route::post('/register', [AuthController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware(['guest:admin']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');

Route::middleware('auth', 'verified')->group(function () {
    Route::get('/attendance', [StaffController::class, 'attendance']);
    Route::post('/attendance/list', [StaffController::class, 'store']);
    Route::get('/attendance/list', [StaffController::class, 'index'])->name('index');
    Route::post('/stamp_correction_request/list', [StaffController::class, 'update']);
});

Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/attendance/list', [AdminController::class, 'index'])->name('admin-index');
    Route::patch('/admin/attendance/list', [AdminController::class, 'update']);
    Route::get('/admin/staff/list', [AdminController::class, 'showStaffs']);
    Route::get('/admin/attendance/staff/{id}', [AdminController::class, 'show'])->name('staff-attendance.show');
    Route::get('/admin/attendance/staff/{id}/export', [AdminController::class, 'export'])->name('export');
    Route::get('/stamp_correction_request/approve/{attendance_correct_request}', [AdminController::class, 'approveForm'])
        ->name('request.approve');
    Route::patch('/stamp_correction_request/approve/{attendance_correct_request}', [AdminController::class, 'approve'])
        ->name('request.approve.patch');
});

Route::middleware(['detect.guard'])->get('/stamp_correction_request/list', function () {});

Route::middleware('shared.access')->group(function () {
    Route::get('/attendance/{id}', [AttendanceController::class, 'show']);
    Route::post('/attendance/date', [AttendanceController::class, 'redirectByDate']);
});
