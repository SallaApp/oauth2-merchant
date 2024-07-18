<?php

namespace Salla\OAuth2\Client\Test;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Token\AccessToken;
use Salla\OAuth2\Client\Provider\Salla;
use PHPUnit\Framework\TestCase;

class SallaTest extends TestCase
{
    protected $provider;

    protected function setUp(): void
    {
        $this->provider = new Salla([
            'clientId' => 'ac940263c5658074da4ec65530f813bd',
            'clientSecret' => '654c5698fb336a2751bf65470b656bcf',
            'redirectUri' => 'https://yourservice.com/callback_url',
        ]);
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('scope', $query);

        $reflection = new \ReflectionClass($this->provider);
        $property = $reflection->getProperty('state');
        $property->setAccessible(true);
        $state = $property->getValue($this->provider);

        $this->assertNotEmpty($state);
    }

    public function testBaseAccessTokenUrl()
    {
        $url = $this->provider->getBaseAccessTokenUrl([]);
        $uri = parse_url($url);

        $this->assertEquals('/oauth2/token', $uri['path']);
        $this->assertEquals('accounts.salla.sa', $uri['host']);
    }

    public function testResourceOwnerDetailsUrl()
    {
        $token = $this->mockAccessToken();

        $url = $this->provider->getResourceOwnerDetailsUrl($token);

        $this->assertEquals('https://accounts.salla.sa/oauth2/user/info', $url);
    }

    public function testUserData()
    {
        $this->provider = $this->getMockBuilder(Salla::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchResourceOwnerDetails'])
            ->getMock();

        // Mock response
        $response = [
            'data' => [
                'id' => '12345',
                'name' => 'mock name',
                'email' => 'mock.name@example.com',
                'mobile' => '05000000',
                'role' => 'user',
                'created_at' => '2018-04-28 17:46:25',
                'merchant' => [
                    'id' => '11111',
                    'owner_id' => '12345',
                    'owner_name' => 'mock name',
                    'username' => 'mock_name',
                    'name' => 'mock name',
                    'avatar' => 'mock_avatar',
                    'store_location' => 'mock_location',
                    'plan' => 'mock_plan',
                    'status' => 'mock_status',
                    'created_at' => '2018-04-28 17:46:25',
                ]
            ]
        ];

        $token = $this->mockAccessToken();

        // Set up the expectation for fetchResourceOwnerDetails method
        $this->provider->expects($this->once())
            ->method('fetchResourceOwnerDetails')
            ->with($this->equalTo($token))
            ->willReturn($response);

        // Execute
        $salla = $this->provider;
        $user = $salla->getResourceOwner($token);

        // Verify
        $this->assertInstanceOf(ResourceOwnerInterface::class, $user);
        $this->assertEquals(12345, $user->getId());
        $this->assertEquals('mock name', $user->getName());
        $this->assertEquals('mock.name@example.com', $user->getEmail());
        $this->assertEquals('05000000', $user->getMobile());
        $this->assertEquals('user', $user->getRole());
        $this->assertEquals('2018-04-28 17:46:25', $user->getCreatedAt()->format('Y-m-d H:i:s'));
        $this->assertEquals(11111, $user->getStoreId());
        $this->assertEquals(12345, $user->getStoreOwnerID());
        $this->assertEquals('mock name', $user->getStoreOwnerName());
        $this->assertEquals('mock_name', $user->getStoreUsername());
        $this->assertEquals('mock name', $user->getStoreName());
        $this->assertEquals('mock_avatar', $user->getStoreAvatar());
        $this->assertEquals('mock_location', $user->getStoreLocation());
        $this->assertEquals('mock_plan', $user->getStorePlan());
        $this->assertEquals('mock_status', $user->getStoreStatus());
        $this->assertEquals('2018-04-28 17:46:25', $user->getStoreCreatedAt()->format('Y-m-d H:i:s'));

        $userArray = $user->toArray();

        $this->assertArrayHasKey('id', $userArray);
        $this->assertArrayHasKey('name', $userArray);
        $this->assertArrayHasKey('email', $userArray);
        $this->assertArrayHasKey('mobile', $userArray);
        $this->assertArrayHasKey('role', $userArray);
        $this->assertArrayHasKey('created_at', $userArray);
        $this->assertArrayHasKey('merchant', $userArray);
    }

    public function testErrorResponse()
    {
        $this->provider = $this->getMockBuilder(Salla::class)
            ->onlyMethods(['getResponse'])
            ->getMock();

        $response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHeader', 'getBody', 'getStatusCode'])
            ->getMock();

        $response->expects($this->once())
            ->method('getHeader')
            ->with('content-type')
            ->willReturn(['application/json']);


        $error_json = '{"error": "invalid_code"}';
        $error_stream = Utils::streamFor($error_json);
        $response->expects($this->once())
            ->method('getBody')
            ->willReturn($error_stream);

        $response->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(400);

        $this->provider->expects($this->once())
            ->method('getResponse')
            ->with($this->isInstanceOf('GuzzleHttp\Psr7\Request'))
            ->willReturn($response);

        $token = $this->createMock(AccessToken::class);

        // Expect
        $this->expectException(IdentityProviderException::class);

        // Execute
        $this->provider->getResourceOwner($token);
    }

    public function testCreateAccessToken()
    {
        $live_time = 3600;
        $response_json = [
            'access_token' => 'moc_access_token',
            'refresh_token' => 'moc_refresh_token',
            'expires_in' => $live_time,
        ];

        $this->provider = $this->getMockBuilder(Salla::class)
            ->onlyMethods(['getResponse'])
            ->getMock();

        $response = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getHeader', 'getBody', 'getStatusCode'])
            ->getMock();

        $response->expects($this->once())
            ->method('getHeader')
            ->with('content-type')
            ->willReturn(['application/json']);

        $response->expects($this->once())
            ->method('getBody')
            ->willReturn(Utils::streamFor(json_encode($response_json)));

        $this->provider->expects($this->once())
            ->method('getResponse')
            ->with($this->isInstanceOf('GuzzleHttp\Psr7\Request'))
            ->willReturn($response);

        /**
         * @var AccessToken $token
         */
        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $this->assertEquals($response_json['access_token'], $token->getToken());
        $this->assertEquals(time() + $response_json['expires_in'], $token->getExpires());
        $this->assertEquals($response_json['refresh_token'], $token->getRefreshToken());
    }

    /**
     * @return AccessToken
     */
    private function mockAccessToken()
    {
        return new AccessToken([
            'access_token' => 'mock_access_token',
        ]);
    }
}
