<?php

namespace AmauryCarrade\Controllers;

use Silex\Application;


class MainPagesController
{
    public function homepage(Application $app)
    {
        return $app['twig']->render('index.html.twig');
    }

    public function contact(Application $app)
    {
        return $app['twig']->render('contact.html.twig');
    }

    public function pgp(Application $app)
    {
        return $app['twig']->render('pgp.html.twig');
    }
}
