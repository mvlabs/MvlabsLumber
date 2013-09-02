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
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\CouchDBHandler;

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
				if (array_key_exists($s_writerName, $this->am_writersConf)) {
					throw new \UnexpectedValueException('Writer ' . $s_writerName . ' has been declared more than once in Lumber configuration');
				}
				$this->am_writersConf[$s_writerName] = $am_writerConf;
			}

		}

		// Lumber logger is created
		$I_logger = new Logger();

		// Channels and writers are registered for this logger
		foreach ($am_loggerConf['channels'] as $s_channelName => $am_channelInfo) {

			$I_channel = new \Monolog\Logger($s_channelName);

			if (!array_key_exists('writers', $am_channelInfo) || !is_array($am_channelInfo['writers']) || count($am_channelInfo['writers']) == 0) {
				throw new \UnexpectedValueException('Configuration for Lumber channel ' . $s_channelName . ' seems to be empty. Cannot continue');
			}

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
			throw new \InvalidArgumentException('Writer ' . $s_writerName . ' needs parameter log_above to be set');
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

    			$s_filePath = $am_writerConf["destination"];

    			// Destination file has to be writable
    			if (!is_writable($s_filePath) && !is_writable(dirname($s_filePath))) {
    				throw new \InvalidArgumentException('Can not continue writing to ' . $s_filePath . ' in writer ' . $s_writerName . ' of type ' . $am_writerConf['type'] . ' in Lumber configuration');
    			}

    			// @TODO: Improve for testability. Get writer out of service manager, instead of creating it here
    			$I_writer = new StreamHandler($am_writerConf["destination"], $s_logAbove, $b_bubble);
    			break;

    		// @TODO: Implement configurartion handling for all other supported Monolog Handlers

    		default:

    			// Only writers defined above are currently supported
    			throw new \InvalidArgumentException('Invalid type for writer ' . $s_writerName . ' in Lumber configuration');
				break;

    	}

    	return $I_writer;

    }


    /**
     * Returns value of a parameter if present, null otherwise
     *
     * @param string $s_paramName parameter name
     * @param array $am_writerConf configuration array
     * @return Ambigous <NULL, parameter value>
     */
    private function getOptionalParam($s_paramName, $am_writerConf) {

    	$m_paramValue = null;

    	if (array_key_exists($s_paramName, $am_writerConf)) {
    		$m_paramValue = $am_writerConf[$s_paramName];
    	}

    	return $m_paramValue;

    }


}
