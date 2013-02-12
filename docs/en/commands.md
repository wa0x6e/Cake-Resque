# Console commands

##### WORKERS RELATED
<div class="btn-group">
    <a href="#start" class="btn">start</a>
    <a href="#stop" class="btn">stop</a>
    <a href="#restart" class="btn">restart</a>
    <a href="#pause" class="btn">pause</a>
    <a href="#resume" class="btn">resume</a>
    <a href="#cleanup" class="btn">cleanup</a>
    <a href="#load" class="btn">load</a>
    <a href="#stats" class="btn">stats</a>
    <a href="#tail" class="btn">tail</a>
	<a href="#startscheduler" class="btn">startscheduler</a>
</div>

##### JOBS RELATED
<div class="btn-group">
	<a href="#enqueue" class="btn">enqueue</a>
	<a href="#enqueue-in" class="btn">enqueueIn</a>
	<a href="#enqueue-at" class="btn">enqueueAt</a>
    <a href="#track" class="btn">track</a>
</div>

<hr/>

<ul class="command-bloc"><li id="start">

	<pre class="console"><code class="no-highlight">cake CakeResque.CakeResque start</code></pre>
	<div class="description">
		<p><strong>To start one or multiple worker</strong></p>
	</div>

	<h5>Options</h5>
	
	<table class="table">
	<tr>
		<th>Option</th>
		<th>Value</th>
		<th>Description</th>
	</tr>
	<tr>
		<td markdown="1">`-u` or `--user`</td>
		<td markdown="1">*[username]*</td>
		<td markdown="1">User running your php application, usually **www-data** for apache on a linux box.<br/>  
<small>Using a different user than the php one can lead to permissions problems.</small></td>
	</tr>
	<tr>
		<td markdown="1">`-q` or `--queue`</td>
		<td markdown="1">*[queue[,queue]]*</td>
		<td markdown="1">A list of queues name, separated with a comma</td>
	</tr>
	<tr>
		<td markdown="1">`-i` or `--interval`</td>
		<td markdown="1">*[second]*</td>
		<td markdown="1">Number of seconds between each polling.</td>
	</tr>
	<tr>
		<td markdown="1">`-n` or `--workers`</td>
		<td markdown="1">*[count]*</td>
		<td markdown="1">Number of workers to create. All these workers will have the same options.<br/>
		<small>Uses pcntl to fork the process, ensure that you PHP is compiled with it.</small></td>
	</tr>
	<tr>
		<td markdown="1">`-l` or `--log`</td>
		<td markdown="1">*[path]*</td>
		<td markdown="1">Absolute or relative path to the *Process Stream* log file.<br/>
		<small>Relative path is relative to CakePHP *app/tmp/logs*.</small></td>
	</tr>
	<tr>
		<td markdown="1">`--log-handler`</td>
		<td markdown="1">*[handlername]*</td>
		<td markdown="1">*Worker Stream* log handler</td>
	</tr>
	<tr>
		<td markdown="1">`--log-handler-target`</td>
		<td markdown="1">*[args]*</td>
		<td markdown="1">Argument passed to the log handler</td>
	</tr>
	</table>
	
	

			
	<div class="alert alert-info"><i class="icon-book"></i> Refer to <a href="/usage#logging">Logging</a> section for more details on <code>--log</code>, <code>--log-handler</code> and <code>--log-handler-target</code> usage.</div>

	<div class="alert"><i class="icon-pushpin"></i> To create multiple workers with different options, just run <code>start</code> again with different options</div>
	
Workers will be started with default settings defined in bootstrap when the option is missing.


		<div class="example-bloc">
		<h6 data-toggle="collapse" data-target="#example-one"><i class="icon-file"></i> Examples</h6>

		<div id="example-one" class="collapse">
		<p>Let's start a new worker with default settings</p>

		<pre><samp class="input">cake CakeResque.CakeResque start</samp><samp class="output">Creating workers
Starting worker ... Done</samp></pre>

		<hr/>

		<p>Let's start 3 workers, polling the queue <code>user-activity</code></p>

					<pre><samp class="input">cake CakeResque.CakeResque start -n 3 -q user-activity</samp><samp class="output">Creating workers
