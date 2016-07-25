<?php
namespace AmauryCarrade\Controllers\Tools;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

use Requests;
use Requests_Hooks;
use Requests_Response;
use Requests_Session;
use Requests_IRI;


class RedirectsTracerController
{
	const MAX_MAX_REDIRECTS = 256;
	const DEFAULT_MAX_REDIRECTS = 21;


	public function redirects(Application $app, Request $request)
	{
		return self::redirects_formats($app, $request, 'html');
	}

	public function redirects_formats(Application $app, Request $request, $format)
	{
		$url = $request->query->get('url');
		$ua = $request->query->get('ua', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:44.0) Gecko/20100101 Firefox/44.0');
		$redirects = min($request->query->get('max_redirects', self::DEFAULT_MAX_REDIRECTS), self::MAX_MAX_REDIRECTS);
		$hops = null;

		if ($url != null) $hops = self::do_redirects_trace($url, $ua, $redirects);

		$data = array(
			'success' => $url != null,
			'url' => $url,
			'user-agent' => $ua,
			'max-redirects' => $redirects,
			'hops' => $hops
		);

		switch ($format) {
			case 'json':
				return $app->json($data);
			
			default:
				return $app['twig']->render('tools/redirects.html.twig', $data);
		}
	}


	private function do_redirects_trace($url, $ua, $max_redirects = 10)
	{
		$hops = array();

		$s = new Requests_Session();
		$s->headers['User-Agent'] = $ua;
		$s->follow_redirects = false;

		$redirects = 0;

		while ($url != null)
		{
			// No more redirects
			if ($redirects > $max_redirects)
			{
				$hops[] = array(
					'called_url' => $url,
					'headers' => null,
					'status_code' => null,
					'duration' => 0,
					'redirect_type' => 'interrupted'
				);

				break;
			}

			$t = microtime(true);
			$r = $s->get($url);
			$time = microtime(true) - $t;

			$hop = array(
				'called_url' => $r->url,
				'headers' => $r->headers,
				'status_code' => $r->status_code,
				'duration' => $time
			);

			if ($r->is_redirect())
			{
				$old_url = $url;
				$url = $r->headers['location'];

				if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
					// relative redirect, for compatibility make it absolute
					$url = Requests_IRI::absolutize($old_url, $url);
					$url = $url->uri;
				}

				$hop['redirect_type'] = 'http_redirect';
				$hop['location'] = $url;
			}
			else
			{
				$hop['redirect_type'] = 'none';
				$hop['location'] = null;
				$url = null;
			}

			$hops[] = $hop;

			if ($hop['location'] != null) $redirects++;
		}

		return $hops;
	}
}
