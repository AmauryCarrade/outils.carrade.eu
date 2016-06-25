<?php

namespace AmauryCarrade\Controllers;

use Silex\Application;


class HomeController
{
    public function homepage(Application $app)
    {
        return $app['twig']->render('index.html.twig');
    }
}
