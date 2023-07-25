<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();
$user = JFactory::getUser();
if(!$user->authorise('j2store.vieworder', 'com_j2store')) {
	return '';
}
require_once( dirname(__FILE__).'/helper.php' );
JFactory::getLanguage()->load('com_j2store', JPATH_SITE);
$moduleclass_sfx = $params->get('moduleclass_sfx','');
$link_type = $params->get('link_type','link');
//$params = JComponentHelper::getParams('com_j2store');
$chart_type = $params->get('chart_type','daily');
require_once (JPATH_COMPONENT_ADMINISTRATOR.'/models/orders.php' );
//$order_model = new J2StoreModelOrders();
$order_model = F0FModel::getTmpInstance('Orders' ,'J2StoreModel');
$helper = new modJ2storeChartHelper();
$order_status = $params->get('order_status',array());
$orders= $helper->getOrders($order_status);
$years = $helper->getYear($order_status);
$months = $helper->getMonth($order_status);
$days = $helper->getDay($order_status);
require( JModuleHelper::getLayoutPath('mod_j2store_chart') );


