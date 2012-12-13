<?php

require __DIR__ . '/vendor/autoload.php';

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

$page = new Kamereon\Page($data);

$headline    = $page->get('headline');
$body        = $page->get('body');
$description = $page->get('description', 1);

?>

<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="description" content="<?=$description?>">
    <title><?=$headline?> :: Acme</title>
    <style>
    * { padding: 0; margin: 0; }
    body { font-family: "OpenSans", "Helvetica Neue", "Helvetica", Arial, Verdana, sans-serif; }
    h1, p { padding: 20px; }
    h1 { color: white; background-color: #94D135; }
    </style>
</head>
<body>
    <div>
        <h1><?=$headline?></h1>
        <p><?=$body?></p>
    </div>
</body>
</html>
