<?php

namespace Kamereon;

/**
 *  A placeholder for variable content on a page, which a value will be assigned
 *  to it as a Card instance
 *
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
     *  Create new slot with name, key binding and its cards
     *  and if the slot has nested slots, assign the names of
     *  those slots and set its hasNestedSlots flag to true
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
     *  Get the binded key
     *
     *  @return string
     */
    public function getCard($index)
    {
        if (array_key_exists($index, $this->cards)) {
            return $this->cards[$index];
        }
        throw new \InvalidArgumentException(sprintf(
            'Card with index "%s" for slot "%s" does not exist', $index, $this->name));
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
    public function getHasNestedSlots()
    {
        return count($this->nestedSlots) > 0;
    }
}
