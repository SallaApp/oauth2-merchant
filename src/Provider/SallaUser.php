<?php

namespace Salla\OAuth2\Client\Provider;

use Exception;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

class SallaUser implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $response;

    /**
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    public function getId()
    {
        return $this->getResponseValue('data.id');
    }

    /**
     * Get preferred name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->getResponseValue('data.name');
    }

    /**
     * Get email address.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->getResponseValue('data.email');
    }

    /**
     * Get mobile number.
     *
     * @return string|null
     */
    public function getMobile()
    {
        return $this->getResponseValue('data.mobile');
    }

    /**
     * Get user role.
     *
     * @return string|null
     */
    public function getRole()
    {
        return $this->getResponseValue('data.role');
    }

    /**
     * Get user created_at.
     *
     * @return \DateTime
     * @throws Exception
     */
    public function getCreatedAt()
    {
        return new \DateTime($this->getResponseValue('data.created_at'));
    }

    /**
     * Get store id.
     *
     * @return string|null
     */
    public function getStoreId()
    {
        return $this->getResponseValue('data.merchant.id');
    }

    /**
     * Get store owner id.
     *
     * @return string|null
     * @deprecated it will be removed next version
     */
    public function getStoreOwnerID()
    {
        return $this->getResponseValue('data.merchant.owner_id');
    }

    /**
     * Get store owner name.
     *
     * @return string|null
     * @deprecated it will be removed next version
     */
    public function getStoreOwnerName()
    {
        return $this->getResponseValue('data.merchant.owner_name');
    }

    /**
     * Get store username.
     *
     * @return string|null
     */
    public function getStoreUsername()
    {
        return $this->getResponseValue('data.merchant.username');
    }

    /**
     * Get store name.
     *
     * @return string|null
     */
    public function getStoreName()
    {
        return $this->getResponseValue('data.merchant.name');
    }

    /**
     * Get store avatar.
     *
     * @return string|null
     */
    public function getStoreAvatar()
    {
        return $this->getResponseValue('data.merchant.avatar');
    }

    /**
     * Get store location.
     *
     * @return string|null
     */
    public function getStoreLocation()
    {
        return $this->getResponseValue('data.merchant.store_location');
    }

    /**
     * Get store plan.
     *
     * @return string|null
     */
    public function getStorePlan()
    {
        return $this->getResponseValue('data.merchant.plan');
    }

    /**
     * Get store status.
     *
     * @return string|null
     */
    public function getStoreStatus()
    {
        return $this->getResponseValue('data.merchant.status');
    }

    /**
     * Get store created at.
     *
     * @return \DateTime
     * @throws Exception
     */
    public function getStoreCreatedAt()
    {
        return new \DateTime($this->getResponseValue('data.merchant.created_at'));
    }

    /**
     * Get user data as an array.
     *
     * @return array
     * @throws Exception
     */
    public function toArray()
    {
        try {
            return $this->response['data'];
        }catch (Exception $exception){
            throw new Exception('User data not found');
        }
    }


    private function getResponseValue($key)
    {
        $context = $this->response;
        $pieces = explode('.', $key);
        foreach ($pieces as $piece) {
            if (!is_array($context) || !array_key_exists($piece, $context)) {
                return null;
            }
            $context = &$context[$piece];
        }
        return $context;
    }
}
