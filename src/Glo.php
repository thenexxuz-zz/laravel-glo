<?php

namespace TheNexxuz\LaravelGlo;

use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Oauth;

class Glo
{
    private $accessToken = null;

    private $clientId = null;

    private $clientSecret = null;

    private $perPage = 25;

    private $page = 1;

    public function __construct(array $options)
    {
        $accessToken = false;
        $clientId = false;
        $clientSecret = false;

        if (array_key_exists('accessToken', $options)) {
            $this->setAccessToken($options['accessToken']);
            $accessToken = true;
        }
        if (array_key_exists('clientId', $options)) {
            $this->setClientId($options['clientId']);
            $clientId = true;
        }
        if (array_key_exists('clientSecret', $options)) {
            $this->setClientSecret($options['clientSecret']);
        }

        if (array_key_exists('perPage', $options)) {
            $this->setPerPage($options['perPage']);
        }

        if (array_key_exists('page', $options)) {
            $this->setPage($options['page']);
        }

        if (!$accessToken && !$clientId && $clientSecret) {
            throw new \Exception('Client ID and Secret both must be set. Client ID is missing.');
        }

        if (!$accessToken && $clientId && !$clientSecret) {
            throw new \Exception('Client ID and Secret both must be set. Client secret is missing.');
        }

        if ($accessToken && $clientId && $clientSecret) {
            throw new \Exception("Please use only PAT or Client ID/Secret not both.");
        }
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

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * @param int $perPage
     */
    public function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }
}
