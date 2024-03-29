SlotMachine - An A-B page testing library
===========

Each 'slot' on a page can have its content changed by query parameters, allowing
for any possible number of permutations of content on the same page.

This is especially useful for marketing and creating landing pages for A-B testing
or for prototyping a design of a webpage without cluttering your templates with a
bunch of if-statements.

For example, visiting `exmaple.com/page?h=1&b=1` and `exmaple.com/page?h=2&b=2` will give
you a page with a different headline and body if we were to assign the following:

```yaml
headline:
    1: Book your test drive
    2: Test drive our new vehicle
    
body:
    1: Fill in this quick form and one of our representatives will call you back
    2: Request a callback for a 48 hour test drive today 
```

Works with PHP 5.3 through PHP 8 and HHVM.

Version 1.0 is fully released, build on top of Pimple 1.0 and Symfony HttpFoundation 2.8.

Version 2.0 provides compatibility with PHP 7+, Symfony HttpFoundation 3+ and Pimple 2+,
but still works with older versions of PHP and these packages.


![Build Status](https://github.com/archfizz/slotmachine/actions/workflows/php.yml/badge.svg?branch=master)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/archfizz/slotmachine/badges/quality-score.png?s=46d3930204f5f9e70ef31729ba490d572fbc2964)](https://scrutinizer-ci.com/g/archfizz/slotmachine/)

[![Latest Stable Version](https://poser.pugx.org/slotmachine/slotmachine/v/stable.png)](https://packagist.org/packages/slotmachine/slotmachine)
[![Total Downloads](https://poser.pugx.org/slotmachine/slotmachine/downloads.png)](https://packagist.org/packages/slotmachine/slotmachine)
[![Latest Unstable Version](https://poser.pugx.org/slotmachine/slotmachine/v/unstable.png)](https://packagist.org/packages/slotmachine/slotmachine)
[![License](https://poser.pugx.org/slotmachine/slotmachine/license.png)](https://packagist.org/packages/slotmachine/slotmachine)

Since November 2012, SlotMachine has been used on several live web pages with tens of thousands of hits a day, mostly landing pages from Facebook ads.

Concept
-------

For those working in digital marketing, a effective landing page is always
required for an advertisement to lead to. This is more effective if the page
matches the content and design of the ad. However if one has many ads, many
static pages would therefore be required. Sometimes only one feature on the
page such as the headline or the border color of a container would want to be
different to another page.

SlotMachine is a PHP library that allows for one HTML page
to display different variants of content on the fly. These can be defined
beforehand or with a query string. (ie. example.com/landingpage?h=2&c=3)

Think of a bus that changes its blinds (assuming they still use blinds instead of a matrix)
to display different combinations of what route number its running,
what places it passes through and what its destination is.
In SlotMachine, that would be three slots.


Basic Usage
-----------

1. Install with Composer
2. Set up the configuration and data
3. Create your page

```php
<?php

// your-landing-page.php

require __DIR__.'/vendor/autoload.php';

$data = include __DIR__.'/slotmachine.config.php';

$slots = new SlotMachine\SlotMachine($data);

$headline    = $slots->get('headline');
$body        = $slots->get('body');
$description = $slots->get('description');
$image       = $slots->get('image');

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
    <img src="<?=$image?>" alt="Featured Image" />
    <small>© <?php echo date('Y'); ?> My Site</small>
</body>
</html>
```

Installation
----------------------------

First, [Download Composer](http://getcomposer.org/download/) from the command line

    $ cd path/to/your/project
    $ curl -s https://getcomposer.org/installer | php


Then create a `composer.json` file in your project with the most recent and widely compatible version 2

```json
{
    "require": {
        "slotmachine/slotmachine": "~2.0"
    }
}
```

Or to use the older, more stable version (currently v1.0)

```json
{
    "require": {
        "slotmachine/slotmachine": "~1.0"
    }
}
```


Then install with the following command

    $ php composer.phar install

Configuring and Setting Data
----------------------------

All new instances of `SlotMachine\SlotMachine` will take an array for its configuration.
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
            'undefined_card' => 'DEFAULT_CARD'
        ),
    ),
    'slots' => array(
        'headline' => array(
            'reel' => 'headline',
            'keys'  => array('h'),
            'nested' => array(
                'user'
            )
        ),
        'body' => array(
            'reel' => 'body',
            'keys'  => array('c'),
        ),
        'description' => array(
            'reel' => 'description',
            'keys'  => array('c'),
        ),
        'user' => array(
            'reel' => array(
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
            'keys'  => array('uid'),
        ),
    ),
);
```

Usage
-----

### Basic

The values returned from a slot are called __cards__.

To get a card from a single slot, pass the name of the slot to `get()` method

```php
$slots->get('headline');
```

To get a card from every slot in the SlotMachine, use the `all()` method.

```php
$slot = $slots->all();

echo $slot['headline'];
```

To override the default card returned from a slot, pass the card's array key
to the second argument of the `get()` method.

```php
$slots->get('headline', 4);
```

### Assigning multiple HTTP query parameters to a slot

If you would like `example.com/?app_data[i]=1` and `example.com/?i=1` to render the same result,
just assign an array of HTTP query parameters to the `keys` attribute.
This is useful for passing parameters to the Facebook Page Tab, but not having to
use `app_data` each time.

```php
$config = array(
    //...
    'slots' => array(
        //...
        'hero_image' => array(
            'keys' => array('i', 'app_data[i]'),
            'reel' => 'hero_image'
        )
    )
)
```

Dependencies
------------

SlotMachine uses the [Symfony2 HttpFoundation component](http://symfony.com/doc/current/components/http_foundation/introduction.html) to resolve the page based on query string parameters.

The SlotMachine class also extends [Pimple](http://pimple.sensiolabs.org), a lightweight dependency injection container, which the Slots are injected into.

Using YAML files for configuration
----------------------------------

Although not required, it is recommended that you install the Symfony Yaml component.
Just add `"symfony/yaml": "~2.0"` to your `composer.json` file.
This makes reading the configuration easier and give access to more features that YAML files provide.

The following YAML would be used instead of the PHP array above

```yaml
#slots.config.yml

slots:
    headline:
        keys: [ h ]
        reel: headline
        nested: [ 'user' ]

    body:
        keys: [ c ]
        reel: body

    description:
        keys: [ c ]
        reel: description

    user:
        keys: [ 'uid' ]
        reel:
            cards:
                0: 'valued customer'
                1: 'Peter'
                2: 'Lois'
                3: 'Brian'
                4: 'Chris'
                5: 'Meg'
                6: 'Stewie'

reels:
    headline:
        cards:
            - 'Join our free service today.'
            - 'Welcome, valued customer.'
            - 'Welcome back, {user}!'
            - 'Check out our special offers'
    body:
        aliases: { xmas: 2 }
        cards:
            0: 'Time is of the essence, apply now!'
            1: 'Get a discount today'
            2: 'Merry Christmas'


    description:
        undefined_card: DEFAULT_CARD
        aliases: { _default: 9001 }
        cards:
            0:    'Acme Corp. Specialists for anvils.'
            1:    'Special offer only online at Acme.'
            9001: 'Acme has anvils for all occasions.'

```

Integration with Silex
----------------------

Silex is a micro-framework that uses Symfony2 components. Like `SlotMachine`, it also extends Pimple, and uses Symfony HttpFoundation.
Since SlotMachine was initally designed to work with Silex, a service provider is included as part of the the library.

Note that the the `SlotMachineServiceProvider` is removed from the `2.0` version
of SlotMachine since SlotMachine uses Pimple 2.0 and Silex is still using Pimple 1.0.

Below is an example of using SlotMachine in Silex, including Twig for templating.

```php
<?php

require __DIR__.'/vendor/autoload.php';

use SlotMachine\SlotMachineServiceProvider;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\HttpFoundation\Request;

$app = new Application();
$app['debug'] = true;

$app->register(new SlotMachineServiceProvider(), array(
    'slotmachine.config'  => Yaml::parse(file_get_contents(__DIR__.'/config/slots.config.yml')),
    'slotmachine.request' => Request::createFromGlobals()
));

$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/templates';
));

$pageData['slot'] = $app['slotmachine']->all();
$pageData['site_name'] = 'My Site';

$app['page_data'] = $pageData;

// If a Silex app is iframed in Facebook, the request type has to match POST first before matching GET
$app->match('/', function() use ($app) {
    return $app['twig']->render('landing_page.html.twig', $app['page_data']);
})->method('POST|GET');

$app->run();

```

The above would render the following Twig template.

```html
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="description" content="{{ slot.description }}">
    <title>{{ slot.headline }} :: {{ site_name }}</title>
</head>
<body>
    <h1>{{ slot.headline|upper }}</h1>
    <p>{{ slot.body }}</p>
    <small>© {{ "now"|date('Y') }} {{ site_name }}</small>
</body>
</html>

```

Assign all slots to one HTTP query parameter - Working with apps for the Facebook Page Tab
-------------------------------------

#### Incompability with Symfony 3

Unfortunately, this feature will not work for installations of Symfony 3 or installations of HttpFoundation 3.0 and above!
See https://github.com/symfony/symfony/issues/14039

#### Usage

Whilst a normal query string combination is used to resolve cards and render the page uniquely...

    http://example.com/campaign/landingpage?a=1&b=2&c=3

... and given the following configuration that will resolve this query...

```yaml
slots:
    foo:
        keys: [ a ]
        reel: foo
    bar:
        keys: [ b ]
        reel: bar
    baz:
        keys: [ c ]
        reel: baz
```

However, you can assign all of them to one parameter, so the query string would use array keys.
This is required for Facebook apps and landing pages being accessed through the Facebook Page Tab
as Facebook will only whitelist the `app_data` query parameter and ignore others.

    http://mycampaign.facebook.com/landingpage?app_data[a]=1&app_data[b]=2&app_data[c]=3

Here is the following configuration which will resolve this query:

```yaml
slots:
    foo:
        keys: [ "q[a]" ]
        reel: foo # "[]" must be in quotes so not to be confused for a YAML array
    bar:
        keys: [ "q[b]" ]
        reel: bar
    baz:
        keys: [ "q[c]" ]
        reel: baz
```

Run Tests with PHPUnit
----------------------

These commands assumes that PHPUnit and Composer are installed globally on your system.

    $ cd path/to/SlotMachine/
    $ composer install
    $ phpunit

Found a bug? Missing feature?
-----------------------------

Please feel free to create a new issue on the GitHub repository https://github.com/archfizz/slotmachine


