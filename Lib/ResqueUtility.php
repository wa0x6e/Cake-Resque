<?php

class ResqueUtility
{
	/**
	 * Get the list of all available Jobs
	 */
	function getJobs()
	{
		$jobs = array();
		$files = glob(APP . 'Console' .DS . 'Command' . DS . '*.php');
		foreach($files as $shell)
		{
			include_once($shell);
			$className = basename($shell, '.php');
			$reflector = new ReflectionClass($className);
			if ($reflector->hasMethod('perform'))
				$jobs[] = $className;
		}
		return $jobs;
	}
	
	
	/**
	 * Get a list of the available jobs file fullpath
	 */
	function getJobsFile()
	{
		$jobs = array();
		$files = glob(APP . 'Console' .DS . 'Command' . DS . '*.php');
		foreach($files as $shell)
		{
			include_once($shell);
			$className = basename($shell, '.php');
			$reflector = new ReflectionClass($className);
			if ($reflector->hasMethod('perform'))
			$jobs[] = $shell;
		}
		return $jobs;
	}
}
