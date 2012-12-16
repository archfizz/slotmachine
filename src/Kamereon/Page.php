<?php

namespace Kamereon;

use Symfony\Component\HttpFoundation\Request;

/**
 *  The base for a new dynamic landing page.
 *  Each dynamic placeholder is called a slot
 *  where a slot will hold many cards for one
 *  to be displayed depending on a set of
 *  given parameters.
 *
 *  @package kamereon
 *  @author Adam Elsodaney <adam@archfizz.co.uk>
 */
class Page implements \ArrayAccess
{
    /**
     *  The Symfony HttpFoundation Request object.
     */
    protected $request;

    /**
     *  Raw configuration data.
     */
    protected $config = array();

    /**
     *  Collection of Slot objects.
     */
    protected $slots  = array();

    /**
     *  Loads the config data and creates new Slot instances.
     *  A custom Request can be injected, otherwise defaults 
     *  to creating one from PHP globals.
     *
     *  @param array $config
     *  @param Request $request
     */
    public function __construct(array $config, Request $request = null)
    {
        $this->request = (is_null($request)) ? Request::createFromGlobals() : $request;
        $this->config  = $config;

        // create new instances for each slot configured
        foreach ($config['slots'] as $slotName => $slotData) {
            $this->slots[$slotName] = new Slot($slotName, $slotData);
        }

        // inject nested slots
        foreach ($config['slots'] as $slotName => $slotData) {
            if (isset($slotData['nestedWith']) && count($slotData['nestedWith']) > 0) {
                foreach ($slotData['nestedWith'] as $nestedSlotName) {
                    $this->slots[$slotName]->addNestedSlot($this->slots[$nestedSlotName]);
                }
            }
        }
    }

    /**
     * Checks if a Slot is set.
     *
     * @param string $slotName The unique identifier for the Slot
     *
     * @return Boolean
     */
    public function offsetExists($slotName)
    {
        return array_key_exists($slotName, $this->slots);
    }

    /**
     * Unsets a Slot.
     *
     * @param string $slotName The unique identifier for the Slot
     */
    public function offsetUnset($slotName)
    {
        unset($this->slots[$slotName]);
        return true;
    }

    /**
     * Gets a Slot.
     *
     * @param string $slotName The unique identifier for the Slot
     *
     * @return Slot The Slot object
     *
     * @throws InvalidArgumentException if the slot id is not found or not defined
     */
    public function offsetGet($slotName)
    {
        if (!array_key_exists($slotName, $this->slots)) {
            throw new \InvalidArgumentException(sprintf('Slot "%s" could not be found', $slotName));
        }

        if ($this->offsetExists($slotName)) {
            return $this->slots[$slotName];
        }
    }
 
    /**
     * Sets a Slot.
     *
     * @param string $slotName The unique identifier for the Slot
     * @param Slot   $slot     The Slot object
     */
    public function offsetSet($slotName, $slot)
    {
        $this->slots[$slotName] = $slot;
    }

    /**
     *  Get the configuration array.
     *
     *  @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     *  Get the card value for a slot. 
     *
     *  @param  string $slotName
     *  @param  string $default
     *  @return string
     */
    public function get($slotName, $default = '0')
    {
        $slot = $this->offsetGet($slotName);

        try {
            $card = $slot->getCard($this->request->get($slot->getKeyBind(), $default));
        } catch (\InvalidArgumentException $e) {
            $card = '';
        }

        if ($slot->hasNestedSlots()) {

            foreach ($slot->getNestedSlots() as $nestedSlot) {
                try {
                    $nestedCards[$nestedSlot->getName()] = $nestedSlot->getCard(
                        $this->request->get($nestedSlot->getKeyBind(), $default)
                    );
                } catch (\Exception $e) {
                    $nestedCards[$nestedSlot->getName()] = '';
                }
            }

            foreach ($nestedCards as $cardName => $cardValue) {
                $card = str_replace(
                    sprintf('{%s}', $cardName),
                    $cardValue,
                    $card
                );
            }
        }

        return $card;
    }

    /**
     *  Override the request instance by injecting your own.
     *
     *  @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     *  Get the request instance.
     *
     *  @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }
}
