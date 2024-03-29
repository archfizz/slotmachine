<?php

/*
 * This file is part of the SlotMachine library.
 *
 * (c) Adam Elsodaney <adam@archfizz.co.uk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SlotMachine;

use Symfony\Component\HttpFoundation\Request;

if (!class_exists('Pimple')) {
    class_alias('Pimple\Container', 'Pimple');
}

/**
 * SlotMachine is a content container for dynamic pages written for PHP 5.3 and
 * above. Each component on a page that can change its value is called a slot,
 * and works is much the same way a slot machine does, except that the slot's
 * cards are not randomly displayed, (but it can be if you wanted it to).
 *
 * Please visit the official git repository for any issues you may have.
 *
 * @link https://github.com/archfizz/slotmachine
 *
 * @author Adam Elsodaney <adam.elso@gmail.com>
 */
class SlotMachine extends \Pimple implements \Countable
{
    const VERSION = '2.0.0';
    const MAJOR_VERSION = 2;
    const MINOR_VERSION = 0;
    const PATCH_VERSION = 0;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var array
     */
    protected $reels;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var array
     */
    protected $delimiter = array('{', '}');

    /**
     * @var integer
     */
    protected $undefinedCardResolution = UndefinedCardResolution::DEFAULT_CARD;

    const NOT_SET_PARAMETER = "not_set";

    /**
     * @param array         $config   The SlotMachine configuration data
     * @param Request|null  $request  The Request object
     */
    public function __construct(array $config = array(), Request $request = null)
    {
        parent::__construct();

        $this->config = $config;
        $this->request = !is_null($request) ? $request : Request::createFromGlobals();

        $this->initialize();
    }

    /**
     * Set up the SlotMachine in a ready to use state
     */
    private function initialize()
    {
        $this->undefinedCardResolution = isset($this->config['options']['undefined_card'])
            ? static::translateUndefinedCardResolution($this->config['options']['undefined_card'])
            : UndefinedCardResolution::DEFAULT_CARD;

        if (isset($this->config['options']['delimiter'])) {
            $this->delimiter = $this->config['options']['delimiter'];
        }

        if (!isset($this->config['slots'])) {
            $this->config['slots'] = array();
        }

        foreach ($this->config['slots'] as $slotName => &$slotData) {
            $slotData['name'] = $slotName;

            if (is_string($slotData['reel'])) {
                $slotData['reel'] = $this->config['reels'][$slotData['reel']];
            }

            if (!isset($slotData['nested'])) {
                $slotData['nested'] = array();
            }

            $slotData['undefined_card'] = (!isset($slotData['undefined_card']))
                ? $this->undefinedCardResolution
                : static::translateUndefinedCardResolution($slotData['undefined_card']);

            $this->offsetSet($slotName, function () use ($slotData) {
                return new Slot($slotData);
            });
        }
    }

    /**
     * @param string $option  The name of the constant
     *
     * @return integer
     *
     * @throws \InvalidArgumentException
     */
    public static function translateUndefinedCardResolution($option)
    {
        if (defined($setting = '\\SlotMachine\\UndefinedCardResolution::' . $option)) {
            return constant($setting);
        }

        throw new \InvalidArgumentException($setting . ' is not a valid option');
    }

    /**
     * Interpolates cards values into the cards nested slot placeholders.
     * Based on the example given in the PSR-3 specification.
     *
     * @link https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md PSR-3 specification
     * @param string $card
     * @param array  $nestedCards
     * @param array  $delimiter
     * @throws \LengthException if less than two delimiter tokens are giving.
     * @return string
     */
    public static function interpolate($card, array $nestedCards = array(), array $delimiter = array('{', '}'))
    {
        if (2 > $tokens = count($delimiter)) {
            throw new \LengthException('Number of delimiter tokens too short. Method requires exactly 2.');
        }

        // SlotMachine can still function with more than two delimiter tokens,
        // but will generate a warning.
        if ($tokens > 2) {
            trigger_error('Too many delimiter tokens given', E_USER_WARNING);
        }

        // build a replacement array with braces around the context keys
        $replace = array();
        foreach ($nestedCards as $slot => $nestedCard) {
          $replace[$delimiter[0] . $slot . $delimiter[1]] = $nestedCard;
        }

        // interpolate replacement values into the message and return
        return strtr($card, $replace);
    }

    /**
     * @param string  $slotName
     * @param integer $default
     * @return string
     */
    public function get($slotName, $default = null)
    {
        // Resolve default index. The one passed to the second argument will
        // take precedence, followed by the slot's default index.

        // Check if the slot's default value has been set and the method's
        // default value is empty
        $slotDefault = $this->offsetGet($slotName)->getDefaultIndex();
        if (!is_null($slotDefault) && is_null($default)) {
            $default = $slotDefault;
        }

        // If default has not be set in the slot or the method, use 0.
        if (is_null($default)) {
            $default = 0;
        }

        // If no nested slots, return the card as is.
        if (0 === count($nested = $this->offsetGet($slotName)->getNested())) {
            return $this->offsetGet($slotName)->getCard($this->resolveIndex($slotName, $default));
        }

        // Resolve Nested Slots
        $nestedCards = array();

        // Get the cards of the nested slots
        foreach ($nested as $nestedSlotName) {
            /** @var Slot $nestedSlot */
            $nestedSlot = $this->offsetGet($nestedSlotName);
            $nestedCards[$nestedSlotName] = $nestedSlot->getCard(
                $this->resolveIndex($nestedSlotName, $nestedSlot->getDefaultIndex())
            );
        }

        // Translate the placeholders in the parent card.
        return static::interpolate(
            $this->offsetGet($slotName)->getCard($this->resolveIndex($slotName, $default)),
            $nestedCards,
            $this->delimiter
        );
    }

    /**
     * @param string  $slotName
     * @param integer $default
     * @return integer
     */
    protected function resolveIndex($slotName, $default = null)
    {
        if (null === $default) {
            $default = 0;
        }

        $keyWithSetValue = false;
        $slotKeys = $this->offsetGet($slotName)->getKeys();

        // Perform a dry-run to find out if a value has been set, if it hasn't
        // then assign a string. The `has()` method for the Request's `query`
        // property won't work recursively for array parameters.
        foreach ($slotKeys as $key) {
            $dry = $this->request->query->get($key, static::NOT_SET_PARAMETER, true);
            if (static::NOT_SET_PARAMETER !== $dry) {
                $keyWithSetValue = $key;
                break;
            }
        }

        // If a key was not set a value, return the default value of the first key assigned to the slot.
        return $this->request->query->getInt(($keyWithSetValue ?: $slotKeys[0]), $default, true);
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * The number of Slots in the machine
     *
     * @return integer
     */
    public function count()
    {
        $c = 0;
        // Using Pimple::$values will return the Closures, so instead get the
        // values in the container via ArrayAccess.
        foreach ($this->keys() as $valueName) {
            if ($this[$valueName] instanceof Slot) {
                ++$c;
            }
        }
        return $c;
    }

    /**
     * Return all the slots.
     *
     * @return array
     */
    public function all()
    {
        $all = array();

        // Pimple::keys()
        foreach ($this->keys() as $slotName) {
            $all[$slotName] = $this->get($slotName);
        }

        return $all;
    }

    /**
     * Export current values for all slots in JSON format.
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->all());
    }

    /**
     * Export to JSON by treating the object as a string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
