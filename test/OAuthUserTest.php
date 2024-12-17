<?php

namespace Salla\OAuth2\Client\Test;

use Salla\OAuth2\Client\Models\OAuthUser;
use PHPUnit\Framework\TestCase;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class OAuthUserTest extends TestCase
{
    protected $resourceOwnerMock;
    protected $oauthUser;

    protected function setUp(): void
    {
        $this->resourceOwnerMock = $this->createMock(ResourceOwnerInterface::class);
        $this->oauthUser = new OAuthUser($this->resourceOwnerMock);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(OAuthUser::class, $this->oauthUser);
    }

    public function testMagicGet()
    {
        $this->resourceOwnerMock->method('toArray')->willReturn(['name' => 'John Doe']);
        $this->assertEquals('John Doe', $this->oauthUser->name);
    }

    public function testGetAuthIdentifier()
    {
        $this->resourceOwnerMock->method('getId')->willReturn('12345');
        $this->assertEquals('12345', $this->oauthUser->getAuthIdentifier());
    }

    public function testGetAuthIdentifierNameThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->oauthUser->getAuthIdentifierName();
    }

    public function testGetAuthPasswordThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->oauthUser->getAuthPassword();
    }

    public function testGetAuthPasswordNameThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->oauthUser->getAuthPasswordName();
    }

    public function testGetRememberTokenThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->oauthUser->getRememberToken();
    }

    public function testSetRememberTokenThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->oauthUser->setRememberToken('token');
    }

    public function testGetRememberTokenNameThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->oauthUser->getRememberTokenName();
    }
}
