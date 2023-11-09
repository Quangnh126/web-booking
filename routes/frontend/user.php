<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Web\UserController;

Route::group(['prefix' => '/user'], function () {
    Route::get('show/{id}', [UserController::class, 'show']);
    Route::post('update/{id}', [UserController::class, 'update']);
});
