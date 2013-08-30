<?php

/**
 * Tests for Lumber main logger factory class
 *
 * @copyright Copyright (c) 2010-2013 MV Labs (http://www.mvlabs.it)
 * @link      https://github.com/mvlabs/MvlabsLumber
 * @license   MIT - Please view the LICENSE file that was distributed with this source code
 * @author    Steve Maraspin <steve@mvlabs.it>
 * @package   MvlabsLumber
 */

namespace MvlabsLumberTest;

use PHPUnit_Framework_TestCase;
use MvlabsLumber\Service\LoggerFactory;
use MvlabsLumberTest\Service\MockConfigs\MockConfigs;


class LoggerFactoryTest extends \PHPUnit_Framework_TestCase {


    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $I_mockSM;


    /**
	 * Object containing mock configurations, used to test factory
	 *
	 * @var \MvlabsLumberTest\Service\MockConfigs\MockConfigs
	 */
    protected $I_mockConfig;


    /**
     * Prepare the objects to be tested.
     */
    protected function setUp() {

    	$this->I_mockSM =  \Mockery::mock('Zend\ServiceManager\ServiceManager');
    	$this->I_mockConfig = new MockConfigs();

    }


    protected function tearDown() {

    	\Mockery::close();

    }


    /**
     * Empty configuration is passed, an exception is thrown
     *
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage There seems to be no configuration for Lumber. Cannot continue execution
     */
    public function testMissingConf() {

    	$this->I_factory = new LoggerFactory();
    	$I_mockSM = $this->getMockSM();
    	$this->I_factory->createService($this->I_mockSM);

    }


    /**
     * Invalid configuration is passed, an exception is thrown
     *
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Channel configuration for Lumber ("lumber" key) seems to be empty or invalid
     */
    public function testInvalidConf() {

    	$this->I_factory = new LoggerFactory();
    	$I_mockSM = $this->getMockSM();
    	$this->I_factory->createService($this->I_mockSM);

    }


    /**
     * A logger with basic conf
     *
     * This is the simplest default configuration for Lumber
     */
     public function testWorkingFileWriter() {

     	$this->I_factory = new LoggerFactory();
    	$I_mockSM = $this->getMockSM();

    	$I_logger = $this->I_factory->createService($I_mockSM);

    	// Have we created an instance of our Logger?
    	$this->assertInstanceOf('MvlabsLumber\Service\Logger', $I_logger);

    	// Have we created the default channel?
    	$I_channel = $I_logger->getChannel('default');
    	$this->assertInstanceOf('Monolog\Logger', $I_channel);

    	// Does it have a writer?
    	$I_handler = $I_channel->popHandler();
    	$this->assertInstanceOf('Monolog\Handler\AbstractHandler', $I_handler);

    	// Is it the writer configured to bubble?
    	$this->assertTrue($I_handler->getBubble());

    }


    /**
     * Logger is configured to write to an unwritable directory
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Can not continue writing to
     */
    public function testWrongFileLocationFileWriter() {

    	$this->I_factory = new LoggerFactory();
    	$I_mockSM = $this->getMockSM();
    	$I_logger = $this->I_factory->createService($I_mockSM);

    }


    /**
     * Writer is configured with a not existing level
     *
     * @expectedException \OutOfRangeException
     * @expectedExceptionMessage Invalid logging level fun for writer
     */
    public function testWrongLogLevel() {

    	$this->I_factory = new LoggerFactory();
    	$I_mockSM = $this->getMockSM();
    	$I_logger = $this->I_factory->createService($I_mockSM);

    }

    /**
     * Writers param in conf contains invalid data
     *
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Writers configuration argument is not an array as expected
     */
    public function testInvalidWriters() {

    	$this->I_factory = new LoggerFactory();
    	$I_mockSM = $this->getMockSM();
    	$I_logger = $this->I_factory->createService($I_mockSM);

    }

    /**
     * Configured writer has not been defined
     *
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Requested writer writerdoesnotexist not found in Lumber configuration
     */
    public function testWriterNotExisting() {

    	$this->I_factory = new LoggerFactory();
    	$I_mockSM = $this->getMockSM();
    	$I_logger = $this->I_factory->createService($I_mockSM);

    }


    /**
     * Multiple channels are created
     */
    public function testMultipleChannels() {

    	$this->I_factory = new LoggerFactory();
    	$I_mockSM = $this->getMockSM();
    	$I_logger = $this->I_factory->createService($this->I_mockSM);

    	$aI_channels = $I_logger->getChannels();

		$I_channel1 = $aI_channels['default'];

		// Propagate is true
		$I_writerOne = $I_channel1->popHandler();
		$this->assertFalse($I_writerOne->getBubble());

		// Propagate is false
		$I_writerTwo = $I_channel1->popHandler();
		$this->assertTrue($I_writerTwo->getBubble());

		$I_channel2 = $aI_channels['secondary'];

		// Default (propagate is true)
		$I_writerOne = $I_channel2->popHandler();
		$this->assertFalse($I_writerOne->getBubble());

    }


    /**
     * Multiple writers with propagation is set to on (first) and off (next two)
     */
    public function testMultipleWriters() {

    	$this->I_factory = new LoggerFactory();
    	$I_mockSM = $this->getMockSM();
    	$I_logger = $this->I_factory->createService($this->I_mockSM);

    	$aI_channels = $I_logger->getChannels();

		$I_channel = $aI_channels['default'];

		// Propagate is true
		$I_writerOne = $I_channel->popHandler();
		$this->assertFalse($I_writerOne->getBubble());

		// Propagate is false
		$I_writerTwo = $I_channel->popHandler();
		$this->assertTrue($I_writerTwo->getBubble());

		// Default (propagate is true)
		$I_writerThree = $I_channel->popHandler();
		$this->assertFalse($I_writerThree->getBubble());

		// Propagate is false
		$I_writerFour = $I_channel->popHandler();
		$this->assertTrue($I_writerFour->getBubble());

    }


    /**
     * Constructs a mock service manager with Lumber configuration for a specific test
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    private function getMockSM() {

    	$am_trace = debug_backtrace();
    	$s_confToLoad = lcfirst(substr($am_trace[1]['function'], 4));

    	$am_serviceConf = $this->I_mockConfig->getConf($s_confToLoad);
    	$this->I_mockSM->shouldReceive('get')->with('Config')->once()->andReturn($am_serviceConf);

    	return $this->I_mockSM;

    }

}
