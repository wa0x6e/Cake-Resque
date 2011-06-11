<?php

App::import('Vendor', 'Resque.Resque', array('file' => 'php-resque'. DS .'lib'. DS .'Resque.php'));

class ResqueComponent extends Object {
  function initialize(&$controller, $settings = array()) {
    Configure::load('Resque.resque');
    Resque::setBackend(Configure::read('Resque.Redis.host') .':'. Configure::read('Resque.Redis.port'), Configure::read('Resque.Redis.port'));
  }
}
