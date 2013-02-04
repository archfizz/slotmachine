<?php

namespace SlotMachine;

class PageTest extends \PHPUnit_Framework_TestCase
{
    protected $page;
    protected static $config;
    protected static $yamlConfig;

    public static function setUpBeforeClass()
    {
        self::$config = include(__DIR__.'/../fixtures/slotmachine.config.php');
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

    public function testGetConfigFromYamlFile()
    {
        $yamlConfig = \Symfony\Component\Yaml\Yaml::parse(__DIR__.'/../fixtures/slotmachine.config.yml');
        $this->assertEquals($yamlConfig, self::$config);
        $this->page = new Page($yamlConfig);
        $this->assertTrue(is_array($this->page->getConfig()));
        $this->assertEquals('Check out our special offers', $this->page->get('headline', 3));
    }

    /**
     * @covers SlotMachine\Page::get
     */
    public function testGetDefaultCardForSlot()
    {
        $headlineCard = $this->page->get('headline');
        $this->assertEquals('Join our free service today.', $headlineCard);
    }

    /**
     * @covers SlotMachine\Page::get
     */
    public function testGetCardForSlotWithArgument()
    {
        $headlineCard = $this->page->get('headline', 1);
        $this->assertEquals('Welcome, valued customer.', $headlineCard);
    }

    /**
     * @covers SlotMachine\Page::get
     */
    public function testGetCardForSlotWithHttpGetParameter()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create('?h=3', 'GET');

        $page = new Page(self::$config, $request);
        
        $headlineCard = $page->get('headline');
        $this->assertEquals('Check out our special offers', $headlineCard);
    }


    /**
     * @covers SlotMachine\Page::get
     */
    public function testGetCardForSlotWithHttpGetParameterAndArgument()
    {   
        $request = \Symfony\Component\HttpFoundation\Request::create('?h=3', 'GET');
        $page = new Page(self::$config, $request);

        $headlineCard = $page->get('headline', 1);
        $this->assertEquals('Check out our special offers', $headlineCard);
    }

    /**
     * @covers SlotMachine\Page::get
     * @covers SlotMachine\Page::setRequest
     */
    public function testSetRequest()
    {
        $page = clone $this->page;
        $request = \Symfony\Component\HttpFoundation\Request::create('?h=3', 'GET');

        $page->setRequest($request);

        $headlineCard = $page->get('headline', 1);
        $this->assertEquals('Check out our special offers', $headlineCard);
    }

    /**
     * @covers SlotMachine\Page::get
     */
    public function testGetCardForSlotWithHttpGetParametersAndNestedSlots()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create('?h=2&uid=3', 'GET');
        $page = new Page(self::$config, $request);

        $headlineCard = $page->get('headline');
        $this->assertEquals('Welcome back, Brian!', $headlineCard);
    }

    /**
     * @covers SlotMachine\Page::getRequest
     */
    public function testGetRequest()
    {
        $request = $this->page->getRequest();

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Request', $request);
        // $this->assertTrue($request instanceof \Symfony\Component\HttpFoundation\Request);
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
     *
     */
    public function testGetAllCards()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create('?h=2&uid=3&i=1', 'GET');
        $page = new Page(self::$config, $request);

        $data = $page->all();

        $this->assertEquals('Welcome back, Brian!', $data['headline']);
        $this->assertEquals('hero-one.png', $data['hero_image']);
    }
}
