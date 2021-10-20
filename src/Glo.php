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

    protected $columns = [];

    protected $labels = [];

    protected $boardId = '';

    public function __construct(array $options)
    {
        if (array_key_exists('accessToken', $options)) {
            $this->setAccessToken($options['accessToken']);
        }

        if (array_key_exists('boardId', $options)) {
            $this->setBoardId($options['boardId']);
        }

        if (array_key_exists('perPage', $options)) {
            $this->setPerPage($options['perPage']);
        }

        if (array_key_exists('page', $options)) {
            $this->setPage($options['page']);
        }

        if (array_key_exists('columns', $options) && array_key_exists('boardId', $options)) {
            $this->setColumns($options['columns'], $options['boardId']);
        }

        if (array_key_exists('labels', $options) && array_key_exists('boardId', $options)) {
            $this->setLabels($options['labels'], $options['boardId']);
        }

        if (!$this->getAccessToken()) {
            throw new Exception("Personal Access Token must be set.");
        }
    }

    /**
     * @return mixed
     */
    public function getBoardId()
    {
        return $this->boardId;
    }

    /**
     * @param mixed $boardId
     */
    public function setBoardId($boardId): void
    {
        $this->boardId = $boardId;
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
            $response = $client->get('https://gloapi.gitkraken.com/v1/glo/boards?fields[]=archived_columns&fields[]=archived_date&fields[]=columns&fields[]=created_by&fields[]=created_date&fields[]=invited_members&fields[]=labels&fields[]=members&fields[]=name', [
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

    public function getBoard(string $boardId=''): StdClass
    {
        if ($boardId === '') {
            $boardId = $this->getBoardId();
        }
        if ($boardId === '') {
            throw new Exception("Exception: 'boardId' must be set");
        }
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
            $this->setColumns($cols, $boardId);
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
        if ($boardId === '') {
            $boardId = $this->getBoardId();
        }
        if ($boardId === '') {
            throw new Exception("Exception: 'boardId' must be set");
        }
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

    public function updateBoard(string $name, string $boardId): StdClass
    {
        if ($boardId === '') {
            $boardId = $this->getBoardId();
        }
        if ($boardId === '') {
            throw new Exception("Exception: 'boardId' must be set");
        }
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

    public function setColumns(array $columns, string $boardId): void
    {
        if ($boardId === '') {
            $boardId = $this->getBoardId();
        }
        if ($boardId === '') {
            throw new Exception("Exception: 'boardId' must be set");
        }
        $this->columns[$boardId] = [];
        if (!empty($columns)) {
            foreach ($columns as $column) {
                $this->addColumn($column, $boardId);
            }
        }
    }

    public function addColumn(Column $column, string $boardId = ''): void
    {
        if ($boardId === '') {
            $boardId = $this->getBoardId();
        }
        if ($boardId === '') {
            throw new Exception("Exception: 'boardId' must be set");
        }
        $this->columns[$boardId][] = $column;
    }

    public function getColumns(string $boardId): array
    {
        if ($boardId === '') {
            $boardId = $this->getBoardId();
        }
        if ($boardId === '') {
            throw new Exception("Exception: 'boardId' must be set");
        }
        $columns = [];
        if (array_key_exists($boardId, $this->columns)) {
            $columns = $this->columns[$boardId];
        }
        return $columns;
    }

    public function updateColumn(string $columnId, Column $column, string $boardId = '')
    {
        if ($boardId === '') {
            $boardId = $this->getBoardId();
        }
        if ($boardId === '') {
            throw new Exception("Exception: 'boardId' must be set");
        }
        $r = new StdClass();
        $r->data = array();
        $r->statusCode = 200;
        try {
            $client = new Client();
            $data = array();
            $data['name'] = $column->getName();
            if (!is_null($column->getPosition())) {
                $data['position'] = $column->getPosition();
            }
            $response = $client->post('https://gloapi.gitkraken.com/v1/glo/boards/' . $boardId . '/columns/' . $columnId, [
                'headers' => $this->getHeaders(),
                'http_errors' => false,
                'json' => $data,
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

    public function syncColumns(string $boardId = ''): StdClass
    {
        if ($boardId === '') {
            $boardId = $this->getBoardId();
        }
        if ($boardId === '') {
            throw new Exception("Exception: 'boardId' must be set");
        }
        $r = new StdClass();
        $r->data = array();
        $r->statusCode = 200;
        try {
            $client = new Client();
            if (!empty($this->getColumns($boardId))) {
                foreach ($this->getColumns($boardId) as $column) {
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

    public function findColumnById(string $columnId, string $boardId = '')
    {
        if ($boardId === '') {
            $boardId = $this->getBoardId();
        }
        if ($boardId === '') {
            throw new Exception("Exception: 'boardId' must be set");
        }
        $this->getBoard($boardId);
        $found = false;
        foreach ($this->getColumns($boardId) as $column) {
            if (property_exists($column, 'id') && ($column->getId() === $columnId)) {
                $found = $column;
                continue;
            }
        }
        return $found;
    }

    public function deleteColumn(string $columnId, string $boardId = ''): StdClass
    {
        if ($boardId === '') {
            $boardId = $this->getBoardId();
        }
        if ($boardId === '') {
            throw new Exception("Exception: 'boardId' must be set");
        }
        $r = new StdClass();
        $r->data = [
            'error' => "NotFound: 'columnId' $columnId not found on 'boardId' $boardId"
        ];
        $r->statusCode = 404;
        if ($this->findColumnById($columnId, $boardId)) {
            try {
                $client = new Client();
                $response = $client->delete('https://gloapi.gitkraken.com/v1/glo/boards/' . $boardId . '/columns/' . $columnId, [
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
        }
        return $r;
    }

    public function setLabels(array $labels, string $boardId = '')
    {
        if ($boardId === '') {
            $boardId = $this->getBoardId();
        }
        if ($boardId === '') {
            throw new Exception("Exception: 'boardId' must be set");
        }
        $this->labels[$boardId] = [];
        if (!empty($labels)) {
            foreach ($labels as $label) {
                $this->addLabel($label, $boardId);
            }
        }
    }

    public function addLabel(Label $label, string $boardId = '')
    {
        if ($boardId === '') {
            $boardId = $this->getBoardId();
        }
        if ($boardId === '') {
            throw new Exception("Exception: 'boardId' must be set");
        }
        $this->labels[$boardId][] = $label;
    }

    public function getLabels(string $boardId = ''): array
    {
        if ($boardId === '') {
            $boardId = $this->getBoardId();
        }
        if ($boardId === '') {
            throw new Exception("Exception: 'boardId' must be set");
        }
        $labels = [];
        if (array_key_exists($boardId, $this->labels)) {
            $labels = $this->labels[$boardId];
        }
        return $labels;
    }

    public function syncLabels(string $boardId = ''): StdClass
    {
        if ($boardId === '') {
            $boardId = $this->getBoardId();
        }
        if ($boardId === '') {
            throw new Exception("Exception: 'boardId' must be set");
        }
        $r = new StdClass();
        $r->data = array();
        $r->statusCode = 200;
        try {
            $client = new Client();
            if (!empty($this->getLabels($boardId))) {
                foreach ($this->getLabels($boardId) as $label) {
                    $res = new StdClass();
                    $data = array();
                    $data['name'] = $label->getName();
                    $data['color'] = $label->getColor();
                    $response = $client->post('https://gloapi.gitkraken.com/v1/glo/boards/' . $boardId . '/labels', [
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

    public function updateLabel(string $labelId, Label $label, string $boardId = '')
    {
        if ($boardId === '') {
            $boardId = $this->getBoardId();
        }
        if ($boardId === '') {
            throw new Exception("Exception: 'boardId' must be set");
        }
        $r = new StdClass();
        $r->data = array();
        $r->statusCode = 200;
        try {
            $client = new Client();
            $data = array();
            $data['name'] = $label->getName();
            $data['color'] = $label->getColor();
            $response = $client->post('https://gloapi.gitkraken.com/v1/glo/boards/' . $boardId . '/labels/' . $labelId, [
                'headers' => $this->getHeaders(),
                'http_errors' => false,
                'json' => $data,
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

    public function deleteLabel(string $labelId, string $boardId = ''): StdClass
    {
        if ($boardId === '') {
            $boardId = $this->getBoardId();
        }
        if ($boardId === '') {
            throw new Exception("Exception: 'boardId' must be set");
        }
        $r = new StdClass();
        $r->data = [
            'error' => "NotFound: 'labelId' $labelId not found on 'boardId' $boardId"
        ];
        $r->statusCode = 404;
        if ($this->findLabelById($labelId, $boardId)) {
            try {
                $client = new Client();
                $response = $client->delete('https://gloapi.gitkraken.com/v1/glo/boards/' . $boardId . '/labels/' . $labelId, [
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
        }
        return $r;
    }

    public function findLabelById(string $labelId, string $boardId = '')
    {
        if ($boardId === '') {
            $boardId = $this->getBoardId();
        }
        if ($boardId === '') {
            throw new Exception("Exception: 'boardId' must be set");
        }
        $this->getBoard($boardId);
        $found = false;
        foreach ($this->getLabels($boardId) as $label) {
            if (property_exists($label, 'id') && ($label->getId() === $labelId)) {
                $found = $label;
                continue;
            }
        }
        return $found;
    }

    public function getAuthUser()
    {
        $r = new StdClass();
        try {
            $client = new Client();
            $response = $client->get('https://gloapi.gitkraken.com/v1/glo/user?fields[]=email&fields[]=name&fields[]=username&fields[]=created_date', [
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

    public function getCards(string $boardId = ''): StdClass
    {
        if ($boardId === '') {
            $boardId = $this->getBoardId();
        }
        if ($boardId === '') {
            throw new Exception("Exception: 'boardId' must be set");
        }
        $r = new StdClass();
        try {
            $client = new Client();
            $response = $client->get("https://gloapi.gitkraken.com/v1/glo/boards/$boardId/cards?fields[]=archived_date&fields[]=assignees&fields[]=attachment_count&fields[]=board_id&fields[]=column_id&fields[]=comment_count&fields[]=completed_task_count&fields[]=created_by&fields[]=created_date&fields[]=due_date&fields[]=description&fields[]=labels&fields[]=name&fields[]=total_task_count&fields[]=updated_date", [
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

    public function getCard(string $cardId, string $boardId = ''): StdClass
    {
        if ($boardId === '') {
            $boardId = $this->getBoardId();
        }
        if ($boardId === '') {
            throw new Exception("Exception: 'boardId' must be set");
        }
        $r = new StdClass();
        try {
            $client = new Client();
            $response = $client->get("https://gloapi.gitkraken.com/v1/glo/boards/$boardId/cards/$cardId?fields[]=archived_date&fields[]=assignees&fields[]=attachment_count&fields[]=board_id&fields[]=column_id&fields[]=comment_count&fields[]=completed_task_count&fields[]=created_by&fields[]=created_date&fields[]=due_date&fields[]=description&fields[]=labels&fields[]=name&fields[]=total_task_count&fields[]=updated_date", [
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
