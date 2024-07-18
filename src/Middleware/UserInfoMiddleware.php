<?php

namespace Salla\OAuth2\Middleware;

use Closure;
use Salla\OAuth2\Client\Provider\Salla;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use League\OAuth2\Client\Token\AccessToken;

class OauthMiddleware
{
    public function handle($request, Closure $next, string $scope = null)
    {
        $bearerToken = $request->bearerToken();

        if (empty($bearerToken)) {
            return response()->json(['error' => 'please provide a valid API Key'], 401);
        }

        $cacheKey = 'salla_user_' . substr($bearerToken, -8);
        $cachedUserData = Cache::get($cacheKey);

        if (!$cachedUserData) {

            $tokenObject = new AccessToken(['access_token' => $bearerToken]);

            $resourceOwnerDetailsUrl = $this->salla->getResourceOwnerDetailsUrl($tokenObject);

            $response = Http::withToken($bearerToken)->get($resourceOwnerDetailsUrl);

            if ($response->status() !== 200) {
                return response()->json(['error' => 'Unauthorized Access'], 401);
            }

            $cachedUserData = $response->json();
            Cache::put($cacheKey, $cachedUserData, now()->addMinutes(30)); // TTL is set to 30 minutes
        }

        request()->attributes->set('app_user_data', $cachedUserData['data']);

        return $next($request);
    }
}
