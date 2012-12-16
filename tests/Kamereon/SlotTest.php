<?php

namespace Kamereon;

class SlotTest extends \PHPUnit_Framework_TestCase
{
    protected $page;

    protected function setUp()
    {
        $this->config = include(__DIR__.'/../fixtures/config.php');
        $this->page = new Page($this->config);
    }

    /**
     * @covers Kamereon\Slot::getName
     */
    public function testGetName()
    {
        $this->assertEquals('headline', $this->page['headline']->getName());
    }

    /**
     * @covers Kamereon\Slot::getNestedSlots
     */
    public function testGetNestedSlots()
    {
        $this->assertTrue(is_array($this->page['headline']->getNestedSlots()));
        $this->assertGreaterThan(0, count($this->page['headline']->getNestedSlots()));

        $this->assertEquals(0, count($this->page['body']->getNestedSlots()));
    }

    /**
     * @covers Kamereon\Slot::getCard
     */
    public function testGetCard()
    {
        $cardText = $this->page['body']->getCard(0);
        $this->assertEquals('Time is of the essence, apply now!', $cardText);
    }

    /**
     * @covers Kamereon\Slot::getNestedSlotByName
     * @covers Kamereon\Slot::getCard
     */
    public function testGetNestedSlotCard()
    {
        $cardText = $this->page['headline']->getNestedSlotByName('user')->getCard(2);
        $this->assertEquals('Lois', $cardText);
    }

    /**
     * @covers Kamereon\Slot::getCard
     * @expectedException InvalidArgumentException
     */
    public function testGetCardWithInvalidSlotName()
    {
        $this->setExpectedException('InvalidArgumentException');

        $cardText = $this->page['fake']->getCard(2);
    }

    /**
     * @covers Kamereon\Slot::getKeyBind
     */
    public function testGetKeyBind()
    {
        $this->assertEquals('h', $this->page['headline']->getKeyBind());
    }

    /**
     * @covers Kamereon\Slot::getKeyBind
     * @covers Kamereon\Slot::getNestedSlotByName
     */
    public function testGetKeyBindForNestedSlot()
    {
        $this->assertEquals('uid', $this->page['headline']->getNestedSlotByName('user')->getKeyBind());
    }

    /**
     * @covers Kamereon\Slot::hasNestedSlots
     */
    public function testHasNestedSlots()
    {
        $this->assertTrue($this->page['headline']->hasNestedSlots());
        $this->assertFalse($this->page['user']->hasNestedSlots());
    }
}
