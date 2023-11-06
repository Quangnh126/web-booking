<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\RoomV2Controller;

Route::group(['prefix' => '/room'], function () {
    Route::get('index', [RoomV2Controller::class, 'index']);
    Route::post('create-room', [RoomV2Controller::class, 'createRoom']);
    Route::post('create-tour', [RoomV2Controller::class, 'createTour']);
    Route::get('show/{id}', [RoomV2Controller::class, 'show']);
    Route::post('update/{id}', [RoomV2Controller::class, 'update']);
    Route::delete('multiple-delete', [RoomV2Controller::class, 'multipleDelete']);
    Route::post('active/{id}', [RoomV2Controller::class, 'activeRoom']);

});
