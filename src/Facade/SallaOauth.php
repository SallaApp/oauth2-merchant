<?php

namespace Salla\OAuth2\Client\Facade;

use Illuminate\Support\Facades\Facade;

class SallaOauth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Salla\OAuth2\Client\Contracts\SallaOauth::class;
    }
}
