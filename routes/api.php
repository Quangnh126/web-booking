<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => 'language'], function () {
    Route::post('/auth/login', [AuthController::class, 'loginUser']);
});

Route::group(['middleware' => ['role:user', 'language']], function () {
    Route::group(['middleware' => 'auth:sanctum'], function () {
//        API FE
        \App\Helpers\RouteHelper::includeRouteFiles(__DIR__ . '/frontend');
    });
});

Route::group(['middleware' => ['role:admin', 'language']], function () {
    Route::group(['middleware' => 'auth:sanctum'], function () {
//        API ADMIN
        \App\Helpers\RouteHelper::includeRouteFiles(__DIR__ . '/admin');
    });
});
