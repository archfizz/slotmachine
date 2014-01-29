<?php

namespace spec\SlotMachine;

use PhpSpec\ObjectBehavior;
use PhpSpec\Exception\Example\PendingException;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class SlotMachineSpec extends ObjectBehavior
{
    function let()
    {
        $config = array(
            'slots' => array(
                'headline' => array(
                    'nested' => array(
                        "user"
                    ),
                    'keys' => array(
                        'h',
                        'app_data[h]',
                    ),
                    'reel' => array(
                        'cards' => array(
                            0 => "Howdy, stranger. Please take a moment to register.",
                            1 => "Register today for your free gift.",
                            2 => "Sign up now to begin your free download.",
                            3 => "Welcome back, {user}!",
                            4 => "See you again, {user}!",
                        ),
                    ),
                ),
                'facebook_page' => array(
                    'keys' => array(
                        "app_data[fb]"
                    ),
                    'reel' => array(
                        'cards' => array(
                            0 => "company_page",
                            1 => "product_page",
                            2 => "promotional_page",
                        ),
                    ),
                ),
                'city' => array(
                    'keys' => array(
                        "c"
                    ),
                    'reel' => 'city',
                ),
                'city_l10n' => array(
                    'keys' => array(
                        "c"
                    ),
                    'reel' => 'city_l10n',
                ),
                'user' => array(
                    'keys' => array(
                        "uid",
                        "app_data[uid]"
                    ),
                    'reel' => 'user',
                ),
                'featured_image' => array(
                    'keys' => array(
                        "i",
                        "app_data[i]"
                    ),
                    'reel' => array(
                        'aliases' => array(
                            '_default' => 3,
                            '_fallback' => 6,
                            'seal' => 4,
                        ),
                        'cards' => array(
                            0 => "dog.png",
                            1 => "cat.png",
                            2 => "parrot.png",
                            3 => "penguin.png",
                            4 => "seal.png",
                            5 => "elephant.png",
                            6 => "tiger.png",
                        ),
                    ),
                ),
                'featured_image_html' => array(
                    'keys' => array(
                        "ih",
                        "app_data[ih]"
                    ),
                    'nested' => array(
                        "featured_image"
                    ),
                    'reel' => array(
                        'aliases' => array(
                            '_default' => 99
                        ),
                        'cards' => array(
                            0 => '<img src="{featured_image}" />',
                            99 => '<img src="{featured_image}" alt="Featured Image" />',
                        ),
                    ),
                ),
                'music_genre' => array(
                    'keys' => array(
                        "fm",
                        "app_data[fm]"
                    ),
                    'undefined_card' => 'FALLBACK_CARD',
                    'reel' => array(
                        'aliases' => array(
                            '_fallback' => 3
                        ),
                        'cards' => array(
                            0 => 'Pop',
                            1 => 'Jazz',
                            2 => 'House',
                            3 => 'Dubstep',
                            4 => 'Garage',
                            5 => 'Grime',
                            6 => 'Trap',
                            7 => 'Drum and Bass',
                            8 => 'Jungle',
                            9 => 'Broken Beat',
                            10 => 'Hardstyle',
                            11 => 'Hardcore',
                            12 => 'Progressive',
                        ),
                    ),
                ),
                'music_genre_required' => array(
                    'keys' => array("fm", "app_data[fm]"),
                    'undefined_card' => 'NO_CARD_FOUND_EXCEPTION',
                    'reel' => array(
                        'cards' => array(
                            0 => 'Pop',
                            1 => 'Jazz',
                            2 => 'House',
                            3 => 'Dubstep',
                            4 => 'Garage',
                            5 => 'Grime',
                            6 => 'Trap',
                            7 => 'Drum and Bass',
                            8 => 'Jungle',
                            9 => 'Broken Beat',
                            10 => 'Hardstyle',
                            11 => 'Hardcore',
                            12 => 'Progressive',
                        ),
                    ),
                ),
                'music_genre_optional' => array(
                    'keys' => array(
                        "fm",
                        "app_data[fm]"
                    ),
                    'undefined_card' => 'BLANK_CARD',
                    'reel' => array(
                        'cards' => array(
                            0 => 'Pop',
                            1 => 'Jazz',
                            2 => 'House',
                            3 => 'Dubstep',
                            4 => 'Garage',
                            5 => 'Grime',
                            6 => 'Trap',
                            7 => 'Drum and Bass',
                            8 => 'Jungle',
                            9 => 'Broken Beat',
                            10 => 'Hardstyle',
                            11 => 'Hardcore',
                            12 => 'Progressive',
                        ),
                    ),
                ),
            ),
            'reels' => array(
                'city' => array(
                    'cards' => array(
                        0 =>  "London",
                        1 =>  "Shanghai",
                        2 =>  "Sao Paulo",
                        3 =>  "Tokyo",
                        4 =>  "Cairo",
                        5 =>  "New York",
                        6 =>  "Bangkok",
                        7 =>  "Malaga",
                        8 =>  "Moscow",
                        9 =>  "Cologne",
                        10 => "Venice",
                    ),
                ),
                'city_l10n' => array(
                    'cards' => array(
                        0 =>  "London",
                        1 =>  "上海", # shang hai
                        2 =>  "São Paulo",
                        3 =>  "東京", # to kyo
                        4 =>  "القاهرة", # el-qahirah - Text show be RTL
                        5 =>  "New York",
                        6 =>  "กรุงเทพมหานคร", # krung thep maha nakhon
                        7 =>  "Málaga",
                        8 =>  "Москва", # Moskva
                        9 =>  "Köln",
                        10 => "Venezia",
                    ),
                ),
                'user' => array(
                    'cards' => array(
                        0 => 'Guest',
                        1 => 'Brian',
                        2 => 'Chris',
                        3 => 'Stewie',
                        4 => 'Meg',
                        5 => 'Peter',
                        6 => 'Lois',
                        7 => 'Stan',
                        8 => 'Francine',
                        9 => 'Steve',
                        10 => 'Hayley',
                        11 => 'Claus',
                    ),
                ),
            ),
        );

        $this->beConstructedWith($config);
    }


    function it_is_initializable()
    {
        // Test by calling getCard directly on the Slot manufactured in the container.
        // This way we know that it has been setup.
        $this['headline']->getCard()->shouldBe('Howdy, stranger. Please take a moment to register.');
    }

    function it_is_countable()
    {
        $this->shouldImplement('\Countable');
        $this->count()->shouldReturn(10);
    }

    function it_retrieves_the_default_card_from_a_specified_slot()
    {
        $this->get('headline')->shouldBe('Howdy, stranger. Please take a moment to register.');
    }

    function it_retrieves_a_custom_default_card_from_a_specified_slot()
    {
        $this->get('featured_image')->shouldBe('penguin.png');
    }

    function it_retrieves_the_default_cards_from_all_the_slots()
    {
        $this->all()->shouldHaveCount(10);
        $this->all()->shouldHaveSlotCard('featured_image', 'penguin.png');
        $this->all()->shouldHaveSlotCard('headline', 'Howdy, stranger. Please take a moment to register.');
    }

    function it_encodes_to_json_format_when_casting_to_string()
    {
        $this->__toString()->shouldHaveJsonPropertyAndValue('featured_image', 'penguin.png');
    }

    function it_resolves_each_slots_card_by_using_the_values_of_http_get_parameters_from_query_string()
    {
        throw new PendingException(<<<'EOL'
Test using
    new SlotMachine($config, Request::create('?h=2', 'GET'));

EOL
);
    }

    function it_resolves_each_slots_card_by_using_the_values_of_http_get_parameters_from_array_data()
    {
        throw new PendingException(<<<'EOL'
Test using
    new SlotMachine($config, Request::create('/', 'GET', array('h' => '2')));

EOL
);
    }

    function it_retrieves_a_specific_card_from_a_specific_slot_overriding_the_http_get_parameters()
    {
        $this->get('headline', 2)->shouldBe('Sign up now to begin your free download.');
    }

    function it_retrieves_a_specific_card_from_a_specific_slot_overriding_the_http_get_parameters_and_irrespective_of_a_custom_default_index()
    {
        $this->get('featured_image', 2)->shouldBe('parrot.png');
    }

    function it_resolves_a_slots_card_to_the_default_card_if_the_requested_index_is_undefined()
    {
        $this->get('featured_image', 9001)->shouldBe('penguin.png');
    }

    function it_resolves_a_slots_card_to_the_fallback_card_if_the_requested_index_is_undefined()
    {
        $this->get('music_genre', 9001)->shouldBe('Dubstep');
    }

    function it_can_resolve_a_slots_card_with_array_http_get_parameters()
    {
        throw new PendingException();
    }

    function it_can_resolve_a_slots_card_with_multiple_http_get_parameters_bound_to_one_slot()
    {
        throw new PendingException();
    }

    function it_can_have_the_name_of_a_reel_containing_cards_assigned_to_a_slot()
    {
        $this->get('city')->shouldBe('London');
    }

    function it_can_handle_utf8_strings()
    {
        $this->get('city_l10n', 6)->shouldBe('กรุงเทพมหานคร');
    }

    function it_can_resolve_nested_slots_and_have_the_card_value_be_interpolated_into_the_parent_card()
    {
        throw new PendingException();

        // Request::create('?uid=7&h=3')
        // $this->get('headline')->shouldBe('Welcome back, Stan!');
    }

    function it_can_resolve_slots_with_nested_slots_with_array_http_get_parameters()
    {
        throw new PendingException();

        // Request::create('?app_data[uid]=6&app_data[h]=4'));
        // $this->get('headline')->shouldBe('See you again, Lois!');
    }

    function it_can_resolve_slots_with_nested_slots_that_have_custom_defaults()
    {
        throw new PendingException();

        // Request::create('?app_data[uid]=6&app_data[h]=4'));
        // $this->get('headline')->shouldBe('See you again, Lois!');
    }

    function it_returns_the_initialized_configuration()
    {
        $this->getConfig()->shouldHaveKey('slots');
    }

    function it_returns_the_request_instance()
    {
        $this->getRequest()->shouldHaveType('\Symfony\Component\HttpFoundation\Request');
    }

    function it_can_be_injected_with_a_request_after_initialization()
    {
        throw new PendingException();
    }

    function it_interpolates_placeholders_in_strings_delimited_by_curly_braces()
    {
        $this->interpolate(
            'I used to {verb} {preposition} {noun}, but then I took an arrow to the knee.',
            array(
                'verb'        => 'be',
                'preposition' => 'an',
                'noun'        => 'adventurer'
            )
        )
        ->shouldBe('I used to be an adventurer, but then I took an arrow to the knee.');
    }

    function it_interpolates_placeholders_in_strings_using_a_custom_pair_of_delimiters()
    {
        $this->interpolate(
            'I used to %verb% %preposition% %noun%, but then I took an arrow to the knee.',
            array(
                'verb'        => 'listen',
                'preposition' => 'to',
                'noun'        => 'dubstep'
            ),
            array(
                '%',
                '%'
            )
        )
        ->shouldBe('I used to listen to dubstep, but then I took an arrow to the knee.');
    }

    function it_throws_exception_if_not_enough_delimiters_are_provided_for_interpolation()
    {
        $this
            ->shouldThrow('\LengthException')
            ->during('interpolate',
                array(
                    'Yo <target>, I\'m real happy for you, Imma let you finish, but <subject> is one of the best <product> of all time!',
                    array(
                        'target'  => 'Zend',
                        'subject' => 'Symfony',
                        'product' => 'PHP frameworks'
                    ),
                    array(
                        '<'
                    )
                )
            )
        ;
    }

    public function it_emits_a_warning_when_more_than_two_delimiters_are_provided_for_interpolation()
    {
        $this
            ->shouldThrow('\PhpSpec\Exception\Example\ErrorException')
            ->during('interpolate',
                array(
                    '"<quote>", said no one ever!',
                    array(
                        'quote' => 'PHP is a solid language',
                    ),
                    array(
                        '<',
                        '>',
                        '*'
                    )
                )
            )
        ;
    }

    public function it_interpolates_while_emitting_a_warning_when_more_than_two_delimiters_are_provided()
    {
        @$this->interpolate(
            '"<quote>", said no one ever!',
            array(
                'quote' => 'I won\'t stay longer than 4 hours in Starbucks for I need to be elsewhere',
            ),
            array(
                '<',
                '>',
                '*'
            )
        )
        ->shouldBe('"I won\'t stay longer than 4 hours in Starbucks for I need to be elsewhere", said no one ever!');
    }

    public function getMatchers()
    {
        return array(
            'haveSlotCard' => function ($subject, $slotName, $resolvedCard) {
                return array_key_exists($slotName, $subject) && $subject[$slotName] === $resolvedCard;
            },
            'haveJsonPropertyAndValue' => function ($subject, $jsonProperty, $jsonValue) {
                $slots = json_decode($subject);
                return property_exists($slots, $jsonProperty) && $slots->{$jsonProperty} === $jsonValue;
            }
        );
    }
}
