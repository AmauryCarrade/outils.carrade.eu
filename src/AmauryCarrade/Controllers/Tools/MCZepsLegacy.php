<?php

namespace AmauryCarrade\Controllers\Tools;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


class MCZepsLegacy
{
    const ZEPS_DOMAIN = 'https://zeps.carrade.eu';

    public function homepage(Application $app)
    {
        return $app->redirect(self::ZEPS_DOMAIN, 301);
    }

    public function results(Application $app, $from, $to, $options)
    {
        return $app->redirect(self::ZEPS_DOMAIN . '/' . $from . '/' . $to . (!empty($options) ? '/' . $options : ''), 301);
    }
}
