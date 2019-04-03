<?php

namespace TheNexxuz\LaravelGlo;

use JsonSerializable;
use GuzzleHttp\Client;
use \Exception;
use StdClass;

class Card implements JsonSerializable
{
    private $accessToken = '';

    protected $cardId = '';

    protected $boardId = '';

    protected $name = '';

    protected $position = null;

    protected $description = null;

    protected $columnId = '';

    protected $assignees = [];

    protected $labels = [];

    protected $dueDate = '';

    /**
     * Label constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (array_key_exists('name', $options)) {
            $this->setName($options['name']);
        }
        if (array_key_exists('position', $options)) {
            $this->setPosition($options['position']);
        }
        if (array_key_exists('description', $options)) {
            $this->setDescription($options['description']);
        }
        if (array_key_exists('columnId', $options)) {
            $this->setColumnId($options['columnId']);
        }
        if (array_key_exists('assignees', $options)) {
            $this->setAssignees($options['assignees']);
        }
        if (array_key_exists('labels', $options)) {
            $this->setLabels($options['labels']);
        }
        if (array_key_exists('dueDate', $options)) {
            $this->setDueDate($options['dueDate']);
        }
        if (!(array_key_exists('name', $options) && array_key_exists('columnId', $options))) {
            throw new Exception("Exception: 'name' and 'columnId' are both required.");
        }
    }

    private function getHeaders()
    {
        return [
            'Authorization' => $this->getAccessToken(),
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
    }

    public function setLabels(array $labels, string $cardId = '')
    {
        if ($cardId === '') {
            $cardId = $this->getCardId();
        }
        if ($cardId === '') {
            throw new Exception("Exception: 'cardId' must be set");
        }
        $this->labels[$cardId] = [];
        if (!empty($labels)) {
            foreach ($labels as $label) {
                $this->addLabel($label, $cardId);
            }
        }
    }

    public function addLabel(Label $label, string $cardId = '')
    {
        if ($cardId === '') {
            $cardId = $this->getCardId();
        }
        if ($cardId === '') {
            throw new Exception("Exception: 'cardId' must be set");
        }
        $this->labels[$cardId][] = $label;
    }

    public function getLabels(string $cardId = ''): array
    {
        if ($cardId === '') {
            $cardId = $this->getCardId();
        }
        if ($cardId === '') {
            throw new Exception("Exception: 'cardId' must be set");
        }
        $labels = [];
        if (array_key_exists($cardId, $this->labels)) {
            $labels = $this->labels[$cardId];
        }
        return $labels;
    }

    public function syncCard(string $cardId = '', string $boardId = ''): StdClass
    {
        if ($cardId === '') {
            $cardId = $this->getCardId();
        }
        if ($cardId === '') {
            throw new Exception("Exception: 'cardId' must be set");
        }
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
            $response = $client->post("https://gloapi.gitkraken.com/v1/glo/boards/$boardId/cards/$cardId", [
                'headers' => $this->getHeaders(),
                'http_errors' => false,
                'json' => $this->jsonSerialize(),
            ]);
            $r->data = json_decode($response->getBody()->getContents());
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

    public function updateLabel(string $labelId, Label $label, string $cardId = '', string $boardId = '')
    {
        if ($cardId === '') {
            $cardId = $this->getCardId();
        }
        if ($cardId === '') {
            throw new Exception("Exception: 'cardId' must be set");
        }
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
            $response = $client->post('https://gloapi.gitkraken.com/v1/glo/boards/' . $cardId . '/labels/' . $labelId, [
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

    public function deleteLabel(string $labelId, string $cardId = '', string $boardId = ''): StdClass
    {
        if ($cardId === '') {
            $cardId = $this->getCardId();
        }
        if ($cardId === '') {
            throw new Exception("Exception: 'cardId' must be set");
        }
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
        if ($this->findLabelById($labelId, $cardId, $boardId)) {
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

    public function findLabelById(string $labelId, string $cardId = '', string $boardId = '')
    {
        if ($cardId === '') {
            $cardId = $this->getCardId();
        }
        if ($cardId === '') {
            throw new Exception("Exception: 'cardId' must be set");
        }
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
     * @return mixed
     */
    public function getCardId()
    {
        return $this->cardId;
    }

    /**
     * @param mixed $cardId
     */
    public function setCardId($cardId): void
    {
        $this->cardId = $cardId;
    }

    /**
     * @return string
     */
    public function getBoardId(): string
    {
        return $this->boardId;
    }

    /**
     * @param string $boardId
     */
    public function setBoardId(string $boardId): void
    {
        $this->boardId = $boardId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param mixed $position
     */
    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @return null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param null $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getColumnId(): string
    {
        return $this->columnId;
    }

    /**
     * @param string $columnId
     */
    public function setColumnId(string $columnId): void
    {
        $this->columnId = $columnId;
    }

    /**
     * @return array
     */
    public function getAssignees(): array
    {
        return $this->assignees;
    }

    /**
     * @param array $assignees
     */
    public function setAssignees(array $assignees): void
    {
        $this->assignees = $assignees;
    }

    /**
     * @return string
     */
    public function getDueDate(): string
    {
        return $this->dueDate;
    }

    /**
     * @param string $dueDate
     */
    public function setDueDate(string $dueDate): void
    {
        $this->dueDate = $dueDate;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}