Starting worker ... Done x3</samp></pre>

		<small><i class="icon-lightbulb"></i> Notice the <code>x3</code>, meaning that 3 workers are created.</small>

		<hr/>

		<p>Let's start a new worker, for running tests. Testing user is <code>jenkins</code>, and all output should go to <code>/var/log/resque-test.log</code></p>

					<pre><samp class="input">cake CakeResque.CakeResque start -u jenkins -l /var/log/resque-test.log</samp><samp class="output">Creating workers
Starting worker ... Done</samp></pre>

		</div>
	</div>

	<li id="stop">

	<pre class="console"><code class="no-highlight">cake CakeResque.CakeResque stop</code></pre>
	<div class="description"><p><strong>To stop workers</strong></p>
	<p>If more than one worker is running, it will display a list of worker to choose from.</p></div>

	
	<h5>Flags</h5>
	
	<table class="table">
	<tr>
		<th>Flag</th>
		<th>Description</th>
	</tr>
	<tr>
		<td markdown="1">`-f` or `--force`</td>
		<td markdown="1">Force workers shutdown, without waiting for jobs to finish processing.</td>
	</tr>
	<tr>
		<td markdown="1">`-a` or `--all`</td>
		<td markdown="1">Stop all workers at once</td>
	</tr>
	</table>

	<div class="example-bloc">
		<h6 data-toggle="collapse" data-target="#example-two"><i class="icon-file"></i> Examples</h6>

		<div id="example-two" class="collapse">
		<p>Let's try stopping workers. There is only one active worker.</p>

		<pre><samp class="input">cake CakeResque.CakeResque stop</samp><samp class="output">Stopping workers
Killing 15492 ... Done</samp></pre>
		<small><i class="icon-lightbulb"></i> <strong>15492</strong> is the PID of the worker process</small>

		<hr/>

		<p>If we have more than one active workers, it will display the list of workers</p>

					<pre><samp class="input">cake CakeResque.CakeResque stop</samp><samp class="output">Stopping workers
Active workers list :
[ 1] - KAMISAMA-MAC.local:15881:achievement, started 2 seconds ago
[ 2] - KAMISAMA-MAC.local:15867:default, started 3 seconds ago
[ 3] - KAMISAMA-MAC.local:15882:achievement, started 2 seconds ago
[ 4] - KAMISAMA-MAC.local:15868:default, started 3 seconds ago
[all] - Stop all workers
Worker to kill :  (1/2/3/4/all) </samp><samp class="input">></samp></pre>

		<p>It will ask you back for the number of the worker to kill, or type <code>all</code> to kill all workers. Let's stop the worker #2.</p>

		<pre><samp class="input">> 2</samp><samp class="output">Killing 15867 ... Done</samp></pre>

		<hr/>

		<p>To stop all workers at once</p>

		<pre><samp class="input">cake CakeResque.CakeResque stop --all</samp><samp class="output">Stopping workers
Killing 15881 ... Done
Killing 15882 ... Done
Killing 15868 ... Done</samp></pre>

		</div>
	</div>
	</li>

	<li id="restart">

	<pre class="console"><code class="no-highlight">cake CakeResque.CakeResque restart</code></pre>
	<div class="description"><p><strong>To restart all workers</strong></p></div>

	<div class="example-bloc">
		<h6 data-toggle="collapse" data-target="#example-three"><i class="icon-file"></i> Examples</h6>

		<div id="example-three" class="collapse">
		<p>Let's try stopping workers. There is only one active worker.</p>

		<pre><samp class="input">cake CakeResque.CakeResque restart</samp><samp class="output">Stopping workers
Killing 16080 ... Done
Killing 16094 ... Done
Killing 16081 ... Done
Killing 16095 ... Done

Restarting workers
Starting worker ... Done x2
Starting worker ... Done x2
</samp></pre>
		<small><i class="icon-lightbulb"></i> Notice the <code>x2</code>, meaning that each start create 2 new workers, for a total of 4 new workers.</small>
		</div>
	</div>

	</li>

	<li id="pause">

	<pre class="console"><code class="no-highlight">cake CakeResque.CakeResque pause</code></pre>
	<div class="description"><p><strong>To pause workers</strong></p>
	<p>If more than one worker is running, it will display a list of worker to choose from.</p></div>

	
	<h5>Flags</h5>
	<table class="table">
	<tr>
		<th>Flag</th>
		<th>Description</th>
	</tr>
	<tr>
		<td markdown="1">`-a` or `--all`</td>
		<td markdown="1">Pause all workers at once</td>
	</tr>
	</table>
	
	<div class="example-bloc">
		<h6 data-toggle="collapse" data-target="#example-pause"><i class="icon-file"></i> Examples</h6>

		<div id="example-pause" class="collapse">

		<p>If we have more than one active workers, it will display the list of workers</p>

					<pre><samp class="input">cake CakeResque.CakeResque stop</samp><samp class="output">Pausing workers
