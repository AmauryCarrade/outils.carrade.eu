<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

use xPaw\MinecraftFormat;

require_once __DIR__ . '/../vendor/autoload.php';

session_start();
date_default_timezone_set('Europe/Paris');


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

$app['twig']->addFilter(new Twig_SimpleFilter('minecraft_format', function ($string)
{
    return nl2br(MinecraftFormat::parse_minecraft_colors(str_replace(' ', '&nbsp;', htmlspecialchars(trim($string)))));
}, array('is_safe' => array('html'))));

$app['twig']->addFilter(new Twig_SimpleFilter('extract_alphanum', function ($string)
{
    return preg_replace('/[^a-zA-Z0-9_]+/', '', $string);
}));

$app['twig']->addFilter(new Twig_SimpleFilter('remove_minecraft_format', function ($string)
{
    return preg_replace('/§[0-9a-fA-FjJkKlLmMoOrR]/', '', $string);
}));


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

$app->get('/old-home', 'AmauryCarrade\\Controllers\\MainPagesController::homepage')
    ->bind('services');

$app->get('/contact', 'AmauryCarrade\\Controllers\\MainPagesController::contact')
    ->bind('contact');

$app->get('/pgp', 'AmauryCarrade\\Controllers\\MainPagesController::pgp')
    ->bind('pgp');

$app->get('/donner', 'AmauryCarrade\\Controllers\\MainPagesController::don')
    ->bind('don');

$app->get('/legal', 'AmauryCarrade\\Controllers\\MainPagesController::about')
    ->bind('legal');



$app->get('/projects', 'AmauryCarrade\\Controllers\\MainPagesController::list_projects')
    ->bind('projects');

$app->get('/projects/{category}/{name}', 'AmauryCarrade\\Controllers\\MainPagesController::show_project')
    ->bind('show_project');



$app->get('/', 'AmauryCarrade\\Controllers\\MainPagesController::list_services')
    ->bind('homepage');



$app->get('/upload', 'AmauryCarrade\\Controllers\\UploadController::upload_form')
    ->bind('upload');

$app->post('/upload', 'AmauryCarrade\\Controllers\\UploadController::process_upload')
    ->bind('upload_process');


$app->get('/coffee', 'AmauryCarrade\\Controllers\\MainPagesController::coffee')
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


$app->get('/minecraft/loot_tables', 'AmauryCarrade\\Controllers\\Tools\\MCOtherToolsController::loot_tables')
    ->bind('tools.minecraft.loot_tables');


$app->get('/stats', 'AmauryCarrade\\Controllers\\Tools\\MSCServerStats::stats_home')
    ->bind('tools.server_stats.home');

$app->get('/{server_type}/stats/{ips}', 'AmauryCarrade\\Controllers\\Tools\\MSCServerStats::stats')
    ->bind('tools.server_stats');

$app->get('/{server_type}/stats/{ip}/data', 'AmauryCarrade\\Controllers\\Tools\\MSCServerStats::stats_data')
    ->bind('tools.server_stats.data');

$app->get('/minecraft/stats/zcraft.fr/uniques/{begin}..{end}', 'AmauryCarrade\\Controllers\\Tools\\MSCServerStats::zcraft_uniques')
    ->value("begin", "0")
    ->value("end", "2147483647")
    ->bind('tools.server_stats.zcraft_uniques');

$app->get('/tools/{server_type}/stats/{ips}', 'AmauryCarrade\\Controllers\\Tools\\MSCServerStats::stats_legacy');


$app->match('/bukkit/permissions', 'AmauryCarrade\\Controllers\\Tools\\BKPermissionsController::generate_permissions')
    ->method('GET|POST')
    ->bind('tools.bukkit.permissions');


$app->get('/tools/minecraft/zcraft/netherrail/', 'AmauryCarrade\\Controllers\\Tools\\MCZepsLegacy::homepage');
$app->get('/tools/minecraft/zcraft/netherrail/{from}/{to}/{options}', 'AmauryCarrade\\Controllers\\Tools\\MCZepsLegacy::results')
    ->value('options', '');



$app->get('/tea', 'AmauryCarrade\\Controllers\\Tools\\TeaController::homepage')
    ->bind('tools.tea');

$app->get('/tea/{search}/{format}', 'AmauryCarrade\\Controllers\\Tools\\TeaController::search')
    ->value('format', 'html')
    ->bind('tools.tea.results');


$app->get('/redirects', 'AmauryCarrade\\Controllers\\Tools\\RedirectsTracerController::redirects')
    ->bind('tools.redirects');

$app->get('/redirects/{format}', 'AmauryCarrade\\Controllers\\Tools\\RedirectsTracerController::redirects_formats')
    ->value('format', 'html')
    ->bind('tools.redirects.formats');


$app->match('/chat_highlighter', 'AmauryCarrade\\Controllers\\Tools\\HighlighterController::highlight')
    ->method('GET|POST')
    ->bind('tools.highlight');

$app->match('/chat_highlighter/{format}', 'AmauryCarrade\\Controllers\\Tools\\HighlighterController::highlight')
    ->method('POST')
    ->bind('tools.highlight.formats');


$app->get('/steam/{key}/{record_type}/{steam_id}', 'AmauryCarrade\\Controllers\\Tools\\SteamController::activity')
    ->bind('tools.steam');

$app->get('/steam/{key}/{steam_id}', 'AmauryCarrade\\Controllers\\Tools\\SteamController::activity_legacy')
    ->bind('tools.steam.legacy');


$app->get('/ajax/shorten', 'AmauryCarrade\\Controllers\\AJAXController::shorten')
    ->bind('ajax.shorten');


$app->get('/{type}/{name}', 'AmauryCarrade\\Controllers\\ContentsController::show_content')
    ->bind('show_content');


// Boot

$app->run();
