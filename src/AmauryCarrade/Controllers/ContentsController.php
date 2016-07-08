<?php
namespace AmauryCarrade\Controllers;

use Silex\Application;

class ContentsController
{
	const CONTENTS_SUB_FOLDER = 'misc';
	
	public function show_content(Application $app, $type, $name)
	{
		$type = str_replace('..', '', $type);
		$name = str_replace('..', '', $name);
		
		$markdown_file = $app['contents_folder'] . '/' . self::CONTENTS_SUB_FOLDER . '/' . $type . '/' . $name . '.md';
		if (!file_exists($markdown_file) || !is_file($markdown_file))
			$app->abort(404);
		
		$raw_content = file_get_contents($markdown_file);
		
		$page_title = $name;
		$title = $name;
		$subtitle = '';
		
		$markdown = $raw_content;
		
		if (strpos($raw_content, '---') !== false)
		{
			$parts = explode('---', $raw_content, 2);
			
			$markdown = $parts[1];
			$headers = $parts[0];
			
			foreach (explode("\n", $headers) as $header)
			{
				$header = explode(':', $header, 2);
				if (count($header) <= 1) continue;
				
				switch (strtolower($header[0]))
				{
					case 'page_title':
						$page_title = $header[1];
						break;
					
					case 'title':
						$title = $header[1];
						break;
					
					case 'subtitle':
						$subtitle = $header[1];
						break;
				}
			}
		}
		
		$rendered_content = \Parsedown::instance()
			->setBreaksEnabled(true)
			->setMarkupEscaped(false)
			->setUrlsLinked(true)
			->text($markdown);
		
		return $app['twig']->render('content.html.twig', array(
			'page_title' => $page_title,
			'title' => $title,
			'subtitle' => $subtitle,
			'content' => $rendered_content
		));
	}
}

