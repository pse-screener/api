<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        //

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::group([
            'middleware' => 'web',
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/web.php');
        });
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::group([
            'namespace' => $this->namespace,
            'prefix' => 'api/v1'
        ], function ($router) { // Unprotected API routes
            Route::post('/emailConfirmation/{hash}', 'Auth\RegisterController@emailConfirmation');
            Route::resource('/contactUs', 'ContactUsController', ['only' => ['store']]);
            /* From the http://<mysite.com>/public/#/sendFreeSMS send button
             * http://www.pse-screener.com/api/v1/sendFreeSms */
            Route::resource('/sendFreeSms', 'SendFreeSmsController', ['only' => ['store']]);

            /** From the downstream, retrieving the SMS messages to be sent because AWS cannot have modem.
             *  No auth:api at the moment
             */
            Route::get('/smsMessages', 'SmsMessagesController@index');


            Route::group(['middleware' => 'auth:api'], function($router) {
                // Inside here are protected API routes
                require base_path('routes/api.php');
            });
        });
    }
}
