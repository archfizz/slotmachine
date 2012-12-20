<?php

namespace Kamereon;

class SlotTest extends \PHPUnit_Framework_TestCase
{
    protected $mainSlot;
    protected $nestedSlot;

    protected function setUp()
    {
        $this->mainSlot  = new Slot('foo', array(
            'keyBind'    => 'a',
            'nestedWith' => array('bar'),
            'cards'      => array(1 => 'one', 2 => 'two', 3 => 'three')
        ));
        $this->nestedSlot = new Slot('bar', array(
            'keyBind'    => 'b',
            'cards'      => array(1 => 'uno', 2 => 'dos', 3 =>'tres')
        ));
        $this->mainSlot->addNestedSlot($this->nestedSlot);
    }

    /**
     * @covers Kamereon\Slot::getName
     */
    public function testGetName()
    {
        $this->assertEquals('foo', $this->mainSlot->getName());
    }

    /**
     * @covers Kamereon\Slot::getNestedSlots
     */
    public function testGetNestedSlots()
    {
        $this->assertTrue(is_array($this->mainSlot->getNestedSlots()));
        $this->assertGreaterThan(0, count($this->mainSlot->getNestedSlots()));

        $this->assertEquals(0, count($this->nestedSlot->getNestedSlots()));
    }

    /**
     * @covers Kamereon\Slot::getCard
     */
    public function testGetCard()
    {
        $cardText = $this->mainSlot->getCard(3);
        $this->assertEquals('three', $cardText);
    }

    /**
     * @covers Kamereon\Slot::getNestedSlotByName
     * @covers Kamereon\Slot::getCard
     */
    public function testGetNestedSlotCard()
    {
        $cardText = $this->mainSlot->getNestedSlotByName('bar')->getCard(2);
        $this->assertEquals('dos', $cardText);
    }

    /**
     * @covers Kamereon\Slot::getKeyBind
     */
    public function testGetKeyBind()
    {
        $this->assertEquals('a', $this->mainSlot->getKeyBind());
    }

    /**
     * @covers Kamereon\Slot::getKeyBind
     * @covers Kamereon\Slot::getNestedSlotByName
     */
    public function testGetKeyBindForNestedSlot()
    {
        $this->assertEquals('b', $this->mainSlot->getNestedSlotByName('bar')->getKeyBind());
    }

    /**
     * @covers Kamereon\Slot::hasNestedSlots
     */
    public function testHasNestedSlots()
    {
        $this->assertTrue($this->mainSlot->hasNestedSlots());
        $this->assertFalse($this->nestedSlot->hasNestedSlots());
    }
}
