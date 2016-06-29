<?php

namespace AmauryCarrade\Controllers\Tools;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;


class MCHistoryController
{
    public function history_home(Application $app, Request $request)
    {
        if ($request->query->has('user'))
        {
            $identifier = $request->query->get('user');

            if ($identifier)
                return $app->redirect($app['url_generator']->generate('tools.minecraft.history.results', array(
                    'identifier' => $identifier
                )));
            else
                return $app->redirect($app['url_generator']->generate('tools.minecraft.history'));
        }

        return $app['twig']->render('tools/history.html.twig', array(
            'data' => false,
            'user' => ''
        ));
    }

    public function history(Application $app, Request $request, $identifier, $format)
    {
        $name = null;
        $uuid = null;

        $valid = true;

        // Names & UUID

        if(strlen($identifier) <= 16)
        {
            $name = $identifier;

            $c = curl_init('https://api.mojang.com/users/profiles/minecraft/'.$name);
            curl_setopt($c, CURLOPT_POST, 0);
            curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($c, CURLOPT_HEADER, 0);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($c);
            curl_close($c);

            $raw_data = json_decode($result, true);

            if (isset($raw_data['id']))
            {
                $uuid = $raw_data['id'];

                $data['legacy'] = isset($raw_data['legacy']);
                $data['demo'] = isset($raw_data['demo']);
                $data['input_is_uuid'] = false;
            }
            else
            {
                $c = curl_init('https://api.mojang.com/users/profiles/minecraft/'.$name.'?at=1422986400');
                curl_setopt($c, CURLOPT_POST, 0);
                curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($c, CURLOPT_HEADER, 0);
                curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
                $result = curl_exec($c);
                curl_close($c);

                $raw_data = json_decode($result, true);

                if (isset($raw_data['id']))
                {
                    $uuid = $raw_data['id'];
                }
                else
                {
                    $valid = false;
                }

                $data['legacy'] = isset($raw_data['legacy']);
                $data['demo'] = isset($raw_data['demo']);
                $data['input_is_uuid'] = false;
            }
        }
        else
        {
            $uuid = str_replace("-", "", $identifier);
            $data['input_is_uuid'] = true;

            // TODO additional requests to check demo + legacy
            $data['legacy'] = false;
            $data['demo'] = false;
        }


        // UUID formatting

        $data['uuid'] = $uuid;

        if (!$request->query->has('raw_uuid'))
        {
            $data['uuid'] = preg_replace("#(\w{8})(\w{4})(\w{4})(\w{4})(\w{12})#", "$1-$2-$3-$4-$5", $data['uuid']);
        }


        // Names history

        $c = curl_init('https://api.mojang.com/user/profiles/' . $uuid . '/names');
        curl_setopt($c, CURLOPT_POST, 0);
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($c, CURLOPT_HEADER, 0);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($c);
        curl_close($c);

        $raw_data = json_decode($result, true);

        if (isset($raw_data[0]['name']))
        {
            if ($name != null)
            {
                $data['name'] = $name;
            }

            $data['history'] = array();

            $data['history'][0]['date'] = null;
            $data['history'][0]['name'] = $raw_data[0]['name'];

            $i = 0;
            while (isset($raw_data[++$i]))
            {
                $data['history'][$i]['date'] = new \DateTime("@" . $raw_data[$i]['changedToAt'] / 1000); // timestamp
                $data['history'][$i]['name'] = $raw_data[$i]['name'];
            }

            $data['current_name'] = $raw_data[$i - 1]['name'];

            $emptyHistory = false;
        }
        else
        {
            $emptyHistory = true;
        }

        switch (strtolower($format))
        {
            case 'json':
                if (!$valid)
                    return $app->json(array("error" => "This user does not exists"), 404);
                else
                    return $app->json($data);

            default:
                return $app['twig']->render('tools/history.html.twig', array(
                    'data' => $data,
                    'user' => $identifier,
                    'valid' => $valid,
                    'emptyHistory' => $emptyHistory
                ));
        }
    }

    public function history_legacy(Application $app, Request $request, $format)
    {
        if ($request->query->has('user'))
        {
            $identifier = $request->query->get('user');

            if ($identifier)
            {
                $args = array('identifier' => $identifier);

                if (!empty(trim($format)) && strtolower($format) != 'html')
                    $args['format'] = $format;

                return $app->redirect($app['url_generator']->generate('tools.minecraft.history.results', $args));
            }
        }

        return $app->redirect($app['url_generator']->generate('tools.minecraft.history'));
    }
}
