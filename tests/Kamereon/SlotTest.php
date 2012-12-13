<?php

namespace Kamereon;

class SlotTest extends \PHPUnit_Framework_TestCase
{
    protected $page;

    protected function setUp()
    {
        require __DIR__.'/../fixtures/config.php';
        $this->page = new Page($kamereon);
    }

    /**
     * @covers Kamereon\Slot::getName
     */
    public function testGetName()
    {
        $this->assertEquals('headline', $this->page->getSlot('headline')->getName());
    }

    /**
     * @covers Kamereon\Slot::getNestedSlots
     */
    public function testGetNestedSlots()
    {
        $this->assertTrue(is_array($this->page->getSlot('headline')->getNestedSlots()));
        $this->assertGreaterThan(0, count($this->page->getSlot('headline')->getNestedSlots()));

        $this->assertEquals(0, count($this->page->getSlot('body')->getNestedSlots()));
    }

    /**
     * @covers Kamereon\Slot::getCard
     */
    public function testGetCard()
    {
        $cardText = $this->page->getSlot('body')->getCard(0);
        $this->assertEquals('Time is of the essence, apply now!', $cardText);
    }

    /**
     * @covers Kamereon\Slot::getNestedSlotByName
     * @covers Kamereon\Slot::getCard
     */
    public function testGetNestedSlotCard()
    {
        $cardText = $this->page->getSlot('headline')->getNestedSlotByName('user')->getCard(2);
        $this->assertEquals('Lois', $cardText);
    }

    /**
     * @covers Kamereon\Slot::getCard
     * @expectedException PHPUnit_Framework_Error_Notice
     */
    public function testGetCardWithInvalidSlotName()
    {
        $this->setExpectedException('PHPUnit_Framework_Error_Notice');

        $cardText = $this->page->getSlot('fake')->getCard(2);
    }

    /**
     * @covers Kamereon\Slot::getKeyBind
     */
    public function testGetKeyBind()
    {
        $this->assertEquals('h', $this->page->getSlot('headline')->getKeyBind());
    }

    /**
     * @covers Kamereon\Slot::getKeyBind
     * @covers Kamereon\Slot::getNestedSlotByName
     */
    public function testGetKeyBindForNestedSlot()
    {
        $this->assertEquals('uid', $this->page->getSlot('headline')->getNestedSlotByName('user')->getKeyBind());
    }

    /**
     * @covers Kamereon\Slot::getHasNestedSlots
     */
    public function testGetHasNestedSlots()
    {
        $this->assertTrue($this->page->getSlot('headline')->getHasNestedSlots());
        $this->assertFalse($this->page->getSlot('user')->getHasNestedSlots());
    }
}
