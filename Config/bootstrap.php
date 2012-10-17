<?php
/**
 * CakeResque configuration file
 *
 * Use to set the default values for the workers settings
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author        Wan Qi Chen <kami@kamisama.me>
 * @copyright     Copyright 2012, Wan Qi Chen <kami@kamisama.me>
 * @link          http://cakeresque.kamisama.me
 * @package       CakeResque
 * @subpackage	  CakeResque.Config
 * @since         0.5
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Configure the default value for Resque
 *
 * ## Mandatory indexes :
 * Redis
 * 		default values used to connect to redis server
 * Worker
 * 		default values used for creating new worker
 * Resque
 * 		default values to init the php-resque library
 *
 * ## Optional indexes :
 * Queues
 * 		An array of queues to start with Resque::load()
 * 		Used when you have multiple queues, as you don't need
 * 		to start each queues individually each time you start Resque
 * Env
 * 		Additional environment variables to pass to Resque
 * Log
 * 		Log handler and its arguments, to save the log with Monolog
 *
 */
	 Configure::write('CakeResque', array(
		'Redis' => array(
			'host' => 'localhost', 		// Redis server hostname
			'port' => 6379 ,			// Redis server port
			'database' => 0,			// Redis database number
			'namespace' => 'resque'		// Redis keys namespace
		),

		'Worker' => array(
			'queue' => 'default',		// Name of the default queue
			'interval' => 5,			// Number of second between each poll
			'workers' => 1, 			// Number of workers to create

			// Path to the log file
			// Can be an
			// - absolute path,
			// - an relative path, that will be relative to
			// 	 app/tmp/logs folder
			// - a simple filename, file will be created inside app/tmp/logs
			'log' => TMP . 'logs' . DS . 'resque-worker.log'
		),
		'Job' => array(
			// Whether to track job status
			// Enabling this will allow you to track a job status by its ID
			// Job status are purged after 24 hours
			//
			// You can also defined per-job tracking by passing true/false
			// as fourth argument when calling CakeResque::enqueue();
			'track' => false
		),
		 /*
		'Queues' => array(
			array(
	 			'queue' => 'default',	// Use default values from above for missing interval and count indexes
	 			'user' => 'www-data'	// If PHP is running as a different user on you webserver
			),
	 		array(
				'queue' => 'my-second-queue',
				'interval' => 10
			)
		)
		*/
		'Resque' => array(

			// Path to the php-resque library
			//
			// Relative or absolute path to the php-resque library
			// If you are using Composer to install dependencies,
			// this is the name of the vendor library
			// Path is relative to the CakeResque/vendor
			// Don't add trailing slash to path
			'lib' => 'kamisama/php-resque-ex'
		),

		// Other usefull environment variable you wish to set
		// Passing a key only will search for its value in the $_SERVER scope
		// eg : array('SERVER_NAME'); => will search for the value in $_SERVER['SERVER_NAME']
		// Passing a key and a value will set the env variable to this value
		// eg : array('ARCH' => 'x64')
		'Env' => array(),

		// Log Handler
		// If saving the logs in a plain text file doesn't suit you
		// you can send them to Mysql, or MongoDB, etc ...
		// In that case, you'll need a handler to manage your logs
		// All logs outputted by resque will go to the handler.
		// The classic log file (above) will still be used, for logging
		// stuff likes php error, or other STDOUT outputted by your job classses
		//
		// php-resque-ex uses Monolog to manage all the logging stuff
		// If you uses the original php-resque library, these settings
		// will be ignored
		//
		// handler
		//		Name of the Handler (the handler classname, without the 'Handler' part)
		// target
		//		Arguments taken by the handler constructor. If the handler required
		//		multiple arguments, separate them with a comma
		//
		// As of now, the following handler are supported:
		//
		// [HANDLER]		[TARGET]
		// Cube 			Cube server address (e.g: http://127.0.0.1:1081)
		// RotatingFile 	Path to the log file (e.g: /path/to/resque.log)
		// Syslog 			Facility name
		// Socket 			Address (e.g: udp://127.0.0.1:23)
		// MongoDB 			MongoDB server address  (e.g: mongodb://localhost:27017)
		'Log' => array(
			'handler' => 'RotatingFile',
			'target' => TMP . 'logs' . DS . 'resque-error.log'
		)
	));

	require_once dirname(__DIR__) . DS . 'Lib' . DS . 'CakeResque.php';

