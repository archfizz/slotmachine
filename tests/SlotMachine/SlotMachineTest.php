<?php

namespace SlotMachine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

class SlotMachineTest extends \PHPUnit_Framework_TestCase
{
    private $page;
    private static $slotsConfig;

    public static function setUpBeforeClass()
    {
        self::$slotsConfig = Yaml::parse(__DIR__.'/../fixtures/slots.config.yml');
    }

    public function setUp()
    {
        $this->page = new SlotMachine(self::$slotsConfig);
    }

    /**
     * @covers SlotMachine\SlotMachine::get
     */
    public function testGet()
    {
        $this->assertEquals('h', $this->page['headline']->getKey());

        $this->assertEquals('Howdy, stranger. Please take a moment to register.', $this->page->get('headline'));
    }

    /**
     * @covers SlotMachine\SlotMachine::get
     */
    public function testGetDefaultViaObjectMethod()
    {
        $this->assertEquals('Sign up now to begin your free download.', $this->page->get('headline', 2));
    }

    /**
     * @covers SlotMachine\SlotMachine::get
     */
    public function testGetViaRequest()
    {
        // Test from passed parameters
        $slots = new SlotMachine(self::$slotsConfig, Request::create('/', 'GET', array('h' => '2')));
        $this->assertEquals('Sign up now to begin your free download.', $slots->get('headline'));

        // Test from query string
        $slots = new SlotMachine(self::$slotsConfig, Request::create('?h=2', 'GET'));
        $this->assertEquals('Sign up now to begin your free download.', $slots->get('headline'));
    }

    /**
     * @covers SlotMachine\SlotMachine::get
     */
    public function testGetFromArrayViaRequest()
    {
        // Test from passed array parameters
        $slots = new SlotMachine(self::$slotsConfig, 
            Request::create('/', 'GET', array(
                'app_data' => array('fb' => 1)
            ))
        );
        $this->assertEquals('product_page', $slots->get('facebook_page'));

        // Test from array query string
        $slots = new SlotMachine(self::$slotsConfig, Request::create('?app_data[fb]=2', 'GET'));
        $this->assertEquals('promotional_page', $slots->get('facebook_page'));
    }

    /**
     * @covers SlotMachine\SlotMachine::initialize
     */
    public function testInitialize()
    {
        // Test by calling getCard directly on the Slot injected into the container.
        // That way we know that it has been setup.
        $this->assertEquals('Howdy, stranger. Please take a moment to register.', $this->page['headline']->getCard());
    }

    /**
     * @covers SlotMachine\SlotMachine::getConfig
     */
    public function testGetConfig()
    {
        $this->assertTrue(is_array($this->page->getConfig()));
    }

    /**
     * @covers SlotMachine\SlotMachine::getRequest
     */
    public function testGetRequest()
    {
        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Request', $this->page->getRequest());
    }

    /**
     * @covers SlotMachine\SlotMachine::setRequest
     */
    public function testSetRequest()
    {
        $c = clone $this->page;
        $c->setRequest(Request::create('?msg=hello'));
        $this->assertEquals('hello', $c->getRequest()->query->get('msg'));
    }

    /**
     * @covers SlotMachine\SlotMachine::count
     */
    public function testCountable()
    {
        $this->assertEquals(2, count($this->page));
    }
}
