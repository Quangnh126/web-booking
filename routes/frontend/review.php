<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Web\ReviewController;

Route::group(['prefix' => 'review'], function () {
    Route::post('/create', [ReviewController::class, 'createReview']);
    Route::get('/{id}', [ReviewController::class, 'listReviewRoom'])->withoutMiddleware(['auth:sanctum', 'role:user']);
});
