<?php

$config['Resque']['jobs'] = array(
  // list your jobs here...
  'your_job_class1',
  'your_job_class2',
  'your_job_class3'
);

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
