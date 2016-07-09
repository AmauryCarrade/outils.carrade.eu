<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

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
$app['web_folder'] = __DIR__;
$app['contents_folder'] = $app['root_folder'] . '/contents';


// Middlewares

$app->before('AmauryCarrade\\Middlewares\\RedirectionMiddleware::handle', Application::EARLY_EVENT);

if (!$app['debug'])
{
    $app->error(function (\Exception $e, Request $request, $code) use ($app)
    {
        return (new AmauryCarrade\Controllers\ErrorsController())->handle($e, $app, $request, $code);
    });
}


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


$app->get('/upload.html', 'AmauryCarrade\\Controllers\\UploadController::upload_form')
    ->bind('upload');

$app->post('/upload.html', 'AmauryCarrade\\Controllers\\UploadController::process_upload')
    ->bind('upload_process');


$app->get('/coffee.html', 'AmauryCarrade\\Controllers\\MainPagesController::coffee')
    ->bind('coffee');


$app->get('/minecraft/history', 'AmauryCarrade\\Controllers\\Tools\\MCHistoryController::history_home')
    ->bind('tools.minecraft.history');

$app->get('/minecraft/history/{identifier}/{format}', 'AmauryCarrade\\Controllers\\Tools\\MCHistoryController::history')
    ->value('format', 'html')
    ->bind('tools.minecraft.history.results');

$app->get('/tools/minecraft/history/{format}', 'AmauryCarrade\\Controllers\\Tools\\MCHistoryController::history_legacy')
    ->value('format', 'html');


$app->get('/minecraft/ping', 'AmauryCarrade\\Controllers\\Tools\\MCPingController::ping_home')
    ->bind('tools.minecraft.ping');

$app->get('/minecraft/ping/{ip}/{format}', 'AmauryCarrade\\Controllers\\Tools\\MCPingController::ping')
    ->value('format', 'html')
    ->bind('tools.minecraft.ping.results');

$app->get('/tools/minecraft/ping/{format}', 'AmauryCarrade\\Controllers\\Tools\\MCPingController::ping_legacy')
    ->value('format', 'html');


$app->get('/{type}/{name}.html', 'AmauryCarrade\\Controllers\\ContentsController::show_content')
    ->bind('show_content');


// Boot

$app->run();
