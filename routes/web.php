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
    // return view('welcome');
    return redirect('public');	// http://170.168.21.55/public/#/
});

Auth::routes();

// Route::get('/home', 'HomeController@index');

/* Actually we want to add constraints that only to be ran locally. */
Route::get('/downloadCompaniesAndPrices', 'routinesController@downloadCompaniesAndPrices');
Route::get('/harvestDownloadedCompaniesAndPrices', 'routinesController@harvestDownloadedCompaniesAndPrices');
Route::get('/materializeRawDataPerMinute', 'routinesController@materializeRawDataPerMinute');
Route::get('/materializeForPerCompanyPerTradingDay', 'routinesController@materializeForPerCompanyPerTradingDay');
// Route::get('/performEOD', 'routinesController@performEOD');	// looks like we don't need this anymore.
Route::get('/sendDailyAlertsToSubscribers', 'routinesController@sendDailyAlertsToSubscribers');
Route::get('/testSMS', 'routinesController@testSMS');

// if there's lacking date
Route::get('/downloadCompaniesAndPricesByDate/{date}', 'routinesController@downloadCompaniesAndPricesByDate');
Route::get('/harvestDownloadedCompaniesAndPricesPerCompany', 'routinesController@harvestDownloadedCompaniesAndPricesPerCompany');

// if current date is lacking
Route::get('/downloadCompaniesAndPricesByCurrentDate', 'routinesController@downloadCompaniesAndPricesByCurrentDate');

// this will scan smsMessages table and send it to recipient
Route::get('/sendSmsMessages', 'routinesController@sendSmsMessages');

// Alert SMS Load status
Route::get('/alertAdministratorLoadStatus', 'routinesController@alertAdministratorLoadStatus');