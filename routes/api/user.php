<?php

use App\Http\Controllers\DonationCore\StripePaymentController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('user')->group(function () {

    Route::controller(UserController::class)->group(function () {
        Route::get('', 'getAuthUser');
        Route::get('/donations', 'getUserDonations');
        Route::get('/subscriptions', 'getUserSubscriptions');
        Route::put('/info', 'updateUserInfo');
        Route::post('/img', 'updateUserImage');
        Route::Delete('', 'deleteUser');
    });

    Route::Delete('/subscriptions/{subscriptionId}', [StripePaymentController::class, 'cancelStripeSubscription']);
});
