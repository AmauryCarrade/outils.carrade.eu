<?php

function get_db_connector(Silex\Application $app, $connection = 'mcstats')
{
    if (!isset($app['credentials']) || !isset($app['credentials']['sgbd']) || !isset($app['credentials']['sgbd'][$connection]))
        return null;

    $db_credentials = $app['credentials']['sgbd'][$connection];
    return new \PDO('mysql:host=' . $db_credentials['host'] . ';dbname=' . $db_credentials['base'] . ';charset=utf8', $db_credentials['user'], $db_credentials['pass']);
}
