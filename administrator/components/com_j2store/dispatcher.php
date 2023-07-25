<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

defined('_JEXEC') or die;

class J2StoreDispatcher extends F0FDispatcher
{
        public $defaultView = 'cpanel';

      public function onBeforeDispatch() {
      		if(J2Store::platform()->isClient('administrator')) {
	      		$layout = new JLayoutFile('joomla.sidebars.submenu');
    	  		$layout->addIncludePaths(JPATH_ADMINISTRATOR. '/components/com_j2store/layouts');
      		}

    	   	require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/strapper.php');
	       	J2StoreStrapper::addJS();
	       	J2StoreStrapper::addCSS();
	       	
	       	require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
	       	J2Store::plugin()->event('BeforeDispatch');
	       	require_once JPATH_ADMINISTRATOR.'/components/com_j2store/library/popup.php';
       		require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/select.php';
       		require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2html.php';
       		return  parent::onBeforeDispatch();
       }
}

