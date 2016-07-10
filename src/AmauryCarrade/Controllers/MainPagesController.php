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

    public function don(Application $app)
    {
        return $app['twig']->render('donner.html.twig');
    }

    public function coffee(Application $app)
    {
        $app->abort(418);
    }


    public function list_projects(Application $app)
    {
        $projects_file = $app['contents_folder'] . '/projects.json';

        if (!file_exists($projects_file))
            $app->abort(404);

        $projects = json_decode(file_get_contents($projects_file));

        return $app['twig']->render('projects.html.twig', array(
            'projects' => $projects
        ));
    }

    public function show_project(Application $app, $category, $name)
    {
        $category = str_replace('..', '', $category);
        $name = str_replace('..', '', $name);

        try
        {
            return $app['twig']->render('@content/projects/' . $category . '/' . $name . '.html.twig');
        }
        catch (\Twig_Error_Loader $e)
        {
            $app->abort(404);
            return null;
        }
    }
}
