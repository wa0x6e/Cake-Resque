<?php
/**
 * CakeResque Shell File
 *
 * Use to manage the workers via CLI
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
 * @subpackage	  CakeResque.Console.Command
 * @since         0.5
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

class CakeResqueShell extends Shell {

	public $uses = array();

/**
 * Absolute path to the php-resque library
 */
	protected $_resqueLibrary = null;

/**
 * Runtime arguments
 */
	protected $_runtime = array();

/**
 * Plugin version
 */
	const VERSION = '2.0.0';

/**
 * Startup callback.
 *
 * Initializes defaults.
 */
	public function startup() {
		if (substr(Configure::read('CakeResque.Resque.lib'), 0, 1) === '/') {
			$this->_resqueLibrary = Configure::read('CakeResque.Resque.lib') . DS;
		} else {
			$this->_resqueLibrary = realpath(App::pluginPath('CakeResque') . 'vendor' . DS . Configure::read('CakeResque.Resque.lib')) . DS;
		}

		require_once $this->_resqueLibrary . 'lib' . DS . 'Resque.php';
		require_once $this->_resqueLibrary . 'lib' . DS . 'Resque' . DS . 'Stat.php';
		require_once $this->_resqueLibrary . 'lib' . DS . 'Resque' . DS . 'Worker.php';

		$this->stdout->styles('success', array('text' => 'green'));
	}

	public function getOptionParser() {
		$startParserArguments = array(
			'options' => array(
				'user' => array(
					'short' => 'u',
					'help' => __d('resque_console', 'User running the workers')
				),
				'queue' => array(
					'short' => 'q',
					'help' => __d('resque_console', 'Name of the queue. If multiple queues, separe with comma.')
				),
				'interval' => array(
					'short' => 'i',
					'help' => __d('resque_console', 'Pause time in seconds between each works')
				),
				'workers' => array(
					'short' => 'n',
					'help' => __d('resque_console', 'Number of workers to fork')
				),
				'log' => array(
					'short' => 'l',
					'help' => __d('resque_console', 'Log path')
				),
				'log-handler' => array(
					'help' => __d('resque_console', 'Log Handler to use for logging.')
				),
				'log-handler-target' => array(
					'help' => __d('resque_console', 'Log Handler arguments')
				)
			)
		);

		$stopParserArguments = array(
			'options' => array(
				'force' => array(
					'short' => 'f',
					'help' => __d('resque_console', 'Force workers shutdown, forcing all the current jobs to finish (and fail)'),
					'boolean' => true
				),
				'all' => array(
					'short' => 'a',
					'help' => __d('resque_console', 'shutdown all workers'),
					'boolean' => true
				)
			),
			'description' => array(
				__d('resque_console', 'Stop one or all workers'),
				__d('resque_console', 'Unless you force the stop with the --force option,'),
				__d('resque_console', 'the worker will wait for all jobs to complete'),
				__d('resque_console', 'before shutting down')
			)
		);

		$pauseParserArguments = array(
			'options' => array(
				'all' => array(
					'short' => 'a',
					'help' => __d('resque_console', 'pause all workers'),
					'boolean' => true
				)
			),
			'description' => array(
				__d('resque_console', 'Pause one or all workers'),
				__d('resque_console', 'Pausing is only supported on Unix system,'),
				__d('resque_console', 'with PHP pcntl extension installed')
			)
		);

		$resumeParserArguments = array(
			'options' => array(
				'all' => array(
					'short' => 'a',
					'help' => __d('resque_console', 'resume all paused workers'),
					'boolean' => true
				)
			),
			'description' => array(
				__d('resque_console', 'Resume one or all paused workers'),
				__d('resque_console', 'Resuming is only supported on Unix system,'),
				__d('resque_console', 'with PHP pcntl extension installed')
			)
		);

		$cleanupParserArguments = array(
			'options' => array(
				'all' => array(
					'short' => 'a',
					'help' => __d('resque_console', 'Clean up all workers'),
					'boolean' => true
				)
			),
			'description' => array(
				__d('resque_console', 'Cleaning Up one or all paused workers'),
				__d('resque_console', 'Cleaning Up will immedately terminate the job'),
				__d('resque_console', 'the worker is currently working on.'),
				__d('resque_console', 'Resuming is only supported on Unix system,'),
				__d('resque_console', 'with PHP pcntl extension installed')
			)
		);

		return parent::getOptionParser()
			->description(__d('resque_console', "A Shell to manage PHP Resque.\n"))
			->addSubcommand('start', array(
				'help' => __d('resque_console', 'Start a new Resque worker.'),
				'parser' => $startParserArguments
			))
			->addSubcommand('stop', array(
				'help' => __d('resque_console', 'Stop a resque worker.'),
				'parser' => $stopParserArguments
			))
			->addSubcommand('pause', array(
				'help' => __d('resque_console', 'Pause a resque worker.'),
				'parser' => $pauseParserArguments
			))
			->addSubcommand('resume', array(
				'help' => __d('resque_console', 'Resume a paused resque worker.'),
				'parser' => $resumeParserArguments
			))
			->addSubcommand('cleanup', array(
				'help' => __d('resque_console', 'Immediately terminate a worker job execution.'),
				'parser' => $cleanupParserArguments
			))
			->addSubcommand('restart', array(
				'help' => __d('resque_console', 'Stop all Resque workers, and start a new one.'),
				'parser' => array_merge_recursive($startParserArguments, $stopParserArguments)
			))
			->addSubcommand('stats', array(
				'help' => __d('resque_console', 'View stats about processed/failed jobs.')
			))
			->addSubcommand('tail', array(
				'help' => __d('resque_console', 'Tail the workers logs.')
			))
			->addSubcommand('track', array(
				'help' => __d('resque_console', 'Track a job status.')
			))
			->addSubcommand('load', array(
				'help' => __d('resque_console', 'Load a set of predefined workers.')
		));
	}

