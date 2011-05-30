<?php

class ResqueShell extends Shell {
  var $uses = array();

  public function help() {
    echo <<<HELP
try one of the following:

  cake resque enqueue YourJobClass1 # enqueue your job

NOTE: need to be able to use passwordless sudo for:

  cake resque start   # start the worker service
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
    passthru('sudo tail -f /var/log/php-resque-worker.log');
  }

  public function start() {
    passthru('sudo /etc/init.d/php-resque-worker start');
  }

  public function stop() {
    passthru('sudo /etc/init.d/php-resque-worker stop');
  }

  public function restart() {
    passthru('sudo /etc/init.d/php-resque-worker restart');
  }
}
