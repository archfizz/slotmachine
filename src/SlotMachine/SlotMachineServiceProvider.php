<?php

namespace SlotMachine;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * This class is created specifically for the Silex micro-framework.
 * To use this Silex service provider, add the following:
 *
 *      $app->register(new SlotMachine\SlotMachineServiceProvider(), array(
 *          'slotmachine.config' => 'path/to/slotmachine.config.php',
 *      ));
 *
 * @package slotmachine
 * @author Adam Elsodaney <adam@archfizz.co.uk>
 */
class SlotMachineServiceProvider implements ServiceProviderInterface
{
    /**
     * The SlotMachine\Page class is accessed as a service by Silex and the
     * configuration array is passed through as a service parameter.
     *
     * @todo Allow other parameters to be set by the service provider
     * @param Application $app
     * @return Page
     */
    public function register(Application $app)
    {
        $app['slotmachine'] = $app->share(function ($name) use ($app) {
            $defaultConfig = $app['slotmachine.config'] ? $app['slotmachine.config'] : array();
            return new Page($defaultConfig);
        });
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }
}
