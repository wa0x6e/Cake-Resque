##Changelog

###**v2.2.1** [2012-10-24]

* [new] Add .pot file for i18n. Help for translation are welcomed.

###**v2.2.0** [2012-10-23]

* [fix] Tracking job not working properly
* [new] Display failed job details using `track` when job status is *fail*

> Require php-resque-ex **1.0.14**.  
> Update dependencies with `composer update`


###**v2.1.0** [2012-10-16]

* [new] Add `track` command to track a job status

> A new `CakeResque.Job.track` setting has been added to the bootstrap file.  
> It's the master value to enable the job tracking status.
> You can also enable/disable tracking on a per-job basis,
> by passing `true`/`false` as fourth argument when queueing job via `CakeResque::enqueue()`.
>
> Job status tracking is disabled by default.  
> Job status is only kept for 24 hours.  
> *Unknown* will be returned 
> 
> - when job ID is invalid, 
> - when job status is expired, 
> - or when job status tracking is disabled.




###**v2.0.0** [2012-10-14]

* [new] Add `pause` command to pause one or all worker 
* [new] Add `resume` command to resume one or all paused worker
* [new] Add `cleanup` command to immediately terminate a worker's job
* [change] Add more documentation

###**v1.2.6** [2012-10-08]

* [new] Use your own php-resque library

###**v1.2.5** [2012-10-03]

* [fix] Strict Error warning when checking for existing user

###**v1.2.4** [2012-10-01]

* [new] Log Job ID for DebugKit resque panel

###**v1.2.3** [2012-10-01]

* [fix] Fix composer dependencies

###**v1.2.2** [2012-09-27]

* [new] Enqueuing a job return job id

###**v1.2.1** [2012-09-10]

* [Fix] Log correct method name when processing job

###**v1.2.0** [2012-09-08]

* [new] Add CakeResque proxy to enable jobs logging
> Refactor all your `Resque::enqueue()` call to `CakeResque::enqueue()`, to enable logging.  
> Install [DebugKitEx](https://github.com/kamisama/DebugKitEx) to view jobs log via DebugKit.  
> `Resque::enqueue()` still works.

###**v1.1.0** [2012-08-29]

* [new] Add `CakeResque.Redis.database` and `CakeResque.Redis.namespace` settings in bootstrap
> **database** to select the redis database (redis database are integer)  
> **namespace** to set the keys namespace (key prefix)  
> Add these new 2 keys to your bootstrap when you update
* [change] Remove CakeResqueComponent
> Remove it from $components in your AppController

###**v.1.0.0** [2012-08-27] 

* [fix] Restart was ignoring workers when they have the same arguments
* [fix] Restart was duplicating workers
* [fix] Various fixes and formatting (@josegonzalez)
* [fix] Starting a worker with `start` now return if the worker was successfully created
* [new] Overwrite bootstrap in app/Lib (@josegonzalez)
* [new] Pass additional environement variable to Resque (@josegonzalez)
* [new] Use Composer to manage dependencies
* [new] Use php-resque-ex instead of php-resque
* [new] `--log` option added to `start`, to specify the path of the log file. Each worker can have its own log
* [new] New `--log-handler` and `log-handler-target` options for `start`, to use another log engine
* [new] `stop` now stop individual worker from a list. Use `--all` flag to stop all workers
* [new] `tail` command display a list of logs to monitor
* [new] All options are validated
* [new] Add a *Resque_Job_Creator* class in bootstrap, to handle all jobs creation
* [change] Remove `--tail` options on `start`, prefer the `tail` command
* [change] Format code to CakePHP coding standard
* [change] Documentation removed from README, refer to [website](http://cakeresque.kamisama.me)
* [change] Rename all files and classes to the plugin name : CakeResque
* [ui] Various fixes and formatting

###**v.0.81** [2012-05-09] 

* [fix] Give the same name for workers count variable in all files

###**v.0.8** [2012-05-08] 

* [new] `Load` Command to start a batch of queues defined in you bootstrap, at once

###**v.0.72** [2012-05-07] 

* [fix] Fallback to Redisent when PhpRedis is not installed was broken

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