<?php

namespace Salla\OAuth2\Client\Auth;

use Illuminate\Http\Request;
use Salla\OAuth2\Client\Models\OAuthUser;

class AuthRequest
{
    public function __invoke(Request $request)
    {
        $user = $request->attributes->get('salla.oauth.user');

        if (!$user) {
            return null;
        }

        return new OAuthUser($user);
    }
}
