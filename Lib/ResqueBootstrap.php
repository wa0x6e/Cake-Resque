<?php

	// From Console/cake.php
	
	define('DS', DIRECTORY_SEPARATOR);
	
	$dispatcher = getenv('CAKE') .  'Console' . DS . 'ShellDispatcher.php';
	$found = false;
	$paths = explode(PATH_SEPARATOR, ini_get('include_path'));
	
	foreach ($paths as $path) {
		if (file_exists($path . DS . $dispatcher)) {
			$found = $path;
		}
	}
	
	if (!$found && function_exists('ini_set')) {
		$root = dirname(dirname(dirname(__FILE__)));
		ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . dirname(getenv('CAKE')));
	}
	
	if (!require_once($dispatcher)) {
		trigger_error('Could not locate CakePHP core files.', E_USER_ERROR);
	}

	unset($paths, $path, $found, $dispatcher, $root, $ds);
	
	// From ShellDipatcher::_boostrap
	
	define('ROOT', dirname(dirname(getenv('CAKE'))));
	define('APP_DIR', 'app');
	define('APP', ROOT . DS . APP_DIR . DS);
	define('WWW_ROOT', APP . 'webroot' . DS);
	if (!is_dir(ROOT . DS . APP_DIR . DS . 'tmp')) {
		define('TMP', CAKE_CORE_INCLUDE_PATH . DS . 'Cake' . DS . 'Console' . DS . 'Templates' . DS . 'skel' . DS . 'tmp' . DS);
	}
	$boot = file_exists(ROOT . DS . APP_DIR . DS . 'Config' . DS . 'bootstrap.php');
	require getenv('CAKE') . DS . 'bootstrap.php';
	
	if (!file_exists(APP . 'Config' . DS . 'core.php')) {
		include_once CAKE_CORE_INCLUDE_PATH . DS . 'Cake' . DS . 'Console' . DS . 'Templates' . DS . 'skel' . DS . 'Config' . DS . 'core.php';
		App::build();
	}
	require_once CAKE . 'Console' . DS . 'ConsoleErrorHandler.php';
	$ErrorHandler = new ConsoleErrorHandler();
	set_exception_handler(array($ErrorHandler, 'handleException'));
	set_error_handler(array($ErrorHandler, 'handleError'), Configure::read('Error.level'));
	
	if (!defined('FULL_BASE_URL')) {
		define('FULL_BASE_URL', 'http://localhost');
	}
	
	// End ShellDispatcher
	
	App::uses('Shell', 'Console');