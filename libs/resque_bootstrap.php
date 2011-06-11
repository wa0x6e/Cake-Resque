<?php

// bootstrap CakePHP
// see also: http://debuggable.com/posts/the-ultimate-cakephp-bootstrap-technique:480f4dd5-2bcc-40cb-b45f-4b1dcbdd56cb
global $argv; $argv[] = '-env'; $argv[] = getenv('ENV')? getenv('ENV') : 'local'; // my custom environment argument
$_GET['url'] = 'favicon.ico';
require_once getenv('WEBROOT') .'index.php';

// utilize environment-based configuration to set Redis backend
Configure::load('Resque.resque');
putenv('REDIS_BACKEND='. Configure::read('Resque.Redis.host') .':'. Configure::read('Resque.Redis.port'));

// initialize ResqueShell base class
class ResqueShell {
  protected function loadModel($modelName) {
    if (App::import('Model', $modelName)) {
      $this->$modelName = new $modelName;
      return true;
    }
    return false;
  }

  protected function out($s, $line_break = true) {
    echo $s . ($line_break? "\n" : '');
  }
}

foreach (Configure::read('Resque.jobs') as $job) {
  require_once APP .'vendors'. DS .'shells'. DS .'jobs'. DS . $job .'.php';
}
