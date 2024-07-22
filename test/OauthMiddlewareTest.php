<?php

namespace Salla\OAuth2\Client\Test;

use League\OAuth2\Client\Token\AccessToken;
use Salla\OAuth2\Client\Contracts\SallaOauth;
use Salla\OAuth2\Client\Http\OauthMiddleware;
use Salla\OAuth2\Client\Provider\Salla;

class OauthMiddlewareTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['router']->get('hello/user')->name('auth.user')->uses(function () {
            return 'hello '. auth()->guard('salla-oauth')->user()->getAuthIdentifier();
        })->middleware(OauthMiddleware::class);

        $app['router']->get('hello/user-order-read-scope')->uses(function () {
            return 'hello '. auth()->guard('salla-oauth')->user()->getAuthIdentifier();
        })->middleware('salla.oauth:orders.read');

        $app['router']->get('hello/guest')->name('auth.guest')->uses(function () {
            return 'hello guest';
        });
    }

    public function testUnAuthWhenTokenIsNotProvided()
    {
        /** @var \Illuminate\Testing\TestResponse|\Illuminate\Http\Response $response */
        $response = $this->get('hello/user');
        $response->assertStatus(401);
    }

    public function testUnAuthWhenTokenIsNotValid()
    {
        /** @var \Illuminate\Testing\TestResponse|\Illuminate\Http\Response $response */
        $response = $this->get('hello/user', [
            'Authorization' => 'Bearer foobar'
        ]);
        $response->assertStatus(401);
    }

    public function testAddsUserinfoToRequest()
    {
        $this->app->singleton(SallaOauth::class, function ()  {
            return $this->getMockBuilder(Salla::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['fetchResourceOwnerDetails'])
                ->getMock();
        });


        // Mock response
        $user = [
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

        $token = new AccessToken([
            'access_token' => 'foobar',
        ]);

        // Set up the expectation for fetchResourceOwnerDetails method
        $this->app->make(SallaOauth::class)->expects($this->once())
            ->method('fetchResourceOwnerDetails')
            ->with($this->equalTo($token))
            ->willReturn($user);

        $response = $this->get('hello/user', [
            'Authorization' => 'Bearer foobar'
        ]);
        $response->assertStatus(200)->assertSeeText('hello 12345');

        $authGuard = auth()->guard('salla-oauth');
        $this->assertTrue($authGuard->check());
        $this->assertSame($user['data']['id'], $authGuard->user()->getAuthIdentifier());
    }

    public function testCheckAllowedUserScope()
    {
        $this->app->singleton(SallaOauth::class, function ()  {
            return $this->getMockBuilder(Salla::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['fetchResourceOwnerDetails'])
                ->getMock();
        });

        // Mock response
        $user = [
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

        $token = new AccessToken([
            'access_token' => 'foobar',
        ]);

        // Set up the expectation for fetchResourceOwnerDetails method
        $this->app->make(SallaOauth::class)->expects($this->once())
            ->method('fetchResourceOwnerDetails')
            ->with($this->equalTo($token))
            ->willReturn($user);

        $response = $this->get('hello/user-order-read-scope', [
            'Authorization' => 'Bearer foobar'
        ]);
        $response->assertStatus(200)->assertSeeText('hello 12345');
    }

    public function testCheckNotAllowedUserScope()
    {
        $this->app->singleton(SallaOauth::class, function ()  {
            return $this->getMockBuilder(Salla::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['fetchResourceOwnerDetails'])
                ->getMock();
        });

        // Mock response
        $user = [
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
                    'scope' => 'customers.read products.read',
                    'exp' => 1721326955
                ]
            ]
        ];

        $token = new AccessToken([
            'access_token' => 'foobar',
        ]);

        // Set up the expectation for fetchResourceOwnerDetails method
        $this->app->make(SallaOauth::class)->expects($this->once())
            ->method('fetchResourceOwnerDetails')
            ->with($this->equalTo($token))
            ->willReturn($user);

        $response = $this->get('hello/user-order-read-scope', [
            'Authorization' => 'Bearer foobar'
        ]);
        $response->assertStatus(401)->assertSeeText('Unauthorized');
    }
}
