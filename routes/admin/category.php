<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\CategoryV2Controller;

Route::group(['prefix' => '/category'], function () {
    Route::get('index', [CategoryV2Controller::class, 'index']);
    Route::post('create', [CategoryV2Controller::class, 'createCategory']);
    Route::get('show/{id}', [CategoryV2Controller::class, 'showCategory']);
    Route::post('update/{id}', [CategoryV2Controller::class, 'updateCategory']);
    Route::delete('multiple-delete', [CategoryV2Controller::class, 'multipleDeleteCategory']);

});
