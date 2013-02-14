<?php

namespace SlotMachine;

/**
 * A placeholder for variable content on a page, which card values will be assigned
 * to it collectively as an instance of a Reel.
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
     * The name of the slot.
     * @var string
     */
    protected $name;

    /**
     * The key name that is bound to the slot.
     * A key can be shared with another slot.
     * @var string
     */
    protected $key;

    /**
     * An array of the names of nested slots.
     * @var array
     */
    protected $nestedSlotNames = array();

    /**
     * The collection array of nested Slot objects.
     * @var array
     */
    protected $nestedSlots = array();

    /**
     * The Reel containing a list cards where one will be returned.
     * @var ReelInterface
     */
    protected $reel;

    /**
     * Setting for what to do if a requested card does not exist.
     * @var null|integer
     */
    protected $resolveUndefined = null;

    /**
     * Create new slot with name, configuration data and its Reel.
     * If the slot has nested slots, initially assign only the names of those slots.
     *
     * @param array         $data
     * @param ReelInterface $reel
     */
    public function __construct(array $data, ReelInterface $reel)
    {
        $this->name   = $data['name'];
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
     * Get the name of the slot.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add a slot to the nested slots collection.
     *
     * @param SlotInterface $slot
     */
    public function addNestedSlot(SlotInterface $slot)
    {
        $this->nestedSlots[$slot->getName()] = $slot;
    }

    /**
     * Get all nested slots.
     *
     * @return array
     */
    public function getNestedSlots()
    {
        return $this->nestedSlots;
    }

    /**
     * Get specific nested slot.
     *
     * @param  string $name
     * @return SlotInterface
     */
    public function getNestedSlotByName($name)
    {
        return $this->nestedSlots[$name];
    }

    /**
     * Get a value of a card by its id.
     * If the card does not exist, resolve based on the slot's resolve_undefined setting.
     *
     * @param  integer $cardId
     * @return string
     * @throws InvalidArgumentException if the key does not exist and
     *         the resolveUndefined property is set to NO_CARD.
     */
    public function getCard($cardId)
    {
        try {
            return $this->reel[$cardId];
        } catch (\InvalidArgumentException $e) {
            switch ($this->resolveUndefined) {
                case self::NO_CARD:
                    throw new \InvalidArgumentException(sprintf(
                        'Card with ID "%s" does not exist in Slot with name "%s"', $cardId, $this->name));
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
     * Gets the default card index assigned to the '_default' alias.
     * Note that this does not return the card itself, which is done
     * by calling `Reel::getCardByAlias('_default')`.
     *
     * @return integer
     */
    public function getDefaultCardIndex()
    {
        return $this->reel->aliases['_default'];
    }

    /**
     * Get the binded key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Check if a slot contains other slots nested within.
     *
     * @return boolean
     */
    public function hasNestedSlots()
    {
        return count($this->nestedSlots) > 0;
    }

    /**
     * Assign a new alias for a card. A card can have more than one alias,
     * but an alias must point to only one card.
     *
     * @param  string  $alias  A unique reference to a card.
     * @param  integer $cardId The card id to be assigned the alias.
     * @throws \InvalidArgumentException if an alias already exists or the cardId does not exist.
     */
    public function addAlias($alias, $cardId)
    {
        if (array_key_exists($alias, $this->reel->aliases)) {
            throw new \InvalidArgumentException(sprintf('Alias "%s" already exists', $alias));
        }

        if (!isset($this->reel[$cardId])) {
            throw new \InvalidArgumentException(sprintf('Cannot assign alias "%s" to undefined card with ID "%d"', $alias, $cardId));
        }

        $this->reel->aliases[$alias] = $cardId;
    }

    /**
     * Change which card an alias refers to.
     *
     * @param string  $alias  A unique reference to a card
     * @param integer $cardId The card id to be assigned the alias
     */
    public function changeCardForAlias($alias, $cardId)
    {
        if (!array_key_exists($alias, $this->reel->aliases)) {
            throw new \InvalidArgumentException(sprintf('Alias `%s` does not exist', $alias));
        }

        if (!isset($this->reel[$cardId])) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot assign alias `%s` to missing card of index `%d`', $alias, $this->reel[$this->reel->aliases[$alias]]
            ));
        }

        $this->reel->aliases[$alias] = $cardId;
    }

    /**
     * Get a card from the Reel by an alias.
     * A Slot is ultimatly in charge for returning a card from the Reel
     * rather than the Reel itself, hence the extra layer.
     *
     * @param  string $alias
     * @return mixed
     */
    public function getCardByAlias($alias)
    {
        return $this->reel->getCardByAlias($alias);
    }

    /**
     * Load a Reel of cards into the Slot.
     *
     * @param ReelInterface $reel
     */
    public function setReel(ReelInterface $reel)
    {
        $this->reel = $reel;
    }

    /**
     * Get the Reel of cards.
     *
     * @return ReelInterface
     */
    public function getReel()
    {
        return $reel;
    }
}
