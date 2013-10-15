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
	 * @var array Lumber configuration
	 */
	protected $am_missingConf = array();


	/**
	 * Empty Lumber configuration
	 * @var array Lumber configuration
	 */
	protected $am_invalidConf = array('lumber' => array());


	/**
	 * Invalid writer type
	 * @var array Lumber configuration
	 */
	protected $am_invalidWriters = array (

			'lumber' => array(

					'writers' => 'not_a_writer_conf',

					'channels' => array(
							'default' => array(
									'writers' => array(
											'default'
									),
							),
					),
			),

	);


	/**
	 * Writer not existing
	 * @var array Lumber configuration
	 */
	protected $am_writerNotExisting = array (

			'lumber' => array(

					'writers' => array(
							'default' => array(
									'type' => 'someWritersWhichDoesntExistAndNeverWill',
									'destination' => '/tmp/test.log',
									'min_severity' => 'info',
							),
					),

					'channels' => array(
							'default' => array(
									'writers' => array(
											'writerdoesnotexist'
									),
							),
					),
			),

	);


	/**
	 * Invalid writer type
	 * @var array Lumber configuration
	 */
	protected $am_invalidWriterType = array (

		'lumber' => array(

			'writers' => array(
					'default' => array(
							'type' => 'someWritersWhichDoesntExistAndNeverWill',
							'destination' => '/tmp/test.log',
							'min_severity' => 'info',
					),
			),

			'channels' => array(
				'default' => array(
					'writers' => array(
						'default'
					),
				),
			),
		),

	);


	/**
	 * Working file writer configuration
	 * @var array Lumber configuration
	 */
    protected $am_workingFileWriter = array (

    	'lumber' => array(

    		'writers' => array(
				'default' => array(
					'type' => 'file',
							'destination' => '/tmp/test.log',
							'min_severity' => 'info',
					),
			),

			'channels' => array(
				'default' => array(
					'writers' => array(
						'default'
					),
				),
			),

		),

	);



    /**
     * Working FirePHP writer configuration
     * @var array Lumber configuration
     */
    protected $am_zendMonitorWriterWithoutExtension = array (

    		'lumber' => array(

    				'writers' => array(
    						'zendmonitor' => array(
    								'type' => 'zendmonitor',
    								'min_severity' => 'notice',
    						),
    				),
    				'channels' => array(
    						'default' => array(
    								'writers' => array(
    										'zendmonitor'
    								),
    						),
    				),

    		),

    );





    /**
     * Working FirePHP writer configuration
     * @var array Lumber configuration
     */
    protected $am_workingWriters = array (

    		'lumber' => array(

    				'writers' => array(
    						'file' => array(
    								'type' => 'file',
    								'destination' => '/tmp/test.log',
    								'min_severity' => 'notice',
    						),
    						'stream' => array(
    								'type' => 'stream',
    								'destination' => '/tmp/test2.log',
    								'min_severity' => 'notice',
    						),
    						'firephp' => array(
    								'type' => 'firephp',
    								'min_severity' => 'notice',
    						),
    						'chrome' => array(
    								'type' => 'chromephp',
    								'min_severity' => 'notice',
    						),
    						'couchdb' => array(
    								'type' => 'couchdb',
    								'min_severity' => 'notice',
    								'options' => array('host' => 'localhost')
    						),
    						'phplog' => array(
    								'type' => 'phplog',
    								'min_severity' => 'notice',
    						),
    						'syslog' => array(
    								'type' => 'syslog',
    								'min_severity' => 'notice',
    								'ident' => 'lumber-test',
    								'facility' => 'user',
    						),
    						'rotatingfile' => array(
    							'type' => 'rotatingfile',
    							'destination' => '/tmp/rotated.log',
    							'min_severity' => 'notice',
    							'days_kept' => 10,
    						),

    				),
    			    'channels' => array(
    			    	'default' => array(
    								'writers' => array(
    										'file',
    										'stream',
    										'firephp',
    										'chrome',
    										'couchdb',
    										'phplog',
    										'syslog',
    								),
    						),
    				),

    		),

    );


	/**
	 * Valid file writer configuration, but file is not writable
	 * @var array Lumber configuration
	 */
    protected $am_wrongFileLocationFileWriter = array (

    	'lumber' => array(

	    	'writers' => array(
	    		'default' => array(
	    			'type' => 'file',
	    			'destination' => '/tmp/idontthinkthisdirectoryexists/shameifitdoes',
	    			'min_severity' => 'info',
	    		),
	    	),

	    	'channels' => array(
	    		'default' => array(
	    			'writers' => array(
	    				'default'
	    			),
	    		),
	    	),
    	),
	);


    /**
     * No severity specified
     * @var array Lumber configuration
     */
    protected $am_noSeveritySet = array (

    		'lumber' => array(

    				'writers' => array(
    						'default' => array(
    								'type' => 'file',
    								'destination' => '/tmp/test.log',
    						),
    				),

    				'channels' => array(
    						'default' => array(
    								'writers' => array(
    										'default'
    								),
    						),
    				),
    		),
    );


    /**
     * Invalid logging level
     * @var array Lumber configuration
     */
  	protected $am_wrongLogLevel = array (

  		'lumber' => array(

  			'writers' => array(
  				'default' => array(
  					'type' => 'file',
  					'destination' => '/tmp/test.log',
  					'min_severity' => 'fun',
  				),
  			),

    		'channels' => array(
    			'default' => array(
    				'writers' => array(
    					'default'
    				),
    			),
    		),
    	),
    );


  	/**
  	 * Multiple writers
  	 * @var array Lumber configuration
  	 */
  	protected $am_multipleWriters = array (

  		'lumber' => array(

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

  			'channels' => array(
  							'default' => array(
  									'writers' => array(
  											'default',
  											'second',
  											'third',
  											'fourth'
  									),
  							),
  					),
  			),
	);

  	/**
  	 * Multiple channels
  	 * @var array Lumber configuration
  	 */
  	protected $am_multipleChannels = array (

  		'lumber' => array(

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
  			),

  			'channels' => array(
  				'default' => array(
  					'writers' => array(
  						'default',
  						'second',
  					),
  				),
  				'secondary' => array(
  					'writers' => array(
  						'second',
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

    		if ('workingWriters' == $s_name &&
    		    function_exists('zend_monitor_custom_event')) {

    			$am_tempConf = $this->$s_confNameVar;
    			$am_tempConf['lumber']['writers']['zendmonitor'] =
			    			array(
			    					'type' => 'zendmonitor',
			    					'min_severity' => 'notice',
			    			);

    			$am_tempConf['lumber']['channels']['default']['writers'][] = 'zendmonitor';

    			return $am_tempConf;

    		}

	    	return $this->$s_confNameVar;
	}


 }
