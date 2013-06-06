<?php

namespace SlotMachine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

class SlotTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers SlotMachine\Slot::getCard
     * @covers SlotMachine\Slot::__toString
     */
    public function testGetCard()
    {
        $slot = new Slot(array(
            'name' => 'city',
            'keys' => array(
                'h'
            ),
            'reel' => array(
                'cards' => array(
                    0 => 'London',
                    1 => 'Paris',
                    2 => 'Madrid'
                )
            ),
        ));
        $this->assertEquals('London', $slot->getCard());
        $this->assertEquals('Madrid', $slot->getCard(2));
        $this->assertEquals('London', $slot);
    }

    /**
     * @covers SlotMachine\Slot::getCardByAlias
     * @covers SlotMachine\Slot::getDefaultIndex
     * @covers SlotMachine\Slot::getDefaultCard
     */
    public function testGetCardByAlias()
    {
        $slot = new Slot(array(
            'name' => 'months',
            'keys' => array(
                'm'
            ),
            'reel' => array(
                'aliases' => array(
                    '_default'  => 4,  // May
                    '_fallback' => 8,  // September
                    'xmas'      => 11, // December
                ),
                'cards' => array(
                    0 => 'January',
                    1 => 'February',
                    2 => 'March',
                    3 => 'April',
                    4 => 'May',
                    5 => 'June',
                    6 => 'July',
                    7 => 'August',
                    8 => 'September',
                    9 => 'October',
                    10 => 'November',
                    11 => 'December'
                )
            ),
        ));

        $this->assertEquals('December', $slot->getCardByAlias('xmas'));
        $this->assertEquals(4, $slot->getDefaultIndex());
        $this->assertEquals('May', $slot->getDefaultCard());
    }

    /**
     * @covers SlotMachine\Slot::getKey
     * @covers SlotMachine\Slot::getKeys
     */
    public function testGetKeys()
    {
        $slot = new Slot(array(
            'name' => 'towns',
            'keys' => array(
                't', 'town', 'app_data[t]'
            ),

            'reel' => array(
                'cards' => array(
                    0 => 'Hastings',
                    1 => 'Cheltenham',
                    2 => 'Poole'
                )
            ),
        ));

        $this->assertEquals('t', $slot->getKey());
        $this->assertEquals(array('t', 'town', 'app_data[t]'), array_values($slot->getKeys()));
    }

    /**
     * @covers SlotMachine\Slot::getNested
     */
    public function testGetNested()
    {
        $slot = new Slot(array(
            'name' => 'message',
            'keys' => array(
                'm',
            ),
            'reel' => array(
                'cards' => array(
                    0 => 'Welcome to {town}',
                    1 => 'Hope you enjoy your stay in {town}',
                    2 => 'Have you ever visted {town} before?'
                )
            ),
            'nested' => array(
                'town'
            ),
        ));

        $nested = $slot->getNested();

        $this->assertEquals(1, count($nested));
        $this->assertEquals('town', $nested[0]);
    }
}
