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
Route::get('/downloadCompaniesAndPrices', 'RoutinesController@downloadCompaniesAndPrices');
Route::get('/harvestDownloadedCompaniesAndPrices', 'RoutinesController@harvestDownloadedCompaniesAndPrices');
Route::get('/materializeRawDataPerMinute', 'RoutinesController@materializeRawDataPerMinute');
Route::get('/materializeForPerCompanyPerTradingDay', 'RoutinesController@materializeForPerCompanyPerTradingDay');
// Route::get('/performEOD', 'RoutinesController@performEOD');	// looks like we don't need this anymore.
Route::get('/sendDailyAlertsToSubscribers', 'RoutinesController@sendDailyAlertsToSubscribers');

/* As this route imply. */
Route::get('/sendPerMinuteAlertsToSubscribers', 'RoutinesController@sendPerMinuteAlertsToSubscribers');

Route::get('/testSms', 'RoutinesController@testSms');

// if there's lacking date
Route::get('/downloadCompaniesAndPricesByDate/{date}', 'RoutinesController@downloadCompaniesAndPricesByDate');
Route::get('/harvestDownloadedCompaniesAndPricesPerCompany', 'RoutinesController@harvestDownloadedCompaniesAndPricesPerCompany');

// if current date is the one lacking
Route::get('/downloadCompaniesAndPricesByCurrentDate', 'RoutinesController@downloadCompaniesAndPricesByCurrentDate');

// this will scan smsMessages table and send it to recipient
// Route::get('/sendSmsMessages', 'RoutinesController@sendSmsMessages');

// Alert SMS Load status
Route::get('/alertAdministratorLoadStatus', 'RoutinesController@alertAdministratorLoadStatus');

Route::get('/deleteOldRecords', 'RoutinesController@deleteOldRecords');