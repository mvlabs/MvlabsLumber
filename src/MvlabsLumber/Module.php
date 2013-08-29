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
use Zend\Mvc\MvcEvent;


/**
 * MvlabsLumber Module
 */
class Module {


	/**
	 * {@inheritDoc}
	 */
	public function onBootstrap(MvcEvent $e)	{
		// @TODO: To be implemented
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

}
