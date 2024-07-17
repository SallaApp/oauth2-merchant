<?php

namespace Salla\OAuth2\Client\Provider;

use Illuminate\Support\ServiceProvider;

class SallaServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->singleton(Salla::class, function ($app) {
            $config = config('salla-oauth');
            return new Salla([
                'clientId' => $config['clientId'],
                'clientSecret' => $config['clientSecret'],
                'redirectUri' => $config['redirectUri'],
            ]);
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/salla-oauth.php' => config_path('salla-oauth.php')
        ], 'salla-oauth');
    }
}
