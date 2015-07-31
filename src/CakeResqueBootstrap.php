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
include getenv('APP') . '../config/bootstrap.php';
use Cake\Console\ShellDispatcher;

class Resque_Job_Creator
{

    /**
     * Create and return a job instance
     *
     * @param string $className className of the job to instanciate
     * @param array $args Array of method name and arguments used to build the job
     * @return object $args a job instance
     * @throws Resque_Exception when the class is not found, or does not follow the job file convention
     */
    public static function createJob($className, $args)
    {
        if (!class_exists('Cake\Console\ShellDispatcher')) {
            throw new Resque_Exception('Resque_Job_Creator could not find Cake\Console\ShellDispatcher.');
        }

        array_unshift($args, 'void', $className);
        $args[] = '-q';

        return [new Cake\Console\ShellDispatcher($args),'dispatch'];

    }
}