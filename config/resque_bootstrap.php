<?php

// bootstrap CakePHP
// see also: http://debuggable.com/posts/the-ultimate-cakephp-bootstrap-technique:480f4dd5-2bcc-40cb-b45f-4b1dcbdd56cb
global $argv; $argv[] = '-env'; $argv[] = getenv('ENV')? getenv('ENV') : 'local'; // my custom environment argument
$_GET['url'] = 'favicon.ico';
require_once(__DIR__ .'/../webroot/index.php');

// utilize environment-based configuration to set Redis backend
putenv('REDIS_BACKEND='. Configure::read('Resque.host') .':'. Configure::read('Resque.port'));

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

foreach (array(
  // list your jobs here...
  'your_job_class1',
  'your_job_class2',
  'your_job_class3'
) as $job) {
  require_once APP .'vendors'. DS .'shells'. DS .'jobs'. DS . $job .'.php';
}
