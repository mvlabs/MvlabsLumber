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

return array(

	'lumber' => array(

		'channels' => array(
			'default' => array(
				'writers' => array(
					'primario' => array(
									'type' => 'file',
									'destination' => __DIR__ . '../../../../../../data/application.log',
									'min_severity' => 'debug',
							        'propagate' => true,
									'avoid_duplicates' => true,
					              ),
				),

			),

		),
	),

);
