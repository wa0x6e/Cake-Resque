<?php
/**
 * Bootstrap file
 *
 * Use to bootstrap the job classes
 * All code is from CakePHP bootstrap files
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
 * @subpackage      CakeResque.lib
 * @since         0.5
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
use Cake\Console\ShellDispatcher;
use Cake\Console\Shell;
use CakeResque\Resque_Job_Creator;

array_push($argv, '--app', getenv('APP'));
file_put_contents('/home/vagrant/Apps/Resque.dev/logs/test.log',print_r($argv, true), FILE_APPEND);
file_put_contents('/home/vagrant/Apps/Resque.dev/logs/test.log', getenv('COUNT'), FILE_APPEND);




new ShellDispatcher($argv);
