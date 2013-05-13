<?php

/**
 * Test class for ResqueStatus
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
 * @subpackage	  CakeResque.Test.Case.Lib
 * @since         3.3.6
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 **/

/**
 * ResqueStatusTest class
 *
 * @package 	CakeResque
 * @subpackage	CakeResque.Test.Case.Lib
 */

App::uses('ResqueStatus', 'CakeResque.Lib');
class ResqueStatusTest extends CakeTestCase
{

	public function setUp() {
		parent::setUp();

		$this->redis = new Redis();
		$this->redis->connect('127.0.0.1', '6379');
		$this->redis->select(6);

		$this->ResqueStatus = new ResqueStatus($this->redis);

		ResqueStatus::$workerStatusPrefix = 'test_' . ResqueStatus::$workerStatusPrefix;
		ResqueStatus::$schedulerWorkerStatusPrefix = 'test_' . ResqueStatus::$schedulerWorkerStatusPrefix;
		ResqueStatus::$pausedWorkerKeyPrefix . 'test_' . ResqueStatus::$pausedWorkerKeyPrefix;

		$this->workers = array();
		$this->workers[] = new Worker('One:queue5', 5);
		$this->workers[] = new Worker('Two:queue1', 10);
		$this->workers[] = new Worker('Three:' . ResqueScheduler\ResqueScheduler::QUEUE_NAME, 145);
	}

