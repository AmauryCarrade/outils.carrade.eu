<?php

namespace AmauryCarrade\Middlewares;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


class RedirectionMiddleware
{
    private static $redirection_map = array(
        '/uuid'        => '/minecraft/history',
        '/ping'        => '/minecraft/ping',
        '/don'         => '/donner.html',
        '/donation'    => '/donner.html',
        '/tools/generators/bukkit/permissions.html' => '/bukkit/permissions.html'
    );


    public function handle(Request $request, Application $app)
    {
        if (array_key_exists($request->getPathInfo(), self::$redirection_map))
        {
            $query_string = $request->getQueryString();
            return $app->redirect($request->getBaseUrl() . self::$redirection_map[$request->getPathInfo()] . ($query_string != null ? '?' . $query_string : ''), 301);
        }

        return null;
    }
}
