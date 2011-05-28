<?php

App::import('Vendor', 'Resque.Resque', array('file' => 'php-resque'. DS .'lib'. DS .'Resque.php'));

class ResqueComponent extends Object {
  function initialize(&$controller, $settings = array()) {
    // Required if redis is located elsewhere
    Resque::setBackend(Configure::read('Resque.host') .':'. Configure::read('Resque.port'), Configure::read('Resque.port'));
  }
}
