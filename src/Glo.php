<?php

namespace TheNexxuz\LaravelGlo;

use \StdClass;
use GuzzleHttp\Client;
use \Exception;
use TheNexxuz\LaravelGlo\Column;

class Glo
{
    private $accessToken = false;

    private $perPage = 25;

    private $page = 1;

    private $columns = [];

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

        if (array_key_exists('columns', $options)) {
            $this->setColumns($options['columns']);
        }

        if (!$this->getAccessToken()) {
            throw new Exception("Personal Access Token must be set.");
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
            'Authorization' => $this->getAccessToken(),
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

    public function getAllBoards(): StdClass
    {
        $r = new StdClass();
        try {
            $client = new Client();
            $response = $client->get('https://gloapi.gitkraken.com/v1/glo/boards', [
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
            $response = $client->get('https://gloapi.gitkraken.com/v1/glo/boards/' . $boardId . '?fields[]=archived_columns&fields[]=archived_date&fields[]=columns&fields[]=created_by&fields[]=created_date&fields[]=invited_members&fields[]=labels&fields[]=members&fields[]=name', [
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
        $decoded = json_decode($r->data);
        if (property_exists($decoded, 'columns')) {
            $cols = [];
            if (!empty($decoded->columns)) {
                foreach ($decoded->columns as $c) {
                    $col = new Column();
                    $col->setId($c->id);
                    $col->setName($c->name);
                    $col->setCreatedBy($c->created_by);
                    $col->setCreatedDate($c->created_date);
                    if (property_exists($c, 'position')) {
                        $col->setPosition($c->position);
                    }
                    if (property_exists($c, 'archived_date')) {
                        $col->setArchivedDate($c->archived_date);
                    }
                    $cols[] = $col;
                }
            }
            $this->setColumns($cols);
        }
        return $r;
    }

    public function createBoard(string $name): StdClass
    {
        $r = new StdClass();
        try {
            $client = new Client();
            $response = $client->post('https://gloapi.gitkraken.com/v1/glo/boards', [
                'headers' => $this->getHeaders(),
                'http_errors' => false,
                'json' => [
                    'name' => $name
                ],
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

    public function deleteBoard(string $boardId): StdClass
    {
        $r = new StdClass();
        try {
            $client = new Client();
            $response = $client->delete('https://gloapi.gitkraken.com/v1/glo/boards/' . $boardId, [
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

    public function updateBoard(string $boardId, string $name): StdClass
    {
        $r = new StdClass();
        try {
            $client = new Client();
            $response = $client->post('https://gloapi.gitkraken.com/v1/glo/boards/' . $boardId, [
                'headers' => $this->getHeaders(),
                'http_errors' => false,
                'json' => [
                    'name' => $name
                ],
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

    public function setColumns(array $columns): void
    {
        $this->columns = [];
        if (!empty($columns)) {
            foreach ($columns as $column) {
                $this->addColumn($column);
            }
        }
    }

    public function addColumn(Column $column): void
    {
        $this->columns[] = $column;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function syncColumns(string $boardId): StdClass
    {
        $r = new StdClass();
        $r->data = array();
        $r->statusCode = 200;
        try {
            $client = new Client();
            if (!empty($this->getColumns())) {
                foreach ($this->getColumns() as $column) {
                    $res = new StdClass();
                    $data = array();
                    $data['name'] = $column->getName();
                    if (!is_null($column->getPosition())) {
                        $data['position'] = $column->getPosition();
                    }
                    $response = $client->post('https://gloapi.gitkraken.com/v1/glo/boards/' . $boardId . '/columns', [
                        'headers' => $this->getHeaders(),
                        'http_errors' => false,
                        'json' => $data,
                    ]);
                    $res->data = json_decode($response->getBody()->getContents());
                    $res->statusCode = $response->getStatusCode();
                    $r->data[] = $res;
                }
            }
            if (!empty($r->data)) {
                $this->getBoard($boardId);
            }
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
