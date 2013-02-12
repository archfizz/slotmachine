<?php

namespace SlotMachine;

/**
 * A Reel contains an array of cards, which are loaded into a Slot
 * This allows for Reels to be loaded interchangably between Slots
 *
 * @package slotmachine
 * @author Adam Elsodaney <adam@archfizz.co.uk>
 */
class Reel implements \Countable, \IteratorAggregate, \ArrayAccess
{
    const NO_CARD       = 0;
    const DEFAULT_CARD  = 1;
    const FALLBACK_CARD = 2;

    /**
     * The name of the Reel
     */
    public $name = '';

    /**
     * Configuration data
     */
    protected $data;

    /**
     * The array of cards
     */
    protected $cards = array();

    /**
     * A list of aliases pointing to any card in the Reel
     */
    protected $aliases = array('_default' => 0);

    /**
     * Setting for what to do if a requested card does not exist.
     */
    public $resolveUndefined = self::NO_CARD;

    /**
     * Load the reel with cards
     *
     * @param array $cards
     */
    public function __construct(array $cards = array())
    {
        //$this->name  = $name;
        //$this->data  = $data;
        $this->cards = $cards;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->cards);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->cards);
    }

    /**
     * @param int $id
     * @param string $value
     */
    public function offsetSet($id, $value)
    {
        $this->cards[$id] = $value;
    }

    /**
     * @param int $id
     */
    public function offsetGet($id)
    {
        // returns true if the card value is null
        if (!array_key_exists($id, $this->cards)) {
            throw new \InvalidArgumentException(sprintf('Card ID "%s" is not defined.', $id));
        }

        return $this->cards[$id];
    }

    /**
     * @param int $id
     */
    public function offsetExists($id)
    {
        return array_key_exists($id, $this->cards);
    }

    /**
     * @param int $id
     */
    public function offsetUnset($id)
    {
        unset($this->cards[$id]);
    }
}
