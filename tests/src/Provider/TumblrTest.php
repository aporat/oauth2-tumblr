<?php

namespace Aporat\OAuth2\Client\Test\Provider;

use Aporat\OAuth2\Client\Provider\Exception\TumblrIdentityProviderException;
use Aporat\OAuth2\Client\Provider\Tumblr; // Changed
use League\OAuth2\Client\Tool\QueryBuilderTrait;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

use function http_build_query;
use function json_encode;
use function uniqid;

class TumblrTest extends TestCase // Changed
{
    use QueryBuilderTrait;

    protected Tumblr $provider; // Changed

    protected function setUp(): void
    {
        $this->provider = new Tumblr( // Changed
            [
                'clientId' => 'mock_client_id',
                'clientSecret' => 'mock_secret',
                'redirectUri' => 'none',
            ]
        );
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);

        // PKCE is required
        $this->assertArrayHasKey('code_challenge', $query);
        $this->assertArrayHasKey('code_challenge_method', $query);

        $this->assertNotNull($this->provider->getState());
    }

    public function testScopes(): void
    {
        $scopeSeparator = ' ';
        $options = ['scope' => [uniqid(), uniqid()]];
        $query = ['scope' => implode($scopeSeparator, $options['scope'])];
        $url = $this->provider->getAuthorizationUrl($options);
        $encodedScope = $this->buildQueryString($query);

        $this->assertStringContainsString($encodedScope, $url);
    }

    public function testGetAuthorizationUrl(): void
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);

        // Updated for Tumblr
        $this->assertEquals('/oauth2/authorize', $uri['path']);
    }


    public function testGetBaseAccessTokenUrl(): void
    {
        $params = [];

        $url = $this->provider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);

        // Updated for Tumblr
        $this->assertEquals('/v2/oauth2/token', $uri['path']);
    }

    public function testGetAccessToken(): void
    {
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')
            ->andReturn($this->createStream(
            // Added expires_in and refresh_token
                '{"access_token":"mock_access_token", "scope":"basic write offline_access", "token_type":"bearer", "expires_in":3600, "refresh_token":"mock_refresh_token"}'
            ));
        $response->shouldReceive('getHeader')
            ->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')
            ->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertNotNull($token->getExpires()); // Changed
        $this->assertEquals('mock_refresh_token', $token->getRefreshToken()); // Changed
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testExceptionThrownWhenErrorReceived(): void
    {
        $status = 401;
        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')
            ->andReturn($this->createStream(json_encode([ // Changed to Tumblr error format
                "error" => "invalid_grant",
                "error_description" => "The code passed is incorrect or expired.",
            ])));
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn($status);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->provider->setHttpClient($client);

        $this->expectException(TumblrIdentityProviderException::class);

        $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
    }

    private function createStream(string $body): StreamInterface
    {
        $stream = m::mock('Psr\Http\Message\StreamInterface');
        $stream->shouldReceive('__toString')
            ->andReturn($body);

        return $stream;
    }
}
