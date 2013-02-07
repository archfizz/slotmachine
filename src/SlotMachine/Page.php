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
     *  The delimiter token for nested data
     */
    protected $delimiter = array('{', '}');

    /**
     *  Global flag to determine what should be returned if a card is not found in a slot
     */
    protected $globalResolveUndefinedFlag = 'NO_CARD';

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

        // isset is used instead of array_key_exists to return false if the value is null
        // if a YAML configuration has the entry `delimiter: ~`, this will return false
        if (isset($this->config['options']['delimiter'])) {
            $numberOfTokens = count($this->config['options']['delimiter']);
            if (2 === $numberOfTokens) {
                $this->delimiter = $this->config['options']['delimiter'];
            } else {
                throw new \LengthException(sprintf(
                    'The page must be configured to receive an array of exactly 2 tokens, one opening and one closing. %d given.',
                    $numberOfTokens
                ));
            }
        }

        if (isset($this->config['options']['resolve_undefined'])) {
            $this->globalResolveUndefinedFlag = $this->config['options']['resolve_undefined'];
        }

        // create new instances for each slot configured
        foreach ($config['slots'] as $slotName => &$slotData) {
            if (!isset($slotData['resolve_undefined'])) {
                $slotData['resolve_undefined'] = $this->globalResolveUndefinedFlag;
            }

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
    public function get($slotName, $customDefaultCardIndex = null)
    {

        $slot = $this->offsetGet($slotName);

        $default = (!is_null($customDefaultCardIndex)) ? $customDefaultCardIndex : $slot->getDefaultCardIndex();

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
                    $this->delimiter[0] . $cardName . $this->delimiter[1],
                    $cardValue,
                    $card
                );
            }
        }

        return $card;
    }

    /**
     *  Get the cards for all slots.
     *
     *  @return array
     */
    public function all()
    {
        $allSlotCards = array();
        foreach (array_keys($this->config['slots']) as $slotName) {
            $allSlotCards[$slotName] = $this->get($slotName);
        }
        return $allSlotCards;
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
