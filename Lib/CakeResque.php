<?php
/**
 * CakeResque Lib File
 *
 * Proxy class to Resque
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
 * @subpackage	  CakeResque.Lib
 * @since         1.2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

require_once App::pluginPath('CakeResque') . 'vendor' . DS . Configure::read('CakeResque.Resque.lib') . DS . 'lib' . DS . 'Resque.php';

Resque::setBackend(
	Configure::read('CakeResque.Redis.host') . ':' . Configure::read('CakeResque.Redis.port'),
	Configure::read('CakeResque.Redis.database'),
	Configure::read('CakeResque.Redis.namespace')
);

/**
 * CakeResque Class
 *
 * Proxy to Resque, enabling logging function
 */
class CakeResque
{

	public static $logs = array();

/**
 * Enqueue a Job
 * and keep a log for debugging
 *
 * @param  string 	$queue       Name of the queue to enqueue the job to
 * @param  string  	$class       Class of the job
 * @param  array  	$args        Arguments passed to the job
 * @param  boolean 	$trackStatus Whether to track the status of the job
 * @return void
 */
	public static function enqueue($queue, $class, $args = array(), $trackStatus = false) {
		Resque::enqueue($queue, $class, $args, $trackStatus);

		if (!is_array($args)) {
			$args = array($args);
		}
		self::$logs[$queue][] = array(
				'queue' => $queue,
				'class' => $class,
				'method' => array_pop($args),
				'args' => $args,
				'caller' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)
			);
	}
}