/**
 * Enqueue a job via CLI.
 */
	public function enqueue() {
		$this->out('<info>Adding a job to worker</info>');
		if (count($this->args) < 3) {
			$this->err('<error>Wrong number of arguments</error>');
			$this->out('Usage : enqueue <queue> <jobclass> <comma-separated-args>', 2);
			return false;
		}

		$jobQueue = $this->args[0];
		$jobClass = $this->args[1];
		$params = explode(',', $this->args[2]);

		$result = CakeResque::enqueue($jobQueue, $jobClass, $params);
		$this->out('<success>Succesfully enqueued Job #' . $result . '</success>');

		$this->out("");
	}

/**
 * Monitor the content of a log file onscreen
 *
 * Ask user to choose from a list of available log file,
 * if there's more than one, and display all new content
 * onscreen
 * This will only search for log file created by resque,
 * and the RotatingFile created by log-handler
 */
	public function tail() {
		$logs = array();
		$i = 1;
		$workers = (array)$this->__getWorkers();

		foreach ($workers as $worker) {
			if ($worker['log'] != '') {
				$logs[] = $worker['log'];
			}
			if ($worker['Log']['handler'] == 'RotatingFile') {
				$fileInfo = pathinfo($worker['Log']['target']);
				$pattern = $fileInfo['dirname'] . DS . $fileInfo['filename'] . '-*' . (!empty($fileInfo['extension']) ? '.' . $fileInfo['extension'] : '');

				$logs = array_merge($logs, glob($pattern));
			}
		}

		$logs = array_values(array_unique($logs));

		$this->out('<info>Tailing log file</info>');
		if (empty($logs)) {
			$this->err('    No log file to tail', 2);
			return;
		} elseif (count($logs) == 1) {
			$index = 1;
		} else {
			foreach ($logs as $log) {
				$this->out(sprintf('    [%2d] - %s', $i++, $log));
			}

			$index = $this->in('Choose a log file to tail :', range(1, $i - 1));
		}

		$this->out('<warning>Tailing ' . $logs[$index - 1] . '</warning>');
		passthru('tail -f ' . escapeshellarg($logs[$index - 1]));
	}

