<?php

/**
 * Configure the default value for Resque
 *
 * ## Mandatory indexes :
 * Redis
 * 		default parameters used to connect to your redis server
 * default
 * 		default value used when creating a new queues
 * Resque
 * 		default values to init the php-resque library
 *
 * ## Optional indexes :
 * queues
 * 		An array of queues to start with Resque::load()
 * 		Used when you have multiple queues, as you don't need
 * 		to start each queues individually each time you start Resque
 *
 *
 */
	 Configure::write('Resque', array(
		'Redis' => array(
			'host' => 'localhost', 		// Redis server hostname
			'port' => 6379 				// Redis server port
		),

		'default' => array(
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
		 /*
		'queues' => array(
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

			// Path to the php-resque library,
			// relative to plugin vendor
			// Lib name follow Composer convention : vendor-name/project-name
			// If you wish use another php-resque library, such as
			// chrisboulton's original php-resque, you'll have to modify the
			// require object in the composer.json, and the following
			// lib accordingly to the new library name
			'lib' => 'kamisama/php-resque-ex'
		),

		// Other usefull environment variable you wish to set
		// Passing a key only will search for its value in the $_SERVER scope
		// eg : array('SERVER_NAME');
		// Passing a key and a value will set the env variable to this value
		// eg : array('ARCH' => 'x64')
		'environment_variables' => array(),

		'Log' => array(
			'handler' => 'RotatingFile',
			'target' => TMP . 'logs' . DS . 'resque-error.log'
		)
	));
