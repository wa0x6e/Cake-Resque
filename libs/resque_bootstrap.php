<?php

// bootstrap CakePHP
// see also: http://debuggable.com/posts/the-ultimate-cakephp-bootstrap-technique:480f4dd5-2bcc-40cb-b45f-4b1dcbdd56cb
global $argv; $argv[] = '-env'; $argv[] = getenv('ENV')? getenv('ENV') : 'local'; // my custom environment argument
$_GET['url'] = 'favicon.ico';
require_once getenv('WEBROOT') .'index.php';

// utilize environment-based configuration to set Redis backend
Configure::load('Resque.resque');
putenv('REDIS_BACKEND='. Configure::read('Resque.Redis.host') .':'. Configure::read('Resque.Redis.port'));

// include ResqueShell base class
App::import('Lib', 'Resque.ResqueShell');

// include job class
App::import('Lib', 'Resque.ResqueUtility');
foreach (ResqueUtility::getJobs() as $job) {
  require_once $job;
}
