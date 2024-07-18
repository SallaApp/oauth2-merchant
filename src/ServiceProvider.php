<?php

namespace Salla\OAuth2\Client;

use Illuminate\Support\Facades\Auth;
use Salla\OAuth2\Client\Auth\AuthRequest;
use Salla\OAuth2\Client\Auth\Guard;
use Salla\OAuth2\Client\Contracts\SallaOauth;
use Salla\OAuth2\Client\Http\OauthMiddleware;
use Salla\OAuth2\Client\Provider\Salla;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/salla-oauth.php', 'salla-oauth');

        $this->app->singleton(SallaOauth::class, function () {
            return (new Salla([
                'clientId' => config('salla-oauth.client_id'),
                'clientSecret' => config('salla-oauth.client_secret'),
                'redirectUri' => config('salla-oauth.redirect_url'),
            ]))->setBaseUrl(config('salla-oauth.base_url'));
        });

        $this->app['config']->set('auth.guards.salla-oauth', [
            'driver' => 'salla-oauth'
        ]);
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/salla-oauth.php' => config_path('salla-oauth.php')
        ], 'salla-oauth');

        app('router')->aliasMiddleware('salla.oauth', OauthMiddleware::class);

        Auth::extend('salla-oauth', function () {
            $guard = new Guard($this->app->make(AuthRequest::class), $this->app['request']);

            $this->app->refresh('request', $guard, 'setRequest');

            return $guard;
        });
    }
}
