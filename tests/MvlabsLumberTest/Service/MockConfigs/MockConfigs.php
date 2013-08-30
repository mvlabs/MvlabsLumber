<?php

/**
 * Lumber Mock configurations to be used in tests
 *
 * @copyright Copyright (c) 2010-2013 MV Labs (http://www.mvlabs.it)
 * @link      https://github.com/mvlabs/MvlabsLumber
 * @license   MIT - Please view the LICENSE file that was distributed with this source code
 * @author    Steve Maraspin <steve@mvlabs.it>
 * @package   MvlabsLumber
 */

namespace MvlabsLumberTest\Service\MockConfigs;

class MockConfigs {

	/**
	 * Missing Lumber configuration
	 * @var array
	 */
	protected $am_missingConf = array();


	/**
	 * Empty Lumber configuration
	 * @var array
	 */
	protected $am_invalidConf = array('lumber' => array());


	/**
	 * Invalid writer type
	 * @var array
	 */
	protected $am_invalidWriterType = array (
		'lumber' => array(
			'channels' => array(
				'default' => array(
					'writers' => array(
						'default' => array(
							'type' => 'someWritersWhichDoesntExistAndNeverWill',
							'destination' => '/tmp/test.log',
							'min_severity' => 'info',
						),
					),
				),
			),
		),
	);


	/**
	 * Working file writer configuration
	 * @var array
	 */
    protected $am_workingFileWriter = array (
		'lumber' => array(
			'channels' => array(
				'default' => array(
					'writers' => array(
						'default' => array(
							'type' => 'file',
							'destination' => '/tmp/lumber-test.log',
							'min_severity' => 'info',
					      ),
					),
				),
			),
		),
	);


	/**
	 * Valid file writer configuration, but file is not writable
	 * @var array
	 */
    protected $am_wrongFileLocationFileWriter = array (
    	'lumber' => array(
    	'channels' => array(
    		'default' => array(
    			'writers' => array(
    				'default' => array(
    					'type' => 'file',
    					'destination' => '/tmp/idontthinkthisdirectoryexists/shameifitdoes',
    					'min_severity' => 'info',
    					),
    				),
    			),
    		),
    	),
	);


    /**
     * Invalid logging level
     * @var array
     */
  	protected $am_wrongLogLevel = array (
    	'lumber' => array(
    		'channels' => array(
    			'default' => array(
    				'writers' => array(
    					'default' => array(
    						'type' => 'file',
    						'destination' => '/tmp/test.log',
    						'min_severity' => 'fun',
    					),
    				),
    			),
    		),
    	),
    );


  	/**
  	 * Multiple writers
  	 * @var array
  	 */
  	protected $am_multipleWriters = array (
  			'lumber' => array(
  					'channels' => array(
  							'default' => array(
  									'writers' => array(
  											'default' => array(
  													'type' => 'file',
  													'destination' => '/tmp/test.log',
  													'min_severity' => 'info',
  													'propagate' => true,
  											),
  											'second' => array(
  													'type' => 'file',
  													'destination' => '/tmp/test2.log',
  													'min_severity' => 'info',
  													'propagate' => false,
  											),
  											'third' => array(
  													'type' => 'file',
  													'destination' => '/tmp/test3.log',
  													'min_severity' => 'info',
  											),
  											'fourth' => array(
  													'type' => 'file',
  													'destination' => '/tmp/test4.log',
  													'min_severity' => 'info',
  													'propagate' => false,
  											),
  									),
  							),
  					),
  			),
  	);


	/**
	 * Returns requested config
	 *
	 * @param string $s_name configuration to be returned
	 */
    public function getConf($s_name) {
    		$s_confNameVar = 'am_'. $s_name;
	    	return $this->$s_confNameVar;
	}


 }
