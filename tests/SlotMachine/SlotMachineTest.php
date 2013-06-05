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
     * @covers SlotMachine\SlotMachine::get
     */
    public function testGetAndResolveParameters()
    {
        // Test from array query string
        $slots = new SlotMachine(self::$slotsConfig, Request::create('?app_data[h]=1', 'GET'));
        $this->assertEquals('Register today for your free gift.', $slots->get('headline'));

        // Test from passed array parameters
        $slots = new SlotMachine(self::$slotsConfig, Request::create('/', 'GET', array('app_data' => array('h' => 2))));
        $this->assertEquals('Sign up now to begin your free download.', $slots->get('headline'));
    }

    /**
     * @covers SlotMachine\SlotMachine::initialize
     */
    public function testAssignReel()
    {
        $this->assertEquals('London', $this->page->get('city'));
        $this->assertEquals('Cologne', $this->page->get('city', 9));
    }

    /**
     * Not sure if this test is really needed, but it's here anyway.
     * @covers SlotMachine\SlotMachine::get
     */
    public function testGetReturnsUtf8()
    {
        // Cyrillic
        $this->assertEquals('Москва', $this->page->get('city_l10n', 8));

        // Arabic
        $this->assertEquals('القاهرة', $this->page->get('city_l10n', 4));

        // Chinese
        $this->assertEquals('上海', $this->page->get('city_l10n', 1));

        // Japanese
        $this->assertEquals('東京', $this->page->get('city_l10n', 3));

        // Thai
        $this->assertEquals('กรุงเทพมหานคร', $this->page->get('city_l10n', 6));

        // Latin Extended
        $this->assertEquals('Köln', $this->page->get('city_l10n', 9));
    }

    /**
     * @covers SlotMachine\SlotMachine::initialize
     */
    public function testWithNestedSlots()
    {
        $slots = new SlotMachine(self::$slotsConfig, Request::create('?uid=7&h=3'));
        $this->assertEquals('Welcome back, Stan!', $slots->get('headline'));
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
        $this->assertEquals(5, count($this->page));
    }

    /**
     * @covers SlotMachine\SlotMachine::interpolate
     */
    public function testInterpolate()
    {
        $card = 'I used to {verb} {article} {noun}, but then I took an arrow to the knee.';
        $interpolated = SlotMachine::interpolate($card, array(
            'verb'    => 'be',
            'article' => 'an',
            'noun'    => 'adventurer'
        ));

        $this->assertEquals('I used to be an adventurer, but then I took an arrow to the knee.', $interpolated);

        // try with custom delimiters
        $card = 'I used to %verb% %article% %noun%, but then I took an arrow to the knee.';
        $interpolated = SlotMachine::interpolate($card, array(
            'verb'    => 'listen',
            'article' => 'to',
            'noun'    => 'dubstep'
        ), array('%', '%'));

        $this->assertEquals('I used to listen to dubstep, but then I took an arrow to the knee.', $interpolated);
    }

    /**
     * @covers SlotMachine\SlotMachine::interpolate
     * @expectedException LengthException
     */
    public function testInterpolateThrowsException()
    {
        $this->setExpectedException('LengthException');

        $card = 'Yo <target>, I\'m real happy for you, Imma let you finish, but <subject> is one of the best <product> of all time!';
        $interpolated = SlotMachine::interpolate($card, array(
            'target'  => 'Zend',
            'subject' => 'Symfony',
            'product' => 'PHP frameworks'
        ), array('<'));
    }

    /**
     * @covers SlotMachine\SlotMachine::interpolate
     * @expectedException PHPUnit_Framework_Error
     */
    public function testInterpolateEmitsWarning()
    {
        $card = '"<quote>", said no one ever!';

        $interpolated = SlotMachine::interpolate($card, array(
            'quote'  => 'PHP is a solid language',
        ), array('<', '>', '*'));
    }

    /**
     * @covers SlotMachine\SlotMachine::interpolate
     */
    public function testInterpolateWhileEmittingWarning()
    {
        $card = '"<quote>", said no one ever!';

        $interpolated = @SlotMachine::interpolate($card, array(
            'quote'  => "I won't stay longer than 4 hours in Starbucks for I need to be elsewhere",
        ), array('<', '>', '*'));

        $this->assertEquals(
            "\"I won't stay longer than 4 hours in Starbucks for I need to be elsewhere\", said no one ever!",
            $interpolated
        );
    }
}