Active workers list :
[ 1] - KAMISAMA-MAC.local:15881:achievement, started 2 seconds ago
[ 2] - KAMISAMA-MAC.local:15867:default, started 3 seconds ago
[ 3] - KAMISAMA-MAC.local:15882:achievement, started 2 seconds ago
[ 4] - KAMISAMA-MAC.local:15868:default, started 3 seconds ago
[all] - Pause all workers
Worker to pause :  (1/2/3/4/all) </samp><samp class="input">></samp></pre>

		<p>It will ask you back for the number of the worker to pause, or type <code>all</code> to pause all workers. Let's pause the worker #2.</p>

		<pre><samp class="input">> 2</samp><samp class="output">Pausing 15867 ... Done</samp></pre>

		</div>
	</div>

	<div class="alert alert-info">Requires the php <a href="http://www.php.net/manual/en/book.pcntl.php">PCNTL extension</a></div>

	</li>

	<li id="resume">

	<pre class="console"><code class="no-highlight">cake CakeResque.CakeResque resume</code></pre>
	<div class="description"><p><strong>To resume paused workers</strong></p>

	<p>If more than one worker is paused, it will display a list of worker to choose from.</p>
</div>
	<h5>Flags</h5>
	<table class="table">
	<tr>
		<th>Flag</th>
		<th>Description</th>
	</tr>
	<tr>
		<td markdown="1">`-a` or `--all`</td>
		<td markdown="1">Resume all workers at once</td>
	</tr>
	</table>
	
	<div class="alert alert-info">Requires the php <a href="http://www.php.net/manual/en/book.pcntl.php">PCNTL extension</a></div>
	</li>

	<li id="cleanup">

	<pre class="console"><code class="no-highlight">cake CakeResque.CakeResque cleanup</code></pre>
	<div class="description"><p><strong>To cleanup workers</strong></p>
	<p>Cleaning up a worker will immediately force terminating the job it is working on.<br/>
	If more than one worker is running, it will display a list of worker to choose from.</p></div>

	
	<h5>Flags</h5>
	<table class="table">
	<tr>
		<th>Flag</th>
		<th>Description</th>
	</tr>
	<tr>
		<td markdown="1">`-a` or `--all`</td>
		<td markdown="1">Cleanup all workers at once</td>
	</tr>
	</table>
	<div class="alert alert-info">Requires the php <a href="http://www.php.net/manual/en/book.pcntl.php">PCNTL extension</a></div>

	</li>



	<li id="load">

	<pre class="console"><code class="no-highlight">cake CakeResque.CakeResque load</code></pre>
	<div class="description"><p><strong>To load all preconfigured workers</strong></p>

	<p>To start a batch of pre-configured queues (in your bootstrap). Documentation inside the bootstrap.php</p>
</div>

	<div class="example-bloc">
		<h6 data-toggle="collapse" data-target="#example-seven"><i class="icon-file"></i> Examples</h6>

		<div id="example-seven" class="collapse">
		<p>If you have 2 pre-configured workers</p>

		<pre><samp class="input">cake CakeResque.CakeResque load</samp><samp class="output">Loading predefined workers
Starting worker ... Done
Starting worker ... Done</samp></pre>
		</div>
	</div>


	</li>

	<li id="stats">

	<pre class="console"><code class="no-highlight">cake CakeResque.CakeResque stats</code></pre>
	<div class="description"><p><strong>To display some stats about the workers</strong></p></div>

	<div class="example-bloc">
		<h6 data-toggle="collapse" data-target="#example-four"><i class="icon-file"></i> Examples</h6>

		<div id="example-four" class="collapse">
		<p>With 2 active workers</p>

		<pre><samp class="input">cake CakeResque.CakeResque stats</samp><samp class="output">Resque Statistics
---------------------------------------------------------------


Jobs Stats
Processed Jobs : 3972
Failed Jobs    : 2

Queues Stats
Queues count : 1
- default	 : 0 pending jobs

Workers Stats
Workers count : 2
REGULAR WORKERS
* KAMISAMA-MAC.local:16391:default
 - Started on     : Wed Sep 26 16:22:16 EDT 2012
 - Processed Jobs : 0
 - Failed Jobs    : 0
