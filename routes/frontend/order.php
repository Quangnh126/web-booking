<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Web\OrderController;

Route::group(['prefix' => '/order'], function () {
    Route::post('/booking-room', [OrderController::class, 'makeBookingRoom']);
    Route::post('/booking-tour', [OrderController::class, 'makeBookingTour']);
});
