<?php

namespace Salla\OAuth2\Client\Test;

use League\OAuth2\Client\Token\AccessToken;
use Salla\OAuth2\Client\Contracts\SallaOauth;
use Salla\OAuth2\Client\Http\OauthMiddleware;
use Salla\OAuth2\Client\Provider\Salla;

class OauthMiddlewareTest extends TestCase
{
    private $userData;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->token = new AccessToken(['access_token' => 'foobar']);

        $this->userData = [
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
                ],
                'context' => [
                    'app' => '123',
                    'scope' => 'orders.read products.read',
                    'exp' => 1721326955
                ]
            ]
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['router']->get('hello/user')->name('auth.user')->uses(function () {
            return 'hello ' . auth()->guard('salla-oauth')->user()->getAuthIdentifier();
        })->middleware(OauthMiddleware::class);

        $app['router']->get('hello/user-order-read-scope')->uses(function () {
            return 'hello ' . auth()->guard('salla-oauth')->user()->getAuthIdentifier();
        })->middleware('salla.oauth:orders.read');

        $app['router']->get('hello/user-order-multiple-scopes')->uses(function () {
            return 'hello ' . auth()->guard('salla-oauth')->user()->getAuthIdentifier();
        })->middleware('salla.oauth:orders.read,orders.read_write');

        $app['router']->get('hello/guest')->name('auth.guest')->uses(function () {
            return 'hello guest';
        });
    }

    protected function setupMockSalla($userData = null)
    {
        $this->app->singleton(SallaOauth::class, function () {
            return $this->getMockBuilder(Salla::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['fetchResourceOwnerDetails'])
                ->getMock();
        });

        $mockSalla = $this->app->make(SallaOauth::class);
        $mockSalla->expects($this->once())
            ->method('fetchResourceOwnerDetails')
            ->with($this->equalTo($this->token))
            ->willReturn($userData ?? $this->userData);

        return $mockSalla;
    }

    protected function makeAuthRequest($uri)
    {
        return $this->get($uri, ['Authorization' => 'Bearer foobar']);
    }

    public function testUnAuthWhenTokenIsNotProvided()
    {
        $response = $this->get('hello/user');
        $response->assertStatus(401);
    }

    public function testUnAuthWhenTokenIsNotValid()
    {
        $response = $this->get('hello/user', ['Authorization' => 'Bearer foobar']);
        $response->assertStatus(401);
    }

    public function testAddsUserinfoToRequest()
    {
        $this->setupMockSalla();

        $response = $this->makeAuthRequest('hello/user');
        $response->assertStatus(200)->assertSeeText('hello 12345');

        $authGuard = auth()->guard('salla-oauth');
        $this->assertTrue($authGuard->check());
        $this->assertSame($this->userData['data']['id'], $authGuard->user()->getAuthIdentifier());
    }

    public function testCheckAllowedUserScope()
    {
        $this->setupMockSalla();

        $response = $this->makeAuthRequest('hello/user-order-read-scope');
        $response->assertStatus(200)->assertSeeText('hello 12345');
    }

    public function testCheckNotAllowedUserScope()
    {
        $userData = $this->userData;
        $userData['data']['context']['scope'] = 'customers.read';

        $this->setupMockSalla($userData);

        $response = $this->makeAuthRequest('hello/user-order-read-scope');
        $response->assertStatus(401)->assertSeeText('Unauthorized');
    }

    public function testCheckAllowedUserMultipleScopes()
    {
        $userData = $this->userData;
        $userData['data']['context']['scope'] = 'orders.read_write';
        $this->setupMockSalla($userData);

        $response = $this->makeAuthRequest('hello/user-order-multiple-scopes');
        $response->assertStatus(200)->assertSeeText('hello 12345');
    }

    public function testCachedUser()
    {
        $userData = $this->userData;
        $userData['data']['context'] = null;
        $this->setupMockSalla($userData);

        $response = $this->makeAuthRequest('hello/user');
        $response->assertStatus(200)->assertSeeText('hello 12345');

        $response = $this->makeAuthRequest('hello/user-order-read-scope');
        $response->assertStatus(401);
    }
}
