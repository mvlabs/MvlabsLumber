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

namespace MvlabsLumberTest;

use PHPUnit_Framework_TestCase;
use MvlabsLumber\Module;
use MvlabsLumberTest\Service\MockConfigs;
use Zend\Http\Response;
use Zend\Http\Request;
use Zend\EventManager\EventManager;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Event;
use Zend\MVc\Application;

/**
 * @covers MvlabsLumber\Module
 */
class ModuleTest extends PHPUnit_Framework_TestCase {

	/**
	 * Module to be tested
	 * @var MvlabsLumber\Module
	 */
	private $I_module;

	/**
	 * Prepare the objects to be tested.
	 */
	protected function setUp() {

		$I_event = \Mockery::mock('Zend\Mvc\MvcEvent');
		$this->I_event = $I_event;

		$this->I_module = new Module();

	}

	/*
	public function base() {

		$am_lumberConfig = include __DIR__ . "/../../config/module.config.php";

		//$I_mockLumber->shouldReceive('isValidSeverityLevel')->with('default')->once()->andReturn(true);
		//$I_mockSM->shouldReceive('get')->with('MvlabsLumber\Service\Logger')->once()->andReturn($I_mockLumber);
		// $I_application->shouldReceive('getServiceManager')->andReturn($I_mockSM);


		$this->I_module->onBootstrap($this->I_event);

		/*
		$applicationEventManager = new EventManager();

		$application = $this->getMock('Zend\Mvc\ApplicationInterface');
		$application
		->expects($this->any())
		->method('getEventManager')
		->will($this->returnValue($applicationEventManager));

		$event = new Event();
		$event->setTarget($application);

		$module = new Module();
		$module->onBootstrap($event);
		*/


		/*
		$dispatchListeners = $applicationEventManager->getListeners(MvcEvent::EVENT_DISPATCH_ERROR);

		foreach ($dispatchListeners as $listener) {
			$metaData = $listener->getMetadata();
			$callback = $listener->getCallback();

			$this->assertEquals('onDispatch', $callback[1]);
			$this->assertEquals(-9999999, $metaData['priority']);
			$this->assertTrue($callback[0] instanceof Module);

		}

	}
	*/

	public function testOnBootstrapWithEmptyConf() {

		$am_lumberConfig = array();

		// $I_event->shouldReceive('setTarget');
		$I_application = $this->getMockApp($am_lumberConfig);

		$this->I_event->shouldReceive('getApplication')->andReturn($I_application);
		// $I_event->setTarget($I_application);

		$this->I_module->onBootstrap($this->I_event);

	}


	public function testOnBootstrapValidConf() {

		$am_lumberConfig = include __DIR__ . "/../../config/module.config.php";

		$am_lumberConfig['lumber']['events']['testevent'] = array('verbose' => true);

		// $I_event->shouldReceive('setTarget');
		$I_application = $this->getMockApp($am_lumberConfig);

		$this->I_event->shouldReceive('getApplication')->andReturn($I_application);
		// $I_event->setTarget($I_application);

		$this->I_module->onBootstrap($this->I_event);

		// A test event, as specified in default configuration
		$I_em = new \Zend\EventManager\EventManager(__CLASS__);

		try {
			throw new \Exception('Test Exception');
		} catch(\Exception $I_e) {
			$I_em->trigger('invocato', $this, array('message' => 'Test Message', 'exception' => $I_e));
		}

		// $I_em = $this->getMock('Zend\\EventManager\\EventManagerInterface');
		// $I_em->expects($this->once())->method('trigger')->with(...);

	}


	/**
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Severity SOMETHING_NOT_EXISTING is invalid
	 */
	public function testOnBootstrapInvalidConf() {

		$am_lumberConfig =  array(
				'lumber' => array(
						'events' => array(
								'application_errors' => array('event' => MvcEvent::EVENT_DISPATCH_ERROR,
								                      'severity' => 'SOMETHING_NOT_EXISTING',
													  'verbose' => true,
						                        ),
						),
				)
		);

		// $I_event->shouldReceive('setTarget');
		$I_application = $this->getMockApp($am_lumberConfig);

		$this->I_event->shouldReceive('getApplication')->andReturn($I_application);
		// $I_event->setTarget($I_application);

		$this->I_module->onBootstrap($this->I_event);

	}


	public function testGetAutoloaderConfig() {

		$this->assertInternalType('array',$this->I_module->getAutoloaderConfig());

	}


	public function testGetConfig() {

		$this->assertInternalType('array', $this->I_module->getConfig());

	}


	private function getMockApp($am_config) {

		// $I_evtManager = \Mockery::mock('Zend\EventManager\EventManager');
		$I_evtManager = $applicationEventManager = new EventManager();

		$I_application = \Mockery::mock('Zend\Mvc\Application');
		$I_application->shouldReceive('getEventManager')->andReturn($I_evtManager);
		$I_sManager = $this->getMockSM();
		$I_application->shouldReceive('getServiceManager')->andReturn($I_sManager);

		$I_application->shouldReceive('getConfig')->andReturn($am_config);

		return $I_application;

	}


	/**
	 * Constructs a mock service manager with basic Lumber configuration
	 * @return \Zend\ServiceManager\ServiceLocatorInterface
	 */
	private function getMockSM() {

		$I_mockLumber =  \Mockery::mock('MvlabsLumber\Service\Logger');

		// Working configuration
		$I_mockLumber->shouldReceive('isValidSeverityLevel')->with('alert')->andReturn(true);
		$I_mockLumber->shouldReceive('isValidSeverityLevel')->with('notice')->andReturn(true);
		$I_mockLumber->shouldReceive('isValidSeverityLevel')->with('SOMETHING_NOT_EXISTING')->andReturn(false);

		$I_mockLumber->shouldReceive('log')->andReturn(true);

		$I_mockSM = \Mockery::mock('Zend\ServiceManager\ServiceManager');
		$I_mockSM->shouldReceive('get')->with('MvlabsLumber\Service\Logger')->andReturn($I_mockLumber);

		$I_mockSM->shouldReceive('get')->with('MvlabsLumber\Service\Logger')->andReturn($I_mockLumber);

		$I_response = new Response();
		$I_mockSM->shouldReceive('get')->with('Response')->andReturn($I_response);

		$I_request = new Request();
		$I_mockSM->shouldReceive('get')->with('Request')->andReturn($I_request);

		return $I_mockSM;

	}


}
