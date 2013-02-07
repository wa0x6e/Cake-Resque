<?php

/**
 * Test class for Resque_Job_Creator
 *
 *  PHP versions 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author        Wan Qi Chen <kami@kamisama.me>
 * @copyright     Copyright 2012, Wan Qi Chen <kami@kamisama.me>
 * @link          http://cakeresque.kamisama.me
 * @package       CakeResque
 * @subpackage    CakeResque.Test.Case.Lib
 * @since         1.2.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 **/

/**
 * Resque_Job_CreatorTest class
 *
 * @package      CakeResque
 * @subpackage   CakeResque.Test.Case.Lib
 */

App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::uses('Resque_Job_Creator', 'CakeResque.Lib');

class Resque_Job_CreatorTest extends CakeTestCase
{

/**
 * Path to the temporary directory for temporary files
 * @var string
 */
	public static $testDir = '';

	public static function setUpBeforeClass() {
		self::$testDir = dirname(dirname(__DIR__)) . DS . 'Temp';

		self::cleanTempDir();

		$shellClassFile = new File(self::$testDir . DS . 'Console' . DS . 'Command' . DS . 'JobClassOneShell.php', true, 0755);
		$shellClassFile->append('<?php class JobClassOneShell extends AppShell { public function funcOne() {} public function funcTwo() {} public function perform() {} }');

		$pluginShellClassFile = new File(self::$testDir . DS . 'Plugin' . DS . 'MyPlugin' . DS . 'Console' . DS . 'Command' . DS . 'PluginJobClassOneShell.php', true, 0755);
		$pluginShellClassFile->append('<?php class PluginJobClassOneShell extends AppShell { public function funcOne() {} public function funcTwo() {} public function perform() {} }');

		Resque_Job_Creator::$rootFolder = self::$testDir . DS;

		parent::setUpBeforeClass();
	}

	public static function tearDownAfterClass() {
		self::cleanTempDir();
		parent::tearDownAfterClass();
	}

/**
 * Removing all temporary files created for testing
 */
	public static function cleanTempDir() {
		$folder = new Folder();
		$folder->delete(self::$testDir);
	}

/**
 * Test Job creation from a regular shell class
 */
	public function testJobCreatorWithSucess() {
		$this->assertInstanceOf('JobClassOneShell', Resque_Job_Creator::createJob('JobClassOneShell', array('funcOne')));
	}

/**
 * Test Job creation from a plugin shell class
 */
	public function testJobCreatorWithSuccessFromPlugin() {
		$this->assertInstanceOf('PluginJobClassOneShell', Resque_Job_Creator::createJob('MyPlugin.PluginJobClassOneShell', array('funcOne')));
	}

/**
 * Test job creation from an inexisting shell class
 *
 * @expectedException Resque_Exception
 */
	public function testJobWithErrorOnInexistingClass() {
		Resque_Job_Creator::createJob('InexistingClassShell', array('funcOne'));
	}

/**
 * Test job creation from a valid shell class, but without the expected method
 *
 * @expectedException Resque_Exception
 */
	public function testJobWithErrorOnInexistingFunction() {
		Resque_Job_Creator::createJob('JobClassOneShell', array('funcThree'));
	}
}
