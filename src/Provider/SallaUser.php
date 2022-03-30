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
     * @deprecated deprecated and will be removed on June 6, 2022. Instead, read the data from the merchant functions.
     */
    public function getStoreId()
    {
        return $this->getResponseValue('data.store.id');
    }

    /**
     * Get store owner id.
     *
     * @return string|null
     * @deprecated deprecated and will be removed on June 6, 2022. Instead, read the data from the merchant functions.
     */
    public function getStoreOwnerID()
    {
        return $this->getResponseValue('data.store.owner_id');
    }

    /**
     * Get store owner name.
     *
     * @return string|null
     * @deprecated deprecated and will be removed on June 6, 2022. Instead, read the data from the merchant functions.
     */
    public function getStoreOwnerName()
    {
        return $this->getResponseValue('data.store.owner_name');
    }

    /**
     * Get store username.
     *
     * @return string|null
     * @deprecated deprecated and will be removed on June 6, 2022. Instead, read the data from the merchant functions.
     */
    public function getStoreUsername()
    {
        return $this->getResponseValue('data.store.username');
    }

    /**
     * Get store name.
     *
     * @return string|null
     * @deprecated deprecated and will be removed on June 6, 2022. Instead, read the data from the merchant functions.
     */
    public function getStoreName()
    {
        return $this->getResponseValue('data.store.name');
    }

    /**
     * Get store avatar.
     *
     * @return string|null
     * @deprecated deprecated and will be removed on June 6, 2022. Instead, read the data from the merchant functions.
     */
    public function getStoreAvatar()
    {
        return $this->getResponseValue('data.store.avatar');
    }

    /**
     * Get store location.
     *
     * @return string|null
     * @deprecated deprecated and will be removed on June 6, 2022. Instead, read the data from the merchant functions.
     */
    public function getStoreLocation()
    {
        return $this->getResponseValue('data.store.store_location');
    }

    /**
     * Get store plan.
     *
     * @return string|null
     * @deprecated deprecated and will be removed on June 6, 2022. Instead, read the data from the merchant functions.
     */
    public function getStorePlan()
    {
        return $this->getResponseValue('data.store.plan');
    }

    /**
     * Get store status.
     *
     * @return string|null
     * @deprecated deprecated and will be removed on June 6, 2022. Instead, read the data from the merchant functions.
     */
    public function getStoreStatus()
    {
        return $this->getResponseValue('data.store.status');
    }

    /**
     * Get store created at.
     *
     * @return \DateTime
     * @throws Exception
     * @deprecated deprecated and will be removed on June 6, 2022. Instead, read the data from the merchant functions.
     */
    public function getStoreCreatedAt()
    {
        return new \DateTime($this->getResponseValue('data.store.created_at'));
    }


    /**
     * Get merchant id.
     *
     * @return string|null
     */
    public function getMerchantId()
    {
        return $this->getResponseValue('data.merchant.id');
    }

    /**
     * Get merchant username.
     *
     * @return string|null
     */
    public function getMerchantUsername()
    {
        return $this->getResponseValue('data.merchant.username');
    }

    /**
     * Get merchant name.
     *
     * @return string|null
     */
    public function getMerchantName()
    {
        return $this->getResponseValue('data.merchant.name');
    }

    /**
     * Get merchant avatar.
     *
     * @return string|null
     */
    public function getMerchantAvatar()
    {
        return $this->getResponseValue('data.merchant.avatar');
    }

    /**
     * Get merchant location.
     *
     * @return string|null
     */
    public function getMerchantLocation()
    {
        return $this->getResponseValue('data.merchant.store_location');
    }

    /**
     * Get merchant plan.
     *
     * @return string|null
     */
    public function getMerchantPlan()
    {
        return $this->getResponseValue('data.merchant.plan');
    }

    /**
     * Get merchant status.
     *
     * @return string|null
     */
    public function getMerchantStatus()
    {
        return $this->getResponseValue('data.merchant.status');
    }

    /**
     * Get merchant created at.
     *
     * @return \DateTime
     * @throws Exception
     */
    public function getMerchantCreatedAt()
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
