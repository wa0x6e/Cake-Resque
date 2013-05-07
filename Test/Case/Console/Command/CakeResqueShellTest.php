<?php

App::uses('ConsoleOutput', 'Console');
App::uses('ConsoleInput', 'Console');
App::uses('ShellDispatcher', 'Console');
App::uses('Shell', 'Console');
App::uses('CakeResqueShell', 'CakeResque.Console/Command');

class CakeResqueShellTest extends CakeTestCase
{

	public function setUp() {
		parent::setUp();
		$out = $this->getMock('ConsoleOutput', array(), array(), '', false);
		$in = $this->getMock('ConsoleInput', array(), array(), '', false);

		$this->Shell = $this->getMock(
			'CakeResqueShell',
			array('in', 'out', 'hr'),
			array($out, $out, $in)
		);
	}

	public function tearDown() {
		parent::tearDown();
		unset($this->Dispatch, $this->Shell);
	}

	public function testTrackingWithNoJobIdReturnError() {
		$this->Shell->expects($this->exactly(2))->method('out');

		$shell = $this->Shell;

		$shell::$cakeResque = $CakeResque = $this->getMockClass('CakeResque', array('getJobStatus'));

		$CakeResque::staticExpects($this->never())->method('getJobStatus');
		$CakeResque::staticExpects($this->never())->method('getFailedJobLog');

		$this->Shell->expects($this->at(0))->method('out')->with($this->stringContains('Tracking job status'));

		$this->Shell->expects($this->at(1))->method('out')->with($this->stringContains('error'));
		$this->Shell->track();
	}

	public function testTrackingJobWithUnknownStatus() {
		$shell = $this->Shell;

		$shell::$cakeResque = $CakeResque = $this->getMockClass('CakeResque', array('getJobStatus'));

		$CakeResque::staticExpects($this->once())
			->method('getJobStatus')
			->will($this->returnValue(false));

		$CakeResque::staticExpects($this->never())->method('getFailedJobLog');

		$this->Shell->args = array('dd');
		$this->Shell->expects($this->at(1))->method('out')->with($this->stringContains('Status'));
		$this->Shell->expects($this->at(1))->method('out')->with($this->matchesRegularExpression('/unknown/i'));
		$this->Shell->expects($this->at(1))->method('out')->with($this->matchesRegularExpression('/warning/'));
		$this->Shell->track();
	}

	public function testTrackingCompletedJob() {
		$shell = $this->Shell;

		$shell::$cakeResque = $CakeResque = $this->getMockClass('CakeResque', array('getJobStatus'));

		$CakeResque::staticExpects($this->once())
			->method('getJobStatus')
			->will($this->returnValue(Resque_Job_Status::STATUS_COMPLETE));

		$CakeResque::staticExpects($this->never())->method('getFailedJobLog');

		$this->Shell->args = array('dd');
		$this->Shell->expects($this->at(1))->method('out')->with($this->matchesRegularExpression('/complete/'));
		$this->Shell->track();
	}

	public function testTrackingRunningJob() {
		$shell = $this->Shell;

		$shell::$cakeResque = $CakeResque = $this->getMockClass('CakeResque', array('getJobStatus'));

		$CakeResque::staticExpects($this->once())
			->method('getJobStatus')
			->will($this->returnValue(Resque_Job_Status::STATUS_RUNNING));

		$CakeResque::staticExpects($this->never())->method('getFailedJobLog');

		$this->Shell->args = array('dd');
		$this->Shell->expects($this->at(1))->method('out')->with($this->matchesRegularExpression('/running/'));
		$this->Shell->track();
	}

	public function testTrackingWaitingJob() {
		$shell = $this->Shell;

		$shell::$cakeResque = $CakeResque = $this->getMockClass('CakeResque', array('getJobStatus'));

		$CakeResque::staticExpects($this->once())
			->method('getJobStatus')
			->will($this->returnValue(Resque_Job_Status::STATUS_WAITING));

		$CakeResque::staticExpects($this->never())->method('getFailedJobLog');

		$this->Shell->args = array('dd');
		$this->Shell->expects($this->at(1))->method('out')->with($this->matchesRegularExpression('/waiting/'));
		$this->Shell->track();
	}

	public function testTrackingFailedJobWithEmptyLog() {
		$shell = $this->Shell;

		$shell::$cakeResque = $CakeResque = $this->getMockClass('CakeResque', array('getJobStatus', 'getFailedJobLog'));

		$CakeResque::staticExpects($this->once())
			->method('getJobStatus')
			->will($this->returnValue(Resque_Job_Status::STATUS_FAILED));

		$CakeResque::staticExpects($this->once())
			->method('getFailedJobLog')
			->will($this->returnValue(array()));

		$this->Shell->args = array('dd');
		$this->Shell->expects($this->exactly(3))->method('out');
		$this->Shell->expects($this->at(1))->method('out')->with($this->matchesRegularExpression('/failed/'));
		$this->Shell->track();
	}

	public function testTrackingFailedJobWithLog() {
		$shell = $this->Shell;

		$shell::$cakeResque = $CakeResque = $this->getMockClass('CakeResque', array('getJobStatus', 'getFailedJobLog'));

		$CakeResque::staticExpects($this->once())
			->method('getJobStatus')
			->will($this->returnValue(Resque_Job_Status::STATUS_FAILED));

		$CakeResque::staticExpects($this->once())
			->method('getFailedJobLog')
			->will($this->returnValue(array("log++")));

		$this->Shell->args = array('dd');
		$this->Shell->expects($this->at(1))->method('out')->with($this->matchesRegularExpression('/failed/'));
		$this->Shell->expects($this->at(3))->method('out')->with($this->matchesRegularExpression('/details/'));
		$this->Shell->track();
	}
}