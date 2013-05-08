<?php

namespace SlotMachine;

use Silex\Application;
use Silex\ServiceProviderInterface;

class SlotMachineServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    protected static $config;

    public static function setUpBeforeClass()
    {
        self::$config = include(__DIR__.'/../fixtures/slotmachine.config.php');
    }

    /**
     * @covers SlotMachine\SlotMachineServiceProvider::register
     */
    public function testRegister()
    {
        $app = new Application();

        $app->register(new SlotMachineServiceProvider(), array(
            'slotmachine.config' => self::$config,
        ));

        $this->assertInstanceOf('SlotMachine\SlotMachine', $app['slotmachine']);
        $this->assertEquals('Join our free service today.', $app['slotmachine']->get('headline'));
    }
}
