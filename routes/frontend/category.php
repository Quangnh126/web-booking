<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Web\CategoryController;

Route::group(['prefix' => 'category'], function () {
    Route::get('index', [CategoryController::class, 'listCategories'])->withoutMiddleware(['auth:sanctum', 'role:user']);
});

