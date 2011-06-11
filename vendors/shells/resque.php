<?php

class ResqueShell extends Shell {
  var $uses = array();

  public function help() {
    echo <<<HELP
try one of the following:

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
    global $argv;
    $args = array_slice($argv, 5);
    if (count($args) < 1) {
      $this->out('Which job class would you like to enqueue?');
      return false;
    }

    $job_class = array_shift($args);
    App::import('Component', 'Resque.Resque');
    Resque::enqueue($job_queue = 'default', $job_class, $args);
    $this->out("Enqueued new job '{$job_class}'...");
  }

  /**
   * Convenience functions.
   */
  public function tail() {
    $log_path = $this->getLogPath();
    if (file_exists($log_path)) {
      passthru('sudo tail -f '. escapeshellarg($this->getLogPath()));
    }
    else {
      $this->out('Log file does not exist. Is the service running?');
    }
  }

  private function getLogPath() {
    return TMP .'logs'. DS .'php-resque-worker.log';
  }

  /**
   *
   */
  public function start() {
    $env = orEquals($this->params['env'], 'local');
    $queue = orEquals($this->params['queue'], 'default');
    exec('id apache 2>&1 >/dev/null', $out, $status); // check if user exists; cross-platform for ubuntu & redhat
    $user = orEquals($this->params['user'], $status===0? 'apache' : 'www-data');

    $path = App::pluginPath('Resque') . 'vendors'. DS .'php-resque'. DS;
    $log_path = $this->getLogPath();
    $config_path = CONFIGS . 'resque_bootstrap.php';
    $php = trim(`which php`);

    $this->out("starting php-resque-worker env:{$env} queue:{$queue} user:{$user}...");
    passthru($cmd = 'nohup sudo -u '.
      escapeshellarg($user) .' sh -c "cd '.
      escapeshellarg($path) .'; ENV='.
      escapeshellarg($env) .' VVERBOSE=true QUEUE='.
      escapeshellarg($queue) .' APP_INCLUDE='.
      escapeshellarg($config_path) .' '.
      escapeshellarg($php) .' ./resque.php > '.
      escapeshellarg($log_path) .' 2>&1" >/dev/null 2>&1 &');

    $this->tail();
  }

  public function stop() {
    $this->out('stopping php-resque-worker...');
    passthru('ps aux | grep resque\\\\.php | awk \'{print$2}\' | sort -rn | xargs sudo kill -s 9');
  }

  public function restart() {
    $this->stop();
    $this->start();
  }
}
