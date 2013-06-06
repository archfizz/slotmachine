<?php

namespace SlotMachine\Exception;

/**
 * An Exception that occurs when the alias is not assigned to any specific card.
 *
 * @package slotmachine
 * @author Adam Elsodaney <aelso1@gmail.com>
 */
class NoSuchAliasException extends \OutOfRangeException
{
}
