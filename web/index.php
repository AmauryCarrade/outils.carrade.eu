<?php
require_once __DIR__ . '/../vendor/autoload.php';

session_start();

$app = new Silex\Application();


// Debug mode

if (isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1')))
{
    $app['debug'] = true;
}


// Credentials

$app['credentials'] = array();
if (file_exists(__DIR__ . '/../credentials.php'))
{
    $app['credentials'] = include(__DIR__ . '/../credentials.php');
}


// Registry

//$app->register(new Silex\Provider\SymfonyBridgesServiceProvider());

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path'       => __DIR__ . '/../templates'
));

$app['twig']->getExtension('core')->setTimezone('Europe/Paris');


// Routing

$app
    ->get('/', 'AmauryCarrade\\Controllers\\MainPagesController::homepage')
    ->bind('homepage');

$app
    ->get('/contact.html', 'AmauryCarrade\\Controllers\\MainPagesController::contact')
    ->bind('contact');

$app
    ->get('/pgp.html', 'AmauryCarrade\\Controllers\\MainPagesController::pgp')
    ->bind('pgp');

// Boot

$app->run();
