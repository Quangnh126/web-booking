<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\CustomerController;

Route::group(['prefix' => '/customer'], function () {
    Route::get('index', [CustomerController::class, 'listCustomer']);
    Route::post('create', [CustomerController::class, 'store']);
    Route::delete('multiple-delete', [CustomerController::class, 'multipleDelete']);
    Route::post('updateStatus/{id}', [CustomerController::class, 'updateStatus']);
});
