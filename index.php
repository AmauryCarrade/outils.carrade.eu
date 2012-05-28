<?php
	require_once __DIR__.'/silex.phar';

	$app = new Silex\Application();

	if (in_array(@$_SERVER['REMOTE_ADDR'], array(
	    '127.0.0.1',
	    '::1',
	))) {
		$app['debug'] = true;
	}

	$credentials = include('credentials.php');

	// Registry
	$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
	$app->register(new Silex\Provider\SymfonyBridgesServiceProvider(), array(
		'symfony_bridges.class_path'  => __DIR__.'/vendor/symfony/src',
	));
	$app->register(new Silex\Provider\TwigServiceProvider(), array(
		'twig.path'       => __DIR__.'/views',
		'twig.class_path' => __DIR__.'/vendor/twig/lib',
	));
	$app->register(new Silex\Provider\SwiftmailerServiceProvider(), array(
	    'swiftmailer.class_path'  => __DIR__.'/vendor/swiftmailer/lib/classes',
		'swiftmailer.options'     => $credentials['smtp']
	));

	$app['recaptcha.public']  = $credentials['recaptcha']['public'];
	$app['recaptcha.private'] = $credentials['recaptcha']['private'];


	/* Pages */

	/** Index
	******************/
	$app->get('/', function () use($app) {
		return $app['twig']->render('index.html.twig', array(
			'section' => 'index'
		));
	})->bind('index');


	/** Projects
	******************/
	$app->get('/projects.html', function () use($app) {
		
		$projects = require_once('data_projects.php');

		// Search
		if(isset($_GET['q']) && !empty($_GET['q'])) {
			$q = htmlspecialchars($_GET['q']);
			foreach($projects AS $id => $project) {
				if(strpos(strtolower($project['title']), strtolower($q)) === false && 
				   strpos(strtolower($project['subtitle']), strtolower($q)) === false && 
				   strpos(strtolower($project['content']), strtolower($q)) === false) {
					unset($projects[$id]);
				}
			}
		}

		// Filter
		$filter = NULL;
		if(isset($_GET['filter']) && !empty($_GET['filter']) && in_array($_GET['filter'], array('big', 'small'))) {
			$filter = htmlspecialchars($_GET['filter']);
			foreach($projects AS $id => $project) {
				if($project['size'] !== $filter) {
					unset($projects[$id]);
				}
			}
		}


		$query = isset($q) ? $q : NULL;

		return $app['twig']->render('projects.html.twig', array(
			'section'  => 'projects',
			'projects' => $projects,
			'query'    => $query,
			'filter'   => $filter
		));
	})->bind('projects');
	

	// Projects pages
	
	$app->get('projects/jquery/autoResize.html', function() use($app) {
		return $app['twig']->render('projects/autoResize.html.twig', array(
			'section' => 'projects'
		));
	})->bind('projects.jquery.textareaAutoresize');

	$app->get('projects/opera/qrcode.html', function() use($app) {
		return $app['twig']->render('projects/opera.qrcode.html.twig', array(
			'section' => 'projects'
		));
	})->bind('projects.opera.qrcode');


	/** Upload
	******************/
	$app->match('/upload.html', function () use($app, $credentials) {
		
		// A simple, minimalist, personal file/image hosting script. - version 0.7
		// Only you can upload a file or image, using the password(s) ($passwords).
		// Anyone can see the images or download the files.
		// Files are stored in a subdirectory (see $subdir).
		// This script is public domain.
		// Original source: http://sebsauvage.net/wiki/doku.php?id=php:imagehosting


		$passwords = $credentials['upload'];
		$subdir    = 'files'; // subdirectory where to store files and images.

		if (!is_dir($subdir)) 
		{
		    mkdir($subdir,0705); chmod($subdir, 0705);
		    $h = fopen($subdir.'/.htaccess', 'w') or die("Can't create subdir/.htaccess file.");
		    fwrite($h,"Options -ExecCGI\nAddHandler cgi-script .php .pl .py .jsp .asp .htm .shtml .sh .cgi");
		    fclose($h);
		    $h = fopen($subdir.'/index.html', 'w') or die("Can't create subdir/index.html file.");
		    fwrite($h,'<html><head><meta http-equiv="refresh" content="0;url='.$_SERVER["SCRIPT_NAME"].'"></head><body></body></html>');
		    fclose($h);
		}

		$scriptname = basename($_SERVER["SCRIPT_NAME"]);
		$flash = NULL;

		if (isset($_FILES['filetoupload']) && isset($_POST['filetoupload_url']) && isset($_POST['password']))
		{   
		    sleep(3); // Reduce brute-force attack effectiveness. 
		    
		    /**
		     * Determines filename.
		     * @param  string       $filename    The input filename
		     * @param  string       $newFileData The content of the new file (used to compare  files' contents)
		     * @param  bool         $secondIsURL Is the second parameter an URL to the file?
		     * @return string|array              The new filename, or an array who contains the filename if this file has already been uploaded.      
		     */
		    function determineFilename($filename, $newFileData, $secondIsURL = false) {
		    	$beforeFilename = str_replace(basename($filename), NULL, $filename);
		    	
		    	if(!file_exists($filename)) {
		    		return $filename;
		    	}
		    	else {
		    		if((!$secondIsURL && sha1_file($filename) == sha1($newFileData)) || ($secondIsURL && sha1_file($filename) == sha1_file($newFileData))) {
		    			return array($filename);
		    		}
		    		else {
		    			$i = 1;
		    			do {
		    				$newFilename = $beforeFilename . $i . '-' . basename($filename);
		    				$i++;
		    			} while(file_exists($newFilename) && ((!$secondIsURL && sha1_file($filename) != sha1($newFileData)) || ($secondIsURL && sha1_file($filename) != sha1_file($newFileData))));
		    			
		    			$i = $i - 2;
		    			$ext = $i <= 0 ? NULL : $i . '-';
		    			if(sha1_file($beforeFilename . $ext . basename($filename)) == sha1($newFileData)) {
		    				return array($beforeFilename . $ext . basename($filename));
		    			}
		    			return basename($newFilename);
		    		}
		    	}
		    }

		    /**
		     * Return the complete file's URL from the file's path.
		     */
		    function getFileURL($filename, $subdir) {
		    	$subdir .= '/';
		    	if(strpos($filename, $subdir) === 0) { // If the filename already contains the sub-directory, we don't add it.
		    		$subdir = NULL;
		    	}
		    	$serverport='';
		        if ($_SERVER["SERVER_PORT"]!='80') { 
		            $serverport=':'.$_SERVER["SERVER_PORT"]; 
		        }

		        return 'http://' . $_SERVER["SERVER_NAME"] . $serverport . dirname($_SERVER["SCRIPT_NAME"]) . '/' . $subdir . $filename;

		    }

		    $downloader;
		    if(!empty($_POST['filetoupload_url'])) {
		    	require_once('vendor/Downloader.php');
		    	$downloader = new Downloader();
		    }

		    $filename;
		    if(isset($_FILES['filetoupload']) && $_FILES['filetoupload']['error'] != UPLOAD_ERR_NO_FILE) {
		    	$filename = $_FILES['filetoupload']['name'];
		    }
		    else {
		    	$filename = basename($_POST['filetoupload_url']);
		    }
		    $filename = $subdir . '/' . $filename;

		    if (!in_array($_POST['password'], $passwords)) {
		        $flash['type']  = 'error';
				$flash['title'] = 'Wrong password.';
				$flash['text']  = 'Foreigners are forbidden here!';
		    }

		    else if ($_FILES['filetoupload']['error'] == UPLOAD_ERR_NO_FILE && empty($_POST['filetoupload_url'])) {
		        $flash['type']  = 'error';
				$flash['title'] = 'No file.';
				$flash['text']  = 'Hey, if you want to upload a file, you need to send it! We can\'t imagine it ;) .';
		    }

		    else if ($_FILES['filetoupload']['error'] == UPLOAD_ERR_FORM_SIZE) {
		        $flash['type']  = 'error';
				$flash['title'] = 'This file is too big.';
				$flash['text']  = 'We do not accept overweight files... Max size is 256 Mo.';
		    }

		    else if(!empty($_POST['filetoupload_url']) && $file_content = $downloader->get($_POST['filetoupload_url'], array(), 'curl')) {
		    	$file_content_data = $file_content['body'];
		    	$filename = determineFilename($subdir . '/' . basename($file_content['infos']['url']), $file_content_data);
				
		    	if(is_array($filename)) {
		    		$fileurl = getFileURL($filename[0], $subdir);
		    		$flash['type']  = 'success';
					$flash['title'] = 'Well done!';
					$flash['text']  = 'You have already uploaded this file. For the record, the file was uploaded to <a href="'.$fileurl.'">'.$fileurl.'</a>.';
		    	}
				else if($file_content['HTTPCode'] != 200) {
		    		$flash['type']  = 'error';
					$flash['title'] = 'Oh snap!';
					$flash['text']  = 'There was an error while downloading the file. The server returned a <strong>' . $file_content['HTTPCode'] . '</strong> HTTP Status Code. Please try again!';
		    	}
				else if(!$file = fopen($subdir . '/' . basename($filename), 'w')) {
		    		$flash['type']  = 'error';
					$flash['title'] = 'Oh snap!';
					$flash['text']  = 'There was an error while creating the file on the disk, please try again!';
		    	}
		    	else if(!fwrite($file, $file_content_data)) {
		    		$flash['type']  = 'error';
					$flash['title'] = 'Oh snap!';
					$flash['text']  = 'There was an error while writing the file on the disk, please try again!';
		    	}
		    	else {
		    		fclose($file);

		    		$fileurl = getFileURL($filename, $subdir);
		    		$flash['type']  = 'success';
					$flash['title'] = 'Well done!';
					$flash['text']  = 'The file was uploaded to <a href="'.$fileurl.'">'.$fileurl.'</a>.';
		    	}
		    }

		    else if (is_array(determineFilename($filename, $_FILES['filetoupload']['tmp_name'], true))) {
		        $flash['type']  = 'error';
				$flash['title'] = 'Oh snap!';
				$flash['text']  = 'This file already exists. Please change his name ;) .';
		    }

		    else if(move_uploaded_file($_FILES['filetoupload']['tmp_name'], $filename)) 
		    {
		        $fileurl = getFileURL($filename, $subdir);

		        $flash['type']  = 'success';
				$flash['title'] = 'Well done!';
				$flash['text']  = 'The file was uploaded to <a href="'.$fileurl.'">'.$fileurl.'</a>.';
		    }

		    else {
		        $flash['type']  = 'error';
				$flash['title'] = 'Oh snap!';
				$flash['text']  = 'There was an error uploading the file, please try again!';
		    }
		}

		return $app['twig']->render('upload.html.twig', array(
			'section'    => 'upload',
			'scriptname' => $scriptname,
			'flash'      => $flash
		));

	})->bind('upload');

	
	/* Contact
	****************/
	$app->match('/contact.html', function() use($app) {
		// We need the ReCaptcha library.
		require_once __DIR__ . '/vendor/recaptcha/recaptchalib.php';

		$flash     = NULL;
		$postClean = array();
		if(isset($_POST['name']) && isset($_POST['mail']) && isset($_POST['message'])) {
			$postClean = $_POST;
			$flash = array();
			$mailCheckRegex = <<<EOR
[a-z0-9!\#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!\#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?
EOR;

			if(empty($postClean['name'])) {
				$flash['type'] = 'error';
				$flash['title'] = 'You have forgotten your name.';
				$flash['text'] = 'It\'s better to talk to an appointed person ;) .';

				unset($postClean['name']);
			}
			else if(empty($postClean['mail'])) {
				$flash['type'] = 'error';
				$flash['title'] = 'You have forgotten your email address.';
				$flash['text'] = 'How do I answer you, me?';

				unset($postClean['mail']);
			}
			else if(!preg_match('#' . $mailCheckRegex . '#i', $postClean['mail'])) {
				$flash['type'] = 'error';
				$flash['title'] = 'The email address you have entered is invalid.';
				$flash['text'] = 'How do I answer you, me?';

				unset($postClean['mail']);
			}
			else if(empty($_POST['message'])) {
				$flash['type'] = 'error';
				$flash['title'] = 'The message is missing.';
				$flash['text'] = 'Talk without discussion? Original.';

				unset($postClean['message']);
			}
			else if(!recaptcha_check_answer($app['recaptcha.private'], $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"])->is_valid) {
				$flash['type']  = 'error';
				$flash['title'] = 'The CAPTCHA isn\'t valid.';
				$flash['text']  = 'You would not be a robot... Yes?';
			}
			else {
				$message = \Swift_Message::newInstance()
					        ->setFrom(array($postClean['mail'] => $postClean['name']))
					        ->setReplyTo(array($postClean['mail'] => $postClean['name']))
					        ->setTo(array('amaury.carrade@free.fr' => 'Amaury Carrade'))
					        ->setBody($postClean['message']);

				if(!empty($postClean['object'])) {
					$message->setSubject(htmlspecialchars($postClean['object']));
				}
				else {
					$message->setSubject('[AmauryCarrade.eu] New message from ' . $postClean['name']);
				}

				if($app['mailer']->send($message)) {
					$flash['type'] = 'success';
					$flash['title'] = 'The message was successfully sent.';
					$flash['text'] = 'I will try to answer as soon as possible ;) .';
				}
				else {
					$flash['type'] = 'error';
					$flash['title'] = 'Oh snap!';
					$flash['text'] = 'There were an error while sending email message. Please try again later.';
				}

				$postClean = array();
			}
		}


		return $app['twig']->render('contact.html.twig', array(
			'section'       => 'contact',
			'flash'         => $flash,
			'postClean'     => $postClean,
			'reCaptchaHTML' => recaptcha_get_html($app['recaptcha.public'], NULL, true)
		));
	})->bind('contact');

	$app->get('/pgp.html', function() use($app) {
		return $app['twig']->render('pgp.html.twig', array(
			'section'       => 'pgp'
		));
	})->bind('pgp');



	// 404 & co.
	if(!$app['debug']) {
		$app->error(function (\Exception $e, $code) use($app) {
			switch ($code) {
		 		case 404:
					return $app['twig']->render('errors/404.html.twig', array(
						'section'       => 'no'
					));
					break;
				default:
					return $app['twig']->render('errors/error.html.twig', array(
						'section'       => 'no'
					));
			}
		});
	}


	$app->run();
