<?php

namespace SlotMachine\Exception;

/**
 * An Exception that occurs when the card requested is not found.
 *
 * @package slotmachine
 * @author Adam Elsodaney <aelso1@gmail.com>
 */
class NoCardFoundException extends \OutOfRangeException
{
}
