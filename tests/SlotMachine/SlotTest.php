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
