<?php

class ResqueShell extends Shell {

	public $uses = array();

	public $log_path = null;

/**
 * Startup callback.
 *
 * Initializes defaults.
 */
	public function startup() {
		$this->log_path = TMP . 'logs' . DS . 'php-resque-worker.log';

		App::import('Lib', 'Resque.ResqueUtility');
		require_once APP . 'Plugin' . DS . 'Resque' . DS . 'Vendor' . DS . 'php-resque' . DS . 'lib' . DS . 'Resque.php';
		require_once APP . 'Plugin' . DS . 'Resque' . DS . 'Vendor' . DS . 'php-resque' . DS . 'lib' . DS . 'Resque' . DS .'Stat.php';
		require_once APP . 'Plugin' . DS . 'Resque' . DS . 'Vendor' . DS . 'php-resque' . DS . 'lib' . DS . 'Resque' . DS .'Worker.php';
	}

	public function getOptionParser() {
		$startParserArguments = array(
			'options' => array(
				'tail' => array(
					'short' => 't',
					'help' => __d('resque_console', 'Display the tail onscreen.'),
					'boolean' => true
				),
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
				)
			)
		);

		$stopParserArguments = array(
			'options' => array(
				'force' => array(
					'short' => 'f',
					'help' => __d('resque_console', 'Force workers shutdown, forcing all the current jobs to finish (and fail)'),
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
    			'help' => __d('resque_console', 'View tail of the workers logs.')
	    	))
			->addSubcommand('jobs', array(
				'help' => __d('resque_console', 'Display a list of all available jobs.'),
				'parser' => array(
					'arguments' => array(
						'jobname' => array(
							'help' => __d('resque_console', 'Name of the job to get description')
						)
					)
				)
			)
		);
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
 * Fork a new php resque worker service.
 */
	public function start($args = null) {
		if (!is_null($args)) {
			$this->params = $args;
		}

		$queue = isset($this->params['queue']) ? $this->params['queue'] : Configure::read('Resque.default.queue');
		$user = isset($this->params['user']) ? $this->params['user'] : get_current_user();
		$interval = isset($this->params['interval']) ? (int) $this->params['interval'] : Configure::read('Resque.default.interval');
		$count = isset($this->params['workers']) ? (int) $this->params['workers'] : Configure::read('Resque.default.workers');

		//exec('id apache 2>&1 >/dev/null', $out, $status); // check if user exists; cross-platform for ubuntu & redhat
		//$user = $status === 0 ? 'apache' : 'www-data';

		$path = App::pluginPath('Resque') . 'Vendor' . DS . 'php-resque' . DS;
		$log_path = $this->log_path;

		if (file_exists(APP . 'Lib' . DS . 'ResqueBootstrap.php')) {
			$bootstrap_path = APP . 'Lib' . DS . 'ResqueBootstrap.php';
		} else {
			$bootstrap_path = App::pluginPath('Resque') . 'Lib' . DS . 'ResqueBootstrap.php';
		}

		$this->out("<warning>Forking new PHP Resque worker service</warning> (<info>queue:</info>{$queue} <info>user:</info>{$user})");

		$env_vars = array();
		$vars = Configure::read('Resque.environment_variables');
		foreach ((array) $vars as $var) {
			if (isset($_SERVER[$var])) {
				$env_vars[] = sprintf("%s=%s", $var, escapeshellarg($_SERVER[$var]));
			}
		}

		$cmd = implode(' ', array(
			sprintf("nohup sudo -u %s", $user),
			sprintf('bash -c "cd %s;', escapeshellarg($path)),
			implode(' ', $env_vars),
			sprintf("VVERBOSE=true QUEUE=%s", escapeshellarg($queue)),
			sprintf("APP_INCLUDE=%s INTERVAL=%s", escapeshellarg($bootstrap_path), escapeshellarg($interval)),
			sprintf("REDIS_BACKEND=%s", escapeshellarg(Configure::read('Resque.Redis.host') . ':' . Configure::read('Resque.Redis.port'))),
			sprintf("CAKE=%s COUNT=%s", escapeshellarg(CAKE), $count),
			sprintf("php ./resque.php >> %s", escapeshellarg($log_path)),
			'2>&1" >/dev/null 2>&1 &'
		));
		passthru($cmd);

		if (isset($this->params['tail']) && $this->params['tail']) {
			sleep(3); // give it time to output to the log for the first time
			$this->tail();
		}

		$this->__addWorker($this->params);
	}

/**
 * Kill all php resque worker services.
 */
	public function stop($shutdown = true) {
		$this->out('<warning>Shutting down Resque Worker complete</warning>');
		$workers = Resque_Worker::all();
		if (empty($workers)) {
			$this->out('   There were no active workers to kill ...');
		} else {
			$this->out('Killing '.count($workers).' workers ...');
			foreach($workers as $w) {
				$this->params['force'] ? $w->shutDownNow() : $w->shutDown();	// Send signal to stop processing jobs
				$w->unregisterWorker();											// Remove jobs from resque environment
				list($hostname, $pid, $queue) = explode(':', (string)$w);
				$this->out('Killing ' . $pid);
				exec('kill -9 '.$pid);											// Kill all remaining system process
			}
		}

		if ($shutdown) $this->__clearWorker();
	}

/**
 * Start a list of predefined queues
 */
	public function load() {
		if (Configure::read('Resque.queues') == null) {
			$this->out('   You have no configured queues to load.');
		} else {
			foreach(Configure::read('Resque.queues') as $queue) {
				$this->start($queue);
			}
		}
	}

/**
 * Restart all workers
 */
	public function restart() {
		$this->stop(false);

		if (false !== $workers = $this->__getWorkers()) {
			foreach($workers as $worker) {
				$this->start($worker);
			}
		} else {
			$this->start();
		}
	}

	public function stats() {
		$this->out("\n");
		$this->out('<info>PHPResque Statistics</info>');
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
		Resque::Redis()->sAdd('ResqueWorker', serialize($args));
	}

	private function __getWorkers() {
		$workers = Resque::Redis()->sMembers('ResqueWorker');
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

}
