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
class ResqueStatus
{

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
 * @return  boolean True if the worker was saved
 */
	public function addWorker($args) {
		unset($args['debug']);
		return $this->_redis->rpush(self::$workerStatusPrefix, serialize($args)) > 0;
	}

/**
 * Register a Scheduler Worker
 *
 * @since  	2.3.0
 * @params 	array 	$workers 	List of active workers
 * @return 	boolean 			True if a Scheduler worker is found among the list of active workers
 */
	public function registerSchedulerWorker($workers) {
		foreach ($workers as $worker) {
			$tokens = explode(':', (string)$worker);
			if (array_pop($tokens) === ResqueScheduler\ResqueScheduler::QUEUE_NAME) {
				$this->_redis->set(self::$schedulerWorkerStatusPrefix, (string)$worker);
				return true;
			}
		}
		return false;
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
 * @param  boolean $check Check agains list of all active workers, in case the previous scheduler worker was not stopped properly
 * @return boolean        True if the scheduler worker is already running
 */
	public function isRunningSchedulerWorker($check = false) {
		return $this->_redis->exists(self::$schedulerWorkerStatusPrefix);
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
		$listLength = $this->_redis->llen(self::$workerStatusPrefix);
		$workers = $this->_redis->lrange(self::$workerStatusPrefix, 0, $listLength - 1);

		$temp = array();
		foreach ($workers as $worker) {
			$temp[] = unserialize($worker);
		}
		return $temp;
	}

/**
 * Clear all workers saved arguments
 */
	public function clearWorkers() {
		$this->_redis->del(self::$workerStatusPrefix);
		$this->_redis->del(self::$pausedWorkerKeyPrefix);
	}

/**
 * Mark a worker as paused
 *
 * @since 2.0.0
 * @param string $workerName Name of the paused worker
 */
	public function setPausedWorker($workerName) {
		$this->_redis->sadd(self::$pausedWorkerKeyPrefix, $workerName);
	}

/**
 * Mark a worker as active
 *
 * @since 2.0.0
 * @param string $workerName Name of the worker
 */
	public function setActiveWorker($workerName) {
		$this->_redis->srem(self::$pausedWorkerKeyPrefix, $workerName);
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