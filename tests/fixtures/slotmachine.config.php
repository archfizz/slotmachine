<?php

/**
 *  Page configuration
 */
return array(
    'slots' => array(
        'headline' => array(
            'key' => 'h',
            'cards' => array(
                'Join our free service today.',
                'Welcome, valued customer.',
                'Welcome back, {user}!',
                'Check out our special offers'
            ),
            'nested_with' => array(
                'user'
            )
        ),
        'body' => array(
            'key' => 'c',
            'cards' => array(
                'Time is of the essence, apply now!',
                'Get a discount today'
            )
        ),
        'user' => array(
            'key' => 'uid',
            'cards' => array(
                0 => 'valued customer',
                1 => 'Peter',
                2 => 'Lois',
                3 => 'Brian',
                4 => 'Chris',
                5 => 'Meg',
                6 => 'Stewie'
            )
        ),
        'hero_image' => array(
            'key' => 'i',
            'cards' => array(
                0 => 'hero-standard.png',
                1 => 'hero-one.png',
                2 => 'hero-two.png',
                3 => 'hero-three.png',
                4 => 'hero-winter.png',
                5 => 'hero-summer.png'
            ),
            'aliases' => array(
                'winter'    => 4,
                'summer'    => 5,
                '_fallback' => 5,
                '_default'  => 2
            ),
            'resolve_undefined' => 'FALLBACK_CARD'
        ),
        'button_label' => array(
            'key' => 'bl',
            'cards' => array(
                0 => 'Apply Now!',
                1 => 'Apply Today',
                2 => 'Sign Up',
                3 => 'Click Me!'
            ),
            'resolve_undefined' => 'DEFAULT_CARD'
        )
    )
);
