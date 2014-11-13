<?php

/**
 * Lumber main logger factory class
 *
 * Builds an instance of Lumber logger taking into account configuration parameters from Service Manager
 *
 * @copyright Copyright (c) 2010-2013 MV Labs (http://www.mvlabs.it)
 * @link      https://github.com/mvlabs/MvlabsLumber
 * @license   MIT - Please view the LICENSE file that was distributed with this source code
 * @author    Steve Maraspin <steve@mvlabs.it>
 * @package   MvlabsLumber
 */

namespace MvlabsLumber\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Monolog\Logger as Monolog;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\CouchDBHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\ZendMonitorHandler;
use Monolog\Handler\NativeMailerHandler;

class LoggerFactory implements FactoryInterface {

	/**
	 * Zemd Framework Service Manager
	 * @var Zend\ServiceManager\ServiceManager
	 */
	protected $I_serviceLocator;

	/**
	 * Writers created from configuration
	 * @var array Available writers
	 */
	protected $aI_writers = array();

	/**
	 * Raw writers configuration (from app conf)
	 * @var array Writers configuration
	 */
	protected $am_writersConf = array();

    /**
     * {@inheritDoc}
     *
     * @return \Contents\Service\ContentService;
     */
    public function createService(ServiceLocatorInterface $I_serviceLocator)    {

    	$this->I_serviceLocator = $I_serviceLocator;

    	// Application config
    	$I_appConfig = $I_serviceLocator->get('Config');
    	if (!array_key_exists('lumber', $I_appConfig)) {
    		throw new \UnexpectedValueException('There seems to be no configuration for Lumber. Cannot continue execution');
    	}

    	// Lumber specific configuration
    	$am_loggerConf = $I_appConfig['lumber'];
		if (!is_array($am_loggerConf) ||
		    !array_key_exists('channels', $am_loggerConf) ||
		    !is_array($am_loggerConf['channels'])) {
			throw new \UnexpectedValueException('Channel configuration for Lumber ("lumber" key) seems to be empty or invalid');
		}

		// Writers conf is "cached" in order to avoid other calls to SM
		if (array_key_exists('writers', $am_loggerConf)) {

			if (!is_array($am_loggerConf["writers"])) {
				throw new \UnexpectedValueException('Writers configuration argument is not an array as expected');
			}

			foreach ($am_loggerConf["writers"] as $s_writerName => $am_writerConf) {
				$this->am_writersConf[$s_writerName] = $am_writerConf;
			}

		}

		// Lumber logger is created
		$I_logger = new Logger();

		// Channels and writers are registered for this logger
		foreach ($am_loggerConf['channels'] as $s_channelName => $am_channelInfo) {

			$I_channel = new Monolog($s_channelName);
			
			foreach ($am_channelInfo['writers'] as $s_writerName) {

				// Proper writer is setup and returned
				$I_writer = $this->getConfiguredWriter($s_writerName);
				
				// Writer is added to Monolog channel
				$I_channel->pushHandler($I_writer);
			}

			$I_logger->addChannel($s_channelName, $I_channel);

		}

		return $I_logger;

    }

