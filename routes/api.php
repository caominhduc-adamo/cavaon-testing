<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::get('/tours', 'Api\V1\TourController@index');
    Route::get('/tours/{tour}', 'Api\V1\TourController@show');
    Route::post('/tours', 'Api\V1\TourController@store');
    Route::put('/tours/{tour}', 'Api\V1\TourController@update');
    Route::patch('/tours/{tour}/publish', 'Api\V1\TourController@publish');
});
