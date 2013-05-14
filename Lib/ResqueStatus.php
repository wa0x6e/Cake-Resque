<?php
/**
 * ResqueStatus File
 *
 * Saving the workers statuses
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
 * @since         3.3.6
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * ResqueStatus Class
 *
 * Saving the workers statuses
 */
class ResqueStatus {

	public static $workerStatusPrefix = 'ResqueWorker';

	public static $schedulerWorkerStatusPrefix = 'ResqueSchedulerWorker';

	public static $pausedWorkerKeyPrefix = 'PausedWorker';

/**
 * Redis instance
 * @var Resque_Redis|Redis
 */
	protected $_redis = null;

	public function __construct($redis) {
		$this->_redis = $redis;
	}

/**
 * Save the workers arguments
 *
 * Used when restarting the worker
 *
 * @param  array $args Worker settings
 */
	public function addWorker($pid, $args) {
		return $this->_redis->hSet(self::$workerStatusPrefix, $pid, serialize($args)) !== false;
	}

/**
 * Register a Scheduler Worker
 *
 * @since  	2.3.0
 * @params 	array 	$workers 	List of active workers
 * @return 	boolean 			True if a Scheduler worker is found among the list of active workers
 */
	public function registerSchedulerWorker($pid) {
		return $this->_redis->set(self::$schedulerWorkerStatusPrefix, $pid);
	}

/**
 * Test if a given worker is a scheduler worker
 *
 * @since 	2.3.0
 * @param 	Worker|string	$worker	Worker to test
 * @return 	boolean 				True if the worker is a scheduler worker
 */
	public function isSchedulerWorker($worker) {
		$tokens = explode(':', (string)$worker);
		return array_pop($tokens) === ResqueScheduler\ResqueScheduler::QUEUE_NAME;
	}

/**
 * Check if the Scheduler Worker is already running
 *
 * @return boolean        True if the scheduler worker is already running
 */
	public function isRunningSchedulerWorker() {
		$pids = $this->_redis->hKeys(self::$workerStatusPrefix);
		$schedulerPid = $this->_redis->exists(self::$schedulerWorkerStatusPrefix);

		if ($schedulerPid !== false) {
			if (in_array($schedulerPid, $pids)) {
				return true;
			}
			// Pid is outdated, remove it
			$this->unregisterSchedulerWorker();
			return false;
		}
		return false;
	}

/**
 * Unregister a Scheduler Worker
 *
 * @since  2.3.0
 * @return boolean True if the scheduler worker existed and was successfully unregistered
 */
	public function unregisterSchedulerWorker() {
		return $this->_redis->del(self::$schedulerWorkerStatusPrefix) > 0;
	}

/**
 * Return all started workers arguments
 *
 * @return array An array of settings, by worker
 */
	public function getWorkers() {
		$workers = $this->_redis->hGetAll(self::$workerStatusPrefix);
		$temp = array();

		foreach ($workers as $name => $value) {
			$temp[$name] = unserialize($value);
		}
		return $temp;
	}

/**
 *
 */
	public function removeWorker($pid) {
		$this->_redis->hDel(self::$workerStatusPrefix, $pid);
	}

/**
 * Clear all workers saved arguments
 */
	public function clearWorkers() {
		$this->_redis->del(self::$workerStatusPrefix);
		$this->_redis->del(self::$pausedWorkerKeyPrefix);
	}

/**
 * Mark a worker as paused/active
 *
 * @since 2.0.0
 * @param string 	$workerName Name of the paused worker
 * @param bool 		$paused 	Whether to mark the worker as paused or active
 */
	public function setPausedWorker($workerName, $paused = true) {
		if ($paused) {
			$this->_redis->sadd(self::$pausedWorkerKeyPrefix, $workerName);
		} else {
			$this->_redis->srem(self::$pausedWorkerKeyPrefix, $workerName);
		}
	}

/**
 * Return a list of paused workers
 *
 * @since 2.0.0
 * @return  array 	An array of paused workers' name
 */
	public function getPausedWorker() {
		return (array)$this->_redis->smembers(self::$pausedWorkerKeyPrefix);
	}

}