/**
 * Create a new worker
 *
 * @param array $args If present, start the worker with these args.
 * @param bool $new Whether the worker is new, or from a restart
 */
	public function start($args = null, $new = true) {
		if ($args === null) {
			$this->out('<info>Creating workers</info>');
		}

		if (!$this->__validate($args)) return;

		if (file_exists(APP . 'Lib' . DS . 'CakeResqueBootstrap.php')) {
			$bootstrapPath = APP . 'Lib' . DS . 'CakeResqueBootstrap.php';
		} else {
			$bootstrapPath = App::pluginPath('CakeResque') . 'Lib' . DS . 'CakeResqueBootstrap.php';
		}

		$envVars = array();
		$vars = Configure::read('CakeResque.Env');
		foreach ($vars as $key => $val) {
			if (is_int($key) && isset($_SERVER[$val])) {
				$envVars[] = sprintf("%s=%s", $val, escapeshellarg($_SERVER[$val]));
			} else {
				$envVars[] = sprintf("%s=%s", $key, escapeshellarg($val));
			}
		}

		$cmd = implode(' ', array(
			sprintf("nohup sudo -u %s", $this->_runtime['user']),
			sprintf('bash -c "cd %s;', escapeshellarg($this->_resqueLibrary)),
			implode(' ', $envVars),
			sprintf("VVERBOSE=true QUEUE=%s ", escapeshellarg($this->_runtime['queue'])),
			sprintf("APP_INCLUDE=%s INTERVAL=%s ", escapeshellarg($bootstrapPath), $this->_runtime['interval']),
			sprintf("REDIS_BACKEND=%s ", escapeshellarg(Configure::read('CakeResque.Redis.host') . ':' . Configure::read('CakeResque.Redis.port'))),
			sprintf("REDIS_DATABASE=%s REDIS_NAMESPACE=%s", Configure::read('CakeResque.Redis.database'), escapeshellarg(Configure::read('CakeResque.Redis.namespace'))),
			sprintf("CAKE=%s COUNT=%s ", escapeshellarg(CAKE), $this->_runtime['workers']),
			sprintf("LOGHANDLER=%s LOGHANDLERTARGET=%s ", escapeshellarg($this->_runtime['Log']['handler']), escapeshellarg($this->_runtime['Log']['target'])),
			sprintf("php ./resque.php >> %s", escapeshellarg($this->_runtime['log'])),
			'2>&1" >/dev/null 2>&1 &'
		));

		$workersCountBefore = Resque::Redis()->scard('workers');
		passthru($cmd);

		$this->out("Starting worker ", 0);
		for ($i = 0; $i < 3;$i++) {
			$this->out(".", 0);
			usleep(150000);
		}

		$workersCountAfter = Resque::Redis()->scard('workers');
		if (($workersCountBefore + $this->_runtime['workers']) == $workersCountAfter) {
			if ($args === null || $new === true) {
				$this->__addWorker($this->_runtime);
			}
			$this->out(' <success>Done</success>' . (($this->_runtime['workers'] == 1) ? '' : ' x' . $this->_runtime['workers']));
		} else {
			$this->out(' <error>Fail</error>');
		}

		if ($args === null) {
			$this->out("");
		}
	}

