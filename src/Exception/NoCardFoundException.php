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
 * An Exception that occurs when the card requested is not found.
 *
 * @author Adam Elsodaney <adam.elso@gmail.com>
 */
class NoCardFoundException extends \OutOfRangeException
{
}
