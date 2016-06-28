<?php
use Silex\Application;

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

$loader = new \Twig_Loader_Filesystem();
$loader->addPath(__DIR__ . '/../contents', 'content');

$app['twig']->getLoader()->addLoader($loader);
$app['twig']->getExtension('core')->setTimezone('Europe/Paris');

$app['root_folder'] = __DIR__ . '/..';
$app['contents_folder'] = $app['root_folder'] . '/contents';


// Middlewares

$app->before('AmauryCarrade\\Middlewares\\RedirectionMiddleware::handle', Application::EARLY_EVENT);


// Routing

$app->get('/', 'AmauryCarrade\\Controllers\\MainPagesController::homepage')
    ->bind('homepage');

$app->get('/contact.html', 'AmauryCarrade\\Controllers\\MainPagesController::contact')
    ->bind('contact');

$app->get('/pgp.html', 'AmauryCarrade\\Controllers\\MainPagesController::pgp')
    ->bind('pgp');


$app->get('/projects.html', 'AmauryCarrade\\Controllers\\MainPagesController::list_projects')
    ->bind('projects');

$app->get('/projects/{category}/{name}.html', 'AmauryCarrade\\Controllers\\MainPagesController::show_project')
    ->bind('show_project');


// Boot

$app->run();
