<?php

namespace SlotMachine;

/**
 * To avoid any collisions or duplicate code, this class is created to contain only constants.
 */
final class UndefinedCardResolution
{
    const NO_CARD_FOUND_EXCEPTION = -1;
    const BLANK_CARD              = 0;
    const DEFAULT_CARD            = 1;
    const FALLBACK_CARD           = 2;

    private function __construct()
    {
        throw new \Exception("Can't get an instance of UndefinedCardResolution");
    }
}