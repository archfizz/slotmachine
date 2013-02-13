<?php

namespace SlotMachine;

/**
 * A placeholder for variable content on a page, which a value will be assigned
 * to it as a Card instance
 *
 * @package slotmachine
 * @author Adam Elsodaney <adam@archfizz.co.uk>
 */
class Slot implements SlotInterface
{
    const NO_CARD       = 0;
    const DEFAULT_CARD  = 1;
    const FALLBACK_CARD = 2;

    /**
     * The name of the slot
     */
    protected $name;

    /**
     * The key name that is bound to the slot
     * A key can be shared with another slot
     */
    protected $key;

    /**
     * An array of the names of nested slots
     */
    protected $nestedSlotNames = array();

    /**
     * The collection array of nested Slot objects
     */
    protected $nestedSlots = array();

    /**
     * The Reel containing a list cards where one will be returned
     */
    protected $reel;

    /**
     * Setting for what to do if a requested card does not exist.
     */
    public $resolveUndefined = null;

    /**
     * Create new slot with name, key binding and its cards
     * and if the slot has nested slots, assign only the names of
     * those slots.
     *
     * @param string $name
     * @param array  $data
     */
    public function __construct($name, array $data, $reel)
    {
        $this->name   = $name;
        $this->key    = $data['key'];
        $this->reel   = $reel;

        if (isset($data['resolve_undefined'])) {
            $this->resolveUndefined = constant('self::'.$data['resolve_undefined']);
        }

        if (isset($data['nested_with'])) {
            $this->nestedSlotNames = $data['nested_with'];
        }

        if (isset($data['aliases'])) {
            $this->reel->aliases = array_replace($this->reel->aliases, $data['aliases']);
        }
    }

    /**
     * Get the name of the slot
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add a slot to the nested slots collection
     *
     * @param Slot $slot
     */
    public function addNestedSlot(SlotInterface $slot)
    {
        $this->nestedSlots[$slot->getName()] = $slot;
    }

    /**
     * Get all nested slots
     *
     * @return array
     */
    public function getNestedSlots()
    {
        return $this->nestedSlots;
    }

    /**
     * Get specific nested slot
     *
     * @return Slot
     */
    public function getNestedSlotByName($name)
    {
        return $this->nestedSlots[$name];
    }

    /**
     * Get a value of a card by its index / array key.
     * If the card does not exist, resolve based on the
     * slot's resolve_undefined setting
     *
     * @return string
     *
     * @throws InvalidArgumentException if the key does not exist and
     *         the resolveUndefined property is set to NO_CARD
     */
    public function getCard($index)
    {
        try {
            return $this->reel[$index];
        } catch (\InvalidArgumentException $e) {
            switch ($this->resolveUndefined) {
                case self::NO_CARD:
                    throw new \InvalidArgumentException(sprintf(
                        'Card with index "%s" for slot "%s" does not exist', $index, $this->name));
                case self::DEFAULT_CARD:
                    return $this->reel->getCardByAlias('_default');
                case self::FALLBACK_CARD:
                    return $this->reel->getCardByAlias('_fallback');
                default:
                    return '';
            }
        }
    }


    /**
     * Gets the default card index assigned to the '_default' alias
     * Note that this does not return the card itself, which is done
     * by calling `Reel::getCardByAlias('_default')`
     *
     * @return int
     */
    public function getDefaultCardIndex()
    {
        return $this->reel->aliases['_default'];
    }

    /**
     * Get the binded key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Check if a slot contains other slots nested within
     *
     * @return boolean
     */
    public function hasNestedSlots()
    {
        return count($this->nestedSlots) > 0;
    }

    /**
     * Assign a new alias for a card. A card can have more than one
     * alias, but an alias must only point to one card.
     *
     * @param string $alias  A unique reference to a card
     * @param int    $card   The card id to be assigned the alias
     */
    public function addAlias($alias, $card)
    {
        if (array_key_exists($alias, $this->reel->aliases)) {
            throw new \InvalidArgumentException(sprintf('Alias `%s` already exists', $alias));
        }

        if (!isset($this->reel[$card])) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot assign alias `%s` to missing card of index `%d`', $alias, $card
            ));
        }

        $this->reel->aliases[$alias] = $card;
    }

    /**
     * Change which card an alias refers to.
     *
     * @param string $alias  A unique reference to a card
     * @param int    $card   The card id to be assigned the alias
     */
    public function changeCardForAlias($alias, $card)
    {
        if (!array_key_exists($alias, $this->reel->aliases)) {
            throw new \InvalidArgumentException(sprintf('Alias `%s` does not exist', $alias));
        }

        if (!isset($this->reel[$card])) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot assign alias `%s` to missing card of index `%d`', $alias, $this->reel[$this->reel->aliases[$alias]]
            ));
        }

        $this->reel->aliases[$alias] = $card;
    }

    /**
     * Get a card from the Reel by an alias.
     * A Slot is ultimatly in charge for returning a card from the Reel
     * rather than the Reel itself, hence the extra layer.
     *
     * @param string $alias
     * @return mixed
     */
    public function getCardByAlias($alias)
    {
        return $this->reel->getCardByAlias($alias);
    }

    /**
     * Load a Reel of cards into the Slot
     *
     * @param ReelInterface $reel
     */
    public function setReel(ReelInterface $reel)
    {
        $this->reel = $reel;
    }

    /**
     * Get the Reel of cards
     *
     * @return ReelInterface
     */
    public function getReel()
    {
        return $reel;
    }
}
