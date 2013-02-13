SlotMachine
========

A dynamic page content container for PHP 5.3

Each 'slot' on a page can have it's content changed by get parameters, allowing for limitless possible variations of the same page, useful for marketing or prototyping.

[![Build Status](https://travis-ci.org/archfizz/slotmachine.png)](https://travis-ci.org/archfizz/slotmachine)

! Important: This library is still in the very early development stages and will change significantly, hence it's not fully documented yet, so only use if you plan to aid in its development, would like to experiment or would like to have something to use now without concern for updating.

Concept
-------

For those working in digital marketing, a effective landing page is always
required for an advertisement to lead to. This is more effective if the page
matches the content and design of the ad. However if one has many ads, many
static pages would therefore be required. Sometimes only one feature on the
page such as the headline or the border color of a container would want to be
different to another page.

SlotMachine (previously codenamed Kamereon, Japanese for Chameleon) is a PHP library that allows for one HTML page
to display different variants of content on the fly. These can be defined
beforehand or with a query string. (ie. example.com/landingpage?h=2&c=3)

Usage
-----

1. Install with Composer
2. Set up the configuration and data
3. Create your page

```php
<?php

// your-landing-page.php

require 'vendor/autoload.php';
$data = include('slotmachine.config.php');

$page = new SlotMachine\Page($data);

$headline    = $page->get('headline');
$body        = $page->get('body');
$description = $page->get('description');

?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title><?=$headline?> :: My Site</title>
    <meta name="description" content="<?=$description?>">
</head>
<body>
    <h1><?=$headline?></h1>
    <p><?=$body?></p>
</body>
</html>
```

Installation
----------------------------

First, [Download Composer](http://getcomposer.org/download/) from the command line

    $ cd path/to/your/project
    $ curl -s https://getcomposer.org/installer | php


Then create a `composer.json` file in your project with the following

```json
{
    "require": {
        "slotmachine/slotmachine": "dev-master"
    }
}
```

Then install with the following command

    $ php composer.phar install

Configuring and Setting Data
----------------------------

All new instances of `SlotMachine\Page` will take an array for it's configuration.
Below is an example that would be used with the page example above.

```php
<?php
// slotmachine.config.php

return array(
    'reels' => array(
        'headline' => array(
            'cards' => array(
                'Join our free service today.',
                'Welcome, valued customer.',
                'Welcome back, {user}!',
                'Check out our special offers'
            ),
        ),
        'body' => array(
            'cards' => array(
                0 => 'Time is of the essence, apply now!',
                1 => 'Get a discount today',
                2 => 'Merry Christmas'
            ),
            'aliases' => array(
                'xmas' => 2
            )
        ),
        'description' => array(
            'cards' => array(
                0 => 'Acme Corp. Specialists for anvils.',
                1 => 'Special offer only online at Acme.',
                9001 => 'Acme has anvils for all occasions.',
            ),
            'aliases' => array(
                '_default' => 9001
            ),
            'resolve_undefined' => 'DEFAULT_CARD'
        ),
        'user' => array(
            'cards' => array(
                0 => 'valued customer',
                1 => 'Peter',
                2 => 'Lois',
                3 => 'Brian',
                4 => 'Chris',
                5 => 'Meg',
                6 => 'Stewie'
            ),
        ),
    ),
    'slots' => array(
        'headline' => array(
            'reel' => 'headline',
            'key'  => 'h',
            'nested_with' => array(
                'user'
            )
        ),
        'body' => array(
            'reel' => 'body',
            'key'  => 'c',
        ),
        'description' => array(
            'reel' => 'description',
            'key'  => 'c',
        ),
        'user' => array(
            'reel' => 'user'
            'key'  => 'uid',
        )
    )
);
```

Dependencies
------------

SlotMachine uses the [Symfony2 HttpFoundation component](http://symfony.com/doc/current/components/http_foundation/introduction.html) to resolve the page based on query string parameters. 

The SlotMachine Page also extends [Pimple](pimple.sensiolabs.org), a lightweight dependency injection container, which the Slots are injected into.

Using YAML files for configuration
----------------------------------

Although not required, it is recommended that you install the Symfony Yaml component.
Just add `"symfony/yaml": "2.1.*"` to your `composer.json` file.
This makes reading the configuration easier and give access to more features that YAML files provide.

The following YAML would be used instead of the PHP array above

```yaml
#slotmachine.config.yml
reels:
    headline:
        cards:
            - 'Join our free service today.'
            - 'Welcome, valued customer.'
            - 'Welcome back, {user}!'
            - 'Check out our special offers'
    body:
        cards:
            0: 'Time is of the essence, apply now!',
            1: 'Get a discount today',
            2: 'Merry Christmas'
        aliases: { xmas: 2 }

    description:
        cards:
            0: 'Acme Corp. Specialists for anvils.'
            1: 'Special offer only online at Acme.'
            9001: 'Acme has anvils for all occasions.'
        aliases: { _default: 9001 }
        resolve_undefined: DEFAULT_CARD

    user:
        cards:
            0: 'valued customer'
            1: 'Peter'
            2: 'Lois'
            3: 'Brian'
            4: 'Chris'
            5: 'Meg',
            6: 'Stewie'

slots:
    headline:
        reel: headline
        key: h
        nested_with: [ 'user' ]

    body:
        reel: body
        key: c

    description:
        reel: description
        key: c

    user:
        reel: user
        key: uid
```

Run Tests
---------

    $ cd path/to/SlotMachine/
    $ php composer.phar install --dev
    $ phpunit


Found a bug? Missing feature?
-----------------------------

Create a new issue on this GitHub repository.


