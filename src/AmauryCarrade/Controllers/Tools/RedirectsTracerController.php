<?php
namespace AmauryCarrade\Controllers\Tools;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Requests;
use Requests_Hooks;
use Requests_Response;
use Requests_Session;
use Requests_Exception;
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
			'start_url' => $url,
			'user_agent' => $ua,
			'max_redirects' => $redirects,
			'hops' => $hops
		);

		switch ($format) {
			case 'json':
				return $app->json($data);
			
			default:
				return $app['twig']->render('tools/web/redirects.html.twig', $data);
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
					'status_text' => null,
					'duration' => 0,
					'wait' => 0,
					'redirect_type' => 'interrupted',
					'location' => null,
					'error' => null
				);

				break;
			}

			$time = 0;

			try
			{
				$t = microtime(true);
				$r = $s->get($url);
				$time = microtime(true) - $t;
			}
			catch (Requests_Exception $e)
			{
				$hops[] = array(
					'called_url' => $url,
					'headers' => null,
					'status_code' => null,
					'status_text' => null,
					'duration' => 0,
					'wait' => 0,
					'redirect_type' => 'error',
					'error' => $e->getMessage()
				);

				break;
			}

			$headers = array();
			$headers_iter = $r->headers->getIterator();

			while ($headers_iter->valid())
			{
				$val = $headers_iter->current();
				$headers[$headers_iter->key()] = is_array($val) ? implode(', ', $val) : $val;

				$headers_iter->next();
			}

			$hop = array(
				'called_url' => $r->url,
				'headers' => $headers,
				'status_code' => $r->status_code,
				'status_text' => isset(Response::$statusTexts[$r->status_code]) ? Response::$statusTexts[$r->status_code] : 'unknown status',
				'duration' => $time
			);

			// HTTP redirection
			if ($r->is_redirect())
			{
				$old_url = $url;
				$url = $r->headers['location'];

				// relative redirect, for compatibility make it absolute
				if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0)
				{
					$url = Requests_IRI::absolutize($old_url, $url);
					$url = $url->uri;
				}

				$hop['wait'] = 0;
				$hop['redirect_type'] = 'http';
				$hop['location'] = $url;
			}
			else
			{
				// Meta redirection? We pick the smallest delay
				$soup = str_get_html($r->body);
				$metas = array();

				foreach ($soup->find('meta[http-equiv]') as $meta)
				{
					// <meta http-equiv="refresh" content="5;URL=http://example.com/" />
					// <meta http-equiv="refresh" content="5;http://example.com/" />
					// <meta http-equiv="refresh" content="5" />
					$http_equiv = $meta->convert_text($meta->attr['http-equiv']);
					if (strtolower(trim($http_equiv)) == 'refresh')
					{
						$content = trim($meta->content);
						if (empty($content))
						{
							continue;
						}
						else if (is_numeric($content))
						{
							$duration = intval($content);
							$metas[$duration] = null;
						}
						else
						{
							$parts = explode(';', $content, 2);

							// Invalid duration: meta skipped
							if (!is_numeric($parts[0]))
								continue;

							$duration = intval($parts[0]);
							$url_part = trim($parts[1]);

							// Some dont use the prefix 'url=', and the W3C shows an example without it in a documentation page,
							// so we support non-prefixed values.
							if (stripos($url_part, 'url=') === 0)
								$url_part = trim(substr($url_part, 4));

							$metas[$duration] = $url_part;
						}
					}
				}

				if (!empty($metas))
				{
					// We pick the lowest redirection time
					ksort($metas);
					reset($metas);

					$duration = key($metas);
					$meta_url = current($metas);

					// Redirection loop
					if ($meta_url == null)
					{
						$hop['wait'] = $duration;
						$hop['redirect_type'] = 'meta_loop';
						$hop['location'] = $url;

						$url = null;
					}
					else
					{
						// relative redirect, for compatibility make it absolute
						if (strpos($meta_url, 'http://') !== 0 && strpos($meta_url, 'https://') !== 0)
						{
							$meta_url = Requests_IRI::absolutize($url, $meta_url);
							$meta_url = $meta_url->uri;
						}

						$hop['wait'] = $duration;
						$hop['redirect_type'] = $url == $meta_url ? 'meta_loop' : 'meta';
						$hop['location'] = $meta_url;

						$url = $meta_url;
					}
				}
				else
				{
					$hop['wait'] = 0;
					$hop['redirect_type'] = 'none';
					$hop['location'] = null;

					$url = null;
				}
			}

			$hop['error'] = null;

			$hops[] = $hop;

			if ($hop['location'] != null) $redirects++;
		}

		return $hops;
	}
}
