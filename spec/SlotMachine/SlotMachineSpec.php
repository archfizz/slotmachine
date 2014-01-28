<?php

namespace spec\SlotMachine;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

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
                    'keys' => array( "i", "app_data[i]" ),
                    'reel' => array(
                        'aliases' => array(
                            array(
                                '_default' => 3,
                                '_fallback' => 6,
                                'seal' => 4,
                            ),
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
                    'keys' => array("ih", "app_data[ih]"),
                    'nested' => array( "featured_image" ),
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
                    'keys' => array("fm", "app_data[fm]"),
                    'undefined_card' => 'FALLBACK_CARD',
                    'reel' => array(
                        'aliases' => array( '_fallback' => 3 ),
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
                    'keys' => array( array("fm", "app_data[fm]") ),
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

    function it_is_countable()
    {
        $this->shouldImplement('\Countable');
        $this->count()->shouldReturn(10);
    }
}
