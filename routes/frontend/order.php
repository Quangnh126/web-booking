<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Web\OrderController;

Route::group(['prefix' => '/order'], function () {
    Route::post('/booking-room', [OrderController::class, 'makeBookingRoom']);
    Route::post('/booking-tour', [OrderController::class, 'makeBookingTour']);
    Route::get('/list-order', [OrderController::class, 'listMyOrder']);
    Route::post('/cancel/{id}', [OrderController::class, 'cancelOrder']);
    Route::get('/show/{id}', [OrderController::class, 'detailOrder']);
});
