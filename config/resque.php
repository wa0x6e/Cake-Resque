<?php

switch (Configure::read('YourApp.environment')) {
  default:
  case 'production':
    $config['Resque']['Redis'] = array(
      'host' => 'localhost', // replace with outside server for best performance
      'port' => 6379
    );
    break;

  case 'staging':
  case 'local':
    $config['Resque']['Redis'] = array(
      'host' => 'localhost',
      'port' => 6379
    );
    break;
}
