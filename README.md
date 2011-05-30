CakePHP PHP-Resque Plugin by Mike Smullin <mike@smullindesign.com>
============

** Lets you use Resque within the CakePHP environment, complete with cake shell and init script. **

Installation & Usage
------------

Place this directory in your plugins dir:

    ./app/plugins/resque/

Move the configuration directory files to your app's config directory,
and make sure to set the environment to whatever is appropriate:

    mv ./app/plugins/resque/config/* ./app/config/
    rm -rf ./app/plugins/resque/config/

    Configure::write('YourApp.environment', 'local');

    Configure::write('Resque.host', 'localhost');
    Configure::write('Resque.port', 6379);

Also add a reference to this configuration fromi the bottom of your ./app/config/core.php:

    /**
     * Include custom configuration files below.
     */
    include_once CONFIGS .'resque.php';

Download the latest version of php-resque to:

    ./app/plugins/resque/vendors/php-resque/

Symlink the php-resque-worker bash executable to init.d:

    sudo ln -s /var/www/.../app/plugins/resque/vendors/php-resque/php-resque-worker /etc/init.d/php-resque-worker

Also be sure to edit the file and change `ENV=local` to whatever is appropriate for that server

Then run the bash executable init script for the php-resque-worker, which will launch
a new fork polling the master Resque server for new jobs to run locally:

    sudo /etc/init.d/php-resque-worker start
    sudo tail -f /var/log/php-resque-worker.log

or you can now use the new CakePHP Shell:

    cake resque start
    cake resque tail

Executing this bash script will first bootstrap the CakePHP environment,
then query the Resque server for jobs, and execute them as a PHP-Resque
worker, one-at-a-time.

How to Queue a Job
------------

    var $components = array('Resque.Resque');

    function action() {
      Resque::enqueue('default', 'YourJobClass1', array($any, $params)); // queue it up
    }

or you can now use the new CakePHP Shell:

    cake resque enqueue YourJobClass1 any params

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

finally, you have to list your new job class under the config beginning on line 13 of:

    ./app/plugins/resque/config/resque_bootstrap.php

AND make sure to `sudo /etc/init.d/php-resque-worker restart` with each change to any of your job classes.

Future goals
------------

It would be nice to clean this up a bit so the install is easier. I think the
files are currently kind of scattered around but it works.

Credits
------------

CakePHP-PHP-Resque is written by Mike Smullin and is released under the WTFPL.

PHP-Resque is written by Chris Boulton see https://github.com/chrisboulton/php-resque

Based on Resque by defunkt see https://github.com/defunkt/resque
