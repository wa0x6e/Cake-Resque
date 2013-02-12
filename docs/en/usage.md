# Usage {#usage}

Before queuing or scheduling jobs, you first need to create some workers to poll your queues, then creating jobs classes for each of your jobs.


## Managing workers {#workers}

Workers are managed exclusively via the [Cake console](http://book.cakephp.org/2.0/en/console-and-shells.html).  
Refer to [CLI commands](/commands) page for more details about each commands.

To **start** a worker, using the default settings defined in bootstrap

	./cake CakeResque.CakeResque start
	
To **start** a worker, with custom settings

	# Start a worker with a polling interval of 15 seconds, monitoring the 'mail' queue
	./cake CakeResque.CakeResque start --interval 15 --queue mail
	
You can also **stop** workers

	./cake CakeResque.CakeResque stop 
	# Append --all to stop all workers at once
	
*<small>If more than one worker is running, it'll prompt you for the worker to stop.</small>*

To **pause**/**resume** workers

	./cake CakeResque.CakeResque pause 
	./cake CakeResque.CakeResque resume
	# Append --all to pause/resume all workers at once
	
*<small>If the pause/resume command apply to more than one worker, it'll prompt you for the worker to pause/resume.</small>*

To view worker **stats**

	./cake CakeResque.CakeResque stats
	
To **tail** worker log file

	./cake CakeResque.CakeResque tail
	
*<small>If more than one log is available, it'll prompt you for the log to tail.</small>*

<div class="alert alert-info" markdown="1"><i class="icon-lightbulb"></i> **NOTES**  
Each of your queue must be polled by at least one worker. One worker can poll multiple queues, and multiple worker can poll the same queue.
</div>

Once your workers are started, you can begin adding jobs to them.


## Creating Job classes {#jobs}

But before we start queuing jobs, we first need a job. 

### What's a job ?
A job is a class that the worker will instantiate then run, with a list of arguments you passed along when queuing the job.

Our goal is to tell CakePHP that we don't want to execute the function lambda from the Model beta right now, but to keep it for later.  
A job class is a gateway to execute directly the lambda function from the Model beta directly, without re-executing all the previous steps.

### How to create a job classes

All job classes are just regular CakePHP Shell Classes, located in :

* <code><i class="icon-folder-open for-code"></i> app/Console/Command</code>
* or <code><i class="icon-folder-open for-code"></i> app/Plugin/PluginName/Console/Command</code>

You don't need a job class for each job, all jobs from the same model can be grouped in the same job class.

#### Example of job class

~~~ .language-php
// app/Console/Command/FriendShell.php
// -----------------------------------
<?php
App::uses('AppShell', 'Console/Command');
class FriendShell extends AppShell
{
	public $uses = array('Friend');

	/**
	 * Our first job, to find new friends for a user
	 **/
	public function findNewFriend() {
		// You can access the arguments you passed 
		// when queuing the job via $this->args
		$this->Friend->findNewFriends($this->args[0], $this->args[1]);
	}
	
	/**
	 * A second job, to notify friends of user activity
	 **/
	public function notifyFriend() {
		$this->Friend->notifyFriends($this->args[0]);
	}
}
~~~

All your shell classes must extend `AppShell`.   
Your `AppShell` class should implement the `perform()` method, as described in the installation guide, to be executable by the worker.

<div class="alert alert-error" markdown=1><i class="icon-exclamation-sign"></i> Restart your workers **each time** you make any changes to your job classes.</div>

<div class="alert alert-info" markdown=1>**<i class="icon-question-sign"></i>Why uses shell classes as job classes ?** <br/>
Because you can't instantiate a CakePHP model by itself, you have to pass through the dispatcher, etc ... And you can use all these classes in the Cake console. One stone two birds.</div>


## Queuing Jobs {#queueing}

There are 3 ways to queue a job : 

* **Immediately** add the job in the queue
* Add the job in the queue **after** a certain time
* Add the job in the queue **at** a certain time

The later 2 ways are used for job scheduling. 


