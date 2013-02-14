<?php

namespace SlotMachine;

/**
 * A Reel contains an array of cards, which are loaded into a Slot.
 * This allows for Reels to be loaded interchangably between Slots.
 *
 * @package slotmachine
 * @author Adam Elsodaney <adam@archfizz.co.uk>
 */
class Reel implements ReelInterface
{
    const NO_CARD       = 0;
    const DEFAULT_CARD  = 1;
    const FALLBACK_CARD = 2;

    /**
     * The name of the Reel.
     * @var string
     */
    public $name = '';

    /**
     * Configuration data.
     * @var array
     */
    protected $options;

    /**
     * The array of cards.
     * @var array
     */
    protected $cards = array();

    /**
     * A list of aliases pointing to any card in the Reel.
     * @var array
     */
    public $aliases = array('_default' => 0);

    /**
     * Setting for what to do if a requested card does not exist.
     * @var integer
     */
    protected $resolveUndefined = self::NO_CARD;

    /**
     * Load the reel with cards.
     *
     * @param array $cards
     */
    public function __construct(array $options = array())
    {
        $this->options = $options;
        $this->cards   = $options['cards'];

        if (isset($options['aliases'])) {
            $this->aliases = array_replace($this->aliases, $options['aliases']);
        }

        if (isset($options['resolve_undefined'])) {
            $this->resolveUndefined = constant('self::'.$options['resolve_undefined']);
        }
    }

    /**
     * Use an alias instead of an ID to retrieve a card.
     *
     * @param  string $alias
     * @return mixed
     */
    public function getCardByAlias($alias)
    {
        return $this[$this->aliases[$alias]];
    }

    /**
     * Counts the number of cards in the Reel.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->cards);
    }

    /**
     * Allows a foreach loop to iterate through all the cards in a Reel object.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->cards);
    }

    /**
     * Adds a card to the Reel.
     *
     * @param integer $id
     * @param mixed   $value
     */
    public function offsetSet($id, $value)
    {
        $this->cards[$id] = $value;
    }

    /**
     * Gets a card by its id.
     * Returns true even if the card value is null.
     *
     * @param integer $id
     * @return mixed
     * @throws \InvalidArgumentException if the card is not defined.
     */
    public function offsetGet($id)
    {
        if (!array_key_exists($id, $this->cards)) {
            switch ($this->resolveUndefined) {
                case self::NO_CARD:
                    throw new \InvalidArgumentException(sprintf('Card ID "%s" is not defined.', $id));
                case self::DEFAULT_CARD:
                    return $this->getCardByAlias('_default');
                case self::FALLBACK_CARD:
                    return $this->getCardByAlias('_fallback');
            }
        }

        return $this->cards[$id];
    }

    /**
     * Checks if a card exists.
     *
     * @param integer $id
     * @return boolean
     */
    public function offsetExists($id)
    {
        return array_key_exists($id, $this->cards);
    }

    /**
     * Removes a card from the Reel.
     *
     * @param integer $id
     */
    public function offsetUnset($id)
    {
        unset($this->cards[$id]);
    }
}
