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
 * CakeResque class, proxying the Resque library
 * @var string
 */
	public static $cakeResque = 'CakeResque';

/**
 * Plugin version
 */
	const VERSION = '3.3.5';

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

		if (substr(Configure::read('CakeResque.Scheduler.lib'), 0, 1) === '/') {
			$this->_ResqueSchedulerLibrary = Configure::read('CakeResque.Scheduler.lib') . DS;
		} else {
			$this->_ResqueSchedulerLibrary = realpath(App::pluginPath('CakeResque') . 'vendor' . DS . Configure::read('CakeResque.Scheduler.lib')) . DS;
		}

		App::uses('ResqueStatus', 'CakeResque.Lib');
		$this->ResqueStatus = new ResqueStatus(Resque::Redis());

		$this->stdout->styles('success', array('text' => 'green'));
		$this->stdout->styles('bold', array('bold' => true));
	}

	public function getOptionParser() {
		$startParserArguments = array(
			'options' => array(
				'user' => array(
					'short' => 'u',
					'help' => __d('cake_resque', 'User running the workers')
				),
				'queue' => array(
					'short' => 'q',
					'help' => __d('cake_resque', 'Name of the queue. If multiple queues, separe with comma.')
				),
				'interval' => array(
					'short' => 'i',
					'help' => __d('cake_resque', 'Pause time in seconds between each works')
				),
				'workers' => array(
					'short' => 'n',
					'help' => __d('cake_resque', 'Number of workers to fork')
				),
				'log' => array(
					'short' => 'l',
					'help' => __d('cake_resque', 'Log path')
				),
				'log-handler' => array(
					'help' => __d('cake_resque', 'Log Handler to use for logging.')
				),
				'log-handler-target' => array(
					'help' => __d('cake_resque', 'Log Handler arguments')
				),
				'verbose' => array(
					'short' => 'v',
					'help' => __d('cake_resque', 'Log more verbose informations'),
					'boolean' => true
				),
				'debug' => array(
					'short' => 'd',
					'help' => __d('cake_resque', 'Print debug informations'),
					'boolean' => true
				)
			)
		);

		$startSchedulerParserArguments = array(
			'options' => array(
				'user' => array(
					'short' => 'u',
					'help' => __d('cake_resque', 'User running the workers')
				),
				'interval' => array(
					'short' => 'i',
					'help' => __d('cake_resque', 'Pause time in seconds between each works')
				),
				'log' => array(
					'short' => 'l',
					'help' => __d('cake_resque', 'Log path')
				),
				'log-handler' => array(
					'help' => __d('cake_resque', 'Log Handler to use for logging.')
				),
				'log-handler-target' => array(
					'help' => __d('cake_resque', 'Log Handler arguments')
				),
				'verbose' => array(
					'short' => 'v',
					'help' => __d('cake_resque', 'Log more verbose informations'),
					'boolean' => true
				),
				'debug' => array(
					'short' => 'd',
					'help' => __d('cake_resque', 'Print debug informations'),
					'boolean' => true
				)
			)
		);

		$stopParserArguments = array(
			'options' => array(
				'force' => array(
					'short' => 'f',
					'help' => __d('cake_resque', 'Force workers shutdown, forcing all the current jobs to finish (and fail)'),
					'boolean' => true
				),
				'all' => array(
					'short' => 'a',
					'help' => __d('cake_resque', 'Shutdown all workers'),
					'boolean' => true
				)
			),
			'description' => array(
				__d('cake_resque', 'Stop one or all workers'),
				__d('cake_resque', 'Unless you force the stop with the --force option,'),
				__d('cake_resque', 'the worker will wait for all jobs to complete'),
				__d('cake_resque', 'before shutting down')
			)
		);

		$pauseParserArguments = array(
			'options' => array(
				'all' => array(
					'short' => 'a',
					'help' => __d('cake_resque', 'Pause all workers'),
					'boolean' => true
				),
				'debug' => array(
					'short' => 'd',
					'help' => __d('cake_resque', 'Print debug informations'),
					'boolean' => true
				)
			),
			'description' => array(
				__d('cake_resque', 'Pause one or all workers'),
				__d('cake_resque', 'Pausing is only supported on Unix system,'),
				__d('cake_resque', 'with PHP pcntl extension installed')
			)
		);

		$resumeParserArguments = array(
			'options' => array(
				'all' => array(
					'short' => 'a',
					'help' => __d('cake_resque', 'Resume all paused workers'),
					'boolean' => true
				),
				'debug' => array(
					'short' => 'd',
					'help' => __d('cake_resque', 'Print debug informations'),
					'boolean' => true
				)
			),
			'description' => array(
				__d('cake_resque', 'Resume one or all paused workers'),
				__d('cake_resque', 'Resuming is only supported on Unix system,'),
				__d('cake_resque', 'with PHP pcntl extension installed')
			)
		);

		$cleanupParserArguments = array(
			'options' => array(
				'all' => array(
					'short' => 'a',
					'help' => __d('cake_resque', 'Clean up all workers'),
					'boolean' => true
				),
				'debug' => array(
					'short' => 'd',
					'help' => __d('cake_resque', 'Print debug informations'),
					'boolean' => true
				)
			),
			'description' => array(
				__d('cake_resque', 'Cleaning Up one or all paused workers'),
				__d('cake_resque', 'Cleaning Up will immedately terminate the job'),
				__d('cake_resque', 'the worker is currently working on.'),
				__d('cake_resque', 'Resuming is only supported on Unix system,'),
				__d('cake_resque', 'with PHP pcntl extension installed')
			)
		);

		$clearParserArguments = array(
			'options' => array(
				'all' => array(
					'short' => 'a',
					'help' => __d('cake_resque', 'Clear all queues'),
					'boolean' => true
				),
				'debug' => array(
					'short' => 'd',
					'help' => __d('cake_resque', 'Print debug informations'),
					'boolean' => true
				)
			),
			'description' => array(
				__d('cake_resque', 'Clear one or all queues'),
				__d('cake_resque', 'Clearing a queue will remove all its jobs')
			)
		);

		$debugOptions = array(
			'options' => array(
				'debug' => array(
					'short' => 'd',
					'help' => __d('cake_resque', 'Print debug informations'),
					'boolean' => true
				)
			)
		);

		return parent::getOptionParser()
			->description(
				__d('cake_resque', "A Shell to manage PHP Resque") . "\n" .
				__d('cake_resque', "Version " . self::VERSION) . "\n" .
				__d('cake_resque', "Wan Chen (2013)")
				)
			->addSubcommand('start', array(
				'help' => __d('cake_resque', 'Start a new worker.'),
				'parser' => $startParserArguments
			))
			->addSubcommand('startscheduler', array(
				'help' => __d('cake_resque', 'Start a new scheduler worker.'),
				'parser' => $startSchedulerParserArguments
			))
			->addSubcommand('stop', array(
				'help' => __d('cake_resque', 'Stop a worker.'),
				'parser' => $stopParserArguments
			))
			->addSubcommand('pause', array(
				'help' => __d('cake_resque', 'Pause a worker.'),
				'parser' => $pauseParserArguments
			))
			->addSubcommand('resume', array(
				'help' => __d('cake_resque', 'Resume a paused worker.'),
				'parser' => $resumeParserArguments
			))
			->addSubcommand('cleanup', array(
				'help' => __d('cake_resque', 'Immediately terminate a worker job execution.'),
				'parser' => $cleanupParserArguments
			))
			->addSubcommand('restart', array(
				'help' => __d('cake_resque', 'Stop all Resque workers, and start a new one.'),
				'parser' => array_merge_recursive($startParserArguments, $stopParserArguments)
			))
			->addSubcommand('clear', array(
				'help' => __d('cake_resque', 'Clear all jobs inside a queue'),
				'parser' => $clearParserArguments
			))
			->addSubcommand('stats', array(
				'help' => __d('cake_resque', 'View stats about processed/failed jobs.'),
				'parser' => $debugOptions
			))
			->addSubcommand('tail', array(
				'help' => __d('cake_resque', 'Tail the workers logs.')
			))
			->addSubcommand('track', array(
				'help' => __d('cake_resque', 'Track a job status.')
			))
			->addSubcommand('load', array(
				'help' => __d('cake_resque', 'Load a set of predefined workers.'),
				'parser' => $debugOptions
		));
	}

