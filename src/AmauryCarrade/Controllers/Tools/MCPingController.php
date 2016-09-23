<?php

namespace AmauryCarrade\Controllers\Tools;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

use xPaw\MinecraftPing;
use xPaw\MinecraftPingException;
use xPaw\MinecraftQuery;
use xPaw\MinecraftQueryException;
use xPaw\MinecraftFormat;

use PDO;

class MCPingController
{
    public function ping_home(Application $app, Request $request)
    {
        if ($request->query->has('ip'))
        {
            $ip = $request->query->get('ip');

            if ($ip)
                return $app->redirect($app['url_generator']->generate('tools.minecraft.ping.results', array(
                    'ip' => $ip
                )));
            else
                return $app->redirect($app['url_generator']->generate('tools.minecraft.ping'));
        }

        return $app['twig']->render('tools/minecraft/ping.html.twig', array(
            'data'  => false,
            'input' => '',
            'ip'    => ''
        ));
    }
    
    public function ping(Application $app, Request $request, $ip, $format)
    {
        $timeout = 5;

        $input = "";
        $port = 25565;

        $data = array();
        $error = "";


        if(!empty($ip))
        {
            $input = $ip;

            $players_only = $request->query->has("players_only") && $format == 'json';

            if(strpos($ip, ":") !== false)
            {
                $exploded_ip = explode(":", $ip);
                $ip = $exploded_ip[0];
                $port = intval($exploded_ip[1]);
            }

            try
            {
                $time = microtime(true);

                $query = new MinecraftPing($ip, $port, $timeout);

                $data["ping"] = (microtime(true) - $time) * 1000;

                $infos = $query->Query();

                if($infos !== false && !empty($infos))
                {
                	if (!$players_only)
                	{
		                // Some servers, like mc.uhc.zone, returns the MOTD in the 'text' key of a sub-array. Don't ask me why.
		                $data["motd"] = is_array($infos["description"]) ? $infos['description']['text'] : $infos['description'];
		                $data["motd_html"] = nl2br(MinecraftFormat::parse_minecraft_colors(str_replace(" ", "&nbsp;", htmlspecialchars($data["motd"]))));
                    }

                    $data["max_players"] = $infos["players"]["max"];
                    $data["online_players"] = $infos["players"]["online"];

                    $data["players"] = array();
                    if(isset($infos["players"]["sample"]) && !empty($infos["players"]["sample"]))
                    {
                        foreach($infos["players"]["sample"] as $player)
                        {
                            $data["players"][] = $player["name"];
                        }
                    }

					if (!$players_only)
					{
		                $data["version"] = $infos["version"]; // version.name, version.protocol

		                if(isset($infos["favicon"]))
		                {
		                    $data["favicon"] = str_replace("\n", "", $infos["favicon"]);
		                }
		                else
		                {
		                    $data["favicon"] = "";
		                }
                    }
                }
                else
                {
                    // < 1.7 server?
                    $query->Close();
                    $query->Connect();

                    $infos = $query->QueryOldPre17();

                    if($infos !== false && !empty($infos))
                    {
                        $data["max_players"] = $infos["MaxPlayers"];
                        $data["online_players"] = $infos["Players"];

						if (!$players_only)
						{
		                    $data["motd"] = $infos["HostName"];
		                    $data["motd_html"] = nl2br(MinecraftFormat::parse_minecraft_colors(htmlspecialchars($infos["HostName"])));
                        
		                    $data["version"] = array();
		                    $data["version"]["name"] = $infos["Version"];
		                    $data["version"]["protocol"] = $infos["Protocol"];
                        }
                    }
                    else
                    {
                        $error = "Impossible de contacter le serveur.";
                    }
                }
            }
            catch(MinecraftPingException $e)
            {
                $error = $e->getMessage();
            }

            if(isset($query)) $query->Close();

            // Default values if the query fails or is disabled.
            $data["version"]["software"] = "";
            $data["plugins"] = array();
            $data["main_map"] = "";
            $data["game_type"] = "";

            if(!$request->query->has("noquery"))
            {
                try
                {
                    $query = new MinecraftQuery();
                    $query->Connect($ip, $port, $timeout);

                    $infos = $query->GetInfo();
                    $players = $query->GetPlayers();

                    if($infos !== false && !empty($infos) && !$players_only)
                    {
                        $data["version"]["software"] = $infos["Software"];
                        $data["plugins"] = $infos["Plugins"];
                        $data["main_map"] = $infos["Map"];
                        $data["game_type"] = $infos["GameType"];

                        if($data["plugins"])
                            sort($data["plugins"], SORT_NATURAL | SORT_FLAG_CASE);
                    }

                    if($players !== false && !empty($players))
                    {
                        // We replace the players sample with this full player list.
                        $data["players"] = $players;
                    }
                }
                catch(MinecraftQueryException $e)
                {
                    // Query blocked or failed :c
                }
            }
        }

        if($format == "json")
        {
            $status_code = 200;
            $json = array();

            if(empty($input))
            {
                $json["statut"] = "failed";
                $json["error"] = "Bad request: IP missing.";
                $status_code = 400;
            }
            else if(!empty($error))
            {
                $json["status"] = "failed";
                $json["ip"] = $ip;
                $json["port"] = $port;
                $json["error"] = $error;
                $status_code = 504;
            }
            else
            {
                $json["status"] = "ok";
                $json["ip"] = $ip;
                $json["port"] = $port;
                $json["data"] = $data;
                $status_code = 200;
            }

            return $app->json($json, $status_code);
        }
        else
        {
            // We first check if this server is a tracked one, to add a link to the track page
            $tracked = false;

            if($ip == 'zcraft.fr')
            {
                $tracked = true;
            }
            else
                {
                try {
                    $pdo = get_db_connector($app);
                    
                    if ($pdo != null)
                    {
                        $query = $pdo->prepare('SELECT COUNT(*) AS count
                                                FROM servers
                                                WHERE LOWER(server_ip) = :server');
                        $query->execute(array('server' => strtolower(trim($input))));

                        $tracked = ($query->fetch(PDO::FETCH_OBJ)->count >= 1);
                    }
                }
                catch(\Exception $e) {}
            }

            // Now the response can be returned.
            return $app['twig']->render('tools/minecraft/ping.html.twig', array(
                    'input'    => $input,
                    'ip'       => $ip,
                    'port'     => $port,
                    'error'    => $error,
                    'data'     => $data,
                    'tracked'  => $tracked
            ));
        }
    }
    
    public function ping_legacy(Application $app, Request $request, $format)
    {
        if ($request->query->has('ip'))
        {
            $ip = $request->query->get('ip');

            if ($ip)
            {
                $args = array('ip' => $ip);

                if (!empty(trim($format)) && strtolower($format) != 'html')
                    $args['format'] = $format;

                return $app->redirect($app['url_generator']->generate('tools.minecraft.ping.results', $args));
            }
        }

        return $app->redirect($app['url_generator']->generate('tools.minecraft.ping'));
    }
}

