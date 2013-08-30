<?php

/**
 * Tests for Lumber main logger class
 *
 * @copyright Copyright (c) 2010-2013 MV Labs (http://www.mvlabs.it)
 * @link      https://github.com/mvlabs/MvlabsLumber
 * @license   MIT - Please view the LICENSE file that was distributed with this source code
 * @author    Steve Maraspin <steve@mvlabs.it>
 * @package   MvlabsLumber
 */

namespace MvlabsLumberTest\Service;

use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceManager;
use MvlabsLumber\Service\LoggerFactory;
use MvlabsLumber\Service\Logger;
use PHPUnit_Framework_TestCase;
use MvlabsLumber;


class LoggerTest extends \PHPUnit_Framework_TestCase {


    /**
     * The object to be tested.
     *
     * @var Logger
     */
    protected $I_logger;


    /**
     * @var \Monolog\Handler\NullHandler
     */
    protected $I_mockMonologLogger;


    /**
     * Prepare the objects to be tested.
     */
    protected function setUp() {
    	$this->I_mockMonologLogger =  \Mockery::mock('Monolog\Logger');
    	$this->I_logger = new Logger();
    }


    protected function tearDown() {
		\Mockery::close();
    }

    /**
     * Tests channel addition
     */
    public function testAddChannel() {

    	// Default channel is added
    	$s_channelName = 'default';
    	$this->I_logger->addChannel($s_channelName, $this->I_mockMonologLogger);
    	$this->assertArrayHasKey($s_channelName, $this->I_logger->getChannels());

    }


    /**
     * Get a list of logger channels
     */
    public function testListChannels() {

    	// Lumber has no channels upfront
		$this->assertEmpty($this->I_logger->getChannels());

		// Default channel is added
		$this->I_logger->addChannel('default', $this->I_mockMonologLogger);
		$this->I_logger->addChannel('second', $this->I_mockMonologLogger);

		$aI_channels = $this->I_logger->getChannels();
		$this->assertCount(2, $aI_channels);

		$I_default = $aI_channels["default"];
		$I_second = $aI_channels["second"];

		$this->assertInstanceOf("Monolog\Logger", $I_default);
		$this->assertInstanceOf("Monolog\Logger", $I_second);

    }


    /**
     * Tests channel removal
     */
    public function testRemoveChannel() {

    	$I_logger = $this->I_logger;

    	// Lumber has no channels upfront
    	$this->assertEmpty($I_logger->getChannels());

    	// Default channel is added
    	$s_channelName = 'default';
    	$this->I_logger->addChannel($s_channelName, $this->I_mockMonologLogger);

    	// Second channel is added
    	$this->I_logger->addChannel('second', $this->I_mockMonologLogger);

    	$this->assertCount(2, $I_logger->getChannels());
    	$this->assertArrayHasKey($s_channelName, $I_logger->getChannels());

    	// Default channel is removed
		$I_logger->removeChannel($s_channelName);

		$this->assertCount(1, $I_logger->getChannels());
		$this->assertArrayNotHasKey($s_channelName, $I_logger->getChannels());


    }


    /**
     * Tests channel removal with not existing channel
     *
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Channel default does not exist. Cannot remove it
     *
     */
    public function testRemoveNotExistingChannel() {

    	$I_logger = $this->I_logger;

    	// Default channel is removed
    	$I_logger->removeChannel('default');

    }


    /**
     * Returns requested channel
     *
     */
    public function testGetChannel() {

    	$I_logger = $this->I_logger;

    	// Default channel is fetched
    	$this->I_logger->addChannel('default', $this->I_mockMonologLogger);
    	$I_channel = $I_logger->getChannel('default');

    	$this->assertInstanceOf('Monolog\Logger', $I_channel);

    }


    /**
     * Returns a specific logging channel
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Channel default does not exist
     */
    public function testGetnotExistingChannel() {

    	$I_logger = $this->I_logger;

    	// Default channel is removed
    	$I_logger->removeChannel('default');

    }


