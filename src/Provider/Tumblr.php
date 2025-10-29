<?php

namespace Aporat\OAuth2\Client\Provider;

use Aporat\OAuth2\Client\Provider\Exception\TumblrIdentityProviderException;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Tumblr extends AbstractProvider
{
    use BearerAuthorizationTrait;

    protected function getPkceMethod(): ?string
    {
        return AbstractProvider::PKCE_METHOD_S256;
    }

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return 'https://www.tumblr.com/oauth2/authorize';
    }

    /**
     * Get access token url to retrieve token
     *
     * @param array $params
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return 'https://api.tumblr.com/v2/oauth2/token';
    }

    /**
     * Get provider url to fetch user details
     *
     * @param AccessToken $token
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        return 'https://api.tumblr.com/v2/user/info';
    }

    /**
     * Get the default scopes used by this provider.
     *
     * @return array
     */
    protected function getDefaultScopes(): array
    {
        return [
            'basic',
            'write',
            'offline_access'
        ];
    }

    /**
     * Returns the string that should be used to separate scopes when building
     * the URL for requesting an access token.
     *
     * @return string Contains one space (` `)
     */
    protected function getScopeSeparator(): string
    {
        return ' ';
    }

    /**
     * Check a provider response for errors.
     *
     * @param ResponseInterface $response
     * @param array $data Parsed response data
     * @return void
     *
     * @throws IdentityProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data): void
    {
        if ($response->getStatusCode() >= 400) {
            throw new TumblrIdentityProviderException(
                $data['meta']['msg'] ?? $data['error_description'] ?? $data['error'] ?? $response->getReasonPhrase(),
                $response->getStatusCode(),
                $response
            );
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param array $response
     * @param AccessToken $token
     * @return TumblrResourceOwner
     */
    protected function createResourceOwner(
        array $response,
        AccessToken $token
    ): TumblrResourceOwner {
        return new TumblrResourceOwner($response);
    }
}
