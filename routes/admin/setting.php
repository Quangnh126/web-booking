<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\SettingV2Controller;

Route::group(['prefix' => 'setting'], function () {
    Route::get('/contact', [SettingV2Controller::class, 'getContact']);
    Route::post('/create-contact', [SettingV2Controller::class, 'createContact']);
});

