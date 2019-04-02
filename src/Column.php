<?php

namespace TheNexxuz\LaravelGlo;

use JsonSerializable;

class Column implements JsonSerializable
{
    protected $id = null;

    protected $name = null;

    protected $position = null;

    protected $archivedDate = null;

    protected $createdDate = null;

    protected $createdBy = null;

    public function __construct(string $name = null, $position = null)
    {
        $this->setName($name);
        $this->setPosition($position);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name): void
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
     * @return mixed
     */
    public function getArchivedDate()
    {
        return $this->archivedDate;
    }

    /**
     * @param mixed $archivedDate
     */
    public function setArchivedDate($archivedDate): void
    {
        $this->archivedDate = $archivedDate;
    }

    /**
     * @return mixed
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @param mixed $createdDate
     */
    public function setCreatedDate($createdDate): void
    {
        $this->createdDate = $createdDate;
    }

    /**
     * @return mixed
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param mixed $createdBy
     */
    public function setCreatedBy($createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
