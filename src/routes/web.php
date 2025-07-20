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

//Route::middleware('auth')->group(function () {
    Route::get('/attendance', [StaffController::class, 'attendance']);
    Route::post('/attendance/list', [StaffController::class, 'store']);
    Route::get('/attendance/list', [StaffController::class, 'index'])->name('index');
    Route::get('/attendance/date/{date}', [StaffController::class, 'redirectByDate'])->name('redirectByDate');
    Route::post('/stamp_correction_request/list', [StaffController::class, 'update']);
//});

Route::middleware('auth:admin')->group(function () {
    Route::get('/attendance/{id}', [AdminController::class, 'show']);
    Route::post('admin/attendance/list', [AdminController::class, 'update']);
});



Route::get('/attendance/{id}', [AttendanceController::class, 'show']);



Route::get('/email/verify', [AuthController::class, 'email']);
    