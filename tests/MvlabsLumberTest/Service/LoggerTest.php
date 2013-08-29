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


class LoggerTest extends \PHPUnit_Framework_TestCase {


    /**
     * The object to be tested.
     *
     * @var Logger
     */
    protected $I_logger;


    /**
     * Prepare the objects to be tested.
     */
    protected function setUp() {
        $this->I_event = null;
        $this->I_logger = new Logger();

    }


    /**
     * Get a list of logger channels
     */
    public function testListChannels() {
   		$this->markTestIncomplete('To be implemented');
    }


    /**
     * Tests channel removal
     */
    public function testRemoveChannel() {
    	$this->markTestIncomplete('To be implemented');
    }


    /**
     * Tests channel addition
     */
    public function testAddChannel() {
    	$this->markTestIncomplete('To be implemented');
    }


    /**
     * Tests logging
     */
    public function testLog() {
    	$this->markTestIncomplete('To be implemented');
    }


    /**
     * Tests available severity levels
     */
    public function testSeverityLevels() {
    	$this->markTestIncomplete('To be implemented');
    }


    /**
     * Tests valid severity level is specified
     */
    public function testValidSeverity() {
    	$this->markTestIncomplete('To be implemented');
    }


    /**
     * Tests invalid severity level
     */
    public function testInvalidSeverity() {
    	$this->markTestIncomplete('To be implemented');
    }


}
