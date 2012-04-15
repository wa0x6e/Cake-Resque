#CakeResque

CakeResque is a CakePHP plugin for creating background jobs that can be processed offline later.

##What you can do with cakeResque

The main goal is to delay some non-essential tasks to later, reducing the waiting time for the user.

###Example
Let's say a lambda user want to update his location, and your website has a lot of social features centered around the user. Updating the location will :

* Update the user's location in the users table (or whatever, depending on your structure) [takes 0.2s]
* Find some new friends around the user's new location [takes 0.8s]
* Update user's activity stream (*eg. Lamdba is now living in Ghana*) [takes 0.3s]
* … and a lot of other stuff like sending emails, refreshing cache etc … [takes 1s]

But the user doesn't care about all of these, and just wanted to update his location. You should return a response immediatly after the first point, and delay the other tasks for later. These tasks should be processed offline, in a separate process, and shouldn't affect the user experience. User should just wait 0.2s instead of 2.3s 

##How it works

Instead of calling for example `findNewFriends($userId)` in your `afterSave()` callback, you call `Resque::enqueue('default', 'findNewFriends', $userId)`. The function will not be called, it will just be added in a temporary list, as a **job**. Later (each x seconds), another process (the **worker**) in the background will read that list, and execute each jobs.

CakeResque is just a tool for using Resque within you CakePHP application. The heart of the plugin is Resque, or precisely PHP-Resque, a php port of Resque, originally written in Ruby and developed by the folks at github.

> Resque (pronounced like "rescue") is a Redis-backed library for creating background jobs, placing those jobs on multiple queues, and processing them later.

