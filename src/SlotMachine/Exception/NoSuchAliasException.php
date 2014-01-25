<?php

/*
 * This file is part of the SlotMachine library.
 *
 * (c) Adam Elsodaney <adam@archfizz.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