/**
 * Stop worker
 *
 * Will ask the user to choose the worker to stop, from a list of worker,
 * if more than one worker is running, or if --all is not passed
 *
 * @param bool $shutdown Whether to force shutdown, or wait for all the jobs to finish first
 * @param bool $all True to directly stop all workers, false will ask the user
 * for the worker to stop, from a list
 */
	public function stop($shutdown = true, $all = false) {
		App::uses('CakeTime', 'Utility');
		$this->out('<info>Stopping workers</info>');
		$workers = Resque_Worker::all();
		if (empty($workers)) {
			$this->out('   There is no active workers to kill ...');
		} else {

			$workerIndex = array();
			if (!$this->params['all'] && !$all && count($workers) > 1) {
				$this->out("Active workers list :");
				$i = 1;
				foreach ($workers as $worker) {
					$this->out(sprintf("    [%2d] - %s, started %s", $i++, $worker,
						CakeTime::timeAgoInWords(Resque::Redis()->get('worker:' . $worker . ':started'))));
				}

				$options = range(1, $i - 1);

				if ($i > 2) {
					$this->out('    [all] - Stop all workers');
					$options[] = 'all';
				}

				$in = $this->in("Worker to kill : ", $options);
				if ($in == 'all') {
					$workerIndex = range(1, count($workers));
				} else {
					$workerIndex[] = $in;
				}

			} else {
				$workerIndex = range(1, count($workers));
			}

			foreach ($workerIndex as $index) {

				$worker = $workers[$index - 1];

				list($hostname, $pid, $queue) = explode(':', (string)$worker);
				$this->out('Killing ' . $pid . ' ... ', 0);
				$this->params['force'] ? $worker->shutDownNow() : $worker->shutDown();	// Send signal to stop processing jobs
				$worker->unregisterWorker();											// Remove jobs from resque environment

				$output = array();
				$message = exec('kill -9 ' . $pid . ' 2>&1', $output, $code);	// Kill all remaining system process

				if ($code == 0) {
					$this->out('<success>Done</success>');
				} else {
					$this->out('<error>' . $message . '</error>');
				}
			}
		}

		if ($shutdown) $this->__clearWorker();
		$this->out("");
	}

/**
 * Clean up worker
 *
 * On supported system, will ask the user to choose the worker to clean up, from a list of worker,
 * if more than one worker is running, or if --all is not passed
 *
 * Clean up will immediately terminate a worker child. Job is left unfinished.
 *
 * @since 2.0.0
 */
	public function cleanup() {
		if (!function_exists('pcntl_signal')) {
			return $this->out("<error>Cleaning up worker is not supported on your system. \nPlease install the PCNTL extension</error>");
		}

		App::uses('CakeTime', 'Utility');
		$this->out('<info>Cleaning up workers</info>');
		$workers = Resque_Worker::all();
		if (empty($workers)) {
			$this->out('   There is no active workers.');
		} else {

			$workerIndex = array();
			if (!$this->params['all'] && count($workers) > 1) {
				$this->out("Active workers list :");
				$i = 1;
				foreach ($workers as $worker) {
					$this->out(sprintf("    [%2d] - %s, started %s", $i++, $worker,
						CakeTime::timeAgoInWords(Resque::Redis()->get('worker:' . $worker . ':started'))));
				}

				$options = range(1, $i - 1);

				if ($i > 2) {
					$this->out('    [all] - Cleanup all workers');
					$options[] = 'all';
				}

				$in = $this->in("Worker to cleanup : ", $options);
				if ($in == 'all') {
					$workerIndex = range(1, count($workers));
				} else {
					$workerIndex[] = $in;
				}

			} else {
				$workerIndex = range(1, count($workers));
			}

			foreach ($workerIndex as $index) {

				$worker = $workers[$index - 1];

				list($hostname, $pid, $queue) = explode(':', (string)$worker);
				$this->out('Cleaning up ' . $pid . ' ... ', 0);

				$output = array();
				$message = exec('kill -USR1 ' . $pid . ' 2>&1', $output, $code);

				if ($code == 0) {
					$this->out('<success>Done</success>');
				} else {
					$this->out('<error>' . $message . '</error>');
				}
			}
		}
		$this->out("");
	}

