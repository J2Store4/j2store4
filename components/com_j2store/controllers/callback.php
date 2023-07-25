<?php
/*------------------------------------------------------------------------
# com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/


/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
class J2StoreControllerCallback extends F0FController
{

	protected $cacheableTasks = array();

	function __construct() {
		$config['csrfProtection'] = 0;
		parent::__construct($config);	
		$this->cacheableTasks = array();		
	}
	
	function execute($task) {
		$this->read();
	}

	function read() {
		// Makes sure SiteGround's SuperCache doesn't cache the subscription page
		J2Store::utilities()->nocache();
		
		$app = JFactory::getApplication();
		$app->setHeader('X-Cache-Control', 'False', true);
		$method = $app->input->getCmd('method', 'none');
		$model = $this->getModel('Callback');		
		$result = $model->runCallback($method);
		echo $result ? 'OK' : 'FAILED';
		$app->close();
	}
}
