<?php

namespace TheNexxuz\LaravelGlo;

use \StdClass;
use GuzzleHttp\Client;
use \Exception;

class Glo
{
    private $accessToken = false;

    private $perPage = 25;

    private $page = 1;

    public function __construct(array $options)
    {
        if (array_key_exists('accessToken', $options)) {
            $this->setAccessToken($options['accessToken']);
        }

        if (array_key_exists('perPage', $options)) {
            $this->setPerPage($options['perPage']);
        }

        if (array_key_exists('page', $options)) {
            $this->setPage($options['page']);
        }

        if (!$this->getAccessToken()) {
            throw new \Exception("Personal Access Token must be set.");
        }
    }

    /**
     * @return string
     */
    public function getAccessToken(): string
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

    private function getHeaders()
    {
        return [
            'accept'        => 'application/json',
        ];
    }

    public function getAllBoards(): StdClass
    {
        $r = new StdClass();
        try {
            $client = new Client();
            $response = $client->get('https://gloapi.gitkraken.com/v1/glo/boards?access_token='.$this->getAccessToken()  , [
                'headers' => $this->getHeaders(),
                'http_errors' => false,
            ]);
            $r->data = $response->getBody()->getContents();
            $r->statusCode = $response->getStatusCode();
        }
        catch (Exception $e) {
            $r->data = [
                'error' => $e->getMessage()
            ];
            $r->statusCode = 500;
        }
        return $r;
    }

    public function getBoard(string $boardId): StdClass
    {
        $r = new StdClass();
        try {
            $client = new Client();
            $response = $client->get('https://gloapi.gitkraken.com/v1/glo/boards/' . $boardId . '?access_token='.$this->getAccessToken()  , [
                'headers' => $this->getHeaders(),
                'http_errors' => false,
            ]);
            $r->data = $response->getBody()->getContents();
            $r->statusCode = $response->getStatusCode();
        }
        catch (Exception $e) {
            $r->data = [
                'error' => $e->getMessage()
            ];
            $r->statusCode = 500;
        }
        return $r;
    }
}
