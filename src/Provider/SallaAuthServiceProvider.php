<?php

namespace Salla\OAuth2\Client\Provider;

use Illuminate\Support\ServiceProvider;

class SallaAuthServiceProvider extends ServiceProvider
{


    /**
     * Register SallaOauth application services.
     */
    public function register()
    {
        $this->app->singleton('SallaOauth', function () {
            return new Salla([
                'clientId' => config('sallaOauth.clientId'),
                'clientSecret' => config('sallaOauth.clientSecret'),
                'redirectUri' => config('sallaOauth.redirectUrl'),
            ], [], config('sallaOauth.base_url', 'https://accounts.salla.sa'));
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/sallaOauth.php' => config_path('sallaOauth.php')
        ], 'salla-oauth');

        app('router')->aliasMiddleware('user.data', \Salla\OAuth2\Client\Middleware\UserInfoMiddleware::class);
    }
}
