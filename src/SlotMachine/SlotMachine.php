<?php

namespace SlotMachine;

use Symfony\Component\HttpFoundation\Request;

/**
 * Dynamic page content container.
 *
 * @package slotmachine
 * @author Adam Elsodaney <adam@archfizz.co.uk>
 */
class SlotMachine extends \Pimple implements \Countable
{
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

    const NOT_SET_PARAMETER = "not_set";

    /**
     * @param array         $config   The SlotMachine configuration data
     * @param Request|null  $request  The Request object
     */
    public function __construct(array $config = array(), Request $request = null)
    {
        parent::__construct();

        $machine = $this;

        $this->config = $config;
        $this->request = !is_null($request) ? $request : Request::createFromGlobals();

        $this->initialize();
    }

    /**
     * Set up the SlotMachine in a ready to use state
     */
    private function initialize()
    {
        $machine = $this;

        foreach ($this->config['slots'] as $slotName => &$slotData) {
            $slotData['name'] = $slotName;

            $this[$slotName] = $this->share(function ($machine) use ($slotData) {
                return new Slot($slotData);
            });
        }
    }

    /**
     * @param string  $slot
     * @param integer $default
     * @return string
     */
    public function get($slot, $default = 0)
    {
        $keyWithSetValue = false;
        $slotKeys = $this[$slot]->getKeys();

        // Perform a dry-run to find out if a value has been set, if it hasn't then assign a string.
        // The `has()` method for the Request's `query` property won't work recursively for array parameters.
        foreach ($slotKeys as $key) {
            $dry = $this->request->query->get($key, static::NOT_SET_PARAMETER, true);
            if (static::NOT_SET_PARAMETER !== $dry) {
                $keyWithSetValue = $key;
                break;
            }
        }

        // If a key was not set a value, get the default value of the first key assigned to the slot.
        $index = $this->request->query->getInt(($keyWithSetValue ?: $slotKeys[0]), $default, true);

        return $this[$slot]->getCard($index);
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
        // Using Pimple::$values will return the Closures, so instead get the
        // values in the container via ArrayAccess.
        foreach ($this->keys() as $valueName) {
            static $count;
            if ($this[$valueName] instanceof Slot) {
                ++$count;
            }
        }
        return $count;
    }
}
