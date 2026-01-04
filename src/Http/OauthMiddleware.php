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


    public function handle($request, Closure $next, string ...$scopes)
    {
        $token = $request->bearerToken();

        if (empty($token)) {
            abort(401, 'Please provide a valid token');
        }

        $cacheKey = config()->get('salla-oauth.cache-prefix') . '.' . $token;

        $user = $this->cacheGet($cacheKey);

        if ($user) {
            $this->user = new SallaUser($user);
        }

        if ($this->user) {
            $this->validateScopes($scopes);

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

        $this->validateScopes($scopes);

        $exception_at = now()->diffInSeconds($this->user->getExpiredAt());

        $this->cachePut($cacheKey, ['data' => $this->user->toArray()], now()->addSeconds($exception_at));

        return $this->nextRequest($next, $request);
    }

    private function validateScopes($scopes)
    {
        if (!empty($scopes) && collect(explode(' ', $this->user->getScope()))->intersect($scopes)->isEmpty()) {
            abort(401, 'Unauthorized Access (The scope not allowed)');
        }
    }

    public function nextRequest(Closure $next, $request): mixed
    {
        request()->attributes->set('salla.oauth.user', $this->user);

        return $next($request);
    }

    private function cacheGet(string $key): mixed
    {
        if (Cache::supportsTags() && config('salla-oauth.cache-tag')) {
            return Cache::tags([config('salla-oauth.cache-tag')])->get($key);
        }

        return Cache::get($key);
    }

    private function cachePut(string $key, mixed $value, \DateTimeInterface $ttl): bool
    {
        if (Cache::supportsTags() && config('salla-oauth.cache-tag')) {
            return Cache::tags([config('salla-oauth.cache-tag')])->put($key, $value, $ttl);
        }

        return Cache::put($key, $value, $ttl);
    }
}
