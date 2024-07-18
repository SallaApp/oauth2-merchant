<?php

namespace Salla\OAuth2\Client\Test;

use Salla\OAuth2\Client\ServiceProvider;
abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}
