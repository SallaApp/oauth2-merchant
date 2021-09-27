<?php

namespace Salla\OAuth2\Client\Test;

use Salla\OAuth2\Client\Provider\Salla;
use PHPUnit\Framework\TestCase;

class SallaScopeTest extends TestCase
{
    public function testDefaultScopes()
    {
        $provider = new Salla([
            'clientId' => 'client-id',
            'clientSecret' => 'client-secret',
        ]);

        $url = $provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('scope', $query);
        $this->assertSame('', $query['scope']);
    }

    public function testOfflineAccessScope()
    {
        $provider = new Salla([
            'clientId' => 'client-id',
            'clientSecret' => 'client-secret',
        ]);

        $url = $provider->getAuthorizationUrl([
            'scope' => ['offline_access'],
        ]);
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('scope', $query);
        $this->assertContains('offline_access', $query['scope']);
    }
}
