<?php

namespace SlotMachine;

class ReelTest extends \PHPUnit_Framework_TestCase
{
    protected static $cards = array(
        0 => "Those who mind don't matter, and those who matter don't mind",
        1 => "Always forgive your enemies, nothing annoys them so much"
    );

    protected static $reel;

    public function setUp()
    {
        self::$reel = new Reel(self::$cards);
    }

    /**
     * @covers SlotMachine\Reel::count
     */
    public function testCount()
    {
        $this->assertEquals(2, count(self::$reel));
    }

    /**
     * @covers SlotMachine\Reel::getIterator
     */
    public function testGetIterator()
    {
        $cards = array();

        foreach (self::$reel as $cardId => $cardValue) {
            $cards[$cardId] = $cardValue;
        }

        $this->assertEquals("Those who mind don't matter, and those who matter don't mind", $cards[0]);
        $this->assertEquals("Always forgive your enemies, nothing annoys them so much", $cards[1]);
    }

    /**
     * @covers SlotMachine\Reel::offsetExists
     * @expectedException InvalidArgumentException
     */
    public function testOffsetExists()
    {
        $this->setExpectedException('InvalidArgumentException');

        $nonExistentCard = self::$reel[420];
    }

    /**
     * SlotMachine\Reel::offsetUnset
     * @expectedException InvalidArgumentException
     */
    public function testOffsetUnset()
    {
        $this->setExpectedException('InvalidArgumentException');

        $reel = clone self::$reel;
        unset($reel[0]);

        $unsetCard = $reel[0];
    }
}
