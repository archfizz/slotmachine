<?php

/*
 * This file is part of the SlotMachine library.
 *
 * (c) Adam Elsodaney <adam@archfizz.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace tests\SlotMachine;

use SlotMachine\Slot;
use SlotMachine\SlotMachine;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

if (!class_exists('PHPUnit_Framework_TestCase')) {
    class_alias('PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
}

class SlotMachineTest extends \PHPUnit_Framework_TestCase
{
    /** @var SlotMachine|Slot[] */
    private $page;

    /** @var array */
    private static $slotsConfig;

    /** @var array */
    private static $slotsConfigWithOptions;

    /** @var bool */
    private static $hasDeepParameterAccess;

    public function doSlotMachineSetUp()
    {
        $parameterBagGet = new \ReflectionMethod('Symfony\Component\HttpFoundation\ParameterBag', 'get');

        self::$slotsConfig = Yaml::parse(file_get_contents(__DIR__.'/../fixtures/slots.config.yml'));
        self::$slotsConfigWithOptions = Yaml::parse(file_get_contents(__DIR__.'/../fixtures/slots_with_options.config.yml'));
        self::$hasDeepParameterAccess = $parameterBagGet->getNumberOfParameters() === 3;

        $this->page = new SlotMachine(self::$slotsConfig);
    }

    /**
     * @covers \SlotMachine\SlotMachine::count
     */
    public function testCountable()
    {
        $this->doSlotMachineSetUp();

        $this->assertEquals(10, count($this->page));
    }

    /**
     * @covers \SlotMachine\SlotMachine::initialize
     */
    public function testInitializeWithOptions()
    {
        $this->doSlotMachineSetUp();

        $s = new SlotMachine(self::$slotsConfigWithOptions);
        $this->assertEquals('Welcome back, Guest!', $s->get('headline', 3));
        $this->assertEquals(2, count($s));

        $t = new SlotMachine(self::$slotsConfigWithOptions, Request::create('?uid=8000'));

        $this->assertEquals('See you again, Admin!', $t->get('headline', 8000));
    }

    /**
     * @covers \SlotMachine\SlotMachine::get
     */
    public function testGet()
    {
        $this->doSlotMachineSetUp();

        $this->assertEquals('h', $this->page['headline']->getKey());

        $this->assertEquals('Howdy, stranger. Please take a moment to register.', $this->page->get('headline'));

        // This slot has a custom default that should be used.
        $this->assertEquals('penguin.png', $this->page->get('featured_image'));
    }

    /**
     * @covers \SlotMachine\SlotMachine::get
     * @covers \SlotMachine\SlotMachine::all
     * @covers \SlotMachine\SlotMachine::toJson
     * @covers \SlotMachine\SlotMachine::__toString
     */
    public function testAll()
    {
        $this->doSlotMachineSetUp();

        $slots = $this->page->all();

        $this->assertEquals('Howdy, stranger. Please take a moment to register.', $slots['headline']);
        $this->assertEquals('penguin.png', $slots['featured_image']);

        $json = json_decode($this->page);
        $this->assertEquals('penguin.png', $json->featured_image);
    }

    /**
     * @covers \SlotMachine\SlotMachine::get
     * @covers \SlotMachine\SlotMachine::all
     * @covers \SlotMachine\SlotMachine::toJson
     * @covers \SlotMachine\SlotMachine::__toString
     */
    public function testAllWithCustomRequestAndDeepParameters()
    {
        $this->doSlotMachineSetUp();

        if (!self::$hasDeepParameterAccess) {
            $this->markTestSkipped('This test requires deep parameter access from Symfony 2');
            return;
        }

        // Now try with a custom request
        $slots = new SlotMachine(self::$slotsConfig, Request::create('?app_data[h]=4&app_data[uid]=11&app_data[i]=2'));
        $data = json_decode($slots);
        $this->assertEquals('See you again, Claus!', $data->headline);
        $this->assertEquals('<img src="parrot.png" alt="Featured Image" />', $data->featured_image_html);
    }

    /**
     * @covers \SlotMachine\SlotMachine::get
     */
    public function testGetUndefinedCard()
    {
        $this->doSlotMachineSetUp();

        // Return the default card
        $this->assertEquals('Howdy, stranger. Please take a moment to register.', $this->page->get('headline', 9001));
        $this->assertEquals('penguin.png', $this->page->get('featured_image', 9001));

        // Return the fallback card
        $this->assertEquals('Dubstep', $this->page->get('music_genre', 9001));

        $this->assertEquals('', $this->page->get('music_genre_optional', 9001));
    }

    /**
     * @covers \SlotMachine\SlotMachine::get
     * @expectedException \SlotMachine\Exception\NoCardFoundException
     */
    public function testGetUndefinedCardThrowsException()
    {
        $this->doSlotMachineSetUp();

        if (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('SlotMachine\Exception\NoCardFoundException');
        } else {
            $this->expectException('SlotMachine\Exception\NoCardFoundException');
        }

        $this->assertEquals('Splittercore', $this->page->get('music_genre_required', 9001));
    }

    /**
     * @covers \SlotMachine\SlotMachine::get
     */
    public function testGetDefaultViaObjectMethod()
    {
        $this->doSlotMachineSetUp();

        $this->assertEquals('Sign up now to begin your free download.', $this->page->get('headline', 2));

        // This slot has a custom default and should be overridden.
        $this->assertEquals('parrot.png', $this->page->get('featured_image', 2));
    }

    /**
     * @covers \SlotMachine\SlotMachine::get
     */
    public function testGetViaRequest()
    {
        $this->doSlotMachineSetUp();

        // Test from passed parameters
        $slots = new SlotMachine(self::$slotsConfig, Request::create('/', 'GET', array('h' => '2')));
        $this->assertEquals('Sign up now to begin your free download.', $slots->get('headline'));

        // Test from query string
        $slots = new SlotMachine(self::$slotsConfig, Request::create('?h=2', 'GET'));
        $this->assertEquals('Sign up now to begin your free download.', $slots->get('headline'));
    }

    /**
     * @covers \SlotMachine\SlotMachine::get
     */
    public function testGetFromArrayViaRequest()
    {
        $this->doSlotMachineSetUp();

        if (!self::$hasDeepParameterAccess) {
            $this->markTestSkipped('This test requires deep parameter access from Symfony 2');
            return;
        }

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

        $slots = new SlotMachine(self::$slotsConfig, Request::create('?app_data[i]=5', 'GET'));
        $this->assertEquals('elephant.png', $slots->get('featured_image'));
    }

    /**
     * @covers \SlotMachine\SlotMachine::get
     */
    public function testGetAndResolveParameters()
    {
        $this->doSlotMachineSetUp();

        if (!self::$hasDeepParameterAccess) {
            $this->markTestSkipped('This test requires deep parameter access from Symfony 2');
            return;
        }

        // Test from array query string
        $slots = new SlotMachine(self::$slotsConfig, Request::create('?app_data[h]=1', 'GET'));
        $this->assertEquals('Register today for your free gift.', $slots->get('headline'));

        // Test from passed array parameters
        $slots = new SlotMachine(self::$slotsConfig, Request::create('/', 'GET', array('app_data' => array('h' => 2))));
        $this->assertEquals('Sign up now to begin your free download.', $slots->get('headline'));
    }

    /**
     * @covers \SlotMachine\SlotMachine::initialize
     */
    public function testAssignReel()
    {
        $this->doSlotMachineSetUp();

        $this->assertEquals('London', $this->page->get('city'));
        $this->assertEquals('Cologne', $this->page->get('city', 9));
    }

    /**
     * Not sure if this test is really needed, but it's here anyway.
     * @covers \SlotMachine\SlotMachine::get
     */
    public function testGetReturnsUtf8()
    {
        $this->doSlotMachineSetUp();

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
     * @covers \SlotMachine\SlotMachine::initialize
     */
    public function testWithNestedSlots()
    {
        $this->doSlotMachineSetUp();

        $slots = new SlotMachine(self::$slotsConfig, Request::create('?uid=7&h=3'));
        $this->assertEquals('Welcome back, Stan!', $slots->get('headline'));
    }

    /**
     * @covers \SlotMachine\SlotMachine::initialize
     */
    public function testWithNestedSlotsAndArrayParameters()
    {
        $this->doSlotMachineSetUp();

        if (!self::$hasDeepParameterAccess) {
            $this->markTestSkipped('This test requires deep parameter access from Symfony 2');
            return;
        }

        $slots = new SlotMachine(self::$slotsConfig, Request::create('?app_data[uid]=6&app_data[h]=4'));
        $this->assertEquals('See you again, Lois!', $slots->get('headline'));
    }

    /**
     * @covers \SlotMachine\SlotMachine::initialize
     */
    public function testWithNestedSlotsAndCustomDefaults()
    {
        $this->doSlotMachineSetUp();

        if (!self::$hasDeepParameterAccess) {
            $this->markTestSkipped('This test requires deep parameter access from Symfony 2');
            return;
        }

        $this->assertEquals('<img src="penguin.png" alt="Featured Image" />', $this->page->get('featured_image_html'));

        $slots = new SlotMachine(self::$slotsConfig, Request::create('?app_data[i]=6'));
        $this->assertEquals('<img src="tiger.png" alt="Featured Image" />', $slots->get('featured_image_html'));

        $slots = new SlotMachine(self::$slotsConfig, Request::create('?app_data[i]=6&app_data[ih]=0'));
        $this->assertEquals('<img src="tiger.png" />', $slots->get('featured_image_html'));

        $slots = new SlotMachine(self::$slotsConfig, Request::create('?app_data[i]=6&app_data[ih]=43'));
        $this->assertEquals('<img src="tiger.png" alt="Featured Image" />', $slots->get('featured_image_html'));
    }

    /**
     * @covers \SlotMachine\SlotMachine::initialize
     */
    public function testInitialize()
    {
        $this->doSlotMachineSetUp();

        // Test by calling getCard directly on the Slot injected into the container.
        // That way we know that it has been setup.
        $this->assertEquals('Howdy, stranger. Please take a moment to register.', $this->page['headline']->getCard());
    }

    /**
     * @covers \SlotMachine\SlotMachine::getConfig
     */
    public function testGetConfig()
    {
        $this->doSlotMachineSetUp();

        $this->assertTrue(is_array($this->page->getConfig()));
    }

    /**
     * @covers \SlotMachine\SlotMachine::getRequest
     */
    public function testGetRequest()
    {
        $this->doSlotMachineSetUp();

        $this->assertInstanceOf('Symfony\\Component\\HttpFoundation\\Request', $this->page->getRequest());
    }

    /**
     * @covers \SlotMachine\SlotMachine::setRequest
     */
    public function testSetRequest()
    {
        $this->doSlotMachineSetUp();

        $c = clone $this->page;
        $c->setRequest(Request::create('?msg=hello'));
        $this->assertEquals('hello', $c->getRequest()->query->get('msg'));
    }

    /**
     * @covers \SlotMachine\SlotMachine::interpolate
     */
    public function testInterpolate()
    {
        $this->doSlotMachineSetUp();

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
     * @covers \SlotMachine\SlotMachine::interpolate
     * @expectedException \LengthException
     */
    public function testInterpolateThrowsException()
    {
        $this->doSlotMachineSetUp();

        if (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('LengthException');
        } else {
            $this->expectException('LengthException');
        }

        $card = 'Yo <target>, I\'m real happy for you, Imma let you finish, but <subject> is one of the best <product> of all time!';
        SlotMachine::interpolate($card, array(
            'target'  => 'Zend',
            'subject' => 'Symfony',
            'product' => 'PHP frameworks'
        ), array('<'));
    }

    /**
     * @covers \SlotMachine\SlotMachine::interpolate
     */
    public function testInterpolateEmitsWarning()
    {
        $this->doSlotMachineSetUp();

        if (method_exists($this, 'setExpectedException')) {
            $this->setExpectedException('PHPUnit_Framework_Error');
        } else {
            $this->expectException('PHPUnit\Framework\Error\Error');
        }

        $card = '"<quote>", said no one ever!';

        SlotMachine::interpolate($card, array(
            'quote'  => 'PHP is a solid language',
        ), array('<', '>', '*'));
    }

    /**
     * @covers \SlotMachine\SlotMachine::interpolate
     */
    public function testInterpolateWhileEmittingWarning()
    {
        $this->doSlotMachineSetUp();

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
