<?php

namespace Kamereon;

class PageTest extends \PHPUnit_Framework_TestCase
{
    protected $page;
    protected $config;

    protected function setUp()
    {
        $this->config = include(__DIR__.'/../fixtures/config.php');
        $this->page = new Page($this->config);
    }

    /**
     * @covers Kamereon\Page::getConfig
     */
    public function testGetConfig()
    {
        $this->assertTrue(is_array($this->page->getConfig()));
    }

    /**
     * @covers Kamereon\Page::get
     */
    public function testGetDefaultCardForSlot()
    {
        $headlineCard = $this->page->get('headline');
        $this->assertEquals('Join our free service today.', $headlineCard);
    }

    /**
     * @covers Kamereon\Page::get
     */
    public function testGetCardForSlotWithArgument()
    {
        $headlineCard = $this->page->get('headline', 1);
        $this->assertEquals('Welcome, valued customer.', $headlineCard);
    }

    /**
     * @covers Kamereon\Page::get
     */
    public function testGetCardForSlotWithHttpGetParameter()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create('?h=3', 'GET');

        $page = new Page($this->config, $request);
        
        $headlineCard = $page->get('headline');
        $this->assertEquals('Check out our special offers', $headlineCard);
    }


    /**
     * @covers Kamereon\Page::get
     */
    public function testGetCardForSlotWithHttpGetParameterAndArgument()
    {   
        $request = \Symfony\Component\HttpFoundation\Request::create('?h=3', 'GET');
        $page = new Page($this->config, $request);

        $headlineCard = $page->get('headline', 1);
        $this->assertEquals('Check out our special offers', $headlineCard);
    }

    /**
     * @covers Kamereon\Page::get
     * @covers Kamereon\Page::setRequest
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
     * @covers Kamereon\Page::get
     */
    public function testGetCardForSlotWithHttpGetParametersAndNestedSlots()
    {
        $request = \Symfony\Component\HttpFoundation\Request::create('?h=2&uid=3', 'GET');
        $page = new Page($this->config, $request);

        $headlineCard = $page->get('headline');
        $this->assertEquals('Welcome back, Brian!', $headlineCard);
    }

    /**
     * @covers Kamereon\Page::getRequest
     */
    public function testGetRequest()
    {
        $request = $this->page->getRequest();

        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Request', $request);
        // $this->assertTrue($request instanceof \Symfony\Component\HttpFoundation\Request);
    }

    /**
     * @covers Kamereon\Page::offsetGet
     */
    public function testOffsetGet()
    {
        $this->assertInstanceOf('\Kamereon\Slot', $this->page['headline']);
    }

    /**
     * @covers Kamereon\Page::offsetExists
     */
    public function testOffsetExists()
    {
        $this->assertTrue(isset($this->page['headline']));
        $this->assertFalse(isset($this->page['missing']));
    }

    /**
     * @covers Kamereon\Page::offsetExists
     * @expectedException InvalidArgumentException
     */
    public function testGetThrowsException()
    {
        $this->setExpectedException('InvalidArgumentException');

        $fake = $this->page->get('fake');
    }

    /**
     * @covers Kamereon\Page::offsetSet
     */
    public function testOffsetSet()
    {
        $newSlot = new Slot('newslot', array(
            'keyBind' => 'z',
            'cards'   => array('One', 'Two')
        ));
        $page = new Page($this->config);
        $page['newslot'] = $newSlot;
        $this->assertInstanceOf('\Kamereon\Slot', $page['newslot']);
    }
}
