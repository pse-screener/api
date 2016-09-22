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

Route::get('/downloadAllCompanies', 'companyController@downloadAllCompanies');
Route::get('/downloadPrice', 'companyController@downloadPrice');