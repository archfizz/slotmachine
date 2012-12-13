<?php

namespace Kamereon;

use Symfony\Component\HttpFoundation\Request;

/**
 *  The base for a new dynamic landing page. Each dynamic placeholder is called a slot
 *  where a slot will hold many cards for one to be displayed depending on a set of
 *  given parameters.
 *
 *  @author Adam Elsodaney <adam@archfizz.co.uk>
 */
class Page
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
     *
     *  @param array $config
     */
    public function __construct(array $config)
    {
        $this->request = Request::createFromGlobals();
        $this->config  = $config;

        // created new instances for each slot
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
     *  Get the configuration array.
     *
     *  @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     *  Get a Slot object from the slot collection by its key.
     *
     *  @param string $slot
     *  @return Slot
     */
    public function getSlot($slot)
    {
        return $this->slots[$slot];
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
        $slot = $this->slots[$slotName];

        try {
            $card = $slot->getCard($this->request->get($slot->getKeyBind(), $default));
        } catch (\Exception $e){
            $card = '';
        }

        if ($slot->getHasNestedSlots()) {

            foreach ($slot->getNestedSlots() as $nestedSlot) {
                try {
                    $nestedCards[$nestedSlot->getName()] = $nestedSlot->getCard(
                        $this->request->get($nestedSlot->getKeyBind(), $default)
                    );
                } catch (\Exception $e){
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
