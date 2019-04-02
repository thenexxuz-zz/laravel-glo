<?php

namespace TheNexxuz\LaravelGlo;

use JsonSerializable;

class Color implements JsonSerializable
{
    protected $r = 0;

    protected $g = 0;

    protected $b = 0;

    protected $a = 1.0;

    /**
     * Color constructor.
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        if (array_key_exists('random', $options) && $options['random']) {
            $options['r'] = rand(0, 255);
            $options['g'] = rand(0, 255);
            $options['b'] = rand(0, 255);
            $options['a'] = round(mt_rand() / mt_getrandmax(), 2);
        }
        if (array_key_exists('r', $options)) {
            $this->setR($options['r']);
        }
        if (array_key_exists('g', $options)) {
            $this->setG($options['g']);
        }
        if (array_key_exists('b', $options)) {
            $this->setB($options['b']);
        }
        if (array_key_exists('a', $options)) {
            $this->setA($options['a']);
        }
    }

    /**
     * @return int
     */
    public function getR(): int
    {
        return $this->r;
    }

    /**
     * @param int $r
     */
    public function setR(int $r): void
    {
        if ((0 <= $r) && ($r <= 255)) {
            $this->r = $r;
        }
    }

    /**
     * @return int
     */
    public function getG(): int
    {
        return $this->g;
    }

    /**
     * @param int $g
     */
    public function setG(int $g): void
    {
        if ((0 <= $g) && ($g <= 255)) {
            $this->g = $g;
        }
    }

    /**
     * @return int
     */
    public function getB(): int
    {
        return $this->b;
    }

    /**
     * @param int $b
     */
    public function setB(int $b): void
    {
        if ((0 <= $b) && ($b <= 255)) {
            $this->b = $b;
        }
    }

    /**
     * @return int
     */
    public function getA(): int
    {
        return $this->a;
    }

    /**
     * @param float $a
     */
    public function setA(float $a): void
    {
        if ((0 <= $a) && ($a <= 1.0)) {
            $this->a = $a;
        }
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
