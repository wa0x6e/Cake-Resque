<?php


	if (!defined('ROOT')) {
		define('ROOT', dirname(dirname(__FILE__)));
	}

	if (!defined('DS')) {
		define('DS', DIRECTORY_SEPARATOR);
	}

	include dirname(dirname(ROOT)) . DS . 'vendor' . DS . 'autoload.php';
	include(ROOT . DS . 'Config' . DS . 'Core.php');

	$app = new Slim\Slim($config);

	$app->get('/', function () use ($app, $settings) {
		$app->response()['Cache-Control'] = 'max-age=' . 60*60*24*7 . ', s-maxage=' . 60*60*24*30;
		$app->render('index.md', array(
			'pageTitle' => '',
			'currentLink' => 'Home'
		));
	});

	$app->get('/fr/', function () use ($app, $settings) {
		$app->response()['Cache-Control'] = 'max-age=' . 60*60*24*7 . ', s-maxage=' . 60*60*24*30;
		$app->render('fr/index.md', array(
			'pageTitle' => '',
			'currentLink' => 'Home',
			'language' => 'fr'
		));
		setHeaderLanguage($app, 'fr');
	});

	$app->get('/install', function () use ($app, $settings) {
		$app->response()['Cache-Control'] = 'max-age=' . 60*60*24*7 . ', s-maxage=' . 60*60*24*30;
		$app->render('install.md', array(
			'pageTitle' => 'Installation',
			'currentLink' => 'Install'
		));
	});

	$app->get('/fr/install', function () use ($app, $settings) {
		$app->response()['Cache-Control'] = 'max-age=' . 60*60*24*7 . ', s-maxage=' . 60*60*24*30;
		$app->render('fr/install.md', array(
			'pageTitle' => 'Installation',
			'currentLink' => 'Installation',
			'language' => 'fr'
		));
		setHeaderLanguage($app, 'fr');
	});

	$app->get('/usage', function () use ($app, $settings) {
		$app->response()['Cache-Control'] = 'max-age=' . 60*60*24*7 . ', s-maxage=' . 60*60*24*30;
		$app->render('usage.md', array(
			'pageTitle' => 'Usage',
			'currentLink' => 'Usage'
		));
	});

	$app->get('/fr/usage', function () use ($app, $settings) {
		$app->response()['Cache-Control'] = 'max-age=' . 60*60*24*7 . ', s-maxage=' . 60*60*24*30;
		$app->render('fr/usage.md', array(
			'pageTitle' => 'Usage',
			'currentLink' => 'Usage',
			'language' => 'fr'
		));
		setHeaderLanguage($app, 'fr');
	});

	$app->get('/commands', function () use ($app, $settings) {
		$app->response()['Cache-Control'] = 'max-age=' . 60*60*24*7 . ', s-maxage=' . 60*60*24*30;
		$app->render('commands.md', array(
			'pageTitle' => 'Commands',
			'currentLink' => 'Commands'
		));
	});

	$app->get('/fr/commands', function () use ($app, $settings) {
		$app->response()['Cache-Control'] = 'max-age=' . 60*60*24*7 . ', s-maxage=' . 60*60*24*30;
		$app->render('fr/commands.md', array(
			'pageTitle' => 'Commandes',
			'currentLink' => 'Commandes',
			'language' => 'fr'
		));
		setHeaderLanguage($app, 'fr');
	});

	$app->get('/usecases', function () use ($app, $settings) {
		$app->response()['Cache-Control'] = 'max-age=' . 60*60*24*7 . ', s-maxage=' . 60*60*24*30;
		$app->render('usecases.md', array(
			'pageTitle' => 'Use Cases',
			'currentLink' => 'Use Cases'
		));
	});

	$app->get('/fr/usecases', function () use ($app, $settings) {
		$app->response()['Cache-Control'] = 'max-age=' . 60*60*24*7 . ', s-maxage=' . 60*60*24*30;
		$app->render('fr/usecases.md', array(
			'pageTitle' => 'Cas d\'utilisation',
			'currentLink' => 'Cas d\'utilisation',
			'language' => 'fr'
		));
		setHeaderLanguage($app, 'fr');
	});


	$app->error(function (\Exception $e) use ($app) {
		$app->response()['Cache-Control'] = 'max-age=' . 60*60*24*7 . ', s-maxage=' . 60*60*24*30;
		$app->render('error.php', array(
			'pageTitle' => 'Error',
			'message' => $e->getMessage()
		));
	});

	function setHeaderLanguage($app, $lang) {
		$res = $app->response();
		$res['Content-Language'] = $lang;
	}

	$app->run();