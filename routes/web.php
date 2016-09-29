<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index');

Route::group(['middleware' => 'auth:api'], function() {
	Route::get('/verify_token', function() {
		// none
	});

	Route::resource('dashboard', 'DashboardController');
});

// Route::get('/downloadAllCompanies', 'routinesController@downloadAllCompanies');
Route::get('/downloadCompaniesAndPrices', 'routinesController@downloadCompaniesAndPrices');
Route::get('/harvestDownloadedCompaniesAndPrices', 'routinesController@harvestDownloadedCompaniesAndPrices');
Route::get('/materializeRawDataPerMinute', 'routinesController@materializeRawDataPerMinute');
Route::get('/materializeForPerCompanyDaily', 'routinesController@materializeForPerCompanyDaily');
Route::get('/performEOD', 'routinesController@performEOD');