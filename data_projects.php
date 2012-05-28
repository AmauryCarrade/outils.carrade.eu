<?php
	return array(
		'webnel' => array(
			'size'     => 'big',
			'title'    => 'Webnel',
			'subtitle' => 'A super-flex, super-fast & super-easy-to-use CMS.',
			'content'  => '
				<p>
					Webnel is a content management system (and more) who is designed to be flexible, fast and easy to use.<br />
					You will can do any website with the help of Webnel, and without any programming knowledge.
				</p>
				<p>
					But the concept isn\'t locked: I probably will change it nearly to implement <a href="http://sebsauvage.net/wiki/doku.php?id=php:puffblob">something like this</a>.
				</p>',
			'buttons'  => array(
				'website' => array(
					'text' => 'Website',
					'url'  => 'http://www.webnel.org',
					'icon' => 'link',
					'type' => 'primary'
				),
				'github' => array(
					'text' => 'On Github',
					'url'  => 'https://github.com/Bubbendorf/Webnel',
					'icon' => 'github-alt'
				)
			),
			'infos' => array(
				'Type'            => 'Web software',
				'License'         => '<a href="http://www.gnu.org/licenses/lgpl.html"><abbr title="GNU Lesser General Public License">GNU LGPL</abbr> v3</a>',
				'Status'          => 'In progress',
				'Current version' => 'Webnel Quadrium (0.3)'
			)
		),

		'zatsme' => array(
			'size'     => 'big',
			'title'    => 'Zat\'s me',
			'subtitle' => 'A simple and <abbr title="Keep It Simple, Stupid!">KISS</abbr>-powered online adress book',
			'content'  => '
				<p>
					Zat\'s me allow you to manage and share your business card online.<br />
					You can personalize, share, export, print and get your card printed on a premium paper.
				</p>
				<p>
					There are three offers: Free, Premium and Business (want more infos? see website).
				</p>',
			'buttons'  => array(
				'website' => array(
					'text' => 'Website',
					'url'  => 'http://zats.me',
					'icon' => 'link',
					'type' => 'primary'
				)
			),
			'infos' => array(
				'Type'            => 'Web app',
				'Status'          => 'In progress',
				'Current version' => '0.1'
			)
		),

		'amaurycarrade' => array(
			'size'     => 'big',
			'title'    => ucfirst(str_replace('www.', '', $app['request']->getHttpHost() . $app['request']->getBasePath())),
			'subtitle' => 'This website',
			'content'  => '
				<p>
					I have — of course! — designed my own website.<br />
					It was created with <a href="http://silex.sensiolabs.org">Silex</a>, the PHP micro-framework based on the Symfony2 Components.
				</p>',
			'buttons'  => array(
				'website' => array(
					'text' => 'Website',
					'url'  => $app['url_generator']->generate('index'),
					'icon' => 'link',
					'type' => 'primary'
				),
				'github' => array(
					'text' => 'On Github',
					'url'  => 'https://github.com/Bubbendorf/Website',
					'icon' => 'github-alt'
				)
			),
			'infos' => array(
				'Type'            => 'Website',
				'Status'          => 'In production',
				'Current version' => '1.0'
			)
		),

		'OperaQRCode' => array(
			'size'     => 'small',
			'title'    => 'QRCode Generator',
			'subtitle' => 'An Opera add-on who generates a QRCode of the current page',
			'content'  => '
				<p>
					This plug-in simply generates a QRCode of the currently viewed page in a popup.<br />
					Hence, you can quickly transfer a web page from your computer to your smartphone, for example.
				</p>',
			'buttons'  => array(
				'opera' => array(
					'text'  => 'On the Extensions Catalog of Opera',
					'url'   => 'https://addons.opera.com/extensions/details/qrcode-generator/',
					'icon'  => 'list-alt',
					'type'  => 'primary'
				),
				'support' => array(
					'text' => 'Support page',
					'url'  => $app['url_generator']->generate('projects.opera.qrcode'),
					'icon' => 'book'
				),
				'github' => array(
					'text' => 'On Github',
					'url'  => 'https://github.com/Bubbendorf/OperaQRCode',
					'icon' => 'github-alt'
				)
			),
			'infos' => array(
				'Type'            => 'Opera add-on',
				'License'         => '<a href="http://www.apache.org/licenses/LICENSE-2.0.html">Apache 2.0</a>',
				'Current version' => '1.1',
				'Compatibility'   => 'Opera 11+'
			)
		),

		'jqueryTextareaAutoresize' => array(
			'size'     => 'small',
			'title'    => 'Textarea auto-resizer',
			'subtitle' => 'A jQuery plugin who resizes automatically the textareas',
			'content'  => '
				<p>
					This is a jQuery plugin who automatically resizes the textareas, based on the content\'s height.
				</p>',
			'buttons'  => array(
				'website' => array(
					'text' => 'Demo & documentation',
					'url'  => $app['url_generator']->generate('projects.jquery.textareaAutoresize'),
					'icon' => 'book',
					'type' => 'primary'
				)
			),
			'infos' => array(
				'Type'            => 'jQuery plugin',
				'License'         => '<a href="http://www.gnu.org/licenses/lgpl.html"><abbr title="GNU Lesser General Public License">GNU LGPL</abbr> v3</a>',
				'Current version' => '1.1'
			)
		),
	);