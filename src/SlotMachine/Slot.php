<?php

namespace SlotMachine;

/**
 * A slot will hold the reel of cards and retrieve a card from it.
 *
 * @package slotmachine
 * @author Adam Elsodaney <aelso1@gmail.com>
 */
class Slot implements SlotInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $keys;

    /**
     * @var array
     */
    protected $reel;

    /**
     * @var array
     */
    protected $nested = array();

    /**
     * @var array
     */
    protected $aliases = array();

    /**
     * @var integer
     */
    protected $undefinedCardResolution = UndefinedCardResolution::NO_CARD_FOUND_EXCEPTION;

    /**
     * @param array
     */
    public function __construct(array $data)
    {
        $this->name     = $data['name'];
        $this->keys     = $data['keys'];
        $this->reel     = $data['reel'];
        $this->aliases  = array_replace($this->aliases, isset($data['reel']['aliases']) ? $data['reel']['aliases'] : array());
        $this->nested   = array_key_exists('nested', $data) ? $data['nested'] : array();
        $this->undefinedCardResolution = array_key_exists('undefined_card', $data)
            ? $data['undefined_card']
            : UndefinedCardResolution::NO_CARD_FOUND_EXCEPTION;
    }

    public function getUcr()
    {
        return $this->undefinedCardResolution;
    }

    /**
     * Get a value of a card by its index.
     * If the card does not exist, resolve based on the slot's resolve_undefined setting.
     *
     * @param  integer $index
     * @return mixed
     * @throws SlotMachine\Exception\NoCardFoundException if the key does not exist and
     *         the undefinedCardResolution property is set to NO_CARD_FOUND_EXCEPTION.
     */
    public function getCard($index = 0)
    {
        if (!array_key_exists($index, $this->reel['cards'])) {
            switch ($this->undefinedCardResolution) {
                case UndefinedCardResolution::NO_CARD_FOUND_EXCEPTION:
                default:
                    throw new Exception\NoCardFoundException(sprintf(
                        "Card of index %d was not found in the slot `%s`.", $index, $this->name
                    ));

                case UndefinedCardResolution::DEFAULT_CARD:
                    return $this->getDefaultCard();

                case UndefinedCardResolution::FALLBACK_CARD:
                    return $this->getFallbackCard();

                case UndefinedCardResolution::BLANK_CARD:
                    return '';

                // End Switch
            }
        }
        return $this->reel['cards'][$index];
    }

    /**
     * Get a card from the reel by an alias.
     *
     * @param  string $alias
     * @return mixed
     */
    public function getCardByAlias($alias)
    {
        if (!array_key_exists($alias, $this->aliases)) {
            throw new Exception\NoSuchAliasException(sprintf('Alias "%s" has not been assigned to any cards.', $alias));
        }

        return $this->getCard($this->aliases[$alias]);
    }

    /**
     * @return string
     */
    public function getDefaultCard()
    {
        try {
            return $this->getCardByAlias('_default');
        } catch (Exception\NoSuchAliasException $e) {
            return $this->getCard();
        }
    }

    /**
     * @return string
     */
    public function getFallbackCard()
    {
        try {
            return $this->getCardByAlias('_fallback');
        } catch (Exception\NoSuchAliasException $e) {
            return $this->getCard();
        }
    }

    /**
     * @return integer|null
     */
    public function getDefaultIndex()
    {
        return array_key_exists('_default', $this->aliases) ? $this->aliases['_default'] : null;
    }

    /**
     * @return integer|null
     */
    public function getFallbackIndex()
    {
        return array_key_exists('_fallback', $this->aliases) ? $this->aliases['_fallback'] : null;
    }

    /**
     * @return integer
     */
    public function getResolvableIndex()
    {
        if (array_key_exists('_default', $this->aliases)) {
            return $this->aliases['_default'];
        } elseif (array_key_exists('_fallback', $this->aliases)) {
            return $this->aliases['_fallback'];
        }

        return 0;
    }

    /**
     * @return array
     */
    public function getNested()
    {
        return $this->nested;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->keys[0];
    }

    /**
     * @return array
     */
    public function getKeys()
    {
        return $this->keys;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getCard();
    }
}
