<?php
namespace AmauryCarrade\Controllers\Tools;

use PDO;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


class SteamController
{
    public function activity(Application $app, Request $request, $key, $steamid)
    {
        if ($key != $app['credentials']['steam_access_key'])
            $app->abort(404);

        // Database connection
        try {
            $pdo = get_db_connector($app, 'steam');

            if ($pdo == null) throw new \RuntimeException;
        }
        catch(\Exception $e)
        {
            $app->abort(500, "Unable to connect the database.");
        }

        $limit = $request->query->has('limit') ? intval($request->query->get('limit')) : 30;
        $activity = $pdo->prepare('SELECT *, (UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP(date_begin)) AS duration FROM steam_track WHERE steam_id = :steamid ORDER BY date_end DESC LIMIT ' . $limit);
        $activity->execute(array('steamid' => trim($steamid)));

        if ($activity->rowCount() == 0)
            $app->abort(404);

        $tracked = $pdo->prepare('SELECT steam_id, steam_name, steam_realname FROM steam_track GROUP BY steam_id ORDER BY steam_name');
        $tracked->execute();

        return $app['twig']->render('tools/stats/steam.html.twig', array(
            'activity' => $activity->fetchAll(PDO::FETCH_ASSOC),
            'tracked' => $tracked->fetchAll(PDO::FETCH_ASSOC),
            'key' => $key
        ));
    }
}
