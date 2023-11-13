<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\RequestCancelV2Controller;

Route::group(['prefix' => '/request-cancel'], function () {
    Route::get('index', [RequestCancelV2Controller::class, 'listRequestCancel']);
    Route::get('show/{id}', [RequestCancelV2Controller::class, 'listRequestCancel']);
    Route::post('/update-status/{id}', [RequestCancelV2Controller::class, 'updateStatusRequestCancel']);
});
