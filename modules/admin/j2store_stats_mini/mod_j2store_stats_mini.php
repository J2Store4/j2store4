<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

defined('_JEXEC') or die;
$user = JFactory::getUser();
if(!$user->authorise('j2store.vieworder', 'com_j2store')) {
	return '';
}

JFactory::getLanguage()->load('com_j2store', JPATH_SITE);
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
$moduleclass_sfx = $params->get('moduleclass_sfx','');

require( JModuleHelper::getLayoutPath('mod_j2store_stats_mini') );
$currency = J2Store::currency();