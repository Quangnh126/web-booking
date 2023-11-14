<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Web\SettingController;

Route::group(['prefix' => '/setting'], function () {
    Route::get('/contact', [SettingController::class, 'getContact']);
});