	public function tearDown() {
		parent::tearDown();
		$this->redis->del(ResqueStatus::$workerStatusPrefix);
		$this->redis->del(ResqueStatus::$schedulerWorkerStatusPrefix);
		$this->redis->del(ResqueStatus::$pausedWorkerKeyPrefix);
	}

/**
 * @covers ResqueStatus::addWorker
 */
	public function testAddWorker() {
		$workers = array(
			array('name' => 'WorkerZero'),
			array('name' => 'workerOne', 'debug' => true)
		);
		$this->redis->rpush(ResqueStatus::$workerStatusPrefix, serialize($workers[0]));

		$res = $this->ResqueStatus->addWorker($workers[1]);

		$this->assertTrue($res);

		$this->assertEquals(2, $this->redis->lSize(ResqueStatus::$workerStatusPrefix));
		$datas = $this->redis->lrange(ResqueStatus::$workerStatusPrefix, 0, 2);

		$this->assertEquals($workers[0], unserialize($datas[0]));
		unset($workers[1]['debug']);
		$this->assertEquals($workers[1], unserialize($datas[1]));
	}

/**
 * @covers ResqueStatus::registerSchedulerWorker
 */
	public function testRegisterSchedulerWorker() {
		$res = $this->ResqueStatus->registerSchedulerWorker((object)$this->workers);

		$this->assertTrue($res);
		$this->assertEquals('Three:' . ResqueScheduler\ResqueScheduler::QUEUE_NAME, $this->redis->get(ResqueStatus::$schedulerWorkerStatusPrefix));
	}

/**
 * @covers ResqueStatus::registerSchedulerWorker
 */
	public function testRegisterSchedulerWorkerWhenThereIsNoSchedulerWorker() {
		unset($this->workers[2]);
		$res = $this->ResqueStatus->registerSchedulerWorker((object)$this->workers);

		$this->assertFalse($res);
		$this->assertFalse($this->redis->exists(ResqueStatus::$schedulerWorkerStatusPrefix));
	}

/**
 * @covers ResqueStatus::isSchedulerWorker
 */
	public function testIsSchedulerWoker() {
		$this->assertTrue($this->ResqueStatus->isSchedulerWorker($this->workers[2]));
	}

/**
 * @covers ResqueStatus::isSchedulerWorker
 */
	public function testIsSchedulerWokerWhenFalse() {
		$this->assertFalse($this->ResqueStatus->isSchedulerWorker($this->workers[0]));
	}

/**
 * @covers ResqueStatus::isRunningSchedulerWorker
 */
	public function testIsRunningSchedulerWorker() {
		$this->redis->set(ResqueStatus::$schedulerWorkerStatusPrefix, 'workerName');
		$this->assertTrue($this->ResqueStatus->isRunningSchedulerWorker());
	}

/**
 * @covers ResqueStatus::isRunningSchedulerWorker
 */
	public function testIsRunningSchedulerWorkerWhenItIsNotRunning() {
		$this->assertFalse($this->ResqueStatus->isRunningSchedulerWorker());
	}

/**
 * @covers ResqueStatus::unregisterSchedulerWorker
 */
	public function testUnregisterSchedulerWorker() {
		$worker = 'schedulerWorker';
		$this->redis->set(ResqueStatus::$schedulerWorkerStatusPrefix, $worker);

		$this->assertTrue($this->ResqueStatus->unregisterSchedulerWorker());
		$this->assertFalse($this->redis->exists(ResqueStatus::$schedulerWorkerStatusPrefix));
	}

/**
 * @covers ResqueStatus::getWorkers
 */
	public function testGetWorkers() {
		foreach ($this->workers as $worker) {
			$this->redis->rpush(ResqueStatus::$workerStatusPrefix, serialize($worker));
		}

		$this->assertEquals($this->workers, $this->ResqueStatus->getWorkers());
	}

/**
 * @covers ResqueStatus::setPausedWorker
 */
	public function testSetPausedWorker() {
		$worker = 'workerName';
		$this->ResqueStatus->setPausedWorker($worker);

		$this->assertEquals(1, $this->redis->sCard(ResqueStatus::$pausedWorkerKeyPrefix));
		$this->assertContains($worker, $this->redis->sMembers(ResqueStatus::$pausedWorkerKeyPrefix));
	}

/**
 * @covers ResqueStatus::setActiveWorker
 */
	public function testSetActiveWorker() {
		$workers = array('workerOne', 'workerTwo');

		$this->redis->sAdd(ResqueStatus::$pausedWorkerKeyPrefix, $workers[0]);
		$this->redis->sAdd(ResqueStatus::$pausedWorkerKeyPrefix, $workers[1]);

		$this->ResqueStatus->setActiveWorker($workers[0]);

		$pausedWorkers = $this->redis->sMembers(ResqueStatus::$pausedWorkerKeyPrefix);
		$this->assertCount(1, $pausedWorkers);
		$this->assertEquals(array($workers[1]), $pausedWorkers);
	}

/**
 * @covers ResqueStatus::getPausedWorker
 */
	public function testGetPausedWorker() {
		$workers = array('workerOne', 'workerTwo');

		$this->redis->sAdd(ResqueStatus::$pausedWorkerKeyPrefix, $workers[0]);
		$this->redis->sAdd(ResqueStatus::$pausedWorkerKeyPrefix, $workers[1]);

		$pausedWorkers = $this->ResqueStatus->getPausedWorker();

		sort($pausedWorkers);
		sort($workers);

		$this->assertEquals($workers, $pausedWorkers);
	}

/**
 * Test that getPausedWorkers always return an array
 * @covers ResqueStatus::getPausedWorker
 */
	public function testGetPausedWorkerWhenThereIsNoPausedWorkers() {
		$this->assertEquals(array(), $this->ResqueStatus->getPausedWorker());
	}

/**
 * @covers ResqueStatus::clearWorkers
 */
	public function testClearWorkers() {
		$this->redis->set(ResqueStatus::$workerStatusPrefix, 'one');
		$this->redis->set(ResqueStatus::$pausedWorkerKeyPrefix, 'two');

		$pausedWorkers = $this->ResqueStatus->clearWorkers();

		$this->assertFalse($this->redis->exists(ResqueStatus::$workerStatusPrefix));
		$this->assertFalse($this->redis->exists(ResqueStatus::$pausedWorkerKeyPrefix));
	}

}

class Worker {

	public function __construct($name, $interval) {
		$this->name = $name;
		$this->interval = $interval;
	}

	public function __toString() {
		return $this->name;
	}
}
