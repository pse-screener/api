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

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

/* Started here.
*  Note: Authentication is already done automatically in RouteServiceProvider.
*/

Route::get('/verify_token', function() {
	// none
});

Route::resource('/company', 'CompanyController');
Route::resource('/alert', 'AlertController');
Route::resource('/dashboard', 'DashboardController');