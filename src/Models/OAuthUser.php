<?php

namespace Salla\OAuth2\Client\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class OAuthUser implements Authenticatable
{
    protected ResourceOwnerInterface $user;

    public function __construct(ResourceOwnerInterface $user)
    {
        $this->user = $user;
    }

    public function __get($name)
    {
        return $this->user->toArray()[$name] ?? null;
    }

    public function getAuthIdentifierName()
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function getAuthIdentifier()
    {
        return $this->user->getId();
    }

    public function getAuthPassword()
    {
        throw new \BadMethodCallException('Not available for OAuth users');
    }

    public function getAuthPasswordName()
    {
        throw new \BadMethodCallException('Not available for OAuth users');
    }

    public function getRememberToken()
    {
        throw new \BadMethodCallException('Not available for OAuth users');
    }

    public function setRememberToken($value)
    {
        throw new \BadMethodCallException('Not available for OAuth users');
    }

    public function getRememberTokenName()
    {
        throw new \BadMethodCallException('Not available for OAuth users');
    }
}
