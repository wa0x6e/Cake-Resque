<?php

App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::uses('Resque_Job_Creator', 'CakeResque.Lib');

class Resque_Job_CreatorTest extends CakeTestCase
{
    public static $TEST_DIR = '';

	public static function setUpBeforeClass()
    {
        self::$TEST_DIR = dirname(dirname(__DIR__)) . DS . 'Temp';

        $shellClassFile = new File(self::$TEST_DIR . DS . 'Console' . DS . 'Command' . DS . 'JobClassOneShell.php', true, 0755);
        $shellClassFile->append('<?php class JobClassOneShell extends AppShell { public function funcOne() {} public function funcTwo() {} }');

        $pluginShellClassFile = new File(self::$TEST_DIR . DS . 'Plugin' . DS . 'MyPlugin' . DS . 'Console' . DS . 'Command' . DS . 'PluginJobClassOneShell.php', true, 0755);
        $pluginShellClassFile->append('<?php class PluginJobClassOneShell extends AppShell { public function funcOne() {} public function funcTwo() {} }');

        Resque_Job_Creator::$ROOT_FOLDER = self::$TEST_DIR . DS;

    	parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass()
    {
        $folder = new Folder();
        $folder->delete(self::$TEST_DIR);
    	parent::tearDownAfterClass();
    }

    protected function cleanTempDir()
    {
        $this->folder->delete($this->testFilesDir);
    }


	public function testJobCreatorWithSucess()
	{
        $this->assertInstanceOf('JobClassOneShell', Resque_Job_Creator::createJob('JobClassOneShell', array('funcOne')));
	}

    public function testJobCreatorWithSucessFromPlugin()
    {
        $this->assertInstanceOf('PluginJobClassOneShell', Resque_Job_Creator::createJob('MyPlugin.PluginJobClassOneShell', array('funcOne')));
    }

    /**
     * @expectedException Resque_Exception
     */
    public function testJobWithErrorOnInexistingClass()
    {
        Resque_Job_Creator::createJob('InexistingClassShell', array('funcOne'));
    }

    /**
     * @expectedException Resque_Exception
     */
    public function testJobWithErrorOnInexistingFunction()
    {
        Resque_Job_Creator::createJob('JobClassOneShell', array('funcThree'));
    }


}
