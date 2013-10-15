<?php

/**
 * Lumber module entry point within ZF2
 *
 * @copyright Copyright (c) 2010-2013 MV Labs (http://www.mvlabs.it)
 * @link      https://github.com/mvlabs/MvlabsLumber
 * @license   MIT - Please view the LICENSE file that was distributed with this source code
 * @author    Steve Maraspin <steve@mvlabs.it>
 * @package   MvlabsLumber
 */


namespace MvlabsLumber;

use Zend\Mvc\ModuleRouteListener;
use Zend\EventManager\Event;
use Zend\Mvc\ApplicationInterface;
use Zend\Mvc\MvcEvent;


/**
 * MvlabsLumber Module
 */
class Module {


	/**
	 * {@inheritDoc}
	 */
	public function onBootstrap(MvcEvent $I_e)	{

		// Application configuration
		$I_application = $I_e->getApplication();
		$this->handleEvents($I_application);

	}


	 /**
     * {@inheritDoc}
     */
	public function getConfig() {
		return  include __DIR__ . '/../../config/module.config.php';
	}


	/**
	 * {@inheritDoc}
	 */
	public function getAutoloaderConfig() {
		return array(
				'Zend\Loader\StandardAutoloader' => array(
						'namespaces' => array(
								__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
						),
				),
		);
	}


	/**
	 * Takes care of registering events to Lumber
	 *
	 * @throws \InvalidArgumentException
	 */
	private function handleEvents(\Zend\Mvc\ApplicationInterface $I_application) {

		// Event Manager is loaded
		$I_eventManager = $I_application->getEventManager();
		$I_sharedManager = $I_application->getEventManager()->getSharedManager();

		$I_moduleRouteListener = new ModuleRouteListener();
		$I_moduleRouteListener->attach($I_eventManager);

		// Lumber is loaded
		$I_sm = $I_application->getServiceManager();
		$I_lumber = $I_sm->get('MvlabsLumber\Service\Logger');

		// Lumber configuration is loaded
		$am_config = $I_application->getConfig();

		// Do we need to do something at all?
		if(!array_key_exists('lumber', $am_config) ||
		   !array_key_exists('events', $am_config['lumber'])) {
			return;
		}

		$am_events = $am_config['lumber']['events'];

		foreach ($am_events as $s_eventName => $am_eventInfo) {

			$am_eventConf = $this->getEventInfo($am_eventInfo);
			list($s_target, $s_event, $s_severity, $b_verbose) = $am_eventConf;

			// Check for problems upon app initialization, rather than when event is triggered
			if (!$I_lumber->isValidSeverityLevel($s_severity)) {
				throw new \InvalidArgumentException('Severity ' . $s_severity . ' is invalid');
			}

			$I_request = $I_sm->get('Request');
			\Zend\EventManager\StaticEventManager::getInstance()->attach($s_target, $s_event,
			// $I_sharedManager->attach($s_target, $s_event,
					                 function($I_event) use ($I_lumber, $I_request, $am_eventInfo, $am_eventConf) {

			    list($s_target, $s_event, $s_severity, $b_verbose) = $am_eventConf;

				$s_message = '';

				if ($I_event instanceof \Zend\EventManager\Event) {

					$s_target = $I_event->getTarget();
					$s_name = $I_event->getName();
					$am_params = $I_event->getParams();

					$am_additionalInfo = array();
					$as_messages = array();

					$s_requestUri = $I_request->getUriString();
					$am_additionalInfo['request'] = $s_requestUri;

					$s_queryParams = json_encode($I_request->getQuery());
					$am_additionalInfo['query_params'] = $s_queryParams;

					$s_postParams = json_encode($I_request->getPost());
					$am_additionalInfo['post_params'] = $s_postParams;

					if (array_key_exists('message', $am_params)) {
						$as_messages[] = $am_params['message'];
					}

					if (array_key_exists('exception', $am_params) &&
					    $am_params['exception'] instanceof \Exception) {

						$I_exception = $am_params['exception'];
						do {

							$as_messages[] = $I_exception->getMessage();
							if($b_verbose) {
								$am_traces = $I_exception->getTrace();
								foreach ($am_traces as $am_trace) {

									$s_tempMessage = 'Error';
									$as_toCheck = array('file', 'line', 'class', 'method');
									foreach ($as_toCheck as $s_check) {
										$s_tempMessage .= (array_key_exists($s_check, $am_trace)?' '.$s_check.': '.$am_trace[$s_check]:'');
									}

									$as_messages[] = $s_tempMessage;
								}
							}

						}
						while($I_exception = $I_exception->getPrevious());

					}
				}

				$am_additionalInfo['event'] = $s_event;
				$am_additionalInfo['target'] = $s_target;

				foreach ($as_messages as $s_message) {
					$I_lumber->log($s_message, $s_severity, $am_additionalInfo);
				}

			});

		}	// Foreach
	}


	private function getEventInfo(array $am_eventInfo) {

		// Target is taken care of - empty means everything
		if (!array_key_exists('target', $am_eventInfo)) {
			$am_eventInfo['target'] = '*';
		}
		$s_target = $am_eventInfo['target'];

		// Event is also taken care of - empty again is everything
		if (!array_key_exists('event',$am_eventInfo)) {
			$am_eventInfo['event'] = '*';
		}
		$s_event = $am_eventInfo['event'];

		// If no severity is specified, notice is used as default
		if (!array_key_exists('severity', $am_eventInfo)) {
			$am_eventInfo['severity'] = 'notice';
		}
		$s_severity = $am_eventInfo['severity'];

		// If no severity is specified, notice is used as default
		if (!array_key_exists('verbose', $am_eventInfo)) {
			$am_eventInfo['verbose'] = false;
		}
		$b_verbose = $am_eventInfo['verbose'];

		return array($s_target, $s_event, $s_severity, $b_verbose);

	}


}
