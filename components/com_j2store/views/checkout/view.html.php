<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
class J2StoreViewCheckout extends F0FViewHtml
{

	protected function onDisplay($tpl = null)
	{
	
		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$user = JFactory::getUser();
		$view = $this->input->getCmd('view', 'checkout');
		
		$this->params = J2Store::config();
		$this->currency = J2Store::currency();
		$this->storeProfile = J2Store::storeProfile();
		$this->user = $user; 
		
		return true; 
	}
	
}
	
	