### Immediately queue a job

~~~ .language-php
CakeResque::enqueue($queue, $jobClassName, $args, $track);
~~~

<table class="table">
	<tr>
		<th>Type</th>
		<th>Argument</th>
		<th>Description</th>
	</tr>
	<tr>
		<td markdown="1">*String*</td>
		<td markdown="1">`$queue`</td>
		<td markdown="1">Name of the queue to add the job to</td>
	</tr>
	<tr>
		<td markdown="1">*String*</td>
		<td markdown="1">`$jobClassName`</td>
		<td markdown="1">Job classname.<br/>
Plugin syntax (`PluginName.ClassName`) is also available.</td>
	</tr>
	<tr>
		<td markdown="1">*Array*</td>
		<td markdown="1">`$args`</td>
		<td markdown="1">List of arguments to pass to the job<br>
First index is the name of the function to call, within the Shell,
other indices are passed to the said function in `$this->args` variable.</td>
	</tr>
	<tr>
		<td markdown="1">*Boolean*</td>
		<td markdown="1">`$track`</td>
		<td markdown="1">*Optional*, default : `false`<br/>
 Whether to track the job status<br/>
Job tracking is a feature to know whether a
job is waiting, running, failed or completed. Statuses are only kept for 24 hours.<br/>
If omitted, the master value in bootstrap will be used</td>
	</tr>
</table>

#### Example

<div class="example"><div markdown=1>
~~~ .language-php
CakeResque::enqueue(
	'default', 
	'FriendShell', 
	array('findNewFriends' 'John Doe', 'Ghana')
);
~~~

This will add the job `FriendShell` with arguments `array('findNewFriends' 'John Doe', 'Ghana')` to the `default` queue.

Once the worker will find the job, it will instantiate the shell/job class **FriendShell**, then call the function **findNewFriends()** with the other elements of the `$args` array accessible via `$this->args`.  

Note that inside the `findNewFriends()` method, `$this->args` will be the following array :  
`array('John Doe', 'Ghana')`  
The first index (*findNewFriends*) is used internally, then removed.
</div></div>

## Scheduling Jobs {#scheduling}

### Queuing a job on a future date

You can specify *when* to queue the job.

~~~ .language-php
CakeResque::enqueueAt($time, $queue, $class, $args, $track);
~~~

<table class="table">
	<tr>
		<th>Type</th>
		<th>Argument</th>
		<th>Description</th>
	</tr>
	<tr>
		<td markdown="1">*DateTime|int*</td>
		<td markdown="1">`$time`</td>
		<td markdown="1">Date when you want to queue the job. Can be a datetime object or a timestamp.</td>
	</tr>
</table>

The last 4 arguments are the same arguments as the basic `CakeResque::enqueue()` above.

#### Example

<div class="example"><div markdown=1>
~~~ .language-php
CakeResque::enqueueAt(
	new DateTime('2012-01-26 15:56:23'),
	'default', 		// Queue Name
	'FriendShell', // Job classname
	array('findNewFriends' 'John Doe', 'Ghana') // Various args
);
~~~
</div></div>

### Queuing a job after a certain time

You can also queue a job after a certain time, for example after 5 minutes,
in case you don't have the exact absolute time, with `CakeResque::enqueueIn()`. It also takes 5 arguments :


~~~ .language-php
CakeResque::enqueueIn($wait, $queue, $class, $args, $track);
~~~

<table class="table">
	<tr>
		<th>Type</th>
		<th>Argument</th>
		<th>Description</th>
	</tr>
	<tr>
		<td markdown="1">*int*</td>
		<td markdown="1">`$wait`</td>
		<td markdown="1">Number of seconds to wait before queuing the job.</td>
	</tr>
</table>

Like with the `CakeResque:enqueueAt()`, last 4 arguments are the same as `CakeResque::enqueue()`.


##### Example
<div class="example"><div markdown=1>

