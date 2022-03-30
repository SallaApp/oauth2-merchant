<div id="top"></div>
<div align="center"> 
  <a href="https://salla.dev"> 
    <img src="https://salla.dev/wp-content/themes/salla-portal/dist/img/salla-logo.svg" alt="Logo" width="80" height="80"> 
  </a>
  <h1 align="center">Salla OAuth 2.0 Client</h1>
  <p align="center">
    This package provides <a href="https://salla.dev/blog/oauth-callback-urls/">Salla OAuth 2.0</a> support for the PHP language <a href="https://github.com/thephpleague/oauth2-client">OAuth 2.0 Client</a>.
    <br />
    <a href="https://salla.dev/"><strong>Explore our blogs »</strong></a>
    <br />
    <br />
    <a href="https://github.com/SallaApp/oauth2-merchant/issues/new">Report Bug</a> · 
    <a href="https://github.com/SallaApp/oauth2-merchant/discussions/new">Request Feature</a>
     · <a href="https://t.me/salladev">&lt;/Salla Developers&gt;</a>
  </p>
</div>

## Overview

To use this package, it will be necessary to have a Salla client ID and client secret. These are referred to as `{client-id}` and `{client-secret}` in the documentation.

Please follow the [Salla instructions][oauth-setup] to create the required credentials.

[oauth-setup]: https://salla.dev/blog/oauth-callback-urls/

## OAuth Workflow

![OAuth Workflow](https://i.ibb.co/xLyn80t/Frame-1236-OAuth-5.png )

## Installation

You can install the package via composer:

```bash
composer require salla/ouath2-merchant
```
<p align="right">(<a href="#top">back to top</a>)</p>

## Usage

### Authorization Code Flow

```php
<?php

require_once './vendor/autoload.php';

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
    // You should store the refresh token somewhere in your system because the access token expired after 14 days
    // so you can use the refresh token after that to generate a new access token without asking any access from the merchant
    //
    // $token = $provider->getAccessToken(new RefreshToken(), ['refresh_token' => $token->getRefreshToken()]);
    //
    echo 'Refresh Token: '.$token->getRefreshToken()."<br>";

    //
    // ## Expire date
    //
    // This helps you to know when the access token will be expired
    // so before that date, you should generate a new access token using the refresh token
    echo 'Expire Date : '.$token->getExpires()."<br>";

    //
    // ## Merchant Details
    //
    // Using the access token, we may look up details about the merchant.
    // --- Same request in Curl ---
    // curl --request GET --url 'https://accounts.salla.sa/oauth2/user/info' --header 'Authorization: Bearer <access-token>'

    /** @var \Salla\OAuth2\Client\Provider\SallaUser $user */
    $user = $provider->getResourceOwner($token);

    /**
    *  {
    *    "id": 1771165749,
    *    "name": "Test User",
    *    "email": "testuser@email.partners",
    *    "mobile": "+966500000000",
    *    "role": "user",
    *    "created_at": "2021-12-31 11:36:57",
    *    "merchant": {
    *      "id": 1803665367,
    *      "username": "dev-j8gtzhp59w3irgsw",
    *      "name": "dev-j8gtzhp59w3irgsw",
    *      "avatar": "https://i.ibb.co/jyqRQfQ/avatar-male.webp",
    *      "store_location": "26.989000873354787,49. 62477639657287",
    *      "plan": "special",
    *      "status": "active",
    *      "domain": "https://salla.sa/YOUR-DOMAIN-NAME",
    *      "created_at": "2021-12-31 11:36:57"
    *    }
    *  }
    */
    var_export($user->toArray());

    echo 'User ID: '.$user->getId()."<br>";
    echo 'User Name: '.$user->getName()."<br>";
    echo 'Store ID: '.$user->getStoreID()."<br>";
    echo 'Store Name: '.$user->getStoreName()."<br>";


    //
    // 🥳
    //
    // You can now save the access token and refresh the token in your database
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
<p align="right">(<a href="#top">back to top</a>)</p>

## Refreshing a Token

Refresh tokens are only provided to applications that request offline access. You can specify offline access by passing the scope option in your getAuthorizationUrl() request.

```php
use Salla\OAuth2\Client\Provider\Salla;

$provider = new Salla([
    'clientId' => '{client-id}',
    'clientSecret' => '{client-secret}',
]);

$refreshToken = 'FromYourStoredData';
$token = $provider->getAccessToken('refresh_token', ['refresh_token' => $refreshToken]);

```

## Testing

```bash
composer test
```
<p align="right">(<a href="#top">back to top</a>)</p>

## Support

The team is always here to help you. Happen to face an issue? Want to report a bug? You can submit one here on Github using the [Issue Tracker](https://github.com/SallaApp/Salla-CLI/issues/new). If you still have any questions, please contact us via the [Telegram Bot](https://t.me/SallaSupportBot) or join in the Global Developer Community on [Telegram](https://t.me/salladev).

## Contributing

Contributions are what make the open-source community such an amazing place to learn, inspire, and create. 
Any contributions you make are **greatly appreciated**.

If you have a suggestion that would make this better, please fork the repo and create a pull request. 
You can also simply open an issue with the tag "enhancement". Don't forget to give the project a star! Thanks again!

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

<p align="right">(<a href="#top">back to top</a>)</p>

## Security

If you discover any securitys-related issues, please email security@salla.sa instead of using the issue tracker.

## Credits

- [Salla](https://github.com/sallaApp)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

<p align="right">(<a href="#top">back to top</a>)</p>
