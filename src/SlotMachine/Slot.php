<?php

namespace SlotMachine;

class Slot
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $keys;

    /**
     * @var array
     */
    protected $reel;

    /**
     * @param array
     */
    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $this->keys = $data['keys'];
        $this->reel = $data['reel'];
    }

    /**
     * @param integer $index
     * @return string
     */
    public function getCard($index = 0)
    {
        return $this->reel[$index];
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->keys[0];
    }

    /**
     * @return array
     */
    public function getKeys()
    {
        return $this->keys;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getCard();
    }
}
