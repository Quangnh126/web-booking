<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\DashboardV2Controller;

Route::group(['prefix' => '/dashboard'], function () {
    Route::get('/general', [DashboardV2Controller::class, 'general']);
});
