<?php
/**
 * ResqueStatus File
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
 * @since         3.3.6
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * ResqueStatus Class
 *
 * Saving the workers status
 */
class ResqueStatus
{

	public $redis = null;

	public function __construct($redis) {
		$this->redis = $redis;
	}

/**
 * Save the workers arguments
 *
 * Used when restarting the worker
 */
	public function addWorker($args) {
		unset($args['debug']);
		$this->redis->rpush('ResqueWorker', serialize($args));
	}

/**
 * Register a Scheduler Worker
 *
 * @since  2.3.0
 * @return boolean True if a Scheduler worker is found among the list of active workers
 */
	public function registerSchedulerWorker() {
		$workers = Resque_Worker::all();
		foreach ($workers as $worker) {
			if (array_pop(explode(':', $worker)) === ResqueScheduler\ResqueScheduler::QUEUE_NAME) {
				$this->redis->set('ResqueSchedulerWorker', (string)$worker);
				return true;
			}
		}
		return false;
	}

/**
 * Test if a given worker is a scheduler worker
 *
 * @param 	Worker|string	$worker	Worker to test
 * @since 	2.3.0
 * @return 	boolean True if the worker is a scheduler worker
 */
	public function isSchedulerWorker($worker) {
		if (Configure::read('CakeResque.Scheduler.enabled') !== true) {
			return false;
		}

		return array_pop(explode(':', (string)$worker)) === ResqueScheduler\ResqueScheduler::QUEUE_NAME;
	}

/**
 * Check if the Scheduler Worker is already running
 * @param  boolean $check Check agains list of all active workers, in case the previous scheduler worker was not stopped properly
 * @return boolean        True if the scheduler worker is already running
 */
	public function isRunningSchedulerWorker($check = false) {
		if (isset($this->params['debug']) && $this->params['debug']) {
			$this->debug(__d('cake_resque', 'Checking if the scheduler worker is running'));
		}

		if ($check) {
			$this->unregisterSchedulerWorker();
			return $this->registerSchedulerWorker();
		}
		return $this->redis->exists('ResqueSchedulerWorker');
	}

/**
 * Unregister a Scheduler Worker
 *
 * @since  2.3.0
 * @return boolean True if the scheduler worker existed and was successfully unregistered
 */
	public function unregisterSchedulerWorker() {
		return $this->redis->del('ResqueSchedulerWorker') > 0;
	}

/**
 * Return all started workers arguments
 *
 * @return array An array of settings, by worker
 */
	public function getWorkers() {
		if (isset($this->params['debug']) && $this->params['debug']) {
			$this->debug(__d('cake_resque', 'Retrieving list of started workers'));
		}

		$listLength = $this->redis->llen('ResqueWorker');
		$workers = $this->redis->lrange('ResqueWorker', 0, $listLength - 1);

		if (isset($this->params['debug']) && $this->params['debug']) {
			$this->debug(__d('cake_resque', 'Found ' . count($workers) . ' started workers'));
		}

		$temp = array();
		foreach ($workers as $worker) {
			$temp[] = unserialize($worker);
		}
		return $temp;
	}

/**
 * Clear all workers saved arguments
 */
	public function clearWorker() {
		$this->redis->del('ResqueWorker');
		$this->redis->del('PausedWorker');
	}

/**
 * Mark a worker as paused
 *
 * @since 2.0.0
 * @param string $workerName Name of the paused worker
 */
	public function setPausedWorker($workerName) {
		$this->redis->sadd('PausedWorker', $workerName);
	}

/**
 * Mark a worker as active
 *
 * @since 2.0.0
 * @param string $workerName Name of the worker
 */
	public function setActiveWorker($workerName) {
		$this->redis->srem('PausedWorker', $workerName);
	}

/**
 * Return a list of paused workers
 *
 * @since 2.0.0
 * @return  array of workers name
 */
	public function getPausedWorker() {
		if (isset($this->params['debug']) && $this->params['debug']) {
			$this->debug(__d('cake_resque', 'Retrieving list of paused workers'));
		}

		$workers = (array)$this->redis->smembers('PausedWorker');

		if (isset($this->params['debug']) && $this->params['debug']) {
			$this->debug(__d('cake_resque', 'Found ' . count($workers) . ' paused workers'));
		}

		return $workers;
	}

}