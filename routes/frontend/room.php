<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Web\RoomController;

Route::group(['prefix' => '/room'], function () {
    Route::get('/', [RoomController::class, 'index'])->withoutMiddleware(['auth:sanctum', 'role:user']);
    Route::get('/detail/{id}', [RoomController::class, 'show'])->withoutMiddleware(['auth:sanctum', 'role:user']);
});
