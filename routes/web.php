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

/* Actually we want to add constraints that only to be ran locally. */
Route::get('/downloadCompaniesAndPrices', 'routinesController@downloadCompaniesAndPrices');
Route::get('/harvestDownloadedCompaniesAndPrices', 'routinesController@harvestDownloadedCompaniesAndPrices');
Route::get('/materializeRawDataPerMinute', 'routinesController@materializeRawDataPerMinute');
Route::get('/materializeForPerCompanyPerTradingDay', 'routinesController@materializeForPerCompanyPerTradingDay');
Route::get('/performEOD', 'routinesController@performEOD');
Route::get('/sendAlertsToSubscribers', 'routinesController@sendAlertsToSubscribers');
Route::get('/testSMS', 'routinesController@testSMS');

// Let's stop this for the meantime.
// Route::get('auth/csrf_token', 'CsrfController@csrf_token');