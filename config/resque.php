<?php

include_once CONFIGS .'yourapp.php';
switch (Configure::read('YourApp.environment')) {
  default:
  case 'production':
    Configure::write('Resque.host', 'localhost'); // replace with outside server for best performance
    Configure::write('Resque.port', 6379);
    break;

  case 'staging':
  case 'local':
    Configure::write('Resque.host', 'localhost');
    Configure::write('Resque.port', 6379);
    break;
}
