<?php

namespace SlotMachine;

/**
 * PHP traits require PHP 5.4 or greater.
 * Designed for use with the Silex micro-framework in conjuction
 * with the SlotMachineServiceProvider.
 *
 * Just add `use SlotMachineTrait;` to your custom Application class
 *
 *
 * @link http://silex.sensiolabs.org/ Silex micro-framework
 *
 * @package slotmachine
 * @author Adam Elsodaney <adam@archfizz.co.uk>
 *
 * @todo Write test coverage for traits, without failing in PHP 5.3
 * @todo Consider adding SlotMachine\Page functions to a PageTrait
 *       as both Silex\Application and SlotMachine\Page extend Pimple
 *       use both use Symfony\Component\HttpFoundation\Request.
 */
trait SlotMachineTrait
{
    /**
     * Get the card for a specific slot.
     *
     * @param string       $slot
     * @param integer|null $defaultCardId
     */
    public function slot($slot, $defaultCardId = null)
    {
        if (is_null($defaultCardId)) {
            return $this['slotmachine']->get($slot);
        }
        
        return $this['slotmachine']->get($slot, $defaultCardId);
    }

    /**
     * This function gets all the resulting cards for each slot, similar to that
     * of a payline on a real slot machine.
     *
     * @link http://en.wikipedia.org/wiki/Slot_machine#Terminology Payline
     *
     * @return array
     */
    public function slotCombination()
    {
        return $this['slotmachine']->all();
    }
}
