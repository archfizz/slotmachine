<?php

/**
 *  Page configuration
 */
return array(
    'options' => array(
        'delimiter' => array('%', '%'),
        'resolve_undefined' => 'DEFAULT_CARD'
    ),
    'slots' => array(
        'headline' => array(
            'key' => 'h',
            'cards' => array(
                'Good to be back in %location%.',
                'I\'ve always wanted to go into %location%, man.',
                'This is %location%, where I belong.',
                '%location% is {really} nice this time of year'
            ),
            'nested_with' => array(
                'location'
            )
        ),
        'location' => array(
            'key' => 'loc',
            'cards' => array(
                0 => 'London',
                1 => 'New York',
                2 => 'Tokyo',
                3 => 'Paris',
                4 => 'Shanghai',
                5 => 'Amsterdam',
                6 => 'hell',
                7 => 'space'
            )
        )
    )
);
