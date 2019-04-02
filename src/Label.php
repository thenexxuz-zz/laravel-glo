<?php

namespace TheNexxuz\LaravelGlo;

use JsonSerializable;
use TheNexxuz\LaravelGlo\Color;

class Label implements JsonSerializable
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var mixed
     */
    protected $color = null;

    /**
     * Label constructor.
     * @param array $options
     */
    public function __construct(array $options)
    {
        if (array_key_exists('name', $options)) {
            $this->setName($options['name']);
        }
        if (array_key_exists('color', $options)) {
            $this->setColor($options['color']);
        }
        if (array_key_exists('randomColor', $options) && $options['randomColor']) {
            $this->setColor(new Color(['random'=>true]));
        }
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
     * @return Color
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param Color $color
     */
    public function setColor(Color $color): void
    {
        $this->color = $color;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
