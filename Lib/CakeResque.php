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

use Kamisama\ResqueScheduler as ResqueScheduler;

if (substr(Configure::read('CakeResque.Resque.lib'), 0, 1) === '/') {
	require_once Configure::read('CakeResque.Resque.lib') . DS . 'lib' . DS . 'Resque.php';
	require_once Configure::read('CakeResque.Resque.lib') . DS . 'lib' . DS . 'Resque' . DS . 'Worker.php';
} else {
	require_once realpath(App::pluginPath('CakeResque') . 'vendor' . DS . Configure::read('CakeResque.Resque.lib') . DS . 'lib' . DS . 'Resque.php');
	require_once realpath(App::pluginPath('CakeResque') . 'vendor' . DS . Configure::read('CakeResque.Resque.lib') . DS . 'lib' . DS . 'Resque' . DS . 'Worker.php');
}

if (Configure::read('CakeResque.Scheduler.enabled') === true) {
	if (substr(Configure::read('CakeResque.Scheduler.lib'), 0, 1) === '/') {
		require_once Configure::read('CakeResque.Scheduler.lib') . DS . 'lib' . DS . 'ResqueScheduler.php';
		require_once Configure::read('CakeResque.Scheduler.lib') . DS . 'lib' . DS . 'ResqueScheduler' . DS . 'Job' . DS . 'Status.php';
	} else {
		require_once realpath(App::pluginPath('CakeResque') . 'vendor' . DS . Configure::read('CakeResque.Scheduler.lib') . DS . 'lib' . DS . 'ResqueScheduler.php');
		require_once realpath(App::pluginPath('CakeResque') . 'vendor' . DS . Configure::read('CakeResque.Scheduler.lib') . DS . 'lib' . DS . 'ResqueScheduler' . DS . 'Job' . DS . 'Status.php');
	}
}

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
 * @return string 	Job Id
 */
	public static function enqueue($queue, $class, $args = array(), $trackStatus = null) {
		if ($trackStatus === null) {
			$trackStatus = Configure::read('CakeResque.Job.track');
		}

		$r = Resque::enqueue($queue, $class, $args, $trackStatus);

		if (!is_array($args)) {
			$args = array($args);
		}

		$caller = version_compare(PHP_VERSION, '5.4.0') >= 0
			? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)
			: debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

		self::$logs[$queue][] = array(
			'queue' => $queue,
			'class' => $class,
			'method' => array_shift($args),
			'args' => $args,
			'jobId' => $r,
			'caller' => $caller
		);

		return $r;
	}

/**
 * Enqueue a Job at a certain time
 *
 * @param  int|DateTime $at			timestamp or DateTime object giving the time when the job should be enqueued
 * @param  string 		$queue      Name of the queue to enqueue the job to
 * @param  string  		$class      Class of the job
 * @param  array  		$args       Arguments passed to the job
 *
 * @since  2.3.0
 * @return string 	Job Id
 */
	public static function enqueueAt($at, $queue, $class, $args = array(), $trackStatus = null) {
		if (Configure::read('CakeResque.Scheduler.enabled') !== true) {
			return false;
		}

		if ($trackStatus === null) {
			$trackStatus = Configure::read('CakeResque.Job.track');
		}

		$r = ResqueScheduler\ResqueScheduler::enqueueAt($at, $queue, $class, $args, $trackStatus);

		if (!is_array($args)) {
			$args = array($args);
		}

		$caller = version_compare(PHP_VERSION, '5.4.0') >= 0
			? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)
			: debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

		self::$logs[$queue][] = array(
			'queue' => $queue,
			'class' => $class,
			'method' => array_shift($args),
			'args' => $args,
			'jobId' => $r,
			'caller' => $caller,
			'time' => $at instanceof DateTime ? $at->getTimestamp() : $at
		);

		return $r;
	}

/**
 * Enqueue a Job after a certain time
 *
 * @param  int 		$in 		Number of second to wait from now before queueing the job
 * @param  string 	$queue      Name of the queue to enqueue the job to
 * @param  string  	$class      Class of the job
 * @param  array  	$args       Arguments passed to the job
 *
 * @since  2.3.0
 * @return string 	Job Id
 */
	public static function enqueueIn($in, $queue, $class, $args = array(), $trackStatus = null) {
		if (Configure::read('CakeResque.Scheduler.enabled') !== true) {
			return false;
		}

		if ($trackStatus === null) {
			$trackStatus = Configure::read('CakeResque.Job.track');
		}

		$r = ResqueScheduler\ResqueScheduler::enqueueIn($in, $queue, $class, $args, $trackStatus);

		if (!is_array($args)) {
			$args = array($args);
		}

		$caller = version_compare(PHP_VERSION, '5.4.0') >= 0
			? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)
			: debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

		self::$logs[$queue][] = array(
			'queue' => $queue,
			'class' => $class,
			'method' => array_shift($args),
			'args' => $args,
			'jobId' => $r,
			'caller' => $caller,
			'time' => time() + $in
		);

		return $r;
	}
}