/**
 * Enqueue a job via CLI.
 */
	public function enqueue() {
		$this->out('<info>' . __d('cake_resque', 'Adding a job to worker') . '</info>');
		if (count($this->args) !== 3) {
			$this->err('<error>' . __d('cake_resque', 'Wrong number of arguments') . '</error>');
			$this->out(__d('cake_resque', 'Usage : enqueue <queue> <jobclass> <comma-separated-args>'), 2);
			return false;
		}

		$result = call_user_func_array(
			self::$cakeResque . '::enqueue',
			array($this->args[0], $this->args[1], explode(',', $this->args[2]))
		);
		$this->out('<success>' . __d('cake_resque', 'Succesfully enqueued Job #%s', $result) . '</success>');

		$this->out('');
	}

/**
 * Enqueue a scheduled job via CLI.
 *
 * @since  2.3.0
 */
	public function enqueueIn() {
		$this->out('<info>' . __d('cake_resque', 'Scheduling a job') . '</info>');
		if (count($this->args) !== 4) {
			$this->err('<error>' . __d('cake_resque', 'Wrong number of arguments') . '</error>');
			$this->out(__d('cake_resque', 'Usage : enqueueIn <seconds> <queue> <jobclass> <comma-separated-args>'), 2);
			return false;
		}

		$result = call_user_func_array(
			self::$cakeResque . '::enqueueIn',
			array($this->args[0], $this->args[1], $this->args[2], explode(',', $this->args[3]), (isset($this->args[4]) ? (bool)$this->args[4] : false))
		);

		$this->out('<success>' . __d('cake_resque', 'Succesfully scheduled Job #%s', $result) . '</success>');

		$this->out('');
	}

/**
 * Enqueue a scheduled job via CLI.
 *
 * @since  2.3.0
 */
	public function enqueueAt() {
		$this->out('<info>' . __d('cake_resque', 'Scheduling a job') . '</info>');
		if (count($this->args) !== 4) {
			$this->err('<error>' . __d('cake_resque', 'Wrong number of arguments') . '</error>');
			$this->out(__d('cake_resque', 'Usage : enqueue <timestamp> <queue> <jobclass> <comma-separated-args>'), 2);
			return false;
		}

		$jobQueue = $this->args[1];
		$jobClass = $this->args[2];
		$params = explode(',', $this->args[3]);

		$result = call_user_func_array(
			self::$cakeResque . '::enqueueAt',
			array($this->args[0], $this->args[1], $this->args[2], explode(',', $this->args[3]), (isset($this->args[4]) ? (bool)$this->args[4] : false))
		);
		$this->out('<success>' . __d('cake_resque', 'Succesfully scheduled Job #%s', $result) . '</success>');

		$this->out('');
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
		$workers = $this->ResqueStatus->getWorkers();

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

		$this->out('<info>' . __d('cake_resque', 'Tailing log file') . '</info>');
		if (empty($logs)) {
			$this->out('    <error>' . __d('cake_resque', 'No log file to tail') . '</error>', 2);
			return;
		} elseif (count($logs) == 1) {
			$index = 1;
		} else {
			foreach ($logs as $log) {
				$this->out(sprintf('    [%3d] - %s', $i++, $log));
			}

			$index = $this->in(__d('cake_resque', 'Choose a log file to tail') . ':', range(1, $i - 1));
		}

		$this->out('<warning>' . __d('cake_resque', 'Tailing %s', $logs[$index - 1]) . '</warning>');
		$this->_tail($logs[$index - 1]);
	}

