Kamereon
========

Dynamic Page Content Production for PHP 5.3


Concept
-------

For those working in digital marketing, a effective landing page is always
required for an advertisement to lead to. This is more effective if the page
matches the content and design of the ad. However if one has many ads, many
static pages would therefore be required. Sometimes only one feature on the
page such as the headline or the border color of a container would want to be
different to another page.

Kamereon (Japanese for Chameleon) is a PHP library that allows for one HTML page
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
require 'kamereon.config.php';

$page = new Kamereon\Page($data);

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

First, (http://getcomposer.org/download/)[Download Composer]

    $ cd path/to/your/project
    $ curl -s https://getcomposer.org/installer | php
    $ curl -s https://getcomposer.org/installer | php


Create or add to your `composer.json` file in your project

```json
{
    "require": {
        "archfizz/kamereon": "*"
    }
}
```

Then install

    $ php composer.phar install

Configuring and Setting Data
----------------------------

All new instances of Kamereon\Page should receive an array as an argument.
Below is an example that would be used with the page example above.

```php
<?php
// kamereon.config.php

$data = array(
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
        'description' => array(
            'keyBind' => 'c',
            'cards' => array(
                0 => 'Acme Corp. Specialists for anvils.',
                1 => 'Special offer only online at Acme.',
                9001 => 'Acme has anvils for all occasions.',
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
```

Dependencies
------------

Kamereon uses the Symfony2 HttpFoundation component.


Run Tests
---------

    $ cd path/to/Kamereon/
    $ php composer.phar install --dev
    $ phpunit


Found a bug? Missing feature?
-----------------------------

Create a new issue on this GitHub repository.


