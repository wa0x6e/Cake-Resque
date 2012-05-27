<?php

/**
 * Configure the default value for Resque
 *
 * ## Mandatory indexes :
 * Redis
 * 		default parameters used to connect to your redis server
 * default
 * 		default value used when creating a new queues
 *
 * ## Optional indexes :
 * queues
 * 		An array of queues to start with Resque::load()
 * 		Used when you have multiples queues, as you don't need
 * 		to start each queues individually each time you start Resque
 *
 *
 */
	 Configure::write('Resque', array(
		'Redis' => array('host' => 'localhost', 'port' => 6379),	// Redis server location
		'default' => array(
			'queue' => 'default',		// Name of the default queue
			'interval' => 5,			// Number of second between each works
			'workers' => 1 				// Number of forks for each workers
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
	));