~~~ .language-php
CakeResque::enqueueIn(
	3600, 			// Queue the job after 1 hour
	'default', 		// Queue Name
	'FriendShell', 	// Job classname
	array('findNewFriends' 'John Doe', 'Ghana') // Various args
);
~~~

</div></div>

### Notes

Job scheduling is *disabled* by default. To enable it :

* set `CakeResque.Scheduler.enabled` to `true` in the bootstrap
* start the *Scheduler Worker*

### The Scheduler Worker

Scheduling a job means that the job will be put on a temporary special queue. 

A special worker, the Scheduler Worker, will poll that queue periodically to move the due jobs to the right queue. To have scheduled jobs, you must have the Scheduler Worker running. 

* Scheduler Worker can be started using the [`startschedule`](commands#command-startscheduler) command. 

	~~~ .language-bash
	./cake CakeResque.CakeResque startscheduler
	~~~
  
	~~~ .language-bash
	# You can also specify the polling time, by default 3 seconds
	./cake CakeResque.CakeResque startscheduler -i 5
	~~~
	
	Unlike the regular [`start`](commands#command-start) command, the `-i` flag is the only flag 	accepted by the Scheduler Worker.
 
* Scheduler Worker is also automatically started using [`load`](commands#command-load) as long as  scheduled jobs is enabled.

<i class="icon-lightbulb"></i> **NOTES** : Only one Scheduler Worker can run at one time. Attempt to start multiple instance will fail. Beside the start process, this worker can be manipulated like any other regular worker, with [`stop`](commands#command-stop), [`pause`](commands#command-pause), [`resume`](commands#command-resume) and [`restart`](commands#command-restart).
	
### Scheduler limitations

#### Scheduler Worker

If the Scheduler Worker is not running for whatever reasons, scheduled jobs will accumulate in the scheduler queue, until you start the Scheduler Worker again. All past jobs will then be queued immediately, nothing is lost.

#### Time precision

Scheduling a job at a time X does not mean it will be executed at that time. It will be just queued at that time. When it will run will depend on the worker polling the queues (number of jobs already in the queue, worker polling time, etc ...)

**We are not scheduling when a job will execute, we are scheduling when it will be queued.**

## Logging {#logging}

By default, the worker will log all sort of actions, for debugging and information purpose. These logs are important, as they are the only way to monitor a worker, since it runs in the background.

Log output is split in 2 categories :

* **worker stream** : well formatted message from the worker
* **process stream** : php warning, fatal error, and other system related message

Each stream by default is logged into a different log engine.

* worker stream is redirected to a Monolog handler
* process stream is redirected to a plain text file

If the monolog handler is unavailable, worker stream will be redirected to the process stream.

Log stream settings are defined in the bootstrap. You can also override these settings using the `--log`, `--log-handler` and `--log-handler-target` flag when starting a worker.

### Worker Stream

<table class="table">
	<tr>
		<th>Key</th>
		<th>Description</th>
	</tr>
	<tr>
		<td markdown="1">`CakeResque.Log.handler`<br/>*String*</td>
		<td markdown="1">*Default Value : RotatingFile*<br/>
Name of the [Monolog](https://github.com/Seldaek/monolog) Handler, without the 'Handler' part.<br/>
List of supported handler [here](https://github.com/kamisama/Monolog-Init)
		</td>
	</tr>
	<tr>
		<td markdown="1">`CakeResque.Log.target`<br/>*String*</td>
		<td markdown="1">*Default Value : TMP . 'logs' . DS . 'resque-error.log'*
Argument passed to the Monolog handler.<br/>
Each handler takes its own type of argument.<br/><br/>
E.g.: **RotatingFile** takes a *pathname*, **Cube** takes an *url*.
		</td>
	</tr>
</table>

### Process Stream

<table class="table">
	<tr>
		<th>Key</th>
		<th>Description</th>
	</tr>
	<tr>
		<td markdown="1">`CakeResque.Worker.Log`<br/>*String*</td>
		<td markdown="1">*Default Value : TMP . 'logs' . DS . 'resque-worker-error.log'*</td>
	</tr>
</table>