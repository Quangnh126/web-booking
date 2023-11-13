<?php

use App\Http\Controllers\Api\Admin\OrderV2Controller;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/order'], function () {
    Route::get('index', [OrderV2Controller::class, 'listOrder']);
    Route::get('show/{id}', [OrderV2Controller::class, 'detailOrder']);
    Route::post('update-status/{id}', [OrderV2Controller::class, 'updateStatusOrder']);
});

