<?php

return [
    'client_id' => env('SALLA_OAUTH_CLIENT_ID'),
    'client_secret' => env('SALLA_OAUTH_CLIENT_SECRET'),
    'redirect_url' => env('SALLA_OAUTH_CLIENT_REDIRECT_URI'),
    'base_url' => env('SALLA_OAUTH_BASE_URL', 'https://accounts.salla.sa'),
    'cache-prefix' => env('SALLA_OAUTH_PREFIX_CACHE', 'oauth'),
    'cache-tag' => env('SALLA_OAUTH_CACHE_TAG', ''),
];
