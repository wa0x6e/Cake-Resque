<?php

class ResqueShell extends Shell
{
	var $uses = array(), $log_path = null;

	/**
	 * Startup callback.
	 *
	 * Initializes defaults.
	 */
	public function startup()
	{
		$this->log_path = TMP . 'logs' . DS . 'php-resque-worker.log';
		 
		App::import('Lib', 'Resque.ResqueUtility');
		App::import('Vendor', 'Resque.Resque', array('file' => 'php-resque' . DS . 'lib' . DS . 'Resque.php'));
		App::import('Vendor', 'Resque.Resque_Stat', array('file' => 'php-resque' . DS . 'lib' . DS . 'Resque' . DS . 'Stat.php'));
		App::import('Vendor', 'Resque.Resque_Worker', array('file' => 'php-resque' . DS . 'lib' . DS . 'Resque' . DS . 'Worker.php'));
	}
		
	public function getOptionParser()
	{
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
							'help' => __d('resque_console', 'Name of the queue.')
						),
	    				'interval' => array(
    						'short' => 'i',
    						'help' => __d('resque_console', 'Pause time in seconds between each works')
	    				),
	  					 'number' => array(
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
			));
	    	

	}
	


	/**
	 * Manually enqueue a job via CLI.
	 *
	 * @param $job_class
	 *   Camelized job class name
	 * @param $args ...
	 *   (optional) one or more arguments to pass to job.
	 */
	public function enqueue()
	{
		if (count($this->args) < 1)
		{
			$this->out('Which job class would you like to enqueue?');
			return false;
		}

		$job_class = &$this->args[0];
		
		$params = array_diff_key($this->params, array_flip(array('working', 'app', 'root', 'webroot')));
		$paramstr = '';
		foreach ($params as $key => &$value)
		{
			$paramstr .= ($paramstr ? ', ' : '') . $key . ':' . $value;
		}

		Resque::enqueue($job_queue, $job_class, $params);
		$this->out('Enqueued new job "' . $job_class . '"' . ($paramstr ? ' with params (' . $paramstr . ')' : '') . '...');
	}

	/**
	 * Convenience functions.
	 */
	public function tail()
	{
		$log_path = $this->log_path;
		if (file_exists($log_path))
		{
			passthru('sudo tail -f ' . escapeshellarg($this->log_path));
		}
		else
		{
			$this->out('Log file does not exist. Is the service running?');
		}
	}


	/**
	 * Fork a new php resque worker service.
	 */
	public function start()
	{
		$queue = isset($this->params['queue']) ? $this->params['queue'] : Configure::read('Resque.queue');
		$user = isset($this->params['user']) ? $this->params['user'] : 'www-data';
		$interval = isset($this->params['interval']) ? $this->params['interval'] : Configure::read('Resque.interval');
		$count = isset($this->params['number']) ? $this->params['number'] : Configure::read('Resque.count');
		
		//exec('id apache 2>&1 >/dev/null', $out, $status); // check if user exists; cross-platform for ubuntu & redhat
		//$user = $status === 0 ? 'apache' : 'www-data';

		$path = App::pluginPath('Resque') . 'Vendor' . DS . 'php-resque' . DS;
		$log_path = $this->log_path;
		$bootstrap_path = App::pluginPath('Resque') . 'Lib' . DS . 'ResqueBootstrap.php';

		$this->out("<warning>Forking new PHP Resque worker service</warning> (<info>queue:</info>{$queue} <info>user:</info>{$user})");
		$cmd = 'nohup sudo -u '.$user.' bash -c "cd ' .
		escapeshellarg($path) . '; VVERBOSE=true QUEUE=' .
		escapeshellarg($queue) . ' APP_INCLUDE=' .
		escapeshellarg($bootstrap_path) . ' INTERVAL=' .
		escapeshellarg($interval) . ' CAKE=' .
		escapeshellarg(CAKE) . ' COUNT=' . $count .
		 ' php ./resque.php';
		$cmd .= ' > '. escapeshellarg($log_path).' 2>&1" >/dev/null 2>&1 &';
		
		passthru($cmd);
		
		if ($this->params['tail'])
		{
			sleep(3); // give it time to output to the log for the first time
			$this->tail();
		}
		
	}

	/**
	 * Kill all php resque worker services.
	 */
	public function stop()
	{
		$this->out('<warning>Shutting down Resque Worker complete</warning>');
		$workers = Resque_Worker::all();
		if (empty($workers))
		{
			$this->out('   There were no active workers to kill ...');
		}
		else
		{
			$this->out('Killing '.count($workers).' workers ...');
			foreach($workers as $w)
			{
				list($hostname, $pid, $queues) = explode(':', $w, 3);
				
				$this->params['force'] ? $w->shutDownNow() : $w->shutDown();	// Send signal to stop processing jobs
				$w->unregisterWorker();											// Remove jobs from resque environment
				exec('Kill -9 '.$pid);											// Kill all remaining system process
			}
		}
		
	}

	/**
	 * Kill all php resque worker services, then restart a single new one, and tail the log.
	 */
	public function restart()
	{
		$this->stop();
		$this->start();
	}

	/**
	 * List available jobs to enqueue.
	 */
	public function jobs()
	{
		if (empty($this->args))
		{
			$this->out("\n");
			$this->out('Available Jobs');
			$this->hr();
			$jobs = ResqueUtility::getJobs();
	
			if (empty($jobs))
			{
				$this->out('<info>No jobs found</info>');
			}
			else
			{
				$jobs = array_keys($jobs);
				foreach ($jobs as $job)
				{
					$this->out("  - " . substr(basename($job), 0, -5));
				}
			}
			$this->out("\n");
		}
		else
		{
			$jobName = $this->args[0] . 'Shell';
			$this->dispatchShell($this->args[0], 'main');
		}
	}
	
	
	public function stats()
	{
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
		
		if (!empty($workers))
		{
			foreach($workers as $worker)
			{
				$this->out("\tWorker : " . $worker);
				$this->out("\t - Started on     : " . Resque::redis()->get('worker:' . $worker . ':started'));
				$this->out("\t - Processed Jobs : " . $worker->getStat('processed'));
				$worker->getStat('failed') == 0
					? $this->out("\t - Failed Jobs    : " . $worker->getStat('failed'))
					: $this->out("\t - <warning>Failed Jobs    : " . $worker->getStat('failed') . "</warning>");
			}
		}
		
		$this->out("\n");
	}
}