/**
 * Pause worker
 *
 * On supported system, will ask the user to choose the worker to pause, from a list of worker,
 * if more than one worker is running, or if --all is not passed
 *
 * @since 2.0.0
 */
	public function pause() {
		if (!function_exists('pcntl_signal')) {
			return $this->out("<error>Pausing worker is not supported on your system. \nPlease install the PCNTL extension</error>");
		}

		App::uses('CakeTime', 'Utility');
		$this->out('<info>Pausing workers</info>');
		$workers = Resque_Worker::all();

		$pausedWorkers = $this->__getPausedWorker();
		if (count($pausedWorkers) > 0) {
			for ($i = count($workers) - 1; $i >= 0; $i--) {
				if (in_array((string)$workers[$i], $pausedWorkers)) {
					unset($workers[$i]);
				}
			}
			$workers = array_values($workers);
		}

		if (empty($workers)) {
			$this->out('   There is no active workers to pause ...');
		} else {

			$workerIndex = array();
			if (!$this->params['all'] && count($workers) > 1) {
				$this->out("Active workers list :");
				$i = 1;
				foreach ($workers as $worker) {
					$this->out(sprintf("    [%2d] - %s, started %s", $i++, $worker,
						CakeTime::timeAgoInWords(Resque::Redis()->get('worker:' . $worker . ':started'))));
				}

				$options = range(1, $i - 1);

				if ($i > 2) {
					$this->out('    [all] - Pause all workers');
					$options[] = 'all';
				}

				$in = $this->in("Worker to pause : ", $options);
				if ($in == 'all') {
					$workerIndex = range(1, count($workers));
				} else {
					$workerIndex[] = $in;
				}

			} else {
				$workerIndex = range(1, count($workers));
			}

			foreach ($workerIndex as $index) {

				$worker = $workers[$index - 1];

				list($hostname, $pid, $queue) = explode(':', (string)$worker);
				$this->out('Pausing ' . $pid . ' ... ', 0);

				$output = array();
				$message = exec('kill -USR2 ' . $pid . ' 2>&1', $output, $code);

				if ($code == 0) {
					$this->out('<success>Done</success>');
					$this->__setPausedWorker((string)$worker);
				} else {
					$this->out('<error>' . $message . '</error>');
				}
			}
		}

		$this->out("");
	}

/**
 * Resume paused worker
 *
 * On supported system, will ask the user to choose the worker to resume, from a list of worker,
 * if more than one worker is running, or if --all is not passed
 *
 * @since 2.0.0
 */
	public function resume() {
		if (!function_exists('pcntl_signal')) {
			return $this->out("<error>Pausing worker is not supported on your system. \nPlease install the PCNTL extension</error>");
		}

		App::uses('CakeTime', 'Utility');
		$this->out('<info>Resuming workers</info>');
		$workers = $this->__getPausedWorker();

		if (empty($workers)) {
			$this->out('   There is no paused workers to resume ...');
		} else {

			$workerIndex = array();
			if (!$this->params['all'] && count($workers) > 1) {
				$this->out("Paused workers list :");
				$i = 1;
				foreach ($workers as $worker) {
					$this->out(sprintf("    [%2d] - %s, started %s", $i++, $worker,
						CakeTime::timeAgoInWords(Resque::Redis()->get('worker:' . $worker . ':started'))));
				}

				$options = range(1, $i - 1);

				if ($i > 2) {
					$this->out('    [all] - Resume all workers');
					$options[] = 'all';
				}

				$in = $this->in("Worker to resume : ", $options);
				if ($in == 'all') {
					$workerIndex = range(1, count($workers));
				} else {
					$workerIndex[] = $in;
				}

			} else {
				$workerIndex = range(1, count($workers));
			}

			foreach ($workerIndex as $index) {

				$worker = $workers[$index - 1];

				list($hostname, $pid, $queue) = explode(':', (string)$worker);
				$this->out('Resuming ' . $pid . ' ... ', 0);

				$output = array();
				$message = exec('kill -CONT ' . $pid . ' 2>&1', $output, $code);

				if ($code == 0) {
					$this->out('<success>Done</success>');
					$this->__setActiveWorker((string)$worker);
				} else {
					$this->out('<error>' . $message . '</error>');
				}
			}
		}

		$this->out("");
	}

