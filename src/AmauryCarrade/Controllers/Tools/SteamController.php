<?php
namespace AmauryCarrade\Controllers\Tools;

use PDO;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


class SteamController
{
    public function activity(Application $app, Request $request, $key, $record_type, $steamid)
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

        $limit = $request->query->has('limit') ? intval($request->query->get('limit')) : 64;
        $activity = $pdo->prepare('SELECT *, (UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP(date_begin)) AS duration
                                   FROM steam_track
                                   WHERE steam_id = :steamid AND record_type = :record_type
                                   ORDER BY date_end DESC, date_begin DESC
                                   LIMIT ' . $limit);
        $activity->execute(['steamid' => trim($steamid), 'record_type' => trim($record_type)]);

        if ($activity->rowCount() == 0)
            $app->abort(404);

        $tracked = $pdo->prepare('SELECT record_type, steam_id, steam_name, steam_realname, state
                                  FROM steam_track
                                  GROUP BY steam_id DESC
                                  ORDER BY record_type, steam_name, steam_realname');
        $tracked->execute();

        $all_track = $tracked->fetchAll(PDO::FETCH_ASSOC);
        $sorted_track = [];
        foreach ($all_track as $track)
        {
            if (!isset($sorted_track[$track['record_type']]))
                $sorted_track[$track['record_type']] = [];

            $sorted_track[$track['record_type']][] = $track;
        }

        return $app['twig']->render('tools/stats/steam.html.twig', [
            'record_type' => $record_type,
            'activity' => $activity->fetchAll(PDO::FETCH_ASSOC),
            'tracked' => $sorted_track,
            'key' => $key
        ]);
    }
}
