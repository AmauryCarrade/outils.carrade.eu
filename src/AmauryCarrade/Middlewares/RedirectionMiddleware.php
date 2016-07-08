<?php

namespace AmauryCarrade\Middlewares;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


class RedirectionMiddleware
{
    private static $redirection_map = array(
        '/uuid' => '/minecraft/history'
    );


    public function handle(Request $request, Application $app)
    {
        if (array_key_exists($request->getPathInfo(), self::$redirection_map))
        {
            return $app->redirect($request->getBaseUrl() . self::$redirection_map[$request->getPathInfo()], 301);
        }

        return null;
    }
}
