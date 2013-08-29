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
     * An invalid writer is requested, an exception is thrown
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid type for writer
     */
    public function testInvalidWriterType() {
    	$this->I_factory = new LoggerFactory();
		$I_mockSM = $this->getMockSM();
    	$this->I_factory->createService($this->I_mockSM);
    }


    /**
     * A logger with basic conf
     */
     public function testWorkingFileWriter() {
     	$this->I_factory = new LoggerFactory();
    	$I_mockSM = $this->getMockSM();
    	$I_logger = $this->I_factory->createService($I_mockSM);
    	$this->assertInstanceOf('MvlabsLumber\Service\Logger', $I_logger);
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
	 * Event shall be propagated after first writer has handled it
	 *
	 *  Testing that Monolog bubble property has been set to true
	 */
    public function testPropagateOn() {
    	$this->markTestIncomplete('To be implemented');
    }


    /**
     * Event shall not be propagated after first writer has handled it
     *
     *  Testing that Monolog bubble property has been set to false
     */
    public function testPropagateOff() {
   		$this->markTestIncomplete('To be implemented');
    }


    /**
     * Constructs a mock service manager with Lumber configuration for a specific test
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    private function getMockSM() {

    	$am_trace = debug_backtrace();
    	$s_confToLoad = lcfirst(substr($am_trace[1]['function'], 4));

    	$am_serviceConf = $this->I_mockConfig->getConf($s_confToLoad);
    	$this->I_mockSM->shouldReceive('setService')->with('Config',$am_serviceConf)->once();
    	$this->I_mockSM->shouldReceive('get')->with('Config')->once()->andReturn($am_serviceConf);

    	return $this->I_mockSM;

    }

}
