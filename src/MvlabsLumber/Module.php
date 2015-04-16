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

use Zend\EventManager\Event;
use Zend\Mvc\ApplicationInterface;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\ModuleRouteListener;

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

		    $b_discardEvent = false;
		    
			$am_eventConf = $this->getEventInfo($am_eventInfo);
			
			list($s_target, $s_event, $s_severity, $b_verbose, $am_filterErrorKinds, $as_filterMessages) = $am_eventConf;
			
			file_put_contents('/tmp/config', json_encode($am_eventConf)."\n", FILE_APPEND);

			// Check for problems upon app initialization, rather than when event is triggered
			if (!$I_lumber->isValidSeverityLevel($s_severity)) {
				throw new \InvalidArgumentException('Severity ' . $s_severity . ' is invalid');
			}

			$I_request = $I_sm->get('Request');
			\Zend\EventManager\StaticEventManager::getInstance()->attach($s_target, $s_event,
					                 function($I_event) use ($I_lumber, $I_request, $am_eventInfo, $am_eventConf) {

			    /*
				file_put_contents('/tmp/log', json_encode($am_eventConf)."\n", FILE_APPEND);
				file_put_contents('/tmp/log', json_encode($I_event->getParams())."\n", FILE_APPEND);
				*/
			    list($s_target, $s_event, $s_severity, $b_verbose, $am_filterErrorKinds, $as_filterMessages) = $am_eventConf;

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

					$s_message = '';
					
					if (array_key_exists('error', $am_params)) {
					    $am_additionalInfo['error'] = $am_params['error'];
					    if (in_array($am_params['error'], $am_filterErrorKinds)) {
					        $b_discardEvent = true;
					    }
					}
					
					if (array_key_exists('message', $am_params)) {
						$s_message .= $am_params['message'];
					}
					
					if (array_key_exists('exception', $am_params) &&
					    $am_params['exception'] instanceof \Exception) {
					
					    $I_exception = $am_params['exception'];
					    $s_message = $I_exception->getMessage();
					    
					    // Are there any filters on message string?
					    foreach ($as_filterMessages as $s_exceptionMessage) {
					        // Make this comparison loose...
					        if (false !== strpos($s_message, $s_exceptionMessage )) {
					            $b_discardEvent = true;
					        }    
					    }
					    
					    
					    // Exceptions need to be made human readable
					    if ($b_verbose) {
					        $s_message .= $I_exception->getTraceAsString();
					    }
					}
					
				}
				
				$am_additionalInfo['event'] = $s_event;
				$am_additionalInfo['target'] = $s_target;
				
				if (!$b_discardEvent) {
				    $I_lumber->log($s_message, $s_severity, $am_additionalInfo);				    
				}
				
			});
		}	// Foreach
	}

	/**
	 * Support function extracting configuration event information
	 *
	 * @param array event configuration record $am_eventInfo
	 * @return array configured event info record
	 */
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

		$as_filters = array();
		if (array_key_exists('filter_messages_containing', $am_eventInfo) &&
		is_array($am_eventInfo['filter_messages_containing'])) {
		    $as_filters = $am_eventInfo['filter_messages_containing'];
		}

		$as_errorsToDrop = array();
		if (array_key_exists('filter_error_kinds', $am_eventInfo) &&
		is_array($am_eventInfo['filter_error_kinds'])) {
		    $as_errorsToDrop = $am_eventInfo['filter_error_kinds'];
		}
		
		return array($s_target, $s_event, $s_severity, $b_verbose, $as_errorsToDrop, $as_filters);

	}
}

