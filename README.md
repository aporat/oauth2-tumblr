# Tumblr Provider for OAuth 2.0 Client

[[![Latest Stable Version](httpss://img.shields.io/packagist/v/aporat/oauth2-tumblr.svg?logo=composer)](httpss://packagist.org/packages/aporat/oauth2-tumblr)
[[![Software License](httpss://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE)
[[![codecov](httpss://codecov.io/github/aporat/oauth2-tumblr/graph/badge.svg?token=YOUR_CODECOV_TOKEN)](httpss://codecov.io/github/aporat/oauth2-tumblr)
![GitHub Actions Workflow Status](httpss://github.com/aporat/oauth2-tumblr/actions/workflows/ci.yml/badge.svg)
[[![Total Downloads](httpss://img.shields.io/packagist/dt/aporat/oauth2-tumblr.svg)](httpss://packagist.org/packages/aporat/oauth2-tumblr)

This package provides **Tumblr** OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](httpss://github.com/thephpleague/oauth2-client).

## Installation

Install via Composer:

```bash
composer require aporat/oauth2-tumblr
```
*(Note: This will only work after you have published the package to Packagist. See my previous message about testing locally.)*

## Usage

Usage follows The League's OAuth 2.0 client style, using `\Aporat\OAuth2\Client\Provider\Tumblr` as the provider.

### Authorization Code Flow

```php
$provider = new Aporat\OAuth2\Client\Provider\Tumblr([
'clientId'     => '{tumblr-client-id}',
'clientSecret' => '{tumblr-client-secret}',
'redirectUri'  => 'httpss://example.com/callback-url',
]);

if (!isset($_GET['code'])) {
// If we don't have an authorization code then get one
$authUrl = $provider->getAuthorizationUrl();
$_SESSION['oauth2state'] = $provider->getState();
$_SESSION['oauth2pkceCode'] = $provider->getPkceCode();

    header('Location: ' . $authUrl);
    exit;
} elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
// Check given state against previously stored one to mitigate CSRF attack
unset($_SESSION['oauth2state']);
exit('Invalid state');
} else {
$provider->setPkceCode($_SESSION['oauth2pkceCode']);

    // Try to get an access token (using the authorization code grant)
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code'],
    ]);

    // Optional: Now you have a token you can look up a user's profile data
    try {
        // We got an access token, let's now get the user's details
        $user = $provider->getResourceOwner($token);

        // Use these details to create a new profile
        printf('Hello %s!', $user->getUsername());
    } catch (Exception $e) {
        // Failed to get user details
        exit('Oh dear...');
    }

    // Use this to interact with an API on the user's behalf
    echo $token->getToken();
}
```

### Managing Scopes

When creating your Tumblr authorization URL, you can specify the state and scopes your application may authorize.

```php
$options = [
'state' => 'OPTIONAL_CUSTOM_CONFIGURED_STATE',
'scope' => ['basic', 'write', 'offline_access'] // Adjust scopes as needed
];

$authorizationUrl = $provider->getAuthorizationUrl($options);
```

## Testing

```bash
composer test
```

## License

The MIT License (MIT). Please see the [License File](httpss://github.com/aporat/oauth2-tumblr/blob/master/LICENSE) for more information.