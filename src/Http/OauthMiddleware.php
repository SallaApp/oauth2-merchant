<?php

namespace Salla\OAuth2\Client\Http;

use Closure;
use Illuminate\Support\Facades\Cache;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Salla\OAuth2\Client\Facade\SallaOauth;
use Salla\OAuth2\Client\Provider\SallaUser;

class OauthMiddleware
{

    private ?ResourceOwnerInterface $user = null;


    public function handle($request, Closure $next, string $scope = null)
    {
        $token = $request->bearerToken();

        if (empty($token)) {
            abort(401, 'Please provide a valid token');
        }

        $cacheKey = config()->get('salla-oauth.cache-prefix') . '.' . $token;

        $user = Cache::get($cacheKey);

        if ($user) {
            $this->user = new SallaUser($user);
        }

        if ($this->user) {
            return $this->nextRequest($next, $request);
        }

        try {
            /** @var \Salla\OAuth2\Client\Provider\SallaUser $user */
            $this->user = SallaOauth::getResourceOwner(new AccessToken([
                'access_token' => $token,
            ]));
        } catch (\Exception $exception) {
            abort(401, 'Unauthorized Access');
        }

        // todo :: implement check the scopes
        // todo:: $this->user->getScope()

        $exception_at = now()->diffInSeconds($this->user->getExpiredAt());

        Cache::put($cacheKey, $this->user->toArray(), now()->addSeconds($exception_at));

        return $this->nextRequest($next, $request);
    }

    public function nextRequest(Closure $next, $request): mixed
    {
        request()->attributes->set('salla.oauth.user', $this->user);

        return $next($request);
    }
}
