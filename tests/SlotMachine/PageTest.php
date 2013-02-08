<?php

namespace SlotMachine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

class PageTest extends \PHPUnit_Framework_TestCase
{
    protected $page;
    protected static $config;
    protected static $yamlConfig;
    protected static $customConfig;

    public static function setUpBeforeClass()
    {
        self::$config       = include(__DIR__.'/../fixtures/slotmachine.config.php');
        self::$customConfig = include(__DIR__.'/../fixtures/slotmachine_custom.config.php');
    }

    protected function setUp()
    {
        $this->page = new Page(self::$config);
    }

    /**
     * @covers SlotMachine\Page::getConfig
     */
    public function testGetConfig()
    {
        $this->assertTrue(is_array($this->page->getConfig()));
    }

    /**
     * @covers SlotMachine\Page::getConfig
     * @covers SlotMachine\Page::get
     */
    public function testGetConfigFromYamlFile()
    {
        $yamlConfig = Yaml::parse(__DIR__.'/../fixtures/slotmachine.config.yml');
        $this->page = new Page($yamlConfig);

        $this->assertTrue(is_array($this->page->getConfig()));
        $this->assertEquals('Check out our special offers', $this->page->get('headline', 3));
    }

    /**
     * @covers SlotMachine\Page::get
     */
    public function testGetDefaultCardForSlot()
    {
        $this->assertEquals('Join our free service today.', $this->page->get('headline'));
    }

    /**
     * @covers SlotMachine\Page::get
     */
    public function testGetCardForSlotWithArgument()
    {
        $this->assertEquals('Welcome, valued customer.', $this->page->get('headline', 1));
    }

    /**
     * @covers SlotMachine\Page::get
     */
    public function testGetCardForSlotWithHttpGetParameter()
    {
        $request = Request::create('?h=3', 'GET');
        $page = new Page(self::$config, $request);

        $this->assertEquals('Check out our special offers', $page->get('headline'));
    }


    /**
     * @covers SlotMachine\Page::get
     */
    public function testGetCardForSlotWithHttpGetParameterAndArgument()
    {
        $request = Request::create('?h=3', 'GET');
        $page = new Page(self::$config, $request);

        $this->assertEquals('Check out our special offers', $page->get('headline', 1));
    }

    /**
     * @covers SlotMachine\Page::get
     * @covers SlotMachine\Page::setRequest
     */
    public function testSetRequest()
    {
        $page = clone $this->page;
        $request = Request::create('?h=3', 'GET');
        $page->setRequest($request);

        $this->assertEquals('Check out our special offers', $page->get('headline', 1));
    }

    /**
     * @covers SlotMachine\Page::get
     */
    public function testGetCardForSlotWithHttpGetParametersAndNestedSlots()
    {
        $request = Request::create('?h=2&uid=3', 'GET');
        $page = new Page(self::$config, $request);

        $this->assertEquals('Welcome back, Brian!', $page->get('headline'));
    }

    /**
     * @covers SlotMachine\Page::getRequest
     */
    public function testGetRequest()
    {
        $request = $this->page->getRequest();

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Request', $request);
    }

    /**
     * @covers SlotMachine\Page::offsetGet
     */
    public function testOffsetGet()
    {
        $this->assertInstanceOf('\SlotMachine\Slot', $this->page['headline']);
    }

    /**
     * @covers SlotMachine\Page::offsetExists
     */
    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->page['headline']));
        $this->assertFalse(isset($this->page['missing']));
    }

    /**
     * @covers SlotMachine\Page::offsetExists
     * @expectedException InvalidArgumentException
     */
    public function testGetThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException');

        $fake = $this->page->get('fake');
    }

    /**
     * @covers SlotMachine\Page::offsetSet
     */
    public function testOffsetSet()
    {
        $newSlot = new Slot('newslot', array(
            'key' => 'z',
            'cards'   => array('One', 'Two')
        ));
        $page = new Page(self::$config);
        $page['newslot'] = $newSlot;

        $this->assertInstanceOf('\SlotMachine\Slot', $page['newslot']);
    }

    /**
     * @covers SlotMachine\Page::offsetSet
     */
    public function testOffsetSetWithClosure()
    {
        $page = new Page(self::$config);

        $newSlot = $page->share(function () {
            return new Slot('newslot', array(
                'key' => 'z',
                'cards'   => array('One', 'Two')
            ));
        });

        $page['newslot'] = $newSlot;

        $this->assertInstanceOf('\SlotMachine\Slot', $page['newslot']);
    }

    /**
     * @covers SlotMachine\Page::get
     */
    public function testGetUndefinedCardForSlotThatResolvesToDefault()
    {
        $this->assertEquals('Apply Now!', $this->page->get('button_label', 9001));
    }

    /**
     * @covers SlotMachine\Page::get
     */
    public function testGetUndefinedCardForSlotThatResolvesToFallback()
    {
        $this->assertEquals('hero-summer.png', $this->page->get('hero_image', 9001));
    }

    /**
     * @covers SlotMachine\Page::get
     */
    public function testGetConfiguredDefaultCardForSlot()
    {
        $this->assertEquals('hero-two.png', $this->page->get('hero_image'));
    }

    /**
     * @covers SlotMachine\Page::all
     */
    public function testGetAllCards()
    {
        $request = Request::create('?h=2&uid=3&i=1', 'GET');
        $page = new Page(self::$config, $request);
        $data = $page->all();

        $this->assertEquals('Welcome back, Brian!', $data['headline']);
        $this->assertEquals('hero-one.png', $data['hero_image']);
    }

    /**
     * @covers SlotMachine\Page::all
     */
    public function testGetCardsWithCustomConfiguredDelimiter()
    {
        $page = new Page(self::$customConfig);
        $pageData = $page->all();

        $this->assertEquals('Good to be back in London.', $pageData['headline']);
    }

    /**
     * @covers SlotMachine\Page::get
     */
    public function testGetUndefinedCardForSlotThatResolvesToDefaultGlobally()
    {
        $page = new Page(self::$customConfig);

        $this->assertEquals('Good to be back in London.', $page->get('headline', 9001));
    }
}
