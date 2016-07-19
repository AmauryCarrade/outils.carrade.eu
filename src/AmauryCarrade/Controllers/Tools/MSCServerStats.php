<?php

namespace AmauryCarrade\Controllers\Tools;

use PDO;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;


class MSCServerStats
{
    public function stats_home(Application $app, Request $request)
    {
        // Database connection
        try {
            $pdo = get_db_connector($app, 'mcstats');

            if ($pdo == null) throw new \RuntimeException;
        }
        catch(\Exception $e)
        {
            $app->abort(500, "Unable to connect the database.");
        }

        $servers_minecraft = array();
        $servers_mumble = array();

        $query = $pdo->prepare('SELECT server_ip AS ip,
                                       server_name AS name,
                                       server_color AS color,
                                       LOWER(server_type) AS server_type,
                                       UNIX_TIMESTAMP(last_ping) AS last_ping,
                                       UNIX_TIMESTAMP(last_successful_ping) AS last_successful_ping
                                FROM servers
                                WHERE hidden = 0
                                ORDER BY server_name');
        $query->execute();

        while($server = $query->fetch(PDO::FETCH_ASSOC))
        {
            switch ($server['server_type'])
            {
                case 'minecraft':
                    $servers_minecraft[] = $server;
                    break;

                case 'mumble':
                    $servers_mumble[] = $server;
                    break;
            }
        }

        $query->closeCursor();

        return new Response($app['twig']->render('tools/stats/stats_home.html.twig', array(
            'servers_minecraft' => $servers_minecraft,
            'servers_mumble' => $servers_mumble
        )));
    }

    public function stats(Application $app, Request $request, $server_type, $ips)
    {
        if($ips == "")
            $app->abort(404);

        $zcraft = false;

        $server_type = strtolower($server_type);
        $ips = explode(",", strtolower($ips));



        // Special case for zcraft (different data).
        if(in_array('zcraft.fr', $ips) && $server_type == 'minecraft')
        {
            $zcraft = true;
            $ips = array('zcraft.fr');
        }



        // MOTD & favicon
        $motd = "";
        $favicon = "";
        $current_players_count = 0;

        if($server_type == 'minecraft')
        {
            if(count($ips) == 1)
            {
                $motd_request = Request::create(
                    $app['url_generator']->generate(
                        'tools.minecraft.ping.results',
                        array('format' => 'json', 'ip' => $ips[0])
                    ),
                    'GET', array('noquery' => 'yes')
                );

                $motd_response = $app->handle($motd_request, HttpKernelInterface::SUB_REQUEST);
                $motd_data = json_decode($motd_response->getContent(), true);

                if($motd_data["status"] == "ok")
                {
                    $motd = $motd_data["data"]["motd_html"];
                    $favicon = $motd_data["data"]["favicon"];
                    $current_players_count = $motd_data["data"]["online_players"];
                }
            }
            else
            {
                // Currently unsupported. TODO.
                $app->abort(404);
            }
        }



        // Database connection
        try {
            $pdo = get_db_connector($app, 'mcstats');

            if ($pdo == null) throw new \RuntimeException;
        }
        catch(\Exception $e)
        {
            $app->abort(500, "Unable to connect the database.");
        }



        // All servers
        $servers = array();
        $query = $pdo->prepare('SELECT server_ip AS ip, server_name AS name
                                FROM servers
                                WHERE server_type = :type AND hidden = 0
                                ORDER BY server_name');
        $query->execute(array('type' => $server_type));

        while($server = $query->fetch(PDO::FETCH_ASSOC))
        {
            $servers[] = $server;
        }

        $query->closeCursor();



        // Graph data
        $data = array();

        $data['ping_counts'] = 0;

        $data['min'] = array();
        $data['min']['count'] = 9999999;
        $data['max'] = array();
        $data['max']['count'] = -1;

        $query;

        if($zcraft)
        {
            $data['server']['name'] = 'Zcraft';

            // Unique players
            $query = $pdo->prepare("SELECT COUNT(DISTINCT `player_uuid`) AS `unique_players`,
            (
                SELECT COUNT(DISTINCT `player_uuid`)
                FROM `sessions_zcraft`
                WHERE `logout` > (
                    CASE
                        WHEN CURRENT_TIME() > '04:00:00' THEN DATE_ADD(CURRENT_DATE(), INTERVAL 4 HOUR)
                        ELSE DATE_ADD(DATE_SUB(CURRENT_DATE(), INTERVAL 1 DAY), INTERVAL 4 HOUR)
                    END
                )
            ) AS `unique_players_last_day`,
            (
                SELECT COUNT(DISTINCT `player_uuid`)
                FROM `sessions_zcraft`
                WHERE `logout` > DATE_SUB(CURRENT_DATE(), INTERVAL 1 WEEK)
            ) AS `unique_players_last_week`,
            (
                SELECT `login`
                FROM `sessions_zcraft`
                ORDER BY `login` ASC
                LIMIT 1
            ) AS `first_day`
            FROM `sessions_zcraft`;");
            $query->execute();
            $data['uniques'] = $query->fetch(PDO::FETCH_ASSOC);

            $query->closeCursor();


            // Pings
            $query = $pdo->prepare('SELECT UNIX_TIMESTAMP(ping_date) AS time, players_count, guardians_count, admins_count
                                    FROM pings_zcraft');
            $query->execute();
        }
        else
        {
            // Server name & properties?
            $query = $pdo->prepare('SELECT server_name AS name,
                                       server_color AS color,
                                       UNIX_TIMESTAMP(last_ping) AS last_ping,
                                       UNIX_TIMESTAMP(last_successful_ping) AS last_successful_ping
                                FROM servers
                                WHERE server_ip = :server AND server_type = :type');
            $query->execute(array('server' => $ips[0], 'type' => $server_type));

            if($query->rowCount() == 0)
            {
                // No server found: not tracked server.
                $app->abort(404);
            }

            $data['server'] = $query->fetch(PDO::FETCH_ASSOC);
            $query->closeCursor();

            // Data
            $query = $pdo->prepare('SELECT UNIX_TIMESTAMP(ping_date) AS time, players_count
                                FROM pings
                                WHERE server = :server');

            $query->execute(array(
                'server' => $ips[0]
            ));
        }

        while($ping = $query->fetch())
        {
            $data['ping_counts']++;
            // Extrema
            if($ping['players_count'] > $data['max']['count'])
            {
                $data['max']['count'] = $ping['players_count'];
                $data['max']['times'] = array($ping['time']);
                $data['max']['times_count'] = 1;
            }
            else if($ping['players_count'] == $data['max']['count'])
            {
                $data['max']['times'][] = $ping['time'];
                $data['max']['times_count']++;
            }

            if($ping['players_count'] < $data['min']['count'])
            {
                $data['min']['count'] = $ping['players_count'];
                $data['min']['times'] = array($ping['time']);
                $data['min']['times_count'] = 1;
            }
            else if($ping['players_count'] == $data['min']['count'])
            {
                if($data['min']['times_count'] < 15)
                    $data['min']['times'][] = $ping['time'];

                $data['min']['times_count']++;
            }
        }

        $query->closeCursor();



        return new Response($app['twig']->render('tools/stats/stats.html.twig', array(
            'zcraft' => $zcraft,
            'ip' => $ips[0],
            'motd' => $motd,
            'favicon' => $favicon,
            'current_player_count' => $current_players_count,
            'data' => $data,
            'servers' => $servers,
            'server_type' => $server_type
        )));
    }

    public function stats_data(Application $app, $server_type, $ip)
    {
        // Database connection
        try {
            $pdo = get_db_connector($app, 'mcstats');

            if ($pdo == null) throw new \RuntimeException;
        }
        catch(\Exception $e)
        {
            return $app->json(array('error' => 'Unable to connect the database.'), 500);
        }

        $server_type = strtolower($server_type);
        $ip          = strtolower($ip);
        $zcraft      = $server_type == 'minecraft' && $ip == 'zcraft.fr';

        if ($zcraft)
        {
            $query = $pdo->prepare('SELECT UNIX_TIMESTAMP(ping_date) AS time, players_count, guardians_count, admins_count
                                FROM pings_zcraft');
            $query->execute();
        }
        else
        {
            $query = $pdo->prepare('SELECT UNIX_TIMESTAMP(ping_date) AS time, players_count
                                FROM pings
                                WHERE server = :server');
            $query->execute(array(
                'server' => $ip
            ));
        }

        if($query->rowCount() == 0)
            return $app->json(array('error' => 'Server unknown'), 404);


        $data = array('times' => array(), 'players' => array());
        if ($zcraft)
        {
            $data['guardians'] = array();
            $data['admins'] = array();
        }

        while($ping = $query->fetch())
        {
            $data['times'][] = (int) $ping['time'];
            $data['players'][] = (int) $ping['players_count'];

            if ($zcraft)
            {
                $data['guardians'][] = (int) $ping['guardians_count'];
                $data['admins'][] = (int) $ping['admins_count'];
            }
        }

        return $app->json($data);
    }

    public function zcraft_uniques(Application $app, $begin, $end)
    {
        // Database connection
        try {
            $pdo = get_db_connector($app, 'mcstats');

            if ($pdo == null) throw new \RuntimeException;
        }
        catch(\Exception $e)
        {
            return $app->json(array('error' => 'Unable to connect the database.'), 500);
        }

        // Unique players
        $query = $pdo->prepare("SELECT
            (
                SELECT COUNT(DISTINCT `player_uuid`) AS `unique_players`
                FROM `sessions_zcraft`
                WHERE UNIX_TIMESTAMP(`login`) >= :begin
                  AND UNIX_TIMESTAMP(`logout`) <= :end
            ) AS `unique_players`,
            (
                SELECT UNIX_TIMESTAMP(`login`)
                FROM `sessions_zcraft`
                ORDER BY `login` ASC
                LIMIT 1
            ) AS `first_day`");
        $query->execute(array('begin' => $begin, 'end' => $end));
        $result = $query->fetch(PDO::FETCH_ASSOC);

        if($result['first_day'] > $end)
            return "N/A";
        else
            return $result['unique_players'] . ($result['first_day'] > $begin ? "+" : "");
    }

    public function stats_legacy(Application $app, $server_type, $ips)
    {
        return $app->redirect($app['url_generator']->generate('tools.server_stats', array(
            'server_type' => $server_type,
            'ips' => $ips
        )));
    }
}
