<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Web\UserController;

Route::group(['prefix' => '/user'], function () {
    Route::get('show/{id}', [UserController::class, 'show']);
    Route::post('update/{id}', [UserController::class, 'update']);
    Route::post('updatePs/{id}', [UserController::class, 'updatePs']);

    Route::withoutMiddleware(['auth:sanctum','role:user'])->group(function () {
        Route::post('/resend-code', [UserController::class, 'reSendCode']);
        Route::post('/send-code', [UserController::class, 'sendCodeForgotPassword']);
        Route::post('/send-password', [UserController::class, 'sendPasswordForgotPassword']);
        Route::post('/verify-code', [UserController::class, 'codeVerify']);
        Route::post('/reset-password', [UserController::class, 'resetPassword']);
    });
});
