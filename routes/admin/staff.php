<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\StaffV2Controller;

Route::group(['prefix' => '/staff'], function () {
    Route::get('index', [StaffV2Controller::class, 'listStaff']);
    Route::post('create', [StaffV2Controller::class, 'store']);
    Route::get('show/{id}', [StaffV2Controller::class, 'show']);
    Route::post('update/{id}', [StaffV2Controller::class, 'update']);
    Route::delete('multiple-delete', [StaffV2Controller::class, 'multipleDelete']);

});
