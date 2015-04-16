<?php

/**
 * Lumber Default configuration
 *
 * @copyright Copyright (c) 2010-2013 MV Labs (http://www.mvlabs.it)
 * @link      https://github.com/mvlabs/MvlabsLumber
 * @license   MIT - Please view the LICENSE file that was distributed with this source code
 * @author    Steve Maraspin <steve@mvlabs.it>
 * @package   MvlabsLumber
 */

namespace Zend\Mvc;

return array(


	'lumber' => array(

		'events' => array(

						'application_errors' => array('event' => MvcEvent::EVENT_DISPATCH_ERROR,
								                      'severity' => 'alert',
						                        ),

						'custom_handler' => array('event' => 'invocato',
												  'target' => 'Application\Controller\ErrorController',
												  'verbose' => true,
                        						  'filter_exception_messages_containing' => array(),
                        						  'filter_event_errors_containing' => array(),
						),

					),

		'writers' => array(
			'primary' => array(
				'type' => 'file',
				'destination' => __DIR__ . '/../../../../data/application.log',
				'min_severity' => 'debug',
				'propagate' => true,
			),
		),

		'channels' => array(
			'default' => array(
				'writers' => array(
					'primary'
				)
			),
		),

	),


	'service_manager' => array(
			'factories' => array(
					'MvlabsLumber\Service\Logger' => 'MvlabsLumber\Service\LoggerFactory',
			),
	),


);