Read the [Resque](https://github.com/defunkt/resque) official page for more details on how background jobs, workers and jobs if these terms doesn't sound familiar to you.

##Requirements

To use the plugin, you will need :

* [CakePHP 2.0](http://cakephp.org/) (or higher)
* [Redis](http://www.redis.io) 2.2 (or higher) (to store the jobs list)
* [PHPRedis extension for PHP](https://github.com/nicolasff/phpredis) (PHP API for communicating with redis)

Installation of Redis and PhpRedis are detailed in their own homepage. If you can't install PHPRedis, it will fallback to Redisent, another Redis API, included in the plugin. It's not as performant as PHPRedis though.

##Installation

1. Drop the folder in app/Plugin directory

2. Load the Plugin in your *app/Config/bootstrap.php*

		CakePlugin::loadAll(array(
			'Resque' => array('bootstrap' => true)
		));

3. Load the component in your *app/Controller/AppController.php*
	
		public $components = array('Resque.Resque');

4. Create the **AppShell.php** file in *app/Console/Command*, if it doesn't exist

5. Add the following method to AppShell.php

		public function perform()
		{
			$this->initialize();
			$this->{array_shift($this->args)}();
		}

##Configuration

I should assume that at this point, you have redis installed and running. We just have to let the plugin know how to connect to your redis server, and do some basic configuration.  
Replace **host** and **port** in *app/Plugin/Resque/Config/bootstrap.php* with yours
	<?php

	 Configure::write('Resque', array(
		'Redis' => array('host' => 'localhost', 'port' => 6379),	// Redis server location
		'queue' => 'default',										// Name of the default queue
		'interval' => 5,											// Number of second between each works
		'count' => 1												// Number of forks for each workers
	));
 

##Usage

In order to process the background jobs, at least one worker have to be running, and pooling a jobs' list.

There's two step : start the workers, and send the jobs to the workers

###Manage the workers

A shell is available to manage the workers, just call 

	cake Resque.resque

Available sub-commands are :

* **start**

To start a new resque worker. Be default, it will use the default configuration defined in the bootstrap, and create a queue named default (`queue`), and a worker that will be pooling this queue each 5 seconds (`interval`). When the queue contains some jobs, `count` workers will be forked to process the jobs. Starts does takes options :

**-u** User running the php process. Default is the current user running the command. Must be defined if your php is running under a different user, or it will not have the permission to shutdown the workers.

**-q** A list of queues, separated with a comma : to create multiple queues at the same time. eg : `-q 5squeue,10squeue, 15squeue`, or it will fallback to the queue defined in the bootstrap.

**-i** Number of seconds between pooling each queues. Default to the bootstrap one.

**-n** Number of workers working on the same queue. Uses pcntl to fork the process, ensure that you PHP is compiled with it.

For creating multiple queues with different options, just run start again.


* **stop**

To shutdown all resque workers.

* **restart**

To restart the workers, with their previous settings

* **stats**

Display total number of failed/processed jobs, as well as stats for each workers.

* **tail**

Tail the workers' logs. Each workers activity are logged in `app/temp/logs/php-resque-worker.log`
You should also see the workers activity via `redis-cli monitor`.

###Enqueue jobs

Today main goal is to enqueue jobs, and have a worker process it later. To enqueue a job :

	Resque::enqueue('default', 'Friend', array('findNewFriends' 'John Doe', 'Ghana'));

This will add the job `Friend` with arguments `array('findNewFriends' 'John Doe', 'Ghana')` to the `default` queue.

* First argument is the name of the queue to add the job to (you can create as many queue as you like, with a different interval time between each pooling).
* Second argument is the name of the Shell. You can also use the plugin syntax to reference a plugin Shell : `Pluginname.Modelnane` (more details below)
* Third argument is an array of arguments. First index is the name of the function to call, within the Shell, other indices are passed to the function called

As you know, we can't call directly a method within CakePHP. You **can not** just do that

	<?php
		include('app/Model/Friend.php');

		$friend = new Friend();
		$friend->findNewFriend('John Dow', 'Ghana');
	?>
For a model to work, you have to also load the Router, the associated models, behaviors, database configuration, the cakephp core etc … All of these tasks are done automatically when calling something via the Cake Dispatcher. There is two ways of calling the dispatcher : the web front (http), and the cli (cake shell). We will use the cake shell, for obvious reasons (lightweight, no views rendering, helpers etc …).

To process a job, just create the Shell class `app/Console/Command/FriendShell.php`
	
	<?php

	class FriendShell extends AppShell
	{
		public $uses = array('Friend');

		public function findNewFriend()
		{
			// $this->args == array('John Doe', 'Ghana')
			$this->Friend->findNewFriends($this->args[0], $this->args[1]);
		}
	}
	
Put the Shell class in `app/Console/Command`, or if you're using a plugin shell, in `YourPlugin/Console/Command`.
All Shell class must extends `AppShell` in order to be visible by Resque.

**You have to restart the workers when you make any changes to your jobs classes**

Using the shell is not hard, and if you're not familiar with it, read the [official documentation](http://book.cakephp.org/2.0/en/console-and-shells.html).
If you read until here, I assume that you have more than basic knowledge about cakePHP. You must have a pretty big application to seek delayed jobs :)

##Changelog

###**v.0.71** [2012-03-31] 

* [fix] Shell outside Plugin folder where not found

###**v.0.7** [2012-03-31] 

* [fix] Use user defined redis server configuration for resque


###**v.0.6** [2012-03-14] 
 
* Removed jobs command
* Added CakePHP plugin syntax (*Plugin.Model*) when referencing classname: job classes doesn't have to be located in `app/Console/Command ` anymore, you can leave them in `PluginName/Console/Command`, as long as you extends the `AppShell` class, that contains a `perform` method
* Updated php-resque to latest version
* Added Redisent support: php-resque fallback to Redisent if phpRedis is not installed
* Enabled namespace for all resque keys in redis
* Changed cli `enqueue` command to accept the same arguments as the php one

###**v.0.5** [2012-02-19] 

* `restart` now restore all workers with their options



##Credits

* CakeResque is a fork of [CakePHP-PHP-Resque](https://github.com/mikesmullin/CakePHP-PHP-Resque-Plugin) by Mike Smullin
* [PHP-Resque](https://github.com/chrisboulton/php-resque) is written by Chris Boulton 
* Based on [Resque](https://github.com/defunkt/resque) by defunkt
