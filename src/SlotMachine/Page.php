<?php

namespace SlotMachine;

use Symfony\Component\HttpFoundation\Request;

/**
 *  The base for a new dynamic landing page.
 *  Each dynamic placeholder is called a slot
 *  where a slot will hold many cards for one
 *  to be displayed depending on a set of
 *  given parameters.
 *
 *  @package slotmachine
 *  @author Adam Elsodaney <adam@archfizz.co.uk>
 */
class Page extends \Pimple
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
     *  Loads the config data and creates new Slot instances.
     *  A custom Request can be injected, otherwise defaults 
     *  to creating one from PHP globals.
     *
     *  @param array $config
     *  @param Request $request
     */
    public function __construct(array $config, Request $request = null)
    {
        parent::__construct();

        $page = $this;

        $this->request = (is_null($request)) ? Request::createFromGlobals() : $request;
        $this->config  = $config;

        $this['slot_class'] = 'SlotMachine\\Slot';

        // create new instances for each slot configured
        foreach ($config['slots'] as $slotName => $slotData) {
            $this[$slotName] = $this->share(function ($page) use ($slotName, $slotData) {
                return new $page['slot_class']($slotName, $slotData);
            });
        }

        // inject nested slots
        foreach ($config['slots'] as $slotName => $slotData) {
            if (isset($slotData['nested_with']) && count($slotData['nested_with']) > 0) {
                foreach ($slotData['nested_with'] as $nestedSlotName) {
                    $this[$slotName]->addNestedSlot($this[$nestedSlotName]);
                }
            }
        }
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
            $card = $slot->getCard($this->request->query->get($slot->getKey(), $default));
        } catch (\InvalidArgumentException $e) {
            $card = '';
        }

        if ($slot->hasNestedSlots()) {

            foreach ($slot->getNestedSlots() as $nestedSlot) {
                try {
                    $nestedCards[$nestedSlot->getName()] = $nestedSlot->getCard(
                        $this->request->query->get($nestedSlot->getKey(), $default)
                    );
                } catch (\InvalidArgumentException $e) {
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
