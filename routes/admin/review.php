<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\ReviewV2Controller;

Route::group(['prefix' => '/review'], function () {
    Route::get('index', [ReviewV2Controller::class, 'listReview']);
    Route::get('show/{id}', [ReviewV2Controller::class, 'detailReview']);
    Route::delete('multiple-delete', [ReviewV2Controller::class, 'multipleDeleteReviews']);
});
