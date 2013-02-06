<?php

/**
 * Test class for CakeResqueLib
 *
 * PHP versions 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author        Wan Qi Chen <kami@kamisama.me>
 * @copyright     Copyright 2012, Wan Qi Chen <kami@kamisama.me>
 * @link          http://cakeresque.kamisama.me
 * @package       CakeResque
 * @subpackage	 CakeResque.Test.Case.Lib
 * @since         1.2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 **/

/**
 * CakeResqueTest class
 *
 * @package 		CakeResque
 * @subpackage	CakeResque.Test.Case.Lib
 */

class CakeResqueTest extends CakeTestCase
{

	public function setUp() {
		$this->Resque = $this->getMockClass('Resque', array('enqueue'));
		$this->ResqueScheduler = $this->getMockClass('Kamisama\ResqueScheduler\ResqueScheduler', array('enqueueIn', 'enqueueAt'));
		CakeResque::$resqueClass = $this->Resque;
		CakeResque::$resqueSchedulerClass = $this->ResqueScheduler;

		$this->fixture = array(
				'queue' => 'default',
				'class' => 'TestShell',
				'args' => array('main', 'arg1'),
				'track' => false
			);

		parent::setUp();
	}

	public function tearDown() {
		CakeResque::$logs = array();
		parent::tearDown();
	}

	public function testEnqueueWithSuccess() {
		$id = md5(time());

		$Resque = $this->Resque;
		$Resque::staticExpects($this->any())
			->method('enqueue')
			->will($this->returnValue($id));

		extract($this->fixture);

		$response = CakeResque::enqueue($queue, $class, $args, $track);

		$this->assertEqual($id, $response);
		$this->_testLogs(CakeResque::$logs[$queue][0], $id);
	}

	public function testEnqueueWithSuccessWithoutTrackingArgument() {
		$id = md5(time());

		$Resque = $this->Resque;
		$Resque::staticExpects($this->any())
			->method('enqueue')
			->will($this->returnValue($id));

		unset($this->fixture['track']);

		extract($this->fixture);

		$response = CakeResque::enqueue($queue, $class, $args);

		$this->assertEqual($id, $response);
		$this->_testLogs(CakeResque::$logs[$queue][0], $id);
	}

	public function testEnqueuAtWithSuccessWithDateTime() {
		$id = md5(time());

		$ResqueScheduler = $this->ResqueScheduler;
		$ResqueScheduler::staticExpects($this->any())
			->method('enqueueAt')
			->will($this->returnValue($id));

		$this->fixture['at'] = new DateTime('now');

		extract($this->fixture);

		$response = CakeResque::enqueueAt($at, $queue, $class, $args);

		$this->assertEqual($id, $response);
		$this->_testLogs(CakeResque::$logs[$queue][0], $id);
		$this->assertEqual($at->getTimeStamp(), CakeResque::$logs[$queue][0]['time']);
	}

	public function testEnqueuAtWithSuccessWithTimestamp() {
		$id = md5(time());

		$ResqueScheduler = $this->ResqueScheduler;
		$ResqueScheduler::staticExpects($this->any())
			->method('enqueueAt')
			->will($this->returnValue($id));

		$this->fixture['at'] = time();

		extract($this->fixture);

		$response = CakeResque::enqueueAt($at, $queue, $class, $args);

		$this->assertEqual($id, $response);
		$this->_testLogs(CakeResque::$logs[$queue][0], $id);
		$this->assertEqual($at, CakeResque::$logs[$queue][0]['time']);
	}

	public function testEnqueueInWithSuccess() {
		$id = md5(time());

		$ResqueScheduler = $this->ResqueScheduler;
		$ResqueScheduler::staticExpects($this->any())
			->method('enqueueIn')
			->will($this->returnValue($id));

		$this->fixture['in'] = 10;

		extract($this->fixture);

		$response = CakeResque::enqueueIn($in, $queue, $class, $args);

		$this->assertEqual($id, $response);
		$this->_testLogs(CakeResque::$logs[$queue][0], $id);
		$this->assertEqual(time() + $in, CakeResque::$logs[$queue][0]['time']);
	}

	public function testEnqueueAreLogged() {
		$Resque = $this->Resque;
		$Resque::staticExpects($this->any())
			->method('enqueue')
			->will($this->returnValue(''));

		extract($this->fixture);

		CakeResque::enqueue('one', $class, $args, $track);
		CakeResque::enqueue('one', $class, $args, $track);
		CakeResque::enqueue('two', $class, $args, $track);
		CakeResque::enqueue('three', $class, $args, $track);

		$this->assertCount(3, CakeResque::$logs);
		$this->assertCount(2, CakeResque::$logs['one']);
		$this->assertCount(1, CakeResque::$logs['two']);
		$this->assertCount(1, CakeResque::$logs['three']);
	}

	protected function _testLogs($log, $id) {
		extract($this->fixture);

		$this->assertEqual($queue, $log['queue']);
		$this->assertEqual($class, $log['class']);
		$this->assertEqual(array_shift($args), $log['method']);
		$this->assertEqual($args, $log['args']);
		$this->assertEqual($id, $log['jobId']);
	}
}
