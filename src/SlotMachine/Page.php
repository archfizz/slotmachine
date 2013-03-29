<?php

namespace SlotMachine;

use Symfony\Component\HttpFoundation\Request;

/**
 * The base for a new dynamic landing page.
 * Each dynamic placeholder is called a slot where a slot will hold many cards for one
 * to be displayed depending on a set of given parameters.
 *
 * @package slotmachine
 * @author Adam Elsodaney <adam@archfizz.co.uk>
 */
class Page extends \Pimple implements \Countable
{
    /**
     * The Symfony HttpFoundation Request object.
     * @var Request
     */
    protected $request;

    /**
     * Raw configuration data.
     * @var array
     */
    protected $config = array();

    /**
     * The delimiter token for nested data.
     * @var array
     */
    protected $delimiter = array('{', '}');

    /**
     * Global flag to determine what should be returned if a card is not found in a slot.
     * @var string
     */
    protected $globalResolveUndefinedFlag = 'NO_CARD';

    /**
     * Loads the config data and creates new Slot instances.
     * A custom Request can be injected, otherwise defaults to creating one from PHP globals.
     *
     * @param array        $config
     * @param Request|null $request
     */
    public function __construct(array $config, Request $request = null)
    {
        parent::__construct();

        $page = $this;

        $this->request = (is_null($request)) ? Request::createFromGlobals() : $request;
        $this->config  = $config;

        $this['slot_class'] = 'SlotMachine\\Slot';
        $this['reel_class'] = 'SlotMachine\\Reel';

        // isset is used instead of array_key_exists to return false if the value is null
        // if a YAML configuration has the entry `delimiter: ~`, this will return false
        if (isset($this->config['options']['delimiter'])) {
            $this->setDelimiter($this->config['options']['delimiter']);
        }

        if (isset($this->config['options']['resolve_undefined'])) {
            $this->globalResolveUndefinedFlag = $this->config['options']['resolve_undefined'];
        }

        // a temporary container for the Reels
        $reels = array();

        // create new Reels
        foreach ($this->config['reels'] as $reelName => $reelData) {
            $options = $reelData;
            $options['name'] = $reelName;

            $reels[$reelName] = new $this['reel_class']($options);
        }

        // create new instances for each slot configured
        foreach ($config['slots'] as $slotName => &$slotData) {
            if (!isset($slotData['resolve_undefined'])) {
                $slotData['resolve_undefined'] = $this->globalResolveUndefinedFlag;
            }

            $slotData['name'] = $slotName;

            $this->createSlot($slotData, $reels[$slotData['reel']]);
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
     * Inject new Slot instances into the container by returning them as a shared service.
     *
     * @param array              $slotData
     * @param ReelInterface|null $reel
     */
    public function createSlot(array $slotData, ReelInterface $reel = null)
    {
        $page = $this;

        $this[$slotData['name']] = $this->share(function ($page) use ($slotData, $reel) {
            return new $page['slot_class']($slotData, $reel);
        });
    }

    /**
     * Sets the delimiter tokens for nested slots.
     *
     * @param array $delimiterTokens
     * @throws \LengthException if anything other than an array of two tokens is received.
     * @todo Cover exception throwing in tests.
     */
    public function setDelimiter(array $delimiterTokens)
    {
        if (2 !== $numberOfTokens = count($delimiterTokens)) {
            throw new \LengthException(sprintf(
                'The page must be configured to receive an array of exactly 2 tokens, one opening and one closing. %d given.',
                $numberOfTokens
            ));
        }

        $this->delimiter = $delimiterTokens;
    }

    /**
     * Get the configuration array.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Get the card value for a slot.
     *
     * @param  string       $slotName
     * @param  integer|null $customDefaultCardIndex
     * @return string
     */
    public function get($slotName, $customDefaultCardIndex = null)
    {
        $slot = $this->offsetGet($slotName);

        $default = (!is_null($customDefaultCardIndex)) ? $customDefaultCardIndex : $slot->getDefaultCardIndex();
        $key     = $slot->getKey();
        $isDeep  = strpos($key, '[') !== false && strpos($key, ']') !== false;

        try {
            $card = $slot->getCard($this->request->query->get($key, $default, $isDeep));
        } catch (\InvalidArgumentException $e) {
            return '';
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

            $card = static::interpolate($card, $nestedCards, $this->delimiter);
        }

        return $card;
    }

    /**
     * Interpolates cards values into the cards nested slot placeholders.
     * Based on the example given in the PSR-3 specification.
     *
     * @link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md PSR-3 specification
     * @param string $card
     * @param array  $nestedCards
     * @param array  $delimiter
     * @return string
     */
    public static function interpolate($card, array $nestedCards, array $delimiter = array('{', '}'))
    {
        if (2 > $tokens = count($delimiter)) {
            throw new \LengthException('Number of delimiter tokens too short. Method requires exactly 2.');
        }

        if ($tokens > 2) {
            trigger_error('Too many delimiter tokens given', E_USER_WARNING);
        }

        // build a replacement array with custom delimiters around the nested slots
        $replace = array();
        foreach ($nestedCards as $nestedSlotName => $nestedCard) {
            $replace[$delimiter[0] . $nestedSlotName . $delimiter[1]] = $nestedCard;
        }

        // interpolate replacement values into the message and return
        return strtr($card, $replace);
    }

    /**
     * Get the cards for all slots.
     *
     * @return array
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
     * Override the request instance by injecting your own.
     *
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Get the request instance.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Count the number of slots created.
     *
     * @return integer
     */
    public function count()
    {
        // Pimple::keys
        foreach ($this->keys() as $valueName) {
            static $count;
            if ($this[$valueName] instanceof $this['slot_class']) {
                ++$count;
            }
        }

        return $count;
    }
}
