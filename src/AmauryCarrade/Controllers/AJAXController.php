<?php

namespace AmauryCarrade\Controllers;

use Requests;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class AJAXController
{
    const SHORTENER = 'https://l.carrade.eu/?url=';

    public function shorten(Application $app, Request $request)
    {
        if (!$request->query->has('url')) abort(400);

        $r = Requests::get(self::SHORTENER . rawurlencode($request->query->get('url')));
        return new Response($r->body, 200, [
            'Content-Type' => 'text/plain'
        ]);
    }
}
