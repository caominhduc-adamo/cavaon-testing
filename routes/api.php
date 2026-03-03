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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::get('/tours', 'Api\V1\TourController@index');
    Route::get('/tours/{tour}', 'Api\V1\TourController@show');
    Route::post('/tours', 'Api\V1\TourController@store');
    Route::put('/tours/{tour}', 'Api\V1\TourController@update');
    Route::patch('/tours/{tour}/publish', 'Api\V1\TourController@publish');

    Route::get('/passengers', 'Api\V1\PassengerController@index');
    Route::get('/passengers/{passenger}', 'Api\V1\PassengerController@show');
    Route::post('/passengers', 'Api\V1\PassengerController@store');
    Route::put('/passengers/{passenger}', 'Api\V1\PassengerController@update');

    Route::get('/bookings', 'Api\V1\BookingController@index');
    Route::get('/bookings/{booking}', 'Api\V1\BookingController@show');
    Route::post('/bookings', 'Api\V1\BookingController@store');
    Route::put('/bookings/{booking}', 'Api\V1\BookingController@update');

    Route::get('/invoices', 'Api\V1\InvoiceController@index');
    Route::get('/invoices/{invoice}', 'Api\V1\InvoiceController@show');
    Route::put('/invoices/{invoice}', 'Api\V1\InvoiceController@update');

    Route::fallback(function () {
        return response()->json([
            'message' => 'API endpoint not found.',
        ], 404);
    });
});
