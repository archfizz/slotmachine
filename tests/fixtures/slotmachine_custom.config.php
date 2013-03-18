<?php

/**
 *  Page configuration using custom options
 */
return array(
    'options' => array(
        'delimiter' => array('%', '%'),
        'resolve_undefined' => 'DEFAULT_CARD'
    ),
    'reels' => array(
        'headline' => array(
            'cards' => array(
                'Good to be back in %location%.',
                'I\'ve always wanted to go into %location%, man.',
                'This is %location%, where I belong.',
                '%location% is {really} nice this time of year'
            ),
        ),
        'location' => array(
            'cards' => array(
                0 => 'London',
                1 => 'New York',
                2 => 'Tokyo',
                3 => 'Paris',
                4 => 'Shanghai',
                5 => 'Amsterdam',
                6 => 'hell',
                7 => 'space'
            ),
        ),
        'animal_image' => array(
            'cards' => array(
                0 => 'cat.jpg',
                1 => 'dog.jpg',
                2 => 'mouse.jpg',
                3 => 'parrot.jpg',
                4 => 'rabbit.jpg'
            ),
            'aliases' => array(
                '_fallback' => 3
            )
        )
    ),
    'slots' => array(
        'headline' => array(
            'reel' => 'headline',
            'key' => 'h',
            'nested_with' => array(
                'location'
            ),
        ),
        'location' => array(
            'reel' => 'location',
            'key' => 'loc',
        ),
        'animal_image' => array(
            'reel' => 'animal_image',
            'key'  => 'a',
            'resolve_undefined' => 'FALLBACK_CARD'
        )
    ),
);