/**
 * Start a list of predefined workers
 */
	public function load() {
		$this->out('<info>Loading predefined workers</info>');
		if (Configure::read('CakeResque.Queues') == null) {
			$this->out('   You have no configured queues to load.');
		} else {
			foreach (Configure::read('CakeResque.Queues') as $queue) {
				$this->start($queue);
			}
		}

		$this->out("");
	}

/**
 * Restart all workers
 */
	public function restart() {
		$this->stop(false, true);

		$this->out('<info>Restarting workers</info>');
		if (false !== $workers = $this->__getWorkers()) {
			foreach ($workers as $worker) {
				$this->start($worker, false);
			}
			$this->out("");
		} else {
			$this->out('<warning>No active workers found, will start brand new worker</warning>');
			$this->start();
		}
	}

	public function stats() {
		$this->out("\n");
		$this->out('<info>Resque Statistics</info>');
		$this->hr();
		$this->out("\n");
		$this->out('<info>Jobs Stats</info>');
		$this->out("   Processed Jobs : " . Resque_Stat::get('processed'));
		$this->out("   <warning>Failed Jobs    : " . Resque_Stat::get('failed') . "</warning>");
		$this->out("\n");
		$this->out('<info>Workers Stats</info>');
		$workers = Resque_Worker::all();
		$this->out("   Workers count : " . count($workers));

		$pausedWorkers = $this->__getPausedWorker();

		if (!empty($workers)) {
			foreach ($workers as $worker) {
				$this->out("\t" . $worker . (in_array((string)$worker, $pausedWorkers) ? " <warning>(paused)</warning>" : ''));
				$this->out("\t   - Started on     : " . Resque::Redis()->get('worker:' . $worker . ':started'));
				$this->out("\t   - Processed Jobs : " . $worker->getStat('processed'));
				$worker->getStat('failed') == 0
					? $this->out("\t   - Failed Jobs    : " . $worker->getStat('failed'))
					: $this->out("\t   - <warning>Failed Jobs    : " . $worker->getStat('failed') . "</warning>");
			}
		}

		$this->out("\n");
	}

/**
 * Track a job status
 *
 * @since 2.1.0
 */
	public function track() {
		$this->out('<info>Tracking job status</info>');

		if (isset($this->args[0])) {
			$jobId = $this->args[0];
		} else {
			return $this->out('<error>Please provide a valid job ID</error>');
		}

		$status = new Resque_Job_Status($jobId);
		$jobStatus = $status->get();

		if ($jobStatus === false) {
			$this->out('Status : <warning>Unknown</warning>');
		} else {

			$statusName = array(
				Resque_Job_Status::STATUS_WAITING => __d('resque_console', 'waiting'),
				Resque_Job_Status::STATUS_RUNNING => __d('resque_console', 'running'),
				Resque_Job_Status::STATUS_FAILED => __d('resque_console', 'failed'),
				Resque_Job_Status::STATUS_COMPLETE => __d('resque_console', 'complete')
			);

			$statusClass = array(
				Resque_Job_Status::STATUS_WAITING => 'info',
				Resque_Job_Status::STATUS_RUNNING => 'info',
				Resque_Job_Status::STATUS_FAILED => 'error',
				Resque_Job_Status::STATUS_COMPLETE => 'success'
			);

			$this->out(sprintf('Status : <%1$s>%2$s</%1$s>', $statusClass[$jobStatus], $statusName[$jobStatus]));
		}
		$this->out("");
	}

/**
 * Save the workers arguments
 *
 * Used when restarting the worker
 */
	private function __addWorker($args) {
		Resque::Redis()->rpush('ResqueWorker', serialize($args));
	}

/**
 * Return all started workers arguments
 *
 * @return array An array of settings, by worker
 */
	private function __getWorkers() {
		$listLength = Resque::Redis()->llen('ResqueWorker');
		$workers = Resque::Redis()->lrange('ResqueWorker', 0, $listLength - 1);
		if (empty($workers)) {
			return false;
		} else {
			$temp = array();
			foreach ($workers as $worker) {
				$temp[] = unserialize($worker);
			}
			return $temp;
		}
	}

