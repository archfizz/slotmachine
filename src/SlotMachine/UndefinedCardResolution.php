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
 * To avoid any collisions or duplicate code, this class is created to contain only constants.
 *
 * @package slotmachine
 * @author Adam Elsodaney <aelso1@gmail.com>
 */
final class UndefinedCardResolution
{
    const NO_CARD_FOUND_EXCEPTION = -1;
    const BLANK_CARD              = 0;
    const DEFAULT_CARD            = 1;
    const FALLBACK_CARD           = 2;

    /**
     * This class should never be instantiated.
     */
    private function __construct()
    {
        throw new \LogicException("Can't get an instance of SlotMachine\\UndefinedCardResolution");
    }
}
