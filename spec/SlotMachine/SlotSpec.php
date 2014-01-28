<?php

namespace spec\SlotMachine;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SlotSpec extends ObjectBehavior
{
    function let()
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
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('SlotMachine\Slot');
    }

    function it_returns_the_card_with_index_of_zero()
    {
        $this->getCard()->shouldBe('London');
    }
}
