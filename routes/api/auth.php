<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
    Route::post('/forgot-password', 'sendPasswordResetLink');
    Route::post('/reset-password', 'createNewPassword');
});

Route::controller(AuthController::class)->middleware('auth:sanctum')->group(function () {
    Route::post('/logout', 'logout');
    Route::get('/verify-email/{id}/{hash}', 'verifyEmail')->name('verification.verify');
    Route::post('/email/verification-notification', 'resendEmailVerificationNotification');
    Route::post('/change-password', 'changePassword');
});
