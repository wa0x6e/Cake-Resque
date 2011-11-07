<?php

	$config['Resque'] = array(
		'Redis' => array('host' => 'localhost', 'port' => 6379),	// Redis server location
		'queue' => array('default'),								// Name of the default queue
		'interval' => 5,											// Number of second between each works
		'count' => 1												// Number of forks for each workers
	);
