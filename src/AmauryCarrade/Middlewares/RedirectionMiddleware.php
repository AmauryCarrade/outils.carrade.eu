<?php

namespace AmauryCarrade\Middlewares;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


class RedirectionMiddleware
{
    private static $redirection_map = array(

    );


    public function handle(Request $request, Application $app)
    {
        if (array_key_exists($request->getPathInfo(), RedirectionMiddleware::$redirection_map))
        {
            return $app->redirect($request->getBaseUrl() . RedirectionMiddleware::$redirection_map[$request->getPathInfo()], 301);
        }

        return null;
    }
}
