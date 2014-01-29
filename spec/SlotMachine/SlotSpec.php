<?php

namespace spec\SlotMachine;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SlotSpec extends ObjectBehavior
{
    function it_is_initializable_with_an_array_configuration()
    {
        $this->beConstructedWith(
            array(
                'name' => 'city',
                'keys' => array(
                    'h'
                ),
                'reel' => array(
                    'cards' => array(
                        0 => 'London',
                        1 => 'Paris',
                        2 => 'Madrid'
                    )
                ),
            )
        );

        $this->shouldHaveType('SlotMachine\Slot');
    }

    function it_retrieves_the_card_with_an_index_of_zero_by_default()
    {
        $this->beConstructedWith(
            array(
                'name' => 'city',
                'keys' => array(
                    'h'
                ),
                'reel' => array(
                    'cards' => array(
                        0 => 'London',
                        1 => 'Paris',
                        2 => 'Madrid'
                    )
                ),
            )
        );

        $this->getCard()->shouldBe('London');
    }

    function it_retrieves_a_card_with_a_specified_index_number()
    {
        $this->beConstructedWith(
            array(
                'name' => 'city',
                'keys' => array(
                    'h'
                ),
                'reel' => array(
                    'cards' => array(
                        0 => 'London',
                        1 => 'Paris',
                        2 => 'Madrid'
                    )
                ),
            )
        );

        $this->getCard(2)->shouldBe('Madrid');
    }

    function it_casts_to_a_string()
    {
        $this->beConstructedWith(
            array(
                'name' => 'city',
                'keys' => array(
                    'h'
                ),
                'reel' => array(
                    'cards' => array(
                        0 => 'London',
                        1 => 'Paris',
                        2 => 'Madrid'
                    )
                ),
            )
        );

        $this->__toString()->shouldBe('London');
    }

    function it_retrieves_a_card_by_an_assigned_alias()
    {
        $this->beConstructedWith(
            array(
                'name' => 'months',
                'keys' => array(
                    'm'
                ),
                'reel' => array(
                    'aliases' => array(
                        '_default'  => 4,  // May
                        '_fallback' => 8,  // September
                        'xmas'      => 11, // December
                    ),
                    'cards' => array(
                        0 => 'January',
                        1 => 'February',
                        2 => 'March',
                        3 => 'April',
                        4 => 'May',
                        5 => 'June',
                        6 => 'July',
                        7 => 'August',
                        8 => 'September',
                        9 => 'October',
                        10 => 'November',
                        11 => 'December'
                    )
                ),
            )
        );

        $this->getCardByAlias('xmas')->shouldBe('December');
    }

    function it_can_be_configured_to_have_a_default_card_of_any_index_number()
    {
        $this->beConstructedWith(
            array(
                'name' => 'months',
                'keys' => array(
                    'm'
                ),
                'reel' => array(
                    'aliases' => array(
                        '_default'  => 4,  // May
                        '_fallback' => 8,  // September
                        'xmas'      => 11, // December
                    ),
                    'cards' => array(
                        0 => 'January',
                        1 => 'February',
                        2 => 'March',
                        3 => 'April',
                        4 => 'May',
                        5 => 'June',
                        6 => 'July',
                        7 => 'August',
                        8 => 'September',
                        9 => 'October',
                        10 => 'November',
                        11 => 'December'
                    )
                ),
            )
        );

        $this->getDefaultIndex()->shouldBe(4);
        $this->getDefaultCard()->shouldBe('May');
    }

    function it_retrieves_a_fallback_card_if_configured()
    {
        $this->beConstructedWith(
            array(
                'name' => 'months',
                'keys' => array(
                    'm'
                ),
                'reel' => array(
                    'aliases' => array(
                        '_default'  => 4,  // May
                        '_fallback' => 8,  // September
                        'xmas'      => 11, // December
                    ),
                    'cards' => array(
                        0 => 'January',
                        1 => 'February',
                        2 => 'March',
                        3 => 'April',
                        4 => 'May',
                        5 => 'June',
                        6 => 'July',
                        7 => 'August',
                        8 => 'September',
                        9 => 'October',
                        10 => 'November',
                        11 => 'December'
                    )
                ),
            )
        );

        $this->getFallbackCard()->shouldBe('September');
    }

    function it_can_be_bound_to_multiple_http_get_keys()
    {
        $this->beConstructedWith(
            array(
                'name' => 'towns',
                'keys' => array(
                    't', 'town', 'app_data[t]'
                ),
                'reel' => array(
                    'cards' => array(
                        0 => 'London',
                        1 => 'Cheltenham',
                        2 => 'Jersey',
                        3 => 'Bristol'
                    )
                ),
            )
        );

        $this->getKeys()->shouldReturn(array('t', 'town', 'app_data[t]'));
    }

    function it_retrieves_a_single_bound_http_get_key_if_it_was_the_first_one_specified()
    {
        $this->beConstructedWith(
            array(
                'name' => 'towns',
                'keys' => array(
                    't', 'town', 'app_data[t]'
                ),
                'reel' => array(
                    'cards' => array(
                        0 => 'London',
                        1 => 'Cheltenham',
                        2 => 'Jersey',
                        3 => 'Bristol'
                    )
                ),
            )
        );

        $this->getKey()->shouldBe('t');
    }

    function it_can_be_configured_to_specify_the_names_of_nested_slots_in_card_values_that_will_be_interpolated()
    {
        $this->beConstructedWith(
            array(
                'name' => 'message',
                'keys' => array(
                    'm',
                ),
                'reel' => array(
                    'cards' => array(
                        0 => 'Welcome to {town}',
                        1 => 'Hope you enjoy your stay in {town}',
                        2 => 'Have you ever visited {town} before?'
                    )
                ),
                'nested' => array(
                    'town'
                ),
            )
        );

        $this->getNested()->shouldReturn(array('town'));
    }
}
