<?php

namespace TheNexxuz\LaravelGlo;

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Oauth;

class Glo
{
    private $accessToken = null;

    private $clientId = null;

    private $clientSecret = null;

    public function __construct()
    {
        $this->setAccessToken(getenv('GLO_ACCESS_TOKEN'));
    }

    /**
     * @return null
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param null $accessToken
     */
    public function setAccessToken($accessToken): void
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return null
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @param null $clientId
     */
    public function setClientId($clientId): void
    {
        $this->clientId = $clientId;
    }

    /**
     * @return null
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * @param null $clientSecret
     */
    public function setClientSecret($clientSecret): void
    {
        $this->clientSecret = $clientSecret;
    }

}