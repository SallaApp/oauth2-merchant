<?php

namespace Salla\OAuth2\Client\Test;

use Eloquent\Phony\Phpunit\Phony;
use GuzzleHttp\Psr7\Utils;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Salla\OAuth2\Client\Provider\Salla;
use PHPUnit\Framework\TestCase;
use Mockery as m;

class SallaTest extends TestCase
{
    protected $provider;

    protected function setUp() :void
    {
        $this->provider = new Salla([
            'clientId' => 'ac940263c5658074da4ec65530f813bd',
            'clientSecret' => '654c5698fb336a2751bf65470b656bcf',
            'redirectUri' => 'https://yourservice.com/callback_url',
        ]);
    }

    public function tearDown() :void
    {
        m::close();
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

        $this->assertAttributeNotEmpty('state', $this->provider);
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
        // Mock
        $response = [
            'id' => '12345',
            'name' => 'mock name',
            'email' => 'mock.name@example.com',
            'mobile' => '05000000',
            'role' => 'user',
            'created_at' => '2018-04-28 17:46:25',
            'store'=>[
                'id'=>'11111',
                'owner_id'=> '12345',
                'owner_name'=> 'mock name',
                'username'=> 'mock_name',
                'name'=> 'mock name',
                'avatar'=>'mock_avatar',
                'store_location'=>'mock_location',
                'plan'=>'mock_plan',
                'status'=>'mock_status',
                'created_at'=>'2018-04-28 17:46:25',
            ]
        ];

        $token = $this->mockAccessToken();

        $provider = Phony::partialMock(Salla::class);
        $provider->fetchResourceOwnerDetails->returns($response);
        $salla = $provider->get();

        // Execute
        $user = $salla->getResourceOwner($token);

        // Verify
        Phony::inOrder(
            $provider->fetchResourceOwnerDetails->called()
        );

        $this->assertInstanceOf('League\OAuth2\Client\Provider\ResourceOwnerInterface', $user);

        $this->assertEquals(12345, $user->getId());
        $this->assertEquals('mock name', $user->getName());
        $this->assertEquals('mock.name@example.com', $user->getEmail());
        $this->assertEquals('05000000', $user->getMobile());
        $this->assertEquals('user', $user->getRole());
        $this->assertEquals( '2018-04-28 17:46:25', $user->getCreatedAt()->format('Y-m-d H:i:s'));
        $this->assertEquals(11111, $user->getStoreId());
        $this->assertEquals(12345, $user->getStoreOwnerID());
        $this->assertEquals('mock name', $user->getStoreOwnerName());
        $this->assertEquals('mock_name', $user->getStoreUsername());
        $this->assertEquals('mock name', $user->getStoreName());
        $this->assertEquals('mock_avatar', $user->getStoreAvatar());
        $this->assertEquals('mock_location', $user->getStoreLocation());
        $this->assertEquals('mock_plan', $user->getStorePlan());
        $this->assertEquals('mock_status', $user->getStoreStatus());
        $this->assertEquals( '2018-04-28 17:46:25', $user->getStoreCreatedAt()->format('Y-m-d H:i:s'));

        $user = $user->toArray();

        $this->assertArrayHasKey('id', $user);
        $this->assertArrayHasKey('name', $user);
        $this->assertArrayHasKey('email', $user);
        $this->assertArrayHasKey('mobile', $user);
        $this->assertArrayHasKey('role', $user);
        $this->assertArrayHasKey('role', $user);
        $this->assertArrayHasKey('created_at', $user);
        $this->assertArrayHasKey('store', $user);
    }

    public function testErrorResponse()
    {
        // Mock
        $error_json = '{"error": "invalid_code"}';
        $error_stream = Utils::streamFor('{"error": "invalid_code"}');

        $response = Phony::mock('GuzzleHttp\Psr7\Response');
        $response->getHeader->returns(['application/json']);
        $response->getBody->returns($error_stream);
        $provider = Phony::partialMock(Salla::class);
        $provider->getResponse->returns($response);

        $salla = $provider->get();

        $token = $this->mockAccessToken();
        // Expect
        $this->expectException(IdentityProviderException::class);

        // Execute
        $user = $salla->getResourceOwner($token);

        // Verify
        Phony::inOrder(
            $provider->getResponse->calledWith($this->instanceOf('GuzzleHttp\Psr7\Request')),
            $response->getHeader->called(),
            $response->getBody->called()
        );
    }

    public function testCreateAccessToken()
    {
        $live_time = 3600;
        $response_json = [
            'access_token' => 'moc_access_token',
            'refresh_token' => 'moc_refresh_token',
            'expires_in' => $live_time,
        ];
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn(json_encode($response_json));
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);
        /**
         * @var AccessToken $token
         *
         * */
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
