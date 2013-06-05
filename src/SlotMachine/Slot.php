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
     * @var array
     */
    protected $nested = array();

    /**
     * @param array
     */
    public function __construct(array $data)
    {
        $this->name     = $data['name'];
        $this->keys     = $data['keys'];
        $this->reel     = $data['reel'];
        $this->nested   = array_key_exists('nested', $data) ? $data['nested'] : array();
    }

    /**
     * @param integer $index
     * @return string
     */
    public function getCard($index = 0)
    {
        if (!array_key_exists($index, $this->reel['cards'])) {
            throw new Exception\NoCardFoundException(sprintf("Card of index %d was not found in the slot `%s`.", $index, $this->name));
        }
        return $this->reel['cards'][$index];
    }

    /**
     * @return array
     */
    public function getNested()
    {
        return $this->nested;
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
