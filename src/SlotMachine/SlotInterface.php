<?php

namespace SlotMachine;

/**
 * SlotInterface retrieves a card from an object implementing ReelInterface.
 *
 * @package slotmachine
 * @author Adam Elsodaney <adam@archfizz.co.uk>
 */
interface SlotInterface
{
    /**
     * Get a value of a card by its id.
     * If the card does not exist, resolve based on the slot's resolve_undefined setting.
     *
     * @param  integer $cardId
     * @return mixed
     * @throws InvalidArgumentException if the key does not exist and
     *         the resolveUndefined property is set to NO_CARD.
     */
    public function getCard($cardId);

    /**
     * Get a card from the Reel by an alias.
     *
     * @param  string $alias
     * @return mixed
     */
    public function getCardByAlias($alias);
}
