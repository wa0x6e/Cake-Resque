<?php

class ResqueShell extends Shell {
  var $uses     = array(),
      $log_path =  null;

  /**
   * Startup callback.
   *
   * Initializes defaults.
   */
  public function startup() {
    $this->log_path = TMP .'logs'. DS .'php-resque-worker.log';
  }

  /**
   * Provides end-user with helpful instructions.
   */
  public function help() {
    echo <<<HELP
try one of the following:

  cake resque jobs # list all known jobs
  cake resque enqueue YourJobClass1 # enqueue your job

NOTE: need to be able to use passwordless sudo for:

  cake resque start   # start the worker service
    supports arguments:
    -env=local
    -queue=default
    -user=www-data
  cake resque tail    # tail the worker service log
  cake resque restart # restart the worker service
  cake resque stop    # stop the worker service


HELP
    ;
  }

  /**
   * Manually enqueue a job via CLI.
   *
   * @param $job_class
   *   Camelized job class name
   * @param $args ...
   *   (optional) one or more arguments to pass to job.
   */
  public function enqueue() {
    if (count($this->args) < 1) {
      $this->out('Which job class would you like to enqueue?');
      return false;
    }

    $job_class = array_shift($this->args);
    App::import('Component', 'Resque.Resque');
    Resque::enqueue($job_queue = 'default', $job_class, $this->args);
    $this->out("Enqueued new job '{$job_class}'...");
  }

  /**
   * Convenience functions.
   */
  public function tail() {
    $log_path = $this->log_path;
    if (file_exists($log_path)) {
      passthru('sudo tail -f '. escapeshellarg($this->log_path));
    }
    else {
      $this->out('Log file does not exist. Is the service running?');
    }
  }

  /**
   * Fork a new php resque worker service,
   * and tail the log.
   */
  public function start() {
    $env = orEquals($this->params['env'], 'local');
    $queue = orEquals($this->params['queue'], 'default');
    exec('id apache 2>&1 >/dev/null', $out, $status); // check if user exists; cross-platform for ubuntu & redhat
    $user = orEquals($this->params['user'], $status===0? 'apache' : 'www-data');

    $path = App::pluginPath('Resque') .'vendors'. DS .'php-resque'. DS;
    $log_path = $this->log_path;
    $bootstrap_path = App::pluginPath('Resque') .'libs'. DS .'resque_bootstrap.php';
    $php = trim(`which php`);

    $this->out("Forking new PHP Resque worker service (env:{$env} queue:{$queue} user:{$user})...");
    passthru($cmd = 'nohup sudo -u '.
      escapeshellarg($user) .' sh -c "cd '.
      escapeshellarg($path) .'; ENV='.
      escapeshellarg($env) .' VVERBOSE=true QUEUE='.
      escapeshellarg($queue) .' APP_INCLUDE='.
      escapeshellarg($bootstrap_path) .' WEBROOT='.
      escapeshellarg(APP .'webroot'. DS) .' '.
      escapeshellarg($php) .' ./resque.php > '.
      escapeshellarg($log_path) .' 2>&1" >/dev/null 2>&1 &');

    $this->tail();
  }

  /**
   * Kill all php resque worker services.
   */
  public function stop() {
    $this->out('Killing any/all existing PHP Resque worker services...');
    passthru('ps aux | grep resque\\\\.php | awk \'{print$2}\' | sort -rn | xargs sudo kill -s 9');
  }

  /**
   * Kill all php resque worker services, then restart a single new one, and tail the log.
   */
  public function restart() {
    $this->stop();
    $this->start();
  }

  /**
   * List available jobs to enqueue.
   */
  public function jobs() {
    Configure::load('Resque.resque');
    $this->out("List of jobs currently available for enqueue:\n\n", false);
    foreach ((array) Configure::read('Resque.jobs') as $job) {
      $this->out("  - " . Inflector::camelize($job) ."\n", false);
    }
    $this->out("\nDon't see your job listed? Add it in ./app/config/resque.php\n\n", false);
  }
}
