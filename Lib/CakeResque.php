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

if (substr(Configure::read('CakeResque.Resque.lib'), 0, 1) === '/') {
	require_once Configure::read('CakeResque.Resque.lib') . DS . 'lib' . DS . 'Resque.php';
	require_once Configure::read('CakeResque.Resque.lib') . DS . 'lib' . DS . 'Resque' . DS . 'Worker.php';
} else {
	require_once realpath(App::pluginPath('CakeResque') . 'vendor' . DS . Configure::read('CakeResque.Resque.lib') . DS . 'lib' . DS . 'Resque.php');
	require_once realpath(App::pluginPath('CakeResque') . 'vendor' . DS . Configure::read('CakeResque.Resque.lib') . DS . 'lib' . DS . 'Resque' . DS . 'Worker.php');
}


if (substr(Configure::read('CakeResque.Scheduler.lib'), 0, 1) === '/') {
	require_once Configure::read('CakeResque.Scheduler.lib') . DS . 'lib' . DS . 'ResqueScheduler' . DS . 'ResqueScheduler.php';
	require_once Configure::read('CakeResque.Scheduler.lib') . DS . 'lib' . DS . 'ResqueScheduler' . DS . 'Stat.php';
	require_once Configure::read('CakeResque.Scheduler.lib') . DS . 'lib' . DS . 'ResqueScheduler' . DS . 'Job' . DS . 'Status.php';
} else {
	require_once realpath(App::pluginPath('CakeResque') . 'vendor' . DS . Configure::read('CakeResque.Scheduler.lib') . DS . 'lib' . DS . 'ResqueScheduler' . DS . 'ResqueScheduler.php');
	require_once realpath(App::pluginPath('CakeResque') . 'vendor' . DS . Configure::read('CakeResque.Scheduler.lib') . DS . 'lib' . DS . 'ResqueScheduler' . DS . 'Stat.php');
	require_once realpath(App::pluginPath('CakeResque') . 'vendor' . DS . Configure::read('CakeResque.Scheduler.lib') . DS . 'lib' . DS . 'ResqueScheduler' . DS . 'Job' . DS . 'Status.php');
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

/**
 * Array containing all the queuing activity
 * @var array
 */
	public static $logs = array();

/**
 * Resque classname
 * @var string
 */
	public static $resqueClass = 'Resque';

/**
 * ResqueScheduler classname
 * @var string
 */
	public static $resqueSchedulerClass = 'ResqueScheduler\ResqueScheduler';

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

		if (!is_array($args)) {
			$args = array($args);
		}

		$r = call_user_func_array(self::$resqueClass . '::enqueue', array_merge(array($queue), array($class), array($args), array($trackStatus)));

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

		if (!is_array($args)) {
			$args = array($args);
		}

		$r = call_user_func_array(self::$resqueSchedulerClass . '::enqueueAt', array_merge(array($at), array($queue), array($class), array($args), array($trackStatus)));

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

		if (!is_array($args)) {
			$args = array($args);
		}

		$r = call_user_func_array(self::$resqueSchedulerClass . '::enqueueIn', array_merge(array($in), array($queue), array($class), array($args), array($trackStatus)));

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

/**
 * @codeCoverageIgnore
 * @param  [type] $jobId [description]
 * @return [type]        [description]
 */
	public static function getJobStatus($jobId) {
		$status = new Resque_Job_Status($jobId);
		return $status->get();
	}

/**
 * @codeCoverageIgnore
 * @param  [type] $jobId [description]
 * @return [type]        [description]
 */
	public static function getFailedJobLog($jobId) {
		return Resque_Failure_Redis::get($jobId);
	}

/**
 * @codeCoverageIgnore
 * @return [type] [description]
 */
	public static function getWorkers() {
		return (array)Resque_Worker::all();
	}

/**
 * @codeCoverageIgnore
 * @return [type] [description]
 */
	public static function getQueues() {
		return Resque::Redis()->smembers('queues');
	}

/**
 * Clear all the queue jobs'
 *
 * @codeCoverageIgnore
 * @param  String 	$queue 	Name of the queue to empty
 * @return bool 	False if clearing the queue fail
 */
	public static function clearQueue($queue) {
		return Resque::Redis()->ltrim('queue:' . $queue, 1, 0) !== false;
	}

/**
 * Return the number of jobs inside a queue
 *
 * @codeCoverageIgnore
 * @param  String 	$queue 	Queue name
 * @return int 				Number of jobs
 */
	public static function getQueueLength($queue) {
		return Resque::Redis()->llen('queue:' . $queue);
	}

/**
 * @codeCoverageIgnore
 * @param  [type] $worker [description]
 * @return [type]         [description]
 */
	public static function getWorkerStartDate($worker) {
		return Resque::Redis()->get('worker:' . $worker . ':started');
	}
}
