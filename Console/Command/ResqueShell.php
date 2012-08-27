<?php

class ResqueShell extends Shell {

	public $uses = array();

	protected $_resqueLibrary = null;

	protected $_runtime = array();

/**
 * Startup callback.
 *
 * Initializes defaults.
 */
	public function startup() {
		$this->_resqueLibrary = App::pluginPath('Resque') . 'vendor' . DS . Configure::read('Resque.Resque.lib') . DS;

		App::import('Lib', 'Resque.ResqueUtility');
		require_once $this->_resqueLibrary . 'lib' . DS . 'Resque.php';
		require_once $this->_resqueLibrary . 'lib' . DS . 'Resque' . DS .'Stat.php';
		require_once $this->_resqueLibrary . 'lib' . DS . 'Resque' . DS .'Worker.php';

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
					'short' => 'l',
					'help' => __d('resque_console', 'Log Handler to use for logging.')
				),
				'log-handler-target' => array(
					'short' => 'l',
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
			)
		);

	    return parent::getOptionParser()
			->description(__d('resque_console', "A Shell to manage PHP Resque.\n"))
			->addSubcommand('start', array(
				'help' => __d('resque_console', 'Start a new Resque worker.'),
				'parser' => $startParserArguments
			))
			->addSubcommand('stop', array(
				'help' => __d('resque_console', 'Stop all Resque workers.'),
				'parser' => $stopParserArguments
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
	    	->addSubcommand('load', array(
	    		'help' => __d('resque_console', 'Load a set of predefined workers.')
	    	));
	}

/**
 * Manually enqueue a job via CLI.
 */
	public function enqueue() {
		if (count($this->args) < 1) {
			$this->out('Which job class would you like to enqueue?');
			return false;
		}

		$job_queue = $this->args[0];
		$job_class = $this->args[1];
		$params = explode(',', $this->args[2]);

		Resque::enqueue($job_queue, $job_class, $params);
		$this->out('Enqueued new job "' . $job_class . '"' . ($this->args[2] ? ' with params (' . $this->args[2] . ')' : '') . '...');
	}

/**
 * Convenience functions.
 */
	public function tail() {
		$log_path = $this->log_path;
		if (file_exists($log_path)) {
			passthru('sudo tail -f ' . escapeshellarg($this->log_path));
		} else {
			$this->out('Log file does not exist. Is the service running?');
		}
	}

/**
 * Create a new worker
 *
 * @param array $args
 * @param bool $new Whether the worker is new, or from a restart
 */
	public function start($args = null, $new = true) {
		if (!is_null($args)) {
			$this->_runtime = $args;
		} else {
			$this->_runtime = $this->params;
			$this->out('<info>Creating workers</info>');
		}

		if (!$this->__validate()) return;

		$log_path = $this->_runtime['log'];

		if (file_exists(APP . 'Lib' . DS . 'ResqueBootstrap.php')) {
			$bootstrap_path = APP . 'Lib' . DS . 'ResqueBootstrap.php';
		} else {
			$bootstrap_path = App::pluginPath('Resque') . 'Lib' . DS . 'ResqueBootstrap.php';
		}

		$env_vars = array();
		$vars = Configure::read('Resque.environment_variables');
		foreach ($vars as $key => $val) {
			if (is_int($key) && isset($_SERVER[$val])) {
				$env_vars[] = sprintf("%s=%s", $val, escapeshellarg($_SERVER[$val]));
			} else {
				$env_vars[] = sprintf("%s=%s", $key, escapeshellarg($val));
			}
		}

		$cmd = implode(' ', array(
			sprintf("nohup sudo -u %s", $this->_runtime['user']),
			sprintf('bash -c "cd %s;', escapeshellarg($this->_resqueLibrary)),
			implode(' ', $env_vars),
			sprintf("VVERBOSE=true QUEUE=%s", escapeshellarg($this->_runtime['queue'])),
			sprintf("APP_INCLUDE=%s INTERVAL=%s", escapeshellarg($bootstrap_path), escapeshellarg($this->_runtime['interval'])),
			sprintf("REDIS_BACKEND=%s", escapeshellarg(Configure::read('Resque.Redis.host') . ':' . Configure::read('Resque.Redis.port'))),
			sprintf("CAKE=%s COUNT=%s", escapeshellarg(CAKE), $this->_runtime['workers']),
			sprintf("LOGHANDLER=%s LOGHANDLERTARGET=%s", escapeshellarg($this->_runtime['Log']['handler']), escapeshellarg($this->_runtime['Log']['target'])),
			sprintf("php ./resque.php >> %s", escapeshellarg($this->_runtime['log'])),
			'2>&1" >/dev/null 2>&1 &'
		));

		$workersCountBefore = Resque::Redis()->scard('workers');
		passthru($cmd);

		$this->out("Starting worker ", 0);
		for($i=0; $i<3;$i++) {
			$this->out(".", 0);
			usleep(100000);
		}

		$workersCountAfter = Resque::Redis()->scard('workers');
		if (($workersCountBefore + $this->_runtime['workers']) == $workersCountAfter) {
			if ($args === null || $new === true) $this->__addWorker($this->_runtime);
			$this->out(' <success>Done</success>' . (($this->_runtime['workers'] == 1) ? '' : ' x'.$this->_runtime['workers']));
		} else {
			$this->out(' <error>Fail</error>');
		}

		if ($args === null) {
			$this->out("");
		}
	}

/**
 * Kill workers
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
			if (!$this->params['all'] && !$all) {
				$this->out("Active workers list :");
				$i = 1;
				foreach($workers as $worker) {
					$this->out(sprintf("    [%2d] - %s, started %s", $i++, $worker, CakeTime::timeAgoInWords(Resque::Redis()->get('worker:' . $worker . ':started'))));
				}

				$options = range(1, $i-1);

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

			foreach($workerIndex as $index) {

				$worker = $workers[$index-1];

				list($hostname, $pid, $queue) = explode(':', (string)$worker);
				$this->out('Killing ' . $pid . ' ... ', 0);
				$this->params['force'] ? $worker->shutDownNow() : $worker->shutDown();	// Send signal to stop processing jobs
				$worker->unregisterWorker();									// Remove jobs from resque environment

				$result = exec('kill -9 '.$pid);								// Kill all remaining system process
				if (empty($result)) {
					$this->out('<success>Done</success>');
				} else {
					$this->out('<warning>'.$result.'</warning>');
				}
			}
		}

		if ($shutdown) $this->__clearWorker();
		$this->out("");
	}

/**
 * Start a list of predefined workers
 */
	public function load() {
		$this->out('<info>Loading predefined workers</info>');
		if (Configure::read('Resque.queues') == null) {
			$this->out('   You have no configured queues to load.');
		} else {
			foreach(Configure::read('Resque.queues') as $queue) {
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
			foreach($workers as $worker) {
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
		$this->out("   Active Workers : " . count($workers));

		if (!empty($workers)) {
			foreach ($workers as $worker) {
				$this->out("\tWorker : " . $worker);
				$this->out("\t - Started on     : " . Resque::Redis()->get('worker:' . $worker . ':started'));
				$this->out("\t - Processed Jobs : " . $worker->getStat('processed'));
				$worker->getStat('failed') == 0
					? $this->out("\t - Failed Jobs    : " . $worker->getStat('failed'))
					: $this->out("\t - <warning>Failed Jobs    : " . $worker->getStat('failed') . "</warning>");
			}
		}

		$this->out("\n");
	}

	private function __addWorker($args) {
		Resque::Redis()->rpush('ResqueWorker', serialize($args));
	}

	private function __getWorkers() {
		$listLength = Resque::Redis()->llen('ResqueWorker');
		$workers = Resque::Redis()->lrange('ResqueWorker', 0, $listLength-1);
		if (empty($workers)) {
			return false;
		} else {
			$temp = array();
			foreach($workers as $worker) {
				$temp[] = unserialize($worker);
			}
			return $temp;
		}
	}

	private function __clearWorker() {
		Resque::Redis()->del('ResqueWorker');
	}

/**
 * Validate command line options
 * And print the errors
 *
 * @return true if all options are valid
 */
	private function __validate()
	{
		$errors = array();

		// Validate Log path
		$this->_runtime['log'] = isset($this->_runtime['log']) ? $this->_runtime['log'] : Configure::read('Resque.default.log');
		if (substr($this->_runtime['log'], 0, 2) == './') {
			$this->_runtime['log'] =  TMP . 'logs' . DS . substr($this->_runtime['log'], 2);
		} elseif (substr($this->_runtime['log'], 0, 1) != '/') {
			$this->_runtime['log'] =  TMP . 'logs' . DS . $this->_runtime['log'];
		}

		// Validate Interval
		$this->_runtime['interval'] = isset($this->_runtime['interval']) ? $this->_runtime['interval'] : Configure::read('Resque.default.interval');
		if (!is_numeric($this->_runtime['interval'])) {
			$errors[] = __d('resque_console', 'Interval time [%s] is not valid. Please enter a valid number', $this->_runtime['interval']);
		} else {
			$this->_runtime['interval'] = (int)$this->_runtime['interval'];
		}

		// Validate workers number
		$this->_runtime['workers'] = isset($this->_runtime['workers']) ? $this->_runtime['workers'] : Configure::read('Resque.default.workers');
		if (!is_numeric($this->_runtime['workers'])) {
			$errors[] = __d('resque_console', 'Workers number [%s] is not valid. Please enter a valid number', $this->_runtime['workers']);
		} else {
			$this->_runtime['workers'] = (int)$this->_runtime['workers'];
		}

		$this->_runtime['queue'] = isset($this->_runtime['queue']) ? $this->_runtime['queue'] : Configure::read('Resque.default.queue');

		$this->_runtime['user'] = isset($this->_runtime['user']) ? $this->_runtime['user'] : get_current_user();
		// @todo Validate that user exists on the system

		$this->_runtime['Log']['handler'] = isset($this->_runtime['log-handler']) ? $this->_runtime['log-handler'] : Configure::read('Resque.Log.handler');

		$this->_runtime['Log']['target'] = isset($this->_runtime['log-handler-target']) ? $this->_runtime['log-handler-target'] : Configure::read('Resque.Log.target');

		foreach($errors as $error) {
			$this->err('<error>Error:</error> ' . $error);
		}

		return empty($errors);
	}

}
