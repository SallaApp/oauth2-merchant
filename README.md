# Salla Provider for OAuth 2.0 Client

This package provides [Salla OAuth 2.0][oauth-setup] support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

To use this package, it will be necessary to have a Salla client ID and client
secret. These are referred to as `{client-id}` and `{client-secret}`
in the documentation.

Please follow the [Salla instructions][oauth-setup] to create the required credentials.

[oauth-setup]: https://salla.dev/blog/oauth-callback-urls/

## OAuth Workflow

![OAuth Workflow](https://i.ibb.co/xLyn80t/Frame-1236-OAuth-5.png )

## Installation

You can install the package via composer:

```bash
composer require salla/ouath2-merchant
```

## Usage

### Authorization Code Flow

```php
<?php

use Salla\OAuth2\Client\Provider\Salla;

$provider = new Salla([
    'clientId'     => '{client-id}', // The client ID assigned to you by Salla
    'clientSecret' => '{client-secret}', // The client password assigned to you by Salla
    'redirectUri'  => 'https://yourservice.com/callback_url', // the url for current page in your service
]);

if (!isset($_GET['code']) || empty($_GET['code'])) {
    // If we don't have an authorization code then get one
    $authUrl = $provider->getAuthorizationUrl([
        'scope' => 'offline_access',
        //Important: If you want to generate the refresh token, set this value as offline_access
    ]);

    header('Location: '.$authUrl);
    exit;
}

// Try to obtain an access token by utilizing the authorisations code grant.
try {
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);

    //
    // ## Access Token
    //
    // You should store the access token
    // which may use in authenticated requests against the Salla's API
    echo 'Access Token: '.$token->getToken()."<br>";

    //
    // ## Refresh Token
    //
    // You should store the refresh token in somewhere in your system because the access token expired after 14 days
    // so you can use the refresh token after that to generate an new access token without ask any access from the merchant
    //
    // $token = $provider->getAccessToken(new RefreshToken(), ['refresh_token' => $token->getRefreshToken()]);
    //
    echo 'Refresh Token: '.$token->getRefreshToken()."<br>";

    //
    // ## Expire date
    //
    // This help you to know when the access token will expired
    // so before that date you should generate a new access token using the refresh token
    echo 'Expire Date : '.$token->getExpires()."<br>";

    //
    // ## Merchant Details
    //
    // Using the access token, we may look up details about the merchant.
    // --- Same request in Curl ---
    // curl --request GET --url 'https://accounts.salla.sa/oauth2/user/info' --header 'Authorization: Bearer <access-token>'

    /** @var \Salla\OAuth2\Client\Provider\SallaUser $user */
    $user = $provider->getResourceOwner($token->getToken());

    /**
     *  {
     *      "id": 181690847,
     *      "name": "eman elsbay",
     *      "email": "user@salla.sa",
     *      "mobile": "555454545",
     *      "role": "user",
     *      "created_at": "2018-04-28 17:46:25",
     *      "store": {
     *        "id": 633170215,
     *        "owner_id": 181690847,
     *        "owner_name": "eman elsbay",
     *        "username": "good-store",
     *        "name": "متجر الموضة",
     *        "avatar": "https://cdn.salla.sa/XrXj/g2aYPGNvafLy0TUxWiFn7OqPkKCJFkJQz4Pw8WsS.jpeg",
     *        "store_location": "26.989000873354787,49.62477639657287",
     *        "plan": "special",
     *        "status": "active",
     *        "created_at": "2019-04-28 17:46:25"
     *      }
     *    }
     */
    var_export($user->toArray());

    echo 'User ID: '.$user->getId()."<br>";
    echo 'User Name: '.$user->getName()."<br>";
    echo 'Store ID: '.$user->getStoreID()."<br>";
    echo 'Store Name: '.$user->getStoreName()."<br>";


    //
    // 🥳
    //
    // You can now save the access token and refresh token in your database
    // with the merchant details and redirect him again to Salla dashboard (https://s.salla.sa/apps)


    //
    // ## Access to authenticated APIs for the merchant
    //
    // You can also use the same package to call any authenticated APIs for the merchant
    // Using the access token, information can be obtained from a list of endpoints.
    //
    // --- Same request in Curl ---
    // curl --request GET --url 'https://api.salla.dev/admin/v2/orders' --header 'Authorization: Bearer <access-token>'
    $response = $provider->fetchResource(
        'GET',
        'https://api.salla.dev/admin/v2/orders',
        $token->getToken()
    );
    
    var_export($response);

} catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
    // Failed to get the access token or merchant details.
    // show a error message to the merchant with good UI
    exit($e->getMessage());
}
```

## Refreshing a Token

Refresh tokens are only provided to applications which request offline access. You can specify offline access by passing the scope option in your getAuthorizationUrl() request.

```php
use Salla\OAuth2\Client\Provider\Salla;

$provider = new Salla([
    'clientId' => '{client-id}',
    'clientSecret' => '{client-secret}',
]);

$refreshToken = 'FromYourStoredData';
$token = $provider->getAccessToken('refresh_token', ['refresh_token' => $refreshToken]);

```

### Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email security@salla.sa instead of using the issue tracker.

## Credits

-   [Salla](https://github.com/sallaApp)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
