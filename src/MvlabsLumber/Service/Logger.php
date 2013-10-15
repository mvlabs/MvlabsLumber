<?php

/**
 * Lumber main logger class
 *
 * This class takes care of handling incoming log requests and properly dispatching them to
 * registered Monolog channels. It is pretty much an adapter over multiple instances of Monolog
 *
 * @copyright Copyright (c) 2010-2013 MV Labs (http://www.mvlabs.it)
 * @link      https://github.com/mvlabs/MvlabsLumber
 * @license   MIT - Please view the LICENSE file that was distributed with this source code
 * @author    Steve Maraspin <steve@mvlabs.it>
 * @package   MvlabsLumber
 */


namespace MvlabsLumber\Service;

use Monolog\Logger as Monolog;
use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface {

	const EMERGENCY = 'emergency';

	const ALERT = 'alert';

	const CRITICAL = 'critical';

	const ERROR = 'error';

	const WARNING = 'warning';

	const NOTICE = 'notice';

	const INFO = 'info';

	const DEBUG = 'debug';


	/**
	 * Messages are sent to these channels
	 *
	 * @var array $aI_channels logger channels
	 */
	private $aI_channels = array();


	/**
	 * Maps between Lumber and Monolog levels
	 *
	 * @var array Monolog logging levels
	 */
	protected $as_monologLevels = array(
			'debug'     => 100,
			'info'      => 200,
			'notice'    => 250,
			'warning'   => 300,
			'error'     => 400,
			'critical'  => 500,
			'alert'     => 550,
			'emergency' => 600,
	);


	/**
	 * Lists available logging severity levels
	 *
	 * @return array
	 */
	public static function getSeverityLevels() {

		// @TODO: needs to be replaced if new constants are introduced within the class
		$I_ref = new \ReflectionClass(__CLASS__);
		$am_levels = $I_ref->getConstants();

		return $am_levels;

	}


	/**
	 * Tells whether level param is valid
	 *
	 * @param mixed $m_level Level to be evaluated
	 * @return boolean Is level valid?
	 */
	public static function isValidSeverityLevel($m_level) {

		$am_validLevels = self::getSeverityLevels();

		// If an invalid operator is specified, an Exception is thrown
		if (in_array($m_level,$am_validLevels)) {
			return true;
		}

		return false;

	}


	/**
	 * Adds a channel to current list of registered channels
	 *
	 * @param string $s_channelName Channel name
	 * @param Monolog\Logger $I_channel Monolog Logger instance
	 */
	public function addChannel($s_channelName, Monolog $I_channel) {
		$this->aI_channels[$s_channelName] = $I_channel;
	}


	/**
	 * Lists currently registered channels
	 *
	 * @return array currently registered channels (Monolog instances)
	 */
	public function getChannels() {
		return $this->aI_channels;
	}


	/**
	 * Gets a spcific channel
	 *
	 * @param string $s_channelName Channel name
	 */
	public function getChannel($s_channelName) {

		if (!array_key_exists($s_channelName, $this->aI_channels)) {
			throw new \UnexpectedValueException('Channel ' . $s_channelName . ' does not exist');
		}

		return $this->aI_channels[$s_channelName];
	}


	/**
	 * Removes a channel from list of registered ones
	 *
	 * @param string $s_channelName Channel name
	 */
	public function removeChannel($s_channelName) {

		if (!array_key_exists($s_channelName, $this->aI_channels)) {
			throw new \UnexpectedValueException('Channel ' . $s_channelName . ' does not exist. Cannot remove it');
		}

		unset($this->aI_channels[$s_channelName]);
	}


    /**
     * Logs with severity level to all registered channels
     *
     * @param string $s_message Main message content
     * @param mixed $s_level Message severity level. Default is null, which will result in info, as commonly found in logger implementations
     * @param array $am_context Message context - IE Additional information
     * @return null
     */
    public function log($s_message, $s_level = 'notice', array $am_context = array()) {

    	if (!$this->isValidSeverityLevel($s_level)) {
    		throw new \OutOfRangeException('Severity level ' . $s_level . ' is invalid and can not be used');
    	}

    	foreach ($this->aI_channels as $s_channelName => $I_channel) {
    		$I_channel->addRecord($this->as_monologLevels[$s_level], $s_message, $am_context);
    	}

    }


	/**
	 * Action must be taken immediately.
	 *
	 * Example: Entire website down, database unavailable, etc. This should
	 * trigger the SMS alerts and wake you up.
	 *
     * @param string $s_message Main message content
     * @param array $am_context Message context - IE Additional info
     * @return null
	 */
	public function emergency($s_message, array $am_context = array()) {
		$this->log($s_message, self::EMERGENCY, $am_context);
	}


    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function alert($s_message, array $am_context = array()) {
    	$this->log($s_message, self::ALERT, $am_context);
    }


    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function critical($s_message, array $am_context = array()) {
    	$this->log($s_message, self::CRITICAL, $am_context);
    }


    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function error($s_message, array $am_context = array()) {
    	$this->log($s_message, self::ERROR, $am_context);
    }


    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function warning($s_message, array $am_context = array()) {
    	$this->log($s_message, self::WARNING, $am_context);
    }


    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function notice($s_message, array $am_context = array()) {
    	$this->log($s_message, self::NOTICE, $am_context);
    }


    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function info($s_message, array $am_context = array()) {
    	$this->log($s_message, self::INFO, $am_context);
    }


    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return null
     */
    public function debug($s_message, array $am_context = array()) {
    	$this->log($s_message, self::DEBUG, $am_context);
    }


}