/**
 * Clear all workers saved arguments
 */
	private function __clearWorker() {
		Resque::Redis()->del('ResqueWorker');
		Resque::Redis()->del('PausedWorker');
	}

/**
 * Mark a worker as paused
 *
 * @since 2.0.0
 * @param string $workerName Name of the paused worker
 */
	private function __setPausedWorker($workerName) {
		Resque::Redis()->sadd('PausedWorker', $workerName);
	}

/**
 * Mark a worker as active
 *
 * @since 2.0.0
 * @param string $workerName Name of the worker
 */
	private function __setActiveWorker($workerName) {
		Resque::Redis()->srem('PausedWorker', $workerName);
	}

/**
 * Return a list of paused workers
 *
 * @since 2.0.0
 * @return  array of workers name
 */
	private function __getPausedWorker() {
		return (array)Resque::Redis()->smembers('PausedWorker');
	}

/**
 * Validate command line options
 * And print the errors
 *
 * @since 1.0
 * @return true if all options are valid
 */
	private function __validate($args = null) {
		$this->_runtime = ($args === null) ? $this->params : $args;

		$errors = array();

		// Validate Log path
		$this->_runtime['log'] = isset($this->_runtime['log']) ? $this->_runtime['log'] : Configure::read('CakeResque.Worker.log');
		if (substr($this->_runtime['log'], 0, 2) == './') {
			$this->_runtime['log'] = TMP . 'logs' . DS . substr($this->_runtime['log'], 2);
		} elseif (substr($this->_runtime['log'], 0, 1) != '/') {
			$this->_runtime['log'] = TMP . 'logs' . DS . $this->_runtime['log'];
		}

		// Validate Interval
		$this->_runtime['interval'] = isset($this->_runtime['interval']) ? $this->_runtime['interval'] : Configure::read('CakeResque.Worker.interval');
		if (!is_numeric($this->_runtime['interval'])) {
			$errors[] = __d('resque_console', 'Interval time [%s] is not valid. Please enter a valid number', $this->_runtime['interval']);
		} else {
			$this->_runtime['interval'] = (int)$this->_runtime['interval'];
		}

		// Validate workers number
		$this->_runtime['workers'] = isset($this->_runtime['workers']) ? $this->_runtime['workers'] : Configure::read('CakeResque.Worker.workers');
		if (!is_numeric($this->_runtime['workers'])) {
			$errors[] = __d('resque_console', 'Workers number [%s] is not valid. Please enter a valid number', $this->_runtime['workers']);
		} else {
			$this->_runtime['workers'] = (int)$this->_runtime['workers'];
		}

		$this->_runtime['queue'] = isset($this->_runtime['queue']) ? $this->_runtime['queue'] : Configure::read('CakeResque.Worker.queue');

		$this->_runtime['user'] = isset($this->_runtime['user']) ? $this->_runtime['user'] : get_current_user();

		$output = array();
		exec('id ' . $this->_runtime['user'] . ' 2>&1', $output, $status);
		if ($status != 0) {
			$errors[] = __d('resque_console', 'User [%s] does not exists. Please enter a valid system user', $this->_runtime['user']);
		}

		$this->_runtime['Log']['handler'] = isset($this->_runtime['log-handler']) ? $this->_runtime['log-handler'] : Configure::read('CakeResque.Log.handler');

		$this->_runtime['Log']['target'] = isset($this->_runtime['log-handler-target']) ? $this->_runtime['log-handler-target'] : Configure::read('CakeResque.Log.target');
		if (substr($this->_runtime['Log']['target'], 0, 2) == './') {
			$this->_runtime['Log']['target'] = TMP . 'logs' . DS . substr($this->_runtime['Log']['target'], 2);
		}

		if (!empty($errors)) {
			foreach ($errors as $error) {
				$this->err('<error>Error:</error> ' . $error);
			}
			$this->out();
		}
		return empty($errors);
	}

}