* KAMISAMA-MAC.local:16406:default
 - Started on     : Wed Sep 26 16:22:23 EDT 2012
 - Processed Jobs : 0
 - Failed Jobs    : 0</samp></pre>

	</div>
</div>

	</li>

	<li id="tail">

	<pre class="console"><code class="no-highlight">cake CakeResque.CakeResque tail</code></pre>
	<div class="description"><p><strong>To monitor the logs files</strong></p>

	<p>Display the content of the log file onscreen. <br/>
	Will display a list of logs file to choose from, if more than one log file is present.</p>
</div>
	<div class="example-bloc">
		<h6 data-toggle="collapse" data-target="#example-five"><i class="icon-file"></i> Examples</h6>

		<div id="example-five" class="collapse">
		<p>If you have only one log file, located at <code>/var/log/resque-worker.log</code>, it will directly tail it</p>

		<pre><samp class="input">cake CakeResque.CakeResque tail</samp><samp class="output">Tailing log file
Tailing /var/log/resque-worker.log
[content of you log file]
...
...</samp></pre>

		<hr/>

		<p>If more than one log file is used, a list will be displayed</p>

		<pre><samp class="input">cake CakeResque.CakeResque tail</samp><samp class="output">Tailing log file
[ 1] - /var/log/resque-worker-1.log
[ 2] - /var/log/resque-worker-2.log
Choose a log file to tail : (1/2) </samp><samp class="input">> 1</samp><samp class="output">Tailing /var/log/resque-worker-1.log
[content of you log file]
...
...</samp></pre>

	</div>
</div>

	</li>

	<li id="startscheduler">

	<pre class="console"><code class="no-highlight">cake CakeResque.CakeResque startscheduler</code></pre>
	<div class="description"><p><strong>To start the Scheduler Worker</strong></p>

	<p>The Scheduler must be enabled in your bootstrap, under <code>CakeResque.Scheduler.enabled</code>.<br/>
		Only one scheduler worker can run at one time. <br/>
		The <code>load</code> command will automatically start the Scheduler Worker if scheduled jobs is enabled
	</p>

	</div>
	
	<h5>Options</h5>
	<table class="table">
	<tr>
		<th>Option</th>
		<th>Value</th>
		<th>Description</th>
	</tr>
	<tr>
		<td markdown="1">`-i` or `--interval`</td>
		<td markdown="1">*[second]*</td>
		<td markdown="1">Number of seconds between each polling.</td>
	</tr>

	</table>
	
	<div class="example-bloc">
		<h6 data-toggle="collapse" data-target="#example-startscheduler"><i class="icon-file"></i> Examples</h6>

		<div id="example-startscheduler" class="collapse">

		<pre><samp class="input">cake CakeResque.CakeResque startscheduler</samp><samp class="output">Creating the scheduler worker
Starting the scheduler worker ... Done</samp></pre>
		<hr/>

		<p>If you attempt to start an already started scheduler worker</p>

		<pre><samp class="input">cake CakeResque.CakeResque startscheduler</samp><samp class="output">Creating the scheduler worker
The scheduler worker is already running</samp></pre>

	</div>
</div>

	</li>

	<li id="enqueue">


		<pre class="console"><code class="no-highlight">cake CakeResque.CakeResque enqueue [queue] [jobclass] "[args[,args]]" [track]</code></pre>
		<div class="description"><p><strong>To enqueue a new job</strong></p></div>

		<p>Takes 3 arguments :</p>
		
		<table class="table">
	<tr>
		<th>Argument</th>
		<th>Description</th>
	</tr>
	<tr>

		<td markdown="1">`queue`</td>
		<td markdown="1">Name of the queue to add the job to</td>
	</tr>
	<tr>

		<td markdown="1">`jobclass`</td>
		<td markdown="1">Job classname.<br/>
Plugin syntax (`PluginName.ClassName`) is also available.</td>
	</tr>
	<tr>

		<td markdown="1">`args`</td>
		<td markdown="1"><p>List of arguments to pass to the job<br>
First index is the name of the function to call, within the Shell,
other indices are passed to the said function in `$this->args` variable.<br>
If passing multiple arguments, separate them with a comma.</p>
<div class="alert alert-error"><i class="icon-warning-sign"></i> Don't' forget to quote your arguments if they contains spaces</div></td>
	</tr>
	<tr>

		<td markdown="1">`track`</td>
		<td markdown="1">*Optional*, `0` or `1`<br/>
 Whether to track the job status<br/>
