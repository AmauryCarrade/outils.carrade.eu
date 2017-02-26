<?php

namespace AmauryCarrade\Controllers\Tools;

use Silex\Application;


class MCOtherToolsController
{
    public function loot_tables(Application $app)
    {
        return $app['twig']->render('tools/minecraft/loot_tables.html.twig');
    }
}
