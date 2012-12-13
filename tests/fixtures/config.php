<?php

/**
 *  Page configuration
 */
$kamereon = array(
    'slots' => array(
        'headline' => array(
            'keyBind' => 'h',
            'cards' => array(
                'Join our free service today.',
                'Welcome, valued customer.',
                'Welcome back, {user}!',
                'Check out our special offers'
            ),
            'nestedWith' => array(
                'user'
            )
        ),
        'body' => array(
            'keyBind' => 'c',
            'cards' => array(
                'Time is of the essence, apply now!',
                'Get a discount today'
            )
        ),
        'user' => array(
            'keyBind' => 'uid',
            'cards' => array(
                0 => 'valued customer',
                1 => 'Peter',
                2 => 'Lois',
                3 => 'Brian',
                4 => 'Chris',
                5 => 'Meg',
                6 => 'Stewie'
            )
        )
    )
);
