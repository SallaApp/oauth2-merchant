<?php

namespace Salla\OAuth2\Client\Provider;

use Illuminate\Support\ServiceProvider;

class SallaServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->singleton('SallaOauth', function ($app) {
            return new Salla([
                'clientId' => config('salla-oauth.clientId'),
                'clientSecret' => config('salla-oauth.clientSecret'),
                'redirectUri' => config('salla-oauth.redirectUri'),
            ], [], config('salla-oauth.base_url', 'https://accounts.salla.sa'));
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/salla-oauth.php' => config_path('salla-oauth.php')
        ], 'salla-oauth');

        app('router')->aliasMiddleware('salla.auth', \Salla\OAuth2\Client\Middleware\UserInfoMiddleware::class);
    }
}