    /**
     * Tests logging with different severity levels
     *
     * This is the only place where I accept violating DRY
     * for the sake of clarity
     */
    public function testIsEventSentToLogger() {

    	$I_logger = $this->I_logger;

    	$s_messageToLog = 'Message Sent';

    	// Default channel is added
    	$I_logger->addChannel('default', $this->I_mockMonologLogger);

    	// Simplest situation, default values
    	$this->I_mockMonologLogger->shouldReceive('addRecord')->with(250, $s_messageToLog, array())->once();
    	$I_logger->log($s_messageToLog);

    	// Debug
    	$this->I_mockMonologLogger->shouldReceive('addRecord')->with(100, $s_messageToLog, array())->between(3,3);
    	$I_logger->log($s_messageToLog, 'debug');
    	$I_logger->log($s_messageToLog, Logger::DEBUG);
    	$I_logger->debug($s_messageToLog);

    	// Info
    	$this->I_mockMonologLogger->shouldReceive('addRecord')->with(200, $s_messageToLog, array())->between(3,3);
    	$I_logger->log($s_messageToLog, 'info');
    	$I_logger->log($s_messageToLog, Logger::INFO);
    	$I_logger->info($s_messageToLog);

    	// Notice
    	$this->I_mockMonologLogger->shouldReceive('addRecord')->with(250, $s_messageToLog, array())->between(3,3);
    	$I_logger->log($s_messageToLog, 'notice');
    	$I_logger->log($s_messageToLog, Logger::NOTICE);
    	$I_logger->notice($s_messageToLog);

    	// Warning
    	$this->I_mockMonologLogger->shouldReceive('addRecord')->with(300, $s_messageToLog, array())->between(3,3);
    	$I_logger->log($s_messageToLog, 'warning');
    	$I_logger->log($s_messageToLog, Logger::WARNING);
    	$I_logger->warning($s_messageToLog);

    	// Error
    	$this->I_mockMonologLogger->shouldReceive('addRecord')->with(400, $s_messageToLog, array())->between(3,3);
    	$I_logger->log($s_messageToLog, 'error');
    	$I_logger->log($s_messageToLog, Logger::ERROR);
    	$I_logger->error($s_messageToLog);

    	// Critical
    	$this->I_mockMonologLogger->shouldReceive('addRecord')->with(500, $s_messageToLog, array())->between(3,3);
    	$I_logger->log($s_messageToLog, 'critical');
    	$I_logger->log($s_messageToLog, Logger::CRITICAL);
    	$I_logger->critical($s_messageToLog);

    	// Alert
    	$this->I_mockMonologLogger->shouldReceive('addRecord')->with(550, $s_messageToLog, array())->between(3,3);
    	$I_logger->log($s_messageToLog, 'alert');
    	$I_logger->log($s_messageToLog, Logger::ALERT);
    	$I_logger->alert($s_messageToLog);

    	// Emergency
    	$this->I_mockMonologLogger->shouldReceive('addRecord')->with(600, $s_messageToLog, array())->between(3,3);
    	$I_logger->log($s_messageToLog, 'emergency');
    	$I_logger->log($s_messageToLog, Logger::EMERGENCY);
    	$I_logger->emergency($s_messageToLog);

		// Context Passed along
    	$this->I_mockMonologLogger->shouldReceive('addRecord')->with(200, $s_messageToLog, array('user' => 'steve'));
    	$this->I_logger->log($s_messageToLog, 'info', array('user' => 'steve'));

    }


    /**
     * Tests available severity levels
     */
    public function testSeverityLevels() {
    	$this->assertTrue(($this->getPsrSeverityLevels() == $this->I_logger->getSeverityLevels()));
    }


    /**
     * Tests severity level is set
     */
    public function testIsSeverityValid() {

    	$this->assertTrue($this->I_logger->isValidLevel('info'));
    	$this->assertFalse($this->I_logger->isValidLevel('fun'));

    }


    /**
     * Gets PSR logging severity levels
     *
     * @return array Valid severity levels
     */
    private function getPsrSeverityLevels() {

    	$I_reflection = new \ReflectionClass('Psr\Log\LogLevel');
    	$am_psrConstants = $I_reflection->getConstants();

    	return $am_psrConstants;

    }


}