</td>
	</tr>
</table>

		<div class="example-bloc">
		<h6 data-toggle="collapse" data-target="#example-six"><i class="icon-file"></i> Examples</h6>
		<div id="example-six" class="collapse">
		<p>Enqueueing a new job</p>

		<pre><samp class="input">cake CakeResque.CakeResque enqueue default Friend "findNewFriends,John Doe,Ghana"</samp><samp class="output">Adding a job to worker
Successfully enqueued Job #687e11c818b10875be01aaf93fe7e2f0</samp></pre>

	</div>
</div>

	</li>

	<li id="enqueue-in">
	<pre class="console"><code class="no-highlight">cake CakeResque.CakeResque enqueueIn [wait]  [queue] [jobclass] "[args[,args]]" [track]</code></pre>
		<div class="description"><p><strong>To enqueue a new job after a number of seconds</strong></p></div>

		<p>Takes 4 arguments :</p>
		
				<table class="table">
	<tr>
		<th>Argument</th>
		<th>Description</th>
	</tr>
	<tr>

		<td markdown="1">`wait`</td>
		<td markdown="1">Number of seconds to wait before queueing the job</td>
	</tr>
	</table>
	
	Other four arguments are the same as in <a href="#enqueue"><code>enqueue</code></a>.

		<div class="example-bloc">
		<h6 data-toggle="collapse" data-target="#example-enqueue-in"><i class="icon-file"></i> Examples</h6>
		<div id="example-enqueue-in" class="collapse">
		<p>Scheduling a new job in 5 seconds</p>

		<pre><samp class="input">cake CakeResque.CakeResque enqueueIn 5 default Friend "findNewFriends,John Doe,Ghana"</samp><samp class="output">Adding a job to worker
Successfully scheduled Job #687e11c818b10875be01aaf93fe7e2f0</samp></pre>

	</div>
</div>

	</li>

	<li id="enqueue-at">
	<pre class="console"><code class="no-highlight">cake CakeResque.CakeResque enqueueAt [time] [queue] [jobclass] "[args[,args]]" [track]</code></pre>
		<div class="description"><p><strong>To enqueue a new job at a certain time</strong></p></div>

		<p>Takes 4 arguments :</p>
		
				<table class="table">
	<tr>
		<th>Argument</th>
		<th>Description</th>
	</tr>
	<tr>

		<td markdown="1">`wait`</td>
		<td markdown="1">Unix timestamp</td>
	</tr>
	</table>
	
	Other four arguments are the same as in <a href="#enqueue"><code>enqueue</code></a>.
		<div class="example-bloc">
		<h6 data-toggle="collapse" data-target="#example-enqueue-at"><i class="icon-file"></i> Examples</h6>
		<div id="example-enqueue-at" class="collapse">
		<p>Scheduling a new job</p>

		<pre><samp class="input">cake CakeResque.CakeResque enqueueAt 1359612628 default Friend "findNewFriends,John Doe,Ghana"</samp><samp class="output">Adding a job to worker
Successfully scheduled Job #687e11c818b10875be01aaf93fe7e2f0</samp></pre>

	</div>
</div>

	</li>

	<li id="track">
<pre class="console"><code class="no-highlight">cake CakeResque.CakeResque track [JOB-ID]</code></pre>
	<div class="description"><p><strong>To track the status of a job</strong></p>

	<p>To track a job status, you must enable it first. Set <code>CakeResque.Job.track</code> to <code>true</code> to enable job status
		tracking for all jobs.<br>
		 You can also enable it on a per-job basis: set the fourth argument of <code>CakeResque::enqueue()</code> to <code>true</code>.</p>

	<p>Job status are kept only for 24 hours. This command will return <code>Unknown</code> if the job ID is not valid, or when the job
		status is expired or disabled.</p>

	<p>When the job failed, this command will also display the job and error details.</p>

	</div>

	<div class="example-bloc">
		<h6 data-toggle="collapse" data-target="#example-track"><i class="icon-file"></i> Examples</h6>

		<div id="example-track" class="collapse">

		<pre><samp class="input">cake CakeResque.CakeResque track 687e11c818b10875be01aaf93fe7e2f0</samp><samp class="output">Tracking job status
Status : complete</samp></pre>
	</div>
</div></li></ul>