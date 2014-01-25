<?php

/*
 * This file is part of the SlotMachine library.
 *
 * (c) Adam Elsodaney <adam@archfizz.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SlotMachine;

/**
 * SlotInterface retrieves a card from the reel.
 *
 * @package slotmachine
 * @author Adam Elsodaney <aelso1@gmail.com>
 */
interface SlotInterface
{
    /**
     * Get a value of a card by its index.
     * If the card does not exist, resolve based on the slot's resolve_undefined setting.
     *
     * @param  integer $index
     * @return mixed
     * @throws SlotMachine\Exception\NoCardFoundException if the key does not exist and
     *         the undefinedCardResolution property is set to NO_CARD_FOUND_EXCEPTION.
     */
    public function getCard($index);

    /**
     * Get a card from the reel by an alias.
     *
     * @param  string $alias
     * @return mixed
     * @throws SlotMachine\Exception\NoSuchAliasException if the alias does not exist.
     */
    public function getCardByAlias($alias);
}
