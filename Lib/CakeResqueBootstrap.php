<?php
/**
 * Bootstrap file
 *
 * Use to bootstrap the job classes
 * All code are from CakePHP bootstrap files
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author        Wan Qi Chen <kami@kamisama.me>
 * @copyright     Copyright 2012, Wan Qi Chen <kami@kamisama.me>
 * @link          http://cakeresque.kamisama.me
 * @package       CakeResque
 * @subpackage	  CakeResque.lib
 * @since         0.5
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/*
 * Copy/Paste from lib/Cake/Console/cake.php
 */
if (!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

$dispatcher = getenv('CAKE') . 'Console' . DS . 'ShellDispatcher.php';
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

if (!require_once ($dispatcher)) {
	trigger_error('Could not locate CakePHP core files.', E_USER_ERROR);
}

unset($paths, $path, $found, $dispatcher, $root, $ds);

/*
 * Copy/Paste from  lib/Cake/Console/ShellDipatcher::_bootstrap()
 */
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


App::uses('Shell', 'Console');


/**
 * Resque Job Creator Class
 *
 * Create a job instance
 *
 * This will find and instanciate a class from a classname.
 * Particulary important if the classname isn't the real classname,
 * like in CakePHP, where the classname can be prefixed with
 * a plugin name, and the classname doesn't give a clue about
 * the class file location.
 *
 * This class is optional, and if missing, Resque will handle the job
 * creation itself, with the standard method.
 *
 * @since 1.0
 * @author kamisama
 *
 */
class Resque_Job_Creator
{

/**
 * Create and return a job instance
 *
 * @param string $className className of the job to instanciate
 * @return object $args a job instance
 * @throws Resque_Exception when the class is not found, or does not follow the job file convention
 */
	public static function createJob($className, $args) {
		list($plugin, $model) = pluginSplit($className);
		$classpath = APP . (empty($plugin) ? '' : 'Plugin' . DS . $plugin . DS) . 'Console' . DS . 'Command' . DS . $model . '.php';
		if (file_exists($classpath)) {
			require_once $classpath;
		} else {
			throw new Resque_Exception('Could not find job class ' . $className . '.');
		}

		if (!class_exists($model)) {
			throw new Resque_Exception('Could not find job class ' . $className . '.');
		}

		if (!method_exists($model, 'perform')) {
			throw new Resque_Exception('Job class ' . $className . ' does not contain a perform method.');
		}

		if (!isset($args[0]) || !method_exists($model, $args[0])) {
			throw new Resque_Exception('Job class ' . $className . ' does not contain ' . $args[0] . ' method.');
		}

		return new $model();
	}
}