<?php

return [
        'clientId' => env('SALLA_OAUTH_CLIENT_ID'),
        'clientSecret' => env('SALLA_OAUTH_CLIENT_SECRET'),
        'redirectUrl' => env('SALLA_OAUTH_REDIRECT_URL'),
        'base_url' => env('SALLA_OAUTH_BASE_URL', 'https://accounts.salla.sa'),
];
