<?php

namespace SlotMachine;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

class SlotMachineTest extends \PHPUnit_Framework_TestCase
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
        $this->page = new SlotMachine(self::$config);
    }

    /**
     * @covers SlotMachine\SlotMachine::getConfig
     */
    public function testGetConfig()
    {
        $this->assertTrue(is_array($this->page->getConfig()));
    }

    public function testBackwardsCompatibilityWithPageClass()
    {
        $page = new Page($this->page->getConfig());

        $this->assertInstanceOf('\SlotMachine\SlotMachine', $page);
    }

    /**
     * @covers SlotMachine\SlotMachine::getConfig
     * @covers SlotMachine\SlotMachine::get
     */
    public function testGetConfigFromYamlFile()
    {
        $yamlConfig = Yaml::parse(__DIR__.'/../fixtures/slotmachine.config.yml');
        $this->page = new SlotMachine($yamlConfig);

        $this->assertTrue(is_array($this->page->getConfig()));
        $this->assertEquals('Check out our special offers', $this->page->get('headline', 3));
    }

    /**
     * @covers SlotMachine\SlotMachine::get
     */
    public function testGetDefaultCardForSlot()
    {
        $this->assertEquals('Join our free service today.', $this->page->get('headline'));
    }

    /**
     * @covers SlotMachine\SlotMachine::get
     */
    public function testGetCardForSlotWithArgument()
    {
        $this->assertEquals('Welcome, valued customer.', $this->page->get('headline', 1));
    }

    /**
     * @covers SlotMachine\SlotMachine::get
     */
    public function testGetCardForSlotWithHttpGetParameter()
    {
        $request = Request::create('?h=3', 'GET');
        $page = new SlotMachine(self::$config, $request);

        $this->assertEquals('Check out our special offers', $page->get('headline'));
    }


    /**
     * @covers SlotMachine\SlotMachine::get
     */
    public function testGetCardForSlotWithHttpGetParameterAndArgument()
    {
        $request = Request::create('?h=3', 'GET');
        $page = new SlotMachine(self::$config, $request);

        $this->assertEquals('Check out our special offers', $page->get('headline', 1));
    }

    /**
     * @covers SlotMachine\SlotMachine::get
     * @covers SlotMachine\SlotMachine::setRequest
     */
    public function testSetRequest()
    {
        $page = clone $this->page;
        $request = Request::create('?h=3', 'GET');
        $page->setRequest($request);

        $this->assertEquals('Check out our special offers', $page->get('headline', 1));
    }

    /**
     * @covers SlotMachine\SlotMachine::get
     * @covers SlotMachine\SlotMachine::interpolate
     */
    public function testGetCardForSlotWithHttpGetParametersAndNestedSlots()
    {
        $request = Request::create('?h=2&uid=3', 'GET');
        $page = new SlotMachine(self::$config, $request);

        $this->assertEquals('Welcome back, Brian!', $page->get('headline'));
    }

    /**
     * @covers SlotMachine\SlotMachine::getRequest
     */
    public function testGetRequest()
    {
        $request = $this->page->getRequest();

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Request', $request);
    }

    /**
     * @covers SlotMachine\SlotMachine::offsetGet
     */
    public function testOffsetGet()
    {
        $this->assertInstanceOf('\SlotMachine\Slot', $this->page['headline']);
    }

    /**
     * @covers SlotMachine\SlotMachine::offsetExists
     */
    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->page['headline']));
        $this->assertFalse(isset($this->page['missing']));
    }

    /**
     * @covers SlotMachine\SlotMachine::offsetExists
     * @expectedException InvalidArgumentException
     */
    public function testGetThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException');

        $fake = $this->page->get('fake');
    }

    /**
     * @covers SlotMachine\SlotMachine::offsetSet
     */
    public function testOffsetSet()
    {
        $newSlot = new Slot(
            array(
                'name' => 'newslot',
                'key'  => 'z',
            ),
            new Reel(array(
                'cards' => array('One', 'Two')
            ))
        );
        $page = new SlotMachine(self::$config);
        $page['newslot'] = $newSlot;

        $this->assertInstanceOf('\SlotMachine\Slot', $page['newslot']);
    }

    /**
     * @covers SlotMachine\SlotMachine::offsetSet
     */
    public function testOffsetSetWithClosure()
    {
        $page = new SlotMachine(self::$config);

        $newSlot = $page->share(function () {
            return new Slot(
                array(
                    'name' => 'newslot',
                    'key'  => 'z',
                ),
                new Reel(array(
                    'cards' => array('One', 'Two')
                ))
            );
        });

        $page['newslot'] = $newSlot;

        $this->assertInstanceOf('\SlotMachine\Slot', $page['newslot']);
    }

    /**
     * @covers SlotMachine\SlotMachine::get
     */
    public function testGetUndefinedCardForSlotThatResolvesToDefault()
    {
        $this->assertEquals('Apply Now!', $this->page->get('button_label', 9001));
    }

    /**
     * @covers SlotMachine\SlotMachine::get
     */
    public function testGetUndefinedCardForSlotThatResolvesToFallback()
    {
        $this->assertEquals('hero-summer.png', $this->page->get('hero_image', 9001));
    }

    /**
     * @covers SlotMachine\SlotMachine::get
     */
    public function testGetUndefinedCardForSlotThatResolvesToFallbackAndHasGlobalResolveOption()
    {
        $page = new SlotMachine(self::$customConfig);

        $this->assertEquals('parrot.jpg', $page->get('animal_image', 9001));
    }

    /**
     * @covers SlotMachine\SlotMachine::get
     */
    public function testGetConfiguredDefaultCardForSlot()
    {
        $this->assertEquals('hero-two.png', $this->page->get('hero_image'));
    }

    /**
     * @covers SlotMachine\SlotMachine::all
     * @covers SlotMachine\SlotMachine::interpolate
     */
    public function testGetAllCards()
    {
        $request = Request::create('?h=2&uid=3&i=1', 'GET');
        $page = new SlotMachine(self::$config, $request);
        $data = $page->all();

        $this->assertEquals('Welcome back, Brian!', $data['headline']);
        $this->assertEquals('hero-one.png', $data['hero_image']);
    }

    /**
     * @covers SlotMachine\SlotMachine::all
     * @covers SlotMachine\SlotMachine::interpolate
     */
    public function testGetCardsWithCustomConfiguredDelimiter()
    {
        $page = new SlotMachine(self::$customConfig);
        $pageData = $page->all();

        $this->assertEquals('Good to be back in London.', $pageData['headline']);
    }

    /**
     * @covers SlotMachine\SlotMachine::get
     * @covers SlotMachine\SlotMachine::interpolate
     */
    public function testGetUndefinedCardForSlotThatResolvesToDefaultGlobally()
    {
        $page = new SlotMachine(self::$customConfig);

        $this->assertEquals('Good to be back in London.', $page->get('headline', 9001));
    }

    /**
     * @covers SlotMachine\SlotMachine::count
     */
    public function testCount()
    {
        $this->assertEquals(6, count($this->page));
    }

    /**
     * @covers SlotMachine\SlotMachine::setDelimiter
     * @covers SlotMachine\SlotMachine::interpolate
     */
    public function testSetDelimiter()
    {
        $quoteSlot = new Slot(
            array(
                'name' => 'quote',
                'key'  => 'a',
            ),
            new Reel(array(
                'cards' => array('I like **item**', 'Do you have any **item**')
            ))
        );

        $itemSlot = new Slot(
            array(
                'name' => 'item',
                'key'  => 'z',
            ),
            new Reel(array(
                'cards'  => array('cake', 'tea')
            ))
        );

        $quoteSlot->addNestedSlot($itemSlot);

        $page = new SlotMachine(self::$config);
        $page['quote'] = $quoteSlot;
        $page['item']  = $itemSlot;

        $page->setDelimiter(array('**', '**'));

        $this->assertEquals('I like cake', $page->get('quote'));
    }

    /**
     * @covers SlotMachine\SlotMachine::createSlot
     */
    public function testCreateSlot()
    {
        $page = new SlotMachine(self::$config);
        $page->createSlot(array('name' => 'hello', 'key' => 'a'), new Reel(array('cards' => array('salut', 'ciao'))));

        $this->assertInstanceOf('\SlotMachine\Slot', $page['hello']);
        $this->assertEquals('ciao', $page->get('hello', 1));
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

    /**
     * @covers SlotMachine\SlotMachine::get
     */
    public function testGetCardForSlotWithHttpGetArray()
    {
        $page = new SlotMachine(array(
            'slots' => array(
                'headline' => array(
                    'reel' => 'headline',
                    'key'  => 'app_data[h]'
                ),
                'image' => array(
                    'reel' => 'image',
                    'key'  => 'app_data[i]'
                )
            ),
            'reels' => array(
                'headline' => array(
                    'cards' => array(
                        0 => 'Sorry, we are closed',
                        1 => 'Welcome, we are open'
                    )
                ),
                'image' => array(
                    'cards' => array(
                        0 => 'go-away.jpg',
                        1 => 'come-in.jpg'
                    )
                )
            )
        ), Request::create('?app_data[h]=1&app_data[i]=1', 'GET'));

        $this->assertEquals('Welcome, we are open', $page->get('headline'));
        $this->assertEquals('come-in.jpg', $page->get('image'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testUndefinedReelForSlotThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException');

        $page = new SlotMachine(array(
            'slots' => array(
                'male_users' => array(
                    'reel' => 'male_users',
                    'key'  => 'm'
                ),
                'female_users' => array(
                    'reel' => 'female_users',
                    'key'  => 'f'
                )
            ),
            'reels' => array(
                'men' => array( // deliberate naming error
                    'cards' => array(
                        0 => 'Adam',
                        1 => 'Brian',
                        2 => 'Costas',
                        3 => 'Derek'
                    )
                ),
                'female_users' => array(
                    'cards' => array(
                        0 => 'Joanne',
                        1 => 'Kelly',
                        2 => 'Ling',
                        3 => 'Marina'
                    )
                )
            )
        ));
    }

    /**
     * @covers SlotMachine\SlotMachine::toJson
     * @covers SlotMachine\SlotMachine::__toString
     */
    public function testToJsonString()
    {
        $page = new SlotMachine(self::$config, Request::create('?h=1'));

        $c = json_decode($page);

        $this->assertEquals('Welcome, valued customer.', $c->headline);
    }

    /**
     * @covers SlotMachine\SlotMachine::get
     */
    public function testGetDefaultCardForSlotWithMultipleKeys()
    {
        $this->assertEquals('company_page', $this->page->get('facebook_like_page'));
    }

    /**
     * @covers SlotMachine\SlotMachine::get
     */
    public function testGetCardForSlotWithMultipleKeys()
    {
        $page = new SlotMachine(self::$config, Request::create('?app_data[f]=1'));

        $this->assertEquals('product_page', $page->get('facebook_like_page'));
    }
}
