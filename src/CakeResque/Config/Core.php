<?php

	include(dirname(dirname(ROOT)) . DS . 'vendor' . DS . 'autoload.php');

	define('APPLICATION_NAME', 'Cake Resque');
	define('TITLE_SEP', ' | ');

	$settings = array(
			/*'mongo' => array(
			 'host' => 'localhost',
					'port' => 27017,
					'database' => 'cube_development'
			),*/
			/* 'redis' => array(
			 'host' => '127.0.0.1',
					'port' => 6379
			),*/
			/*'resquePrefix' => 'resque'*/
			'js' => array(
				'local' => array(
						//'highlightjs' => '',
						//'jquery' => ''
					),
				'prod' => array(
						'highlightjs' => '//yandex.st/highlightjs/7.1/highlight.min.js',
						'jquery' => '//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js',
						'bootstrap' => '/js/bootstrap.min.js?v=2.2.2'
					)
				),
			'css' => array(
				'local' => array(
						//'highlightjs' => ''
					),
				'prod' => array(
					'bootstrap' => '/css/bootstrap.css?v=2.2.2',
					'highlightjs' =>'//yandex.st/highlightjs/7.1/styles/tomorrow.min.css'
					)
				)
	);

	$config = array(
				'debug' => true,
				'view' => 'CakeResque\Lib\MyView',
				'templates.path' => ROOT . DS .'Pages'
			);

	function getCssFiles() {
		global $settings;
		if ($_SERVER['REMOTE_ADDR'] !== "127.0.0.1") {
			return $settings['css']['prod'];
		}
		return array_merge($settings['css']['prod'], $settings['css']['local']);
	}

	function getJsFiles() {
		global $settings;
		if ($_SERVER['REMOTE_ADDR'] !== "127.0.0.1") {
			return $settings['js']['prod'];
		}
		return array_merge($settings['js']['prod'], $settings['js']['local']);
	}