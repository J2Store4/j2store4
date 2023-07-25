<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
class J2StoreViewPostconfig extends F0FViewHtml
{
	public function onBrowse($tpl = null)
	{
		
		$systemPlugin = JPluginHelper::isEnabled('system', 'j2store');
		if(!$systemPlugin) {
			//System plugin disabled. Manually enable it
			J2Store::plugin()->enableJ2StorePlugin();
		}
		$this->assign('systemPlugin', $systemPlugin);
		$this->assign('cachePlugin', JPluginHelper::isEnabled('system', 'cache'));
		
		
		$this->assign('params', J2Store::config());
		
		return true;
	}
}