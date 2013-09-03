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


    /**
     * @covers MvlabsLumber\Service\Logger::addChannel
     * @test
     */
    public function testCanChannelBeAdded() {

    	// Default channel is added
    	$s_channelName = 'default';
    	$this->I_logger->addChannel($s_channelName, $this->I_mockMonologLogger);
    	$this->assertArrayHasKey($s_channelName, $this->I_logger->getChannels());

    }


    /**
     * @test
     * @covers MvlabsLumber\Service\Logger::getChannel
     */
    public function tryGettingChannelByExistingName() {

        $this->I_logger->addChannel('second',$this->I_mockMonologLogger);
        $this->I_mockMonologLogger->shouldReceive("getName")->andReturn("second");

        $I_channel = $this->I_logger->getChannel('second');
        $s_channelName = $I_channel->getName();

        $this->assertInstanceOf("Monolog\Logger", $I_channel);
        $this->assertEquals($s_channelName, 'second');
    }


    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Channel default does not exist
     * @test
     */
    public function tryGettingChannelByNonExistentName() {

        $this->I_logger->addChannel('second',$this->I_mockMonologLogger);
        $this->I_mockMonologLogger->shouldReceive("getName")->andReturn("second");

       $this->I_logger->getChannel('default');

    }


    /**
     * @covers MvlabsLumber\Service\Logger::getChannels
     * @test
     */
    public function getChannelList() {

    	$I_logger = $this->I_logger;

    	// Lumber has no channels upfront
		$this->assertEmpty($I_logger->getChannels());

		// Default channel is added
		$I_logger->addChannel('default', $this->I_mockMonologLogger);
		$I_logger->addChannel('second', $this->I_mockMonologLogger);

		// getChannels is tested
		$aI_channels = $I_logger->getChannels();
		$this->assertCount(2, $aI_channels);

		$I_default = $aI_channels['default'];
		$I_second = $aI_channels['second'];

		$this->assertInstanceOf("Monolog\Logger", $I_default);
		$this->assertInstanceOf("Monolog\Logger", $I_second);

		// getChannel is tested
		$I_defaultDirect = $I_logger->getChannel('default');
		$I_secondDirect = $I_logger->getChannel('second');

		$this->assertInstanceOf("Monolog\Logger", $I_defaultDirect);
		$this->assertInstanceOf("Monolog\Logger", $I_secondDirect);

    }


    /**
     * @covers MvlabsLumber\Service\Logger::removeChannel
     * @test
     */
    public function removeChannel() {

    	$I_logger = $this->I_logger;

    	// Lumber has no channels upfront
    	$this->assertEmpty($I_logger->getChannels());

    	// Default channel is added
    	$s_channelName = 'default';
    	$I_logger->addChannel($s_channelName, $this->I_mockMonologLogger);

    	// Second channel is added
    	$I_logger->addChannel('second', $this->I_mockMonologLogger);

    	$this->assertCount(2, $I_logger->getChannels());
    	$this->assertArrayHasKey($s_channelName, $I_logger->getChannels());

    	// Default channel is removed
		$I_logger->removeChannel($s_channelName);

		$this->assertCount(1, $I_logger->getChannels());
		$this->assertArrayNotHasKey($s_channelName, $I_logger->getChannels());

    }


    /**
     * @covers MvlabsLumber\Service\Logger::removeChannel
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Channel default does not exist. Cannot remove it
     * @test
     *
     */
    public function removeNotExistingChannel() {

    	$I_logger = $this->I_logger;

    	// Default channel is removed
    	$I_logger->removeChannel('default');

    }


    /**
     * @covers MvlabsLumber\Service\Logger::getChannel
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Channel default does not exist
     * @test
     */
    public function canNotExistingChannelBeRemoved() {

    	$I_logger = $this->I_logger;

    	// Default channel is removed
    	$I_logger->removeChannel('default');

    }


    /**
     * @covers MvlabsLumber\Service\Logger::log
     * @covers MvlabsLumber\Service\Logger::debug
     * @covers MvlabsLumber\Service\Logger::info
     * @covers MvlabsLumber\Service\Logger::notice
     * @covers MvlabsLumber\Service\Logger::warning
     * @covers MvlabsLumber\Service\Logger::error
     * @covers MvlabsLumber\Service\Logger::critical
     * @covers MvlabsLumber\Service\Logger::alert
     * @covers MvlabsLumber\Service\Logger::emergency
     * @test
     */
    public function isEventSentToLogger() {

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
     * @covers MvlabsLumber\Service\Logger::getSeverityLevels
     * @test
     */
    public function getSeverityLevels() {

    	$this->assertTrue(($this->getPsrSeverityLevels() == $this->I_logger->getSeverityLevels()));

    }


    /**
     * @covers MvlabsLumber\Service\Logger::isValidSeverityLevel
     * @test
     */
    public function isSeverityValid() {

    	$this->assertTrue($this->I_logger->isValidSeverityLevel('info'));
    	$this->assertFalse($this->I_logger->isValidSeverityLevel('fun'));

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
