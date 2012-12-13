<?php

namespace Kamereon;

class PageTest extends \PHPUnit_Framework_TestCase
{
    protected $page;

    protected function setUp()
    {
        require __DIR__.'/../fixtures/config.php';
        $this->page = new Page($kamereon);
    }

    /**
     * @covers Kamereon\Page::getConfig
     */
    public function testGetConfig()
    {
        $this->assertTrue(is_array($this->page->getConfig()));
    }

    /**
     * @covers Kamereon\Page::getSlot
     */
    public function testGetSlot()
    {
        $headlineSlot = $this->page->getSlot('headline');
        $this->assertInstanceOf('\Kamereon\Slot', $headlineSlot);
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
        $pageWithHttpGetParameter = clone $this->page;
        $request = \Symfony\Component\HttpFoundation\Request::create('?h=3', 'GET');

        $pageWithHttpGetParameter->setRequest($request);

        $headlineCard = $pageWithHttpGetParameter->get('headline');
        $this->assertEquals('Check out our special offers', $headlineCard);
    }


    /**
     * @covers Kamereon\Page::get
     * @covers Kamereon\Page::setRequest
     */
    public function testGetCardForSlotWithHttpGetParameterAndArgument()
    {
        $pageWithHttpGetParameter = clone $this->page;
        $request = \Symfony\Component\HttpFoundation\Request::create('?h=3', 'GET');

        $pageWithHttpGetParameter->setRequest($request);

        $headlineCard = $pageWithHttpGetParameter->get('headline', 1);
        $this->assertEquals('Check out our special offers', $headlineCard);
    }

    /**
     * @covers Kamereon\Page::get
     */
    public function testGetCardForSlotWithHttpGetParametersAndNestedSlots()
    {
        $pageWithHttpGetParameter = clone $this->page;
        $request = \Symfony\Component\HttpFoundation\Request::create('?h=2&uid=3', 'GET');

        $pageWithHttpGetParameter->setRequest($request);

        $headlineCard = $pageWithHttpGetParameter->get('headline');
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
}
