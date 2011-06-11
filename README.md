CakePHP PHP-Resque Plugin by Mike Smullin <mike@smullindesign.com>
============

** Lets you use Resque within the CakePHP environment, complete with cake shell. **

Installation & Usage
------------

Place this directory in your plugins dir:

    git submodule add git://github.com/mikesmullin/CakePHP-PHP-Resque-Plugin.git ./app/plugins/resque/

Download the latest version of Chris Boulton's php-resque into `./app/plugins/resque/vendors/php-resque/`, as well:

    git submodule update --init --recursive

Edit the file `./app/plugins/resque/config/resque.php` and remember to
configure whatever Resque.Redis.host and port are appropriate for your environment:

    switch (Configure::read('YourApp.environment')) {

    $config['Resque']['Redis'] = array(
      'host' => 'localhost',
      'port' => 6379
    );

Then launch a new php-resque-worker fork, which will begin polling the master
Resque server for new jobs to run locally:

    cake resque help # to see available options
    cake resque start

How to Queue a Job
------------

    var $components = array('Resque.Resque');

    function action() {
      Resque::enqueue('default', 'YourJobClass1', array($any, $params)); // queue it up
    }

or you can use the CakePHP Shell:

    cake resque jobs # to see a list of available jobs
    cake resque enqueue YourJobClass1 any other params

How to Write a Job
------------

in the above example it would be a file saved as:

    ./app/vendors/shells/jobs/your_job_class1.php

and the code would look like:

    class YourJobClass1 extends ResqueShell {
      function perform() {
        # CakePHP environment is within scope via ResqueShell base class and App::import()
        $this->loadModel('User');
        $users = $this->User->find('all');
        // ...
        $this->out('Done');
      }
    }

AND make sure to `cake resque restart` with each change to any of your job classes.

Credits
------------

CakePHP-PHP-Resque is written by Mike Smullin and is released under the WTFPL.

PHP-Resque is written by Chris Boulton see https://github.com/chrisboulton/php-resque

Based on Resque by defunkt see https://github.com/defunkt/resque
