<?php

namespace SlotMachine;

class SlotTest extends \PHPUnit_Framework_TestCase
{
    protected $mainSlot;
    protected $nestedSlot;
    protected $thirdSlot;
    protected $fourthSlot;

    /**
     * In some tests, the following slot objects are manipulated,
     * so the setUp method is use to redeclare them before each test.
     * Which is why the setUpBeforeClass method is not utilised
     */
    protected function setUp()
    {
        $this->mainSlot  = new Slot(
            'foo',
            array(
                'key' => 'a',
                'nested_with' => array(
                    'bar'
                )
            ),
            new Reel(array(
                'cards' => array(
                    0 => 'zero',
                    1 => 'one',
                    2 => 'two',
                    3 => 'three'
                )
            ))
        );

        $this->nestedSlot = new Slot(
            'bar',
            array(
                'key' => 'b',
            ),
            new Reel(array(
                'cards' => array(
                    0 => 'cero',
                    1 => 'uno',
                    2 => 'dos',
                    3 => 'tres'
                )
            ))
        );

        $this->mainSlot->addNestedSlot($this->nestedSlot);

        $this->thirdSlot = new Slot(
            'baz',
            array(
                'key' => 'c',
            ),
            new Reel(array(
                'cards' => array(
                    0 => 'niets',
                    1 => 'een',
                    2 => 'twee',
                    3 => 'drie'
                ),
                'aliases' => array(
                    'two' => 2
                ),
                'resolve_undefined' => 'DEFAULT_CARD',
            ))
        );

        $this->fourthSlot = new Slot(
            'qux',
            array(
                'key' => 'd',
            ),
            new Reel(array(
                'cards' => array(
                    0 => 'rei',
                    1 => 'ichi',
                    2 => 'ni',
                    3 => 'san',
                    4 => 'shi'
                ),
                'aliases' => array(
                    '_fallback' => 3,
                    '_default' => 4
                ),
                'resolve_undefined' => 'FALLBACK_CARD',
            ))
        );
    }

    /**
     * @covers SlotMachine\Slot::getName
     */
    public function testGetName()
    {
        $this->assertEquals('foo', $this->mainSlot->getName());
    }

    /**
     * @covers SlotMachine\Slot::getNestedSlots
     */
    public function testGetNestedSlots()
    {
        $this->assertTrue(is_array($this->mainSlot->getNestedSlots()));
        $this->assertGreaterThan(0, count($this->mainSlot->getNestedSlots()));
        $this->assertEquals(0, count($this->nestedSlot->getNestedSlots()));
    }

    /**
     * @covers SlotMachine\Slot::getCard
     */
    public function testGetCard()
    {
        $this->assertEquals('three', $this->mainSlot->getCard(3));
    }

    /**
     * @covers SlotMachine\Slot::getNestedSlotByName
     * @covers SlotMachine\Slot::getCard
     */
    public function testGetNestedSlotCard()
    {
        $this->assertEquals('dos', $this->mainSlot->getNestedSlotByName('bar')->getCard(2));
    }

    /**
     * @covers SlotMachine\Slot::getKey
     */
    public function testGetKey()
    {
        $this->assertEquals('a', $this->mainSlot->getKey());
    }

    /**
     * @covers SlotMachine\Slot::getKey
     * @covers SlotMachine\Slot::getNestedSlotByName
     */
    public function testGetKeyForNestedSlot()
    {
        $this->assertEquals('b', $this->mainSlot->getNestedSlotByName('bar')->getKey());
    }

    /**
     * @covers SlotMachine\Slot::hasNestedSlots
     */
    public function testHasNestedSlots()
    {
        $this->assertTrue($this->mainSlot->hasNestedSlots());
        $this->assertFalse($this->nestedSlot->hasNestedSlots());
    }

    /**
     * @covers SlotMachine\Slot::getCardByAlias
     */
    public function testGetCardByDefaultAlias()
    {
        $this->assertEquals('zero', $this->mainSlot->getCardByAlias('_default'));
    }

    /**
     * @covers SlotMachine\Slot::addAlias
     */
    public function testAddAlias()
    {
        $this->mainSlot->addAlias('drei', 3);

        $this->assertEquals('three', $this->mainSlot->getCardByAlias('drei'));
    }

    /**
     * @covers SlotMachine\Slot::addAlias
     */
    public function testAddMoreThanOneAliasToCard()
    {
        $this->mainSlot->addAlias('drei', 3);
        $this->mainSlot->addAlias('trois', 3);

        $this->assertEquals('three', $this->mainSlot->getCardByAlias('drei'));
        $this->assertEquals('three', $this->mainSlot->getCardByAlias('trois'));
    }

    /**
     * @covers SlotMachine\Slot::addAlias
     * @expectedException InvalidArgumentException
     */
    public function testAddAlreadyDefinedAlias()
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->mainSlot->addAlias('drei', 3);
        $this->mainSlot->addAlias('drei', 3);
    }

    /**
     * @covers SlotMachine\Slot::addAlias
     * @expectedException InvalidArgumentException
     */
    public function testAddAliasToUndefinedCard()
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->mainSlot->addAlias('power-level', 9001);
    }

    /**
     * @covers SlotMachine\Slot::changeCardForAlias
     */
    public function testChangeCardForAlias()
    {
        $this->mainSlot->changeCardForAlias('_default', 3);

        $this->assertEquals('three', $this->mainSlot->getCardByAlias('_default'));
    }

    /**
     * @covers SlotMachine\Slot::getCardByAlias
     */
    public function testGetCustomDefaultCard()
    {
        $this->assertEquals('shi', $this->fourthSlot->getCardByAlias('_default'));
    }

    /**
     * @covers SlotMachine\Slot::changeCardForAlias
     * @expectedException InvalidArgumentException
     */
    public function testChangeCardForUndefinedAlias()
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->mainSlot->changeCardForAlias('unicorns', 1);
    }

    /**
     * @covers SlotMachine\Slot::changeCardForAlias
     * @expectedException InvalidArgumentException
     */
    public function testChangeToUndefinedCardForAlias()
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->mainSlot->changeCardForAlias('_default', 9001);
    }

    /**
     * @covers SlotMachine\Slot::getCard
     */
    public function testGetUndefinedCardWithResolveToDefaultSetting()
    {
        $this->assertEquals('niets', $this->thirdSlot->getCard(9001));
    }

    /**
     * @covers SlotMachine\Slot::getCardByAlias
     */
    public function testGetCardByCustomAlias()
    {
        $this->assertEquals('twee', $this->thirdSlot->getCardByAlias('two'));
    }

    /**
     * @covers SlotMachine\Slot::getCard
     */
    public function testGetUndefinedCardWithResolveToFallbackSetting()
    {
        $this->assertEquals('san', $this->fourthSlot->getCard(9001));
    }
}
