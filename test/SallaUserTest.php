<?php

namespace Salla\OAuth2\Client\Test;

use Salla\OAuth2\Client\Provider\SallaUser;
use PHPUnit\Framework\TestCase;

class SallaUserTest extends TestCase
{
    public function testUserDefaults()
    {
        // Mock
        $user = new SallaUser([
            'status'=> 200,
            'success'=> true,
            'data'=>[
                'id' => '12345',
                'name' => 'mock name',
                'email' => 'mock.name@example.com',
                'mobile' => '05000000',
                'role' => 'user',
                'created_at' => '2018-04-28 17:46:25',
                'store'=>[
                    'id'=>'11111',
                    'username'=> 'mock_name',
                    'name'=> 'mock name',
                    'avatar'=>'mock_avatar',
                    'store_location'=>'mock_location',
                    'plan'=>'mock_plan',
                    'status'=>'mock_status',
                    'created_at'=>'2018-04-28 17:46:25',
                ],
                'merchant'=>[
                    'id'=>'11111',
                    'username'=> 'mock_name',
                    'name'=> 'mock name',
                    'avatar'=>'mock_avatar',
                    'store_location'=>'mock_location',
                    'plan'=>'mock_plan',
                    'status'=>'mock_status',
                    'created_at'=>'2018-04-28 17:46:25',
                ],
        ]]);

        $this->assertEquals(12345, $user->getId());
        $this->assertEquals('mock name', $user->getName());
        $this->assertEquals('mock.name@example.com', $user->getEmail());
        $this->assertEquals('05000000', $user->getMobile());
        $this->assertEquals('user', $user->getRole());
        $this->assertEquals( '2018-04-28 17:46:25', $user->getCreatedAt()->format('Y-m-d H:i:s'));
        $this->assertEquals(11111, $user->getStoreId());
        $this->assertEquals(null, $user->getStoreOwnerID());
        $this->assertEquals(null, $user->getStoreOwnerName());
        $this->assertEquals('mock_name', $user->getStoreUsername());
        $this->assertEquals('mock name', $user->getStoreName());
        $this->assertEquals('mock_avatar', $user->getStoreAvatar());
        $this->assertEquals('mock_location', $user->getStoreLocation());
        $this->assertEquals('mock_plan', $user->getStorePlan());
        $this->assertEquals('mock_status', $user->getStoreStatus());
        $this->assertEquals( '2018-04-28 17:46:25', $user->getStoreCreatedAt()->format('Y-m-d H:i:s'));
    }

    public function testUserPartialData()
    {
        $user = new SallaUser([
            'status'=> 200,
            'success'=> true,
            'data'=>[
                'id' => '12345',
                'name' => 'mock name',
                'mobile' => '05000000',
        ]]);

        $this->assertEquals(12345, $user->getId());
        $this->assertEquals('mock name', $user->getName());
        $this->assertEquals(null, $user->getEmail());
        $this->assertEquals('05000000', $user->getMobile());
    }

    public function testUserMinimalData()
    {
        $user = new SallaUser([
            'status'=> 200,
            'success'=> true,
            'data'=>[
                'id' => '12345',
                'name' => 'mock name',
        ]]);

        $this->assertEquals(null, $user->getEmail());
        $this->assertEquals(null, $user->getMobile());
        $this->assertEquals(null, $user->getRole());
    }
}
