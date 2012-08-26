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
 * Fork a new php resque worker service.
 */
	public function start($args = null) {
		if (!is_null($args)) {
			$this->_runtime = $args;
		}

		if (!$this->__validate()) return;

		//exec('id apache 2>&1 >/dev/null', $out, $status); // check if user exists; cross-platform for ubuntu & redhat
		//$user = $status === 0 ? 'apache' : 'www-data';

		$log_path = $this->_runtime['log'];

		if (file_exists(APP . 'Lib' . DS . 'ResqueBootstrap.php')) {
			$bootstrap_path = APP . 'Lib' . DS . 'ResqueBootstrap.php';
		} else {
			$bootstrap_path = App::pluginPath('Resque') . 'Lib' . DS . 'ResqueBootstrap.php';
		}

		$this->out("<warning>Forking new PHP Resque worker service</warning> (<info>queue:</info>{$this->_runtime['queue']} <info>user:</info>{$this->_runtime['user']})");

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
			sprintf("php ./resque.php >> %s", escapeshellarg($this->_runtime['log'])),
			'2>&1" >/dev/null 2>&1 &'
		));
		passthru($cmd);

		$this->out("<info>Forked worker</info> (<info>queue:</info>{$this->_runtime['queue']} <info>user:</info>{$this->_runtime['user']})");

		$this->out("<info>Adding worker to resque</info> (<info>queue:</info>{$this->_runtime['queue']} <info>user:</info>{$this->_runtime['user']})");
		$this->__addWorker($this->_runtime);
		$this->out("<info>Done</info> (<info>queue:</info>{$this->_runtime['queue']} <info>user:</info>{$this->_runtime['user']})");
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

	private function __validate()
	{
		$errors = array();

		// Validate Log path
		$this->_runtime['log'] = isset($this->params['log']) ? $this->params['log'] : Configure::read('Resque.default.log');
		if (substr($this->_runtime['log'], 0, 2) == './') {
			$this->_runtime['log'] =  TMP . 'logs' . DS . substr($this->_runtime['log'], 2);
		} elseif (substr($this->_runtime['log'], 0, 1) != '/') {
			$this->_runtime['log'] =  TMP . 'logs' . DS . $this->_runtime['log'];
		}

		// Validate Interval
		$this->_runtime['interval'] = isset($this->params['interval']) ? $this->params['interval'] : Configure::read('Resque.default.interval');
		if (!is_numeric($this->_runtime['interval'])) {
			$errors[] = __d('resque_console', 'Interval time is not valid. Please enter a valid number');
		}

		// Validate workers number
		$this->_runtime['workers'] = isset($this->params['workers']) ? $this->params['workers'] : Configure::read('Resque.default.workers');
		if (!is_numeric($this->_runtime['workers'])) {
			$errors[] = __d('resque_console', 'Workers number is not valid. Please enter a valid number');
		}

		$this->_runtime['queue'] = isset($this->params['queue']) ? $this->params['queue'] : Configure::read('Resque.default.queue');
		if ($this->_runtime['queue'] == '') {
			$errors[] = __d('resque_console', 'Please enter a queue name');
		}

		$this->_runtime['user'] = isset($this->params['user']) ? $this->params['user'] : get_current_user();
		// @todo Validate that user exists on the system

		foreach($errors as $error) {
			$this->out('<warning>Error: '.$error.'</warning>');
		}

		return empty($errors);
	}

}
