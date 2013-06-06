<?php

namespace SlotMachine;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Request;

class SlotMachineServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    protected static $slotsConfig;

    public static function setUpBeforeClass()
    {
        self::$slotsConfig = Yaml::parse(__DIR__.'/../fixtures/slots.config.yml');
    }

    /**
     * @covers SlotMachine\SlotMachineServiceProvider::register
     */
    public function testRegister()
    {
        $app = new Application();

        $app->register(new SlotMachineServiceProvider(), array(
            'slotmachine.config'  => self::$slotsConfig,
            'slotmachine.request' => Request::createFromGlobals()
        ));

        $this->assertInstanceOf('SlotMachine\\SlotMachine', $app['slotmachine']);
        $this->assertEquals('Howdy, stranger. Please take a moment to register.', $app['slotmachine']->get('headline'));
    }
}
