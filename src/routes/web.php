<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

Route::post('/register', [AuthController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware(['guest']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verify'])
    ->middleware(['auth', 'signed'])
    ->name('verification.verify');
Route::get('/attendance', [StaffController::class, 'attendance']);




Route::get('/email/verify', [AuthController::class, 'email']);
    