	/**
	 * Gets configured writer (a Monolog handler)
	 *
	 * @param string $s_writerName Handler name
	 * @param array $am_writerConf Handler configuration
	 * @throws \InvalidArgumentException
	 * @throws \OutOfRangeException
	 * @return \Monolog\Handler\AbstractHandler Configured Monolog handler
	 */
    private function getConfiguredWriter($s_writerName) {

    	if (array_key_exists($s_writerName, $this->aI_writers)) {
    		return $this->aI_writers[$s_writerName];
    	}

    	$am_writersConf = $this->am_writersConf;

    	if (!array_key_exists($s_writerName, $am_writersConf)) {
    		throw new \UnexpectedValueException('Requested writer ' . $s_writerName . ' not found in Lumber configuration');
    	}

    	$am_writerConf = $am_writersConf[$s_writerName];

		// Minimum logging level for writer needs to be specified
    	if (!array_key_exists('min_severity', $am_writerConf)) {
			throw new \InvalidArgumentException('Writer ' . $s_writerName . ' needs parameter min_severity to be set');
    	}

		// Is minimum logging level valid?
    	$s_logAbove = $am_writerConf['min_severity'];
    	if (!Logger::isValidSeverityLevel($s_logAbove)) {
    		throw new \OutOfRangeException('Invalid logging level ' . $s_logAbove . ' for writer ' . $s_writerName);
    	}

    	// Has user set whether event shall propagate to other writers in the stack?
    	$b_bubble = $this->getOptionalParam('propagate', $am_writerConf);
    	if (null === $b_bubble) {
    		$b_bubble = true;
    	}

    	// What kind of writer shall we create?
    	switch($am_writerConf['type']) {

    		case 'file':
    		case 'stream':

    			$s_filePath = $am_writerConf["destination"];

    			// Destination file has to be writable
    			if (!is_writable($s_filePath) && !is_writable(dirname($s_filePath))) {
    				throw new \InvalidArgumentException('Can not continue writing to ' . $s_filePath . ' in writer ' . $s_writerName . ' of type ' . $am_writerConf['type'] . ' in Lumber configuration');
    			}

    			$I_writer = new StreamHandler($am_writerConf["destination"], $s_logAbove, $b_bubble);
    			break;


    		case 'firephp':

    			$I_writer = new FirePHPHandler($s_logAbove, $b_bubble);
    			break;


    		case 'chromephp':

    			$I_writer = new ChromePHPHandler($s_logAbove, $b_bubble);
    			break;


    		case 'couchdb':

    			$am_options = array();

    			if (array_key_exists('options', $am_writerConf) &&
    			    is_array($am_writerConf['options']) &&
    			    count($am_writerConf['options']) > 0) {
    				$am_options = $am_writerConf['options'];
    			}

    			$I_writer = new CouchDBHandler($am_options, $s_logAbove, $b_bubble);

    			break;


    		case 'phplog':

    			$I_writer = new ErrorLogHandler($s_logAbove, $b_bubble);
    			break;


    		case 'syslog':

    			$s_ident = 'lumber';
    			if (array_key_exists('ident', $am_writerConf)) {
    				$s_ident = $am_writerConf['ident'];
    			}

    			$s_facility = 'local0';
    			if (array_key_exists('facility', $am_writerConf)) {
    				$s_facility = $am_writerConf['facility'];
    			}

    			$I_writer = new SyslogHandler($s_ident, $s_facility, $s_logAbove, $b_bubble, LOG_PID);
    			break;


    		case 'rotatingfile':

    			$s_filePath = $am_writerConf["destination"];

    			// Destination file has to be writable
    			if (!is_writable($s_filePath) && !is_writable(dirname($s_filePath))) {
    				throw new \InvalidArgumentException('Can not continue writing to ' . $s_filePath . ' in writer ' . $s_writerName . ' of type ' . $am_writerConf['type'] . ' in Lumber configuration');
    			}

    			$i_daysKept = 0;
    			if (array_key_exists('days_kept', $am_writerConf)) {
    				$i_maxFiles = $am_writerConf['days_kept'];
    			}

    			$I_writer = new RotatingFileHandler($am_writerConf["destination"], $i_daysKept, $s_logAbove, $b_bubble);
    			break;


    		case 'zendmonitor':

    			$I_writer = new ZendMonitorHandler($s_logAbove, $b_bubble);
    			break;

			case 'nativemailer':
			
			    if (!array_key_exists('to', $am_writerConf)) {
			        throw new \InvalidArgumentException('Writer ' . $s_writerName . ' needs parameter "to" to be set');
			    }
			    
			    if (!array_key_exists('from', $am_writerConf)) {
			        throw new \InvalidArgumentException('Writer ' . $s_writerName . ' needs parameter "from" to be set');
			    }
			
			    $s_subject = '[Application] Error';
			    if (array_key_exists('subject', $am_writerConf)) {
			        $s_subject = $am_writerConf['subject'];
			    }
			
			    $s_to   = $am_writerConf["to"];
			    $s_from = $am_writerConf['from'];			    
			
			    $I_writer = new NativeMailerHandler($s_to, $s_subject, $s_from, $s_logAbove, $b_bubble);
			    break;

    		default:

    			// Only writers defined above are currently supported
    			throw new \InvalidArgumentException('Invalid type (' . $am_writerConf['type'] . ') for writer ' . $s_writerName . ' in Lumber configuration');
				break;

    	}

    	// No need to rebuild writer if it will be requested next
    	$this->aI_writers[$s_writerName] = $I_writer;

    	return $I_writer;

    }

    /**
     * Returns value of a parameter if present, null otherwise
     *
     * @param string $s_paramName parameter name
     * @param array $am_writerConf configuration array
     * @return mixed parameter value
     */
    private function getOptionalParam($s_paramName, $am_writerConf) {

    	$m_paramValue = null;

    	if (array_key_exists($s_paramName, $am_writerConf)) {
    		$m_paramValue = $am_writerConf[$s_paramName];
    	}

    	return $m_paramValue;

    }
}