/**
 * Create a new worker
 *
 * @param array $args If present, start the worker with these args.
 * @param bool $new Whether the worker is new, or from a restart
 */
	public function start($args = null, $new = true) {
		if ($args === null) {
			$this->out('<info>' . __d('cake_resque', 'Creating workers') . '</info>');
		}

		if (!$this->_validate($args)) {
			return;
		}

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
			sprintf("nohup sudo -u %s \\\n", $this->_runtime['user']),
			sprintf("bash -c \"cd %s; \\\n", escapeshellarg($this->_resqueLibrary)),
			implode(' ', $envVars),
			" \\\n",
			sprintf("%sVERBOSE=true \\\n", $this->_runtime['verbose'] ? 'V' : ''),
			sprintf("QUEUE=%s \\\n", escapeshellarg($this->_runtime['queue'])),
			sprintf("APP_INCLUDE=%s \\\n", escapeshellarg($bootstrapPath)),
			sprintf("INTERVAL=%s \\\n", $this->_runtime['interval']),
			sprintf("REDIS_BACKEND=%s \\\n", escapeshellarg(Configure::read('CakeResque.Redis.host') . ':' . Configure::read('CakeResque.Redis.port'))),
			sprintf("REDIS_DATABASE=%s \\\n", Configure::read('CakeResque.Redis.database')),
			sprintf("REDIS_NAMESPACE=%s \\\n", escapeshellarg(Configure::read('CakeResque.Redis.namespace'))),
			sprintf("CAKE=%s \\\n", escapeshellarg(CAKE)),
			sprintf("COUNT=%s \\\n", $this->_runtime['workers']),
			sprintf("LOGHANDLER=%s \\\n", escapeshellarg($this->_runtime['Log']['handler'])),
			sprintf("LOGHANDLERTARGET=%s \\\n", escapeshellarg($this->_runtime['Log']['target'])),
			sprintf("php %s \\\n", escapeshellarg($this->__getResqueBinFile($this->_resqueLibrary))),
			sprintf(">> %s \\\n", escapeshellarg($this->_runtime['log'])),
			"2>&1\" >/dev/null 2>&1 &"
		));

		if ($this->_runtime['debug']) {
			$this->debug(__d('cake_resque', 'Running command : ' . "\n\t " . str_replace("\n", "\n\t", $cmd)));
		}

		$workersCountBefore = Resque::Redis()->scard('workers');
		$workersCountAfter = 0;
		passthru($cmd);

		$this->out(__d('cake_resque', 'Starting worker '), 0);

		$success = false;
		$attempt = 7;
		while ($attempt-- > 0) {
			for ($i = 0; $i < 3;$i++) {
				$this->out(".", 0);
				usleep(150000);
			}
			if (($workersCountBefore + $this->_runtime['workers']) == ($workersCountAfter = Resque::Redis()->scard('workers'))) {
				if ($args === null || $new === true) {
					$this->ResqueStatus->addWorker($this->_runtime);
				}
				$this->out(' <success>' . __d('cake_resque', 'Done') . '</success>' . (($this->_runtime['workers'] == 1) ? '' : ' x' . $this->_runtime['workers']));
				$success = true;
				break;
			}
		}

		if (!$success) {
			if ($workersCountBefore === $workersCountAfter) {
				$this->out(' <error>' . __d('cake_resque', 'Fail') . '</error>');
			} else {
				$this->out(' <warning>' . __d('cake_resque', 'Warning, could not start %d workers', (($workersCountBefore + $this->_runtime['workers']) - $workersCountAfter)) . '</warning>');
			}
		}

		if ($args === null) {
			$this->out('');
		}
	}

