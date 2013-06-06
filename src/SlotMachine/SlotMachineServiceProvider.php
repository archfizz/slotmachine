<?php

namespace SlotMachine;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * This class is created specifically for the Silex micro-framework.
 * To use this Silex service provider, add the following:
 *
 *      $app->register(new SlotMachine\SlotMachineServiceProvider(), array(
 *          'slotmachine.config' => include('path/to/slotmachine.config.php'),
 *      ));
 *
 * @package slotmachine
 * @author Adam Elsodaney <aelso1@gmail.com>
 */
class SlotMachineServiceProvider implements ServiceProviderInterface
{
    /**
     * The SlotMachine\SlotMachine class is accessed as a service by Silex and the
     * configuration array is passed through as a service parameter.
     *
     * @todo Allow other parameters to be set by the service provider
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app['slotmachine'] = $app->share(function () use ($app) {
            $config  = $app['slotmachine.config']  ?: array();
            $request = $app['slotmachine.request'] ?: $request;

            return new SlotMachine($config);
        });
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }
}
