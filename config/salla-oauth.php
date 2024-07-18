<?php

return [
    'clientId' => env('OAUTH2_CLIENT_ID'),
    'clientSecret' => env('OAUTH2_CLIENT_SECRET'),
    'redirectUri' => env('OAUTH2_REDIRECT_URI'),
    'base_url' => env('SALLA_BASE_URL', 'https://accounts.salla.sa'),
];