/**
 * Start the scheduler worker
 *
 * @param array $args If present, start the worker with these args.
 * @param bool $new Whether the worker is new, or from a restart
 * @since 2.3.0
 */
	public function startScheduler($args = null, $new = true) {
		if ($args === null) {
			$this->out('<info>' . __d('cake_resque', 'Creating the scheduler worker') . '</info>');
		}

		if (Configure::read('CakeResque.Scheduler.enabled') !== true) {
			return $this->out('<error>' . __d('cake_resque', 'Scheduler Worker is not enabled') . '</error>');
		}

		if ($this->ResqueStatus->isRunningSchedulerWorker(true)) {
			return $this->out('<warning>' . __d('cake_resque', 'The scheduler worker is already running') . '</warning>');
		}

		$args['type'] = 'scheduler';

		if (!$this->_validate($args)) {
			return;
		}

		if (file_exists(APP . 'Lib' . DS . 'CakeResqueBootstrap.php')) {
			$bootstrapPath = APP . 'Lib' . DS . 'CakeResqueBootstrap.php';
		} else {
			$bootstrapPath = App::pluginPath('CakeResque') . 'Lib' . DS . 'CakeResqueBootstrap.php';
		}

		$envVars = array();
		$vars = Configure::read('CakeResque.Scheduler.Env');
		foreach ($vars as $key => $val) {
			if (is_int($key) && isset($_SERVER[$val])) {
				$envVars[] = sprintf("%s=%s", $val, escapeshellarg($_SERVER[$val]));
			} else {
				$envVars[] = sprintf("%s=%s", $key, escapeshellarg($val));
			}
		}

		$cmd = implode(' ', array(
			sprintf("nohup sudo -u %s \\\n", $this->_runtime['user']),
			sprintf("bash -c \"cd %s; \\\n", escapeshellarg($this->_ResqueSchedulerLibrary)),
			implode(' ', $envVars),
			" \\\n",
			sprintf("%sVERBOSE=true \\\n", $this->_runtime['verbose'] ? 'V' : ''),
			sprintf("APP_INCLUDE=%s \\\n", escapeshellarg($bootstrapPath)),
			sprintf("INTERVAL=%s \\\n", $this->_runtime['interval']),
			sprintf("RESQUE_PHP=%s \\\n", escapeshellarg($this->_resqueLibrary . 'lib' . DS . 'Resque.php')),
			sprintf("REDIS_BACKEND=%s \\\n", escapeshellarg(Configure::read('CakeResque.Redis.host') . ':' . Configure::read('CakeResque.Redis.port'))),
			sprintf("REDIS_DATABASE=%s \\\n", Configure::read('CakeResque.Redis.database')),
			sprintf("REDIS_NAMESPACE=%s \\\n", escapeshellarg(Configure::read('CakeResque.Redis.namespace'))),
			sprintf("CAKE=%s \\\n", escapeshellarg(CAKE)),
			sprintf("LOGHANDLER=%s \\\n", escapeshellarg($this->_runtime['Log']['handler'])),
			sprintf("LOGHANDLERTARGET=%s \\\n", escapeshellarg($this->_runtime['Log']['target'])),
			"php ./bin/resque-scheduler.php \\\n",
			sprintf(">> %s \\\n", escapeshellarg(Configure::read('CakeResque.Scheduler.log'))),
			"2>&1\" >/dev/null 2>&1 &"
		));

		if ($this->_runtime['debug']) {
			$this->debug(__d('cake_resque', 'Running command : ' . "\n\t " . str_replace("\n", "\n\t", $cmd)));
		}

		$workersCountBefore = Resque::Redis()->scard('workers');
		passthru($cmd);

		$this->out(__d('cake_resque', 'Starting the scheduler worker '), 0);

		$started = false;
		$attempt = 7;
		while ($attempt-- > 0) {
			for ($i = 0; $i < 3;$i++) {
				$this->out(".", 0);
				usleep(150000);
			}
			if (($workersCountBefore + $this->_runtime['workers']) == Resque::Redis()->scard('workers')) {
				if ($args === null || $new === true) {
					$this->ResqueStatus->addWorker($this->_runtime);
					$this->ResqueStatus->registerSchedulerWorker();
				}
				$this->out(' <success>' . __d('cake_resque', 'Done') . '</success>' . (($this->_runtime['workers'] == 1) ? '' : ' x' . $this->_runtime['workers']));
				$started = true;
				break;
			}
		}

		if (!$started) {
			$this->out(' <error>' . __d('cake_resque', 'Fail') . '</error>');
		}

		if ($args === null) {
			$this->out('');
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
		$this->out('<info>' . __d('cake_resque', 'Stopping workers') . '</info>');
		$workers = call_user_func(self::$cakeResque . '::getWorkers');
		if (empty($workers)) {
			$this->out('   ' . __d('cake_resque', 'There is no active workers to kill ...'));
		} else {
			$workerIndex = array();
			if (!$this->params['all'] && !$all && count($workers) > 1) {
				$this->out(__d('cake_resque', 'Active workers list') . ':');
				$i = 1;
				foreach ($workers as $worker) {
					$this->out(sprintf("    [%3d] - %s, started %s", $i++, $this->ResqueStatus->isSchedulerWorker($worker) ? '<comment>**Scheduler Worker**</comment>' : $worker,
						CakeTime::timeAgoInWords(Resque::Redis()->get('worker:' . $worker . ':started'))));
				}

				$options = range(1, $i - 1);

				if ($i > 2) {
					$this->out('    [all] - ' . __d('cake_resque', 'Stop all workers'));
					$options[] = 'all';
				}

				$in = $this->in(__d('cake_resque', 'Worker to kill') . ': ', $options);
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
				if ($this->ResqueStatus->isSchedulerWorker($worker)) {
					$this->ResqueStatus->unregisterSchedulerWorker();
					$this->out(__d('cake_resque', 'Killing the Scheduler Worker ... '), 0);
				} else {
					$this->out(__d('cake_resque', 'Killing %s ... ', $pid), 0);
				}

				$worker->unregisterWorker(); // Remove jobs from resque environment

				$signal = $this->params['force'] ? 'TERM' : 'QUIT'; // Send signal to stop processing jobs
				$killResponse = $this->_kill($signal, $pid); // Kill all remaining system process

				if ($killResponse['code'] == 0) {
					$this->out('<success>' . __d('cake_resque', 'Done') . '</success>');
				} else {
					$this->out('<error>' . $killResponse['message'] . '</error>');
				}
			}
		}

		if ($shutdown) {
			$this->ResqueStatus->clearWorker();
		}

		$this->out('');
	}

/**
 * Clean up worker *
 * On supported system, will ask the user to choose the worker to clean up, from a list of worker,
 * if more than one worker is running, or if --all is not passed
 *
 * Clean up will immediately terminate a worker child. Job is left unfinished.
 *
 * @since 2.0.0
 */
	public function cleanup() {
		App::uses('CakeTime', 'Utility');
		$ResqueStatus = $this->ResqueStatus;

		$actionMessage = function ($pid) {
			return __d('cake_resque', 'Cleaning up %s ... ', $pid);
		};

		$listItemFormatter = function ($worker, $i) use ($ResqueStatus) {
			return sprintf("    [%3d] - %s, started %s", $i, $ResqueStatus->isSchedulerWorker($worker) ? '<comment>**Scheduler Worker**</comment>' : $worker,
					CakeTime::timeAgoInWords(Resque::Redis()->get('worker:' . $worker . ':started')));
		};

		$successCallback = function ($worker) {
		};

		return $this->_sendSignal(
			__d('cake_resque', 'Cleaning up workers'),
			call_user_func(self::$cakeResque . '::getWorkers'),
			__d('cake_resque', 'There is no active workers to clean up ...'),
			__d('cake_resque', 'Active workers list'),
			__d('cake_resque', 'Clean up all workers'),
			__d('cake_resque', 'Worker to Cleanup'),
			__d('cake_resque', 'Cleaning up the Scheduler Worker ... '),
			$actionMessage,
			$listItemFormatter,
			$successCallback,
			'USR1'
		);
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
		App::uses('CakeTime', 'Utility');
		$ResqueStatus = $this->ResqueStatus;

		$actionMessage = function ($pid) {
			return __d('cake_resque', 'Pausing %s ... ', $pid);
		};

		$listItemFormatter = function ($worker, $i) use ($ResqueStatus) {
			return sprintf("    [%3d] - %s, started %s", $i, $ResqueStatus->isSchedulerWorker($worker) ? '<comment>**Scheduler Worker**</comment>' : $worker,
					CakeTime::timeAgoInWords(Resque::Redis()->get('worker:' . $worker . ':started')));
		};

		$successCallback = function ($worker) use ($ResqueStatus) {
			$ResqueStatus->setPausedWorker((string)$worker);
		};

		return $this->_sendSignal(
			__d('cake_resque', 'Pausing workers'),
			call_user_func(self::$cakeResque . '::getWorkers'),
			__d('cake_resque', 'There is no active workers to pause ...'),
			__d('cake_resque', 'Active workers list'),
			__d('cake_resque', 'Pause all workers'),
			__d('cake_resque', 'Worker to pause'),
			__d('cake_resque', 'Pausing the Scheduler Worker ... '),
			$actionMessage,
			$listItemFormatter,
			$successCallback,
			'USR2'
		);
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
		App::uses('CakeTime', 'Utility');
		$ResqueStatus = $this->ResqueStatus;

		$actionMessage = function ($pid) {
			return __d('cake_resque', 'Resuming %s ... ', $pid);
		};

		$listItemFormatter = function ($worker, $i) use ($ResqueStatus) {
			return sprintf("    [%3d] - %s, started %s", $i, $ResqueStatus->isSchedulerWorker($worker) ? '<comment>**Scheduler Worker**</comment>' : $worker,
					CakeTime::timeAgoInWords(Resque::Redis()->get('worker:' . $worker . ':started')));
		};

		$successCallback = function ($worker) use ($ResqueStatus) {
			$ResqueStatus->setActiveWorker((string)$worker);
		};

		return $this->_sendSignal(
			__d('cake_resque', 'Resuming workers'),
			$this->ResqueStatus->getPausedWorker(),
			__d('cake_resque', 'There is no paused workers to resume ...'),
			__d('cake_resque', 'Paused workers list'),
			__d('cake_resque', 'Resume all workers'),
			__d('cake_resque', 'Worker to resume'),
			__d('cake_resque', 'Resuming the Scheduler Worker ... '),
			$actionMessage,
			$listItemFormatter,
			$successCallback,
			'CONT'
		);
	}

	protected function _sendSignal($title, $workers, $noWorkersMessage, $listTitle,
		$allActionMessage, $promptMessage, $schedulerWorkerActionMessage, $workerActionMessage, $formatListItem, $successCallback, $signal) {
		if (!function_exists('pcntl_signal')) {
			return $this->out('<error>' .
				__d('cake_resque', "This function requires the PCNTL extension") . '</error>');
		}

		$this->out('<info>' . $title . '</info>');

		if (empty($workers)) {
			$this->out('   ' . $noWorkersMessage);
		} else {

			$workerIndex = array();
			if (!$this->params['all'] && count($workers) > 1) {
				$this->out($listTitle . ':');
				$i = 1;
				foreach ($workers as $worker) {
					$this->out($formatListItem($worker, $i++));
				}

				$options = range(1, $i - 1);

				if ($i > 2) {
					$this->out('    [all] - ' . $allActionMessage);
					$options[] = 'all';
				}

				$in = $this->in($promptMessage . ': ', $options);
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
				if ($this->ResqueStatus->isSchedulerWorker($worker)) {
					$this->out($schedulerWorkerActionMessage, 0);
				} else {
					$this->out($workerActionMessage($pid), 0);
				}

				$killResponse = $this->_kill($signal, $pid);

				if ($killResponse['code'] === 0) {
					$this->out('<success>' . __d('cake_resque', 'Done') . '</success>');
					$successCallback($worker);
				} else {
					$this->out('<error>' . $killResponse['message'] . '</error>');
				}
			}
		}

		$this->out('');
	}

/**
 * Start a list of predefined workers
 */
	public function load() {
		$this->out('<info>' . __d('cake_resque', 'Loading predefined workers') . '</info>');
		$debug = isset($this->params['debug']) ? $this->params['debug'] : false;

		if (Configure::read('CakeResque.Queues') == null) {
			$this->out('   ' . __d('cake_resque', 'You have no configured queues to load.'));
		} else {
			foreach (Configure::read('CakeResque.Queues') as $queue) {
				$queue['debug'] = $debug;
				$this->start($queue);
			}
		}

		if (Configure::read('CakeResque.Scheduler.enabled') === true) {
			$this->startscheduler(array('debug' => $debug));
		}

		$this->out('');
	}

/**
 * Restart all workers
 */
	public function restart() {
		$this->stop(false, true);

		$this->out('<info>' . __d('cake_resque', 'Restarting workers') . '</info>');
		if (array() !== $workers = $this->ResqueStatus->getWorkers()) {
			$debug = $this->params['debug'];
			foreach ($workers as $worker) {
				$worker['debug'] = $debug;
				if (isset($worker['type']) && $worker['type'] === 'scheduler') {
					$this->startScheduler($worker, false);
				} else {
					$this->start($worker, false);
				}
			}
			$this->out('');
		} else {
			$this->out('<warning>' . __d('cake_resque', 'No active workers found, will start brand new worker') . '</warning>');
			$this->start();
		}
	}

	public function stats() {
		$workers = call_user_func(self::$cakeResque . '::getWorkers');
		// List of all queues
		$queues = call_user_func(self::$cakeResque . '::getQueues');
		if (!empty($queues)) {
			$queues = array_unique($queues);
		}
		// List of queues monitored by a worker
		$activeQueues = array();
		foreach ($workers as $worker) {
			$activeQueues = array_merge($activeQueues, explode(',', array_pop(explode(':', $worker))));
		}

		$this->out("\n");
		$this->out('<info>' . __d('cake_resque', 'Resque Statistics') . '</info>');
		$this->hr();
		$this->out("\n");

		$this->out('<info>' . __d('cake_resque', 'Jobs Stats') . '</info>');
		$this->out('   ' . __d('cake_resque', 'Processed Jobs : %12s', number_format(Resque_Stat::get('processed'))));
		$this->out('   <warning>' . __d('cake_resque', 'Failed Jobs    : %12s', number_format(Resque_Stat::get('failed'))) . '</warning>');

		if (Configure::read('CakeResque.Scheduler.enabled') === true) {
			$this->out('   ' . __d('cake_resque', 'Scheduled Jobs : %12s', number_format(ResqueScheduler\Stat::get())));
		}

		$this->out("\n");

		$this->out('<info>' . __d('cake_resque', 'Queues Stats') . '</info>');
		for ($i = count($queues) - 1; $i >= 0; --$i) {
			$count[$queues[$i]] = Resque::Redis()->llen('queue:' . $queues[$i]);
			if (!in_array($queues[$i], $activeQueues) && $count[$queues[$i]] == 0) {
				unset($queues[$i]);
			}
		}

		$this->out('   ' . __d('cake_resque', 'Queues count : %d', count($queues)));
		foreach ($queues as $queue) {
			$this->out(sprintf("\t- %s \t : %12s %s", $queue, number_format($count[$queue]), __dn('cake_resque', 'pending job', 'pending jobs', $count[$queue]) . (!in_array($queue, $activeQueues) ? " <error>(unmonitored queue)</error>" : '')));
		}

		$this->out("\n");
		$this->out('<info>' . __d('cake_resque', 'Workers Stats') . '</info>');
		$this->out('   ' . __d('cake_resque', 'Workers count : %s', count($workers)));

		$pausedWorkers = $this->ResqueStatus->getPausedWorker();
		$schedulerWorkers = array();

		if (!empty($workers)) {
			$this->out("\t<info>" . strtoupper(__d('cake_resque', 'regular workers')) . "</info>");
			foreach ($workers as $worker) {
				if ($this->ResqueStatus->isSchedulerWorker($worker)) {
					$schedulerWorkers[] = $worker;
					continue;
				}
				$this->out("\t* <bold>" . (string)$worker . '</bold>' . (in_array((string)$worker, $pausedWorkers) ? ' <warning>(' . __d('cake_resque', 'paused') . ')</warning>' : ''));
				$this->out("\t   - " . __d('cake_resque', 'Started on') . "     : " . Resque::Redis()->get('worker:' . $worker . ':started'));
				$this->out("\t   - " . __d('cake_resque', 'Processed Jobs') . " : " . $worker->getStat('processed'));
				$worker->getStat('failed') == 0
					? $this->out("\t   - " . __d('cake_resque', 'Failed Jobs') . "    : " . $worker->getStat('failed'))
					: $this->out("\t   - <warning>" . __d('cake_resque', 'Failed Jobs') . "    : " . $worker->getStat('failed') . "</warning>");
			}
		}

		$this->out("\n");

		if (!empty($schedulerWorkers)) {
			$this->out("\t<info>" . strtoupper(__d('cake_resque', 'scheduler worker')) . "</info>" . (in_array((string)$schedulerWorkers[0], $pausedWorkers) ? ' <warning>(' . __d('cake_resque', 'paused') . ')</warning>' : ''));
			foreach ($schedulerWorkers as $worker) {
				$schedulerWorker = new ResqueScheduler\ResqueScheduler();
				$delayedJobCount = $schedulerWorker->getDelayedQueueScheduleSize();
				$this->out("\t   - " . __d('cake_resque', 'Started on') . "     : " . Resque::Redis()->get('worker:' . $worker . ':started'));
				$this->out("\t   - " . __d('cake_resque', 'Delayed Jobs') . "   : " . $delayedJobCount);

				if ($delayedJobCount > 0) {
					$this->out("\t   - " . __d('cake_resque', 'Next Job on') . "    : " . strftime('%a %b %d %H:%M:%S %Z %Y', $schedulerWorker->nextDelayedTimestamp()));
				}
			}
			$this->out("\n");
		} elseif (Configure::read('CakeResque.Scheduler.enabled') === true) {
			$jobsCount = ResqueScheduler\ResqueScheduler::getDelayedQueueScheduleSize();
			if ($jobsCount > 0) {
				$this->out("\t<error>************ " . __d('cake_resque', 'Alert') . " ************</error>");
				$this->out("\t<bold>" . __d('cake_resque', 'The Scheduler Worker is not running') . "</bold>");
				$this->out("\t" . __d('cake_resque', 'But there is still <bold>%d</bold> scheduled jobs left in its queue', $jobsCount));
				$this->out("\t<error>********************************</error>");
				$this->out("\n");
			}
		}
	}

/**
 * Track a job status
 *
 * @since 2.1.0
 */
	public function track() {
		$this->out('<info>' . __d('cake_resque', 'Tracking job status') . '</info>');

		if (isset($this->args[0])) {
			$jobId = $this->args[0];
		} else {
			return $this->out('<error>' . __d('cake_resque', 'Please provide a valid job ID') . "</error>\n");
		}

		$jobStatus = call_user_func(self::$cakeResque . '::getJobStatus', $jobId);

		if ($jobStatus === false) {
			$this->out(__d('cake_resque', 'Status') . ' : <warning>' . __d('cake_resque', 'Unknown') . '</warning>');
		} else {
			$statusName = array(
				Resque_Job_Status::STATUS_WAITING => __d('cake_resque', 'waiting'),
				Resque_Job_Status::STATUS_RUNNING => __d('cake_resque', 'running'),
				Resque_Job_Status::STATUS_FAILED => __d('cake_resque', 'failed'),
				Resque_Job_Status::STATUS_COMPLETE => __d('cake_resque', 'complete')
			);

			$statusClass = array(
				Resque_Job_Status::STATUS_WAITING => 'info',
				Resque_Job_Status::STATUS_RUNNING => 'info',
				Resque_Job_Status::STATUS_FAILED => 'error',
				Resque_Job_Status::STATUS_COMPLETE => 'success'
			);

			if (Configure::read('CakeResque.Scheduler.enabled') === true) {
				$statusClass[ResqueScheduler\Job\Status::STATUS_SCHEDULED] = 'info';
				$statusName[ResqueScheduler\Job\Status::STATUS_SCHEDULED] = __d('cake_resque', 'scheduled');
			}

			$this->out(
				sprintf(
					__d('cake_resque', 'Status') . ' : <%1$s>%2$s</%1$s>',
					(isset($statusClass[$jobStatus]) ? $statusClass[$jobStatus] : 'warning'),
					isset($statusName[$jobStatus]) ? $statusName[$jobStatus] : __d('cake_resque', 'Unknown')
				)
			);

			if ($jobStatus === Resque_Job_Status::STATUS_FAILED) {
				$log = call_user_func(self::$cakeResque . '::getFailedJobLog', $jobId);
				if (!empty($log)) {
					$this->hr();
					$this->out('<comment>' . __d('cake_resque', 'Failed job details') . '</comment>');
					$this->hr();
					foreach ($log as $key => $value) {
						$this->out(sprintf("<info>%-10s: </info>", strtoupper($key)), 0);
						if (is_string($value)) {
							$this->out($value);
						} else {
							$this->out('');
							foreach ($value as $sKey => $sValue) {
								$this->out(sprintf("    <info>%5s : </info>", $sKey), 0);
								if (is_string($sValue)) {
									$this->out($sValue);
								} else {
									$this->out(str_replace("\n", "\n            ", var_export($sValue, true)));
								}
							}
						}
					}
				}
			}
		}

		$this->out('');
	}

/**
 * Clear a queue
 *
 * Remove all jobs inside a queue
 * If more than one queue is present, it will prompt the user which queue to clear via a menu
 *
 * @since 3.3.0
 */
	public function clear() {
		$this->out('<info>' . __d('cake_resque', 'Clearing queues') . '</info>');

		// List of all queues
		$queues = call_user_func(self::$cakeResque . '::getQueues');
		if (!empty($queues)) {
			$queues = array_unique($queues);
		} else {
			return $this->out(__d('cake_resque', 'There is no queues to clear'));
		}

		if (isset($this->args[0])) {
			if (in_array($this->args[0], $queues)) {
				$queueIndex[] = array_search($this->args[0], $queues) + 1;
			}
		} else {
			$queueIndex = array();
			if (!$this->params['all'] && count($queues) > 1) {
				$this->out(__d('cake_resque', 'Queues list') . ':');
				$i = 1;
				foreach ($queues as $queue) {
					$this->out(sprintf("    [%3d] - %-'.20s<bold>%'.9s</bold> jobs", $i++, $queue, number_format(Resque::Redis()->llen('queue:' . $queue))));
				}

				$options = range(1, $i - 1);

				if ($i > 2) {
					$this->out('    [all] - ' . __d('cake_resque', 'Clear all queues'));
					$options[] = 'all';
				}

				$in = $this->in(__d('cake_resque', 'Queue to clear') . ': ', $options);
				if ($in == 'all') {
					$queueIndex = range(1, count($queues));
				} else {
					$queueIndex[] = $in;
				}

			} else {
				$queueIndex = range(1, count($queues));
			}
		}

		foreach ($queueIndex as $index) {

			$this->out(__d('cake_resque', 'Clearing %s ... ', $queues[$index - 1]), 0);

			$output = array();
			$code = Resque::Redis()->ltrim('queue:' . $queues[$index - 1], 1, 0);

			if ($code) {
				$this->out('<success>' . __d('cake_resque', 'Done') . '</success>');
			} else {
				$this->out('<error>' . __d('cake_resque', 'An unexpected error occured while clearing the queue') . '</error>');
			}
		}
	}

/**
 * Validate command line options
 * And print the errors
 *
 * @since 1.0
 * @return true if all options are valid
 */
	protected function _validate($args = null) {
		$this->_runtime = ($args === null) ? $this->params : $args;

		$errors = array();

		if (!isset($this->_runtime['type'])) {
			$this->_runtime['type'] = 'regular';
		}

		// Validate Log path
		$this->_runtime['log'] = isset($this->_runtime['log'])
		? $this->_runtime['log']
		: (Configure::read('CakeResque.Scheduler.log') && $this->_runtime['type'] === 'scheduler'
			? Configure::read('CakeResque.Scheduler.log')
			: Configure::read('CakeResque.Worker.log')
		);
		if (substr($this->_runtime['log'], 0, 2) == './') {
			$this->_runtime['log'] = TMP . 'logs' . DS . substr($this->_runtime['log'], 2);
		} elseif (substr($this->_runtime['log'], 0, 1) != '/') {
			$this->_runtime['log'] = TMP . 'logs' . DS . $this->_runtime['log'];
		}

		// Validate Interval
		$this->_runtime['interval'] = isset($this->_runtime['interval'])
		? $this->_runtime['interval']
		: (Configure::read('CakeResque.Scheduler.Worker.interval') && $this->_runtime['type'] === 'scheduler'
			? Configure::read('CakeResque.Scheduler.Worker.interval')
			: Configure::read('CakeResque.Worker.interval')
		);
		if (!is_numeric($this->_runtime['interval'])) {
			$errors[] = __d('cake_resque', 'Interval time [%s] is not valid. Please enter a valid number', $this->_runtime['interval']);
		} else {
			$this->_runtime['interval'] = (int)$this->_runtime['interval'];
		}

		// Validate workers number
		$this->_runtime['workers'] = isset($this->_runtime['workers']) ? $this->_runtime['workers'] : Configure::read('CakeResque.Worker.workers');
		if (!is_numeric($this->_runtime['workers'])) {
			$errors[] = __d('cake_resque', 'Workers number [%s] is not valid. Please enter a valid number', $this->_runtime['workers']);
		} else {
			$this->_runtime['workers'] = (int)$this->_runtime['workers'];
		}

		$this->_runtime['queue'] = isset($this->_runtime['queue']) ? $this->_runtime['queue'] : Configure::read('CakeResque.Worker.queue');

		$this->_runtime['user'] = isset($this->_runtime['user']) ? $this->_runtime['user'] : get_current_user();

		$this->_runtime['verbose'] = isset($this->params['verbose']) ? $this->params['verbose'] : Configure::read('CakeResque.Worker.verbose');

		$output = array();
		exec('id ' . $this->_runtime['user'] . ' 2>&1', $output, $status);
		if ($status != 0) {
			$errors[] = __d('cake_resque', 'User [%s] does not exists. Please enter a valid system user', $this->_runtime['user']);
		}

		$this->_runtime['Log']['handler'] = isset($this->_runtime['log-handler'])
		? $this->_runtime['log-handler']
		: (Configure::read('CakeResque.Scheduler.Log.handler') && $this->_runtime['type'] === 'scheduler'
			? Configure::read('CakeResque.Scheduler.Log.handler')
			: Configure::read('CakeResque.Log.handler')
		);

		$this->_runtime['Log']['target'] = isset($this->_runtime['log-handler-target'])
		? $this->_runtime['log-handler-target']
		: (Configure::read('CakeResque.Scheduler.Log.target') && $this->_runtime['type'] === 'scheduler'
			? Configure::read('CakeResque.Scheduler.Log.target')
			: Configure::read('CakeResque.Log.target')
		);
		if (substr($this->_runtime['Log']['target'], 0, 2) == './') {
			$this->_runtime['Log']['target'] = TMP . 'logs' . DS . substr($this->_runtime['Log']['target'], 2);
		}

		if (!empty($errors)) {
			foreach ($errors as $error) {
				$this->err('<error>' . __d('cake_resque', 'Error') . ':</error> ' . $error);
			}
			$this->out();
		}
		return empty($errors);
	}

	public function debug($string) {
		return $this->out('<success>[DEBUG] ' . $string . '</success>');
	}

/**
 * Return the php-resque executable file
 *
 * Maintain backward compatibility, as newer version of
 * php-resque has that file in another location
 *
 * @since  3.3.2
 * @param  String $base Php-resque folder path
 * @return String Relative path to php-resque executable file
 */
	private function __getResqueBinFile($base) {
		if (file_exists($base . DS . 'bin' . DS . 'resque')) {
			return '.' . DS . 'bin' . DS . 'resque';
		} elseif (file_exists($base . 'bin' . DS . 'resque.php')) {
			return '.' . DS . 'bin' . DS . 'resque.php';
		} else {
			return '.' . DS . 'resque.php';
		}
	}

/**
 * Return kill command syntax, intended to be used with exec().
 *
 * @since  3.3.4
 * @param string Kill Signal
 * @param string Process id
 * @return array
 */
	protected function _kill($signal, $pid) {
		$output = array();
		$message = exec(sprintf('/bin/kill -%s %s 2>&1', $signal, $pid), $output, $code);
		return array('code' => $code, 'message' => $message);
	}

/**
 *
 * @since 3.3.6
 * @param  String $path Path to the file to tail
 */
	protected function _tail($path) {
		passthru('tail -f ' . escapeshellarg($path));
	}
}
