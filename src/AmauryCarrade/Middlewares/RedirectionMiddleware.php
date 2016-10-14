<?php

namespace AmauryCarrade\Middlewares;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


class RedirectionMiddleware
{
    private static $redirection_map = array(
    	// Short URLs
        '/uuid'        => '/minecraft/history',
        '/ping'        => '/minecraft/ping',
        '/don'         => '/donner',
        '/donation'    => '/donner',

        // Old paths
        '/tools/generators/bukkit/permissions.html' => '/bukkit/permissions',

        // Aliases for the chat higlighter
        '/chat_highlight' => '/chat_highlighter',
        '/highlight'      => '/chat_highlighter',
        '/chat'           => '/chat_highlighter',

        // Old URLs with ".html"
        '/contact.html'                    => '/contact',
        '/pgp.html'                        => '/pgp',
        '/donner.html'                     => '/donner',
        '/projects.html'                   => '/projects',
        '/projects/jquery/autoResize.html' => '/projects/jquery/autoResize',
        '/projects/opera/qrcode.html'      => '/projects/opera/qrcode',
        '/upload.html'                     => '/upload',
        '/coffee.html'                     => '/coffee',
        '/bukkit/permissions.html'         => '/bukkit/permissions',
        '/programmation/commencer.html'    => '/programmation/commencer',
        '/articles/mumble.html'            => '/articles/mumble'
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
