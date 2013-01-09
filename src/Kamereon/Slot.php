<?php

namespace Kamereon;

/**
 *  A placeholder for variable content on a page, which a value will be assigned
 *  to it as a Card instance
 *
 *  @package kamereon
 *  @author Adam Elsodaney <adam@archfizz.co.uk>
 */
class Slot
{
    /**
     *  The name of the slot
     */
    protected $name;

    /**
     *  The key name that is bound to the slot
     *  A key can be shared with another slot
     */
    protected $keyBind;

    /**
     *  An array of the names of nested slots
     */
    protected $nestedSlotNames = array();

    /**
     *  The collection array of nested Slot objects
     */
    protected $nestedSlots = array();

    /**
     *  A list of cards for each one will be displayed on the page
     */
    protected $cards = array();

    /**
     *  A list of aliases for a card
     */
    public $aliases = array('_default' => 0);

    /**
     *  Create new slot with name, key binding and its cards
     *  and if the slot has nested slots, assign only the names of
     *  those slots.
     *
     *  @param string $name
     *  @param array  $data
     */
    public function __construct($name, array $data)
    {
        $this->name    = $name;
        $this->keyBind = $data['keyBind'];
        $this->cards   = $data['cards'];

        if (isset($data['nestedWith'])) {
            $this->nestedSlotNames = $data['nestedWith'];
        }
    }

    /**
     *  Get the name of the slot
     *
     *  @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *  Add a slot to the nested slots collection
     *
     *  @param Slot $slot
     */
    public function addNestedSlot(Slot $slot)
    {
        $this->nestedSlots[$slot->getName()] = $slot;
    }

    /**
     *  Get all nested slots
     *
     *  @return array
     */
    public function getNestedSlots()
    {
        return $this->nestedSlots;
    }

    /**
     *  Get specific nested slot
     *
     *  @return Slot
     */
    public function getNestedSlotByName($name)
    {
        return $this->nestedSlots[$name];
    }

    /**
     *  Get a value of a card by its index / array key.
     *
     *  @return string
     *
     *  @throws InvalidArgumentException if the key does not exist.
     */
    public function getCard($index)
    {
        if (!array_key_exists($index, $this->cards)) {
            throw new \InvalidArgumentException(sprintf(
                'Card with index "%s" for slot "%s" does not exist', $index, $this->name));
        }

        return $this->cards[$index];
    }

    /**
     *  Get the binded key
     *
     *  @return string
     */
    public function getKeyBind()
    {
        return $this->keyBind;
    }

    /**
     *  Check if a slot contains other slots nested within
     *
     *  @return boolean
     */
    public function hasNestedSlots()
    {
        return count($this->nestedSlots) > 0;
    }

    /**
     *  Check if a slot contains other slots nested within
     *
     *  @return string
     */
    public function getCardByAlias($alias)
    {
        return $this->cards[$this->aliases[$alias]];
    }

    /**
     *  Assign a new alias for a card. A card can have more than one
     *  alias, but an alias must only point to one card.
     *
     *  @param string $alias  A unique reference to a card
     *  @param int    $card   The card id to be assigned the alias
     */
    public function addAlias($alias, $card)
    {
        if (array_key_exists($alias, $this->aliases)) {
            throw new \InvalidArgumentException(sprintf('Alias `%s` already exists', $alias));
        }

        if (!array_key_exists($card, $this->cards)) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot assign alias `%s` to missing card of index `%d`', $alias, $card
            ));
        }

        $this->aliases[$alias] = $card;
    }

    /**
     *  Change which card an alias refers to.
     *
     *  @param string $alias  A unique reference to a card
     *  @param int    $card   The card id to be assigned the alias
     */
    public function changeCardForAlias($alias, $card)
    {
        if (!array_key_exists($alias, $this->aliases)) {
            throw new \InvalidArgumentException(sprintf('Alias `%s` does not exist', $alias));
        }

        if (!array_key_exists($card, $this->cards)) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot assign alias `%s` to missing card of index `%d`', $alias, $this->cards[$this->aliases[$alias]]
            ));
        }

        $this->aliases[$alias] = $card;
    }
}
