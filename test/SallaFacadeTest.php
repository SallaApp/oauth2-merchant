<?php

namespace Salla\OAuth2\Client\Test;

use Salla\OAuth2\Client\Facade\SallaOauth;
use Salla\OAuth2\Client\Provider\Salla;

class SallaFacadeTest extends TestCase
{
    public function testGetProviderByFacade()
    {
        $this->assertInstanceOf(Salla::class, SallaOauth::getFacadeRoot());
        $this->assertStringStartsWith('https://accounts.salla.sa/oauth2/auth?state=', \Salla\OAuth2\Client\Facade\SallaOauth::getAuthorizationUrl());
    }

    public function testGetProvideBySingleton()
    {
        $this->assertInstanceOf(Salla::class, $this->app->make(\Salla\OAuth2\Client\Contracts\SallaOauth::class));
    }
}
