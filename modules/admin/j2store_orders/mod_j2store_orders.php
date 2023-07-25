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
JFactory::getLanguage()->load('com_j2store', JPATH_SITE);
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
$moduleclass_sfx = $params->get('moduleclass_sfx','');
$params = J2Store::config();
$order_model = F0FModel::getTmpInstance('Orders','J2StoreModel');
$orders = $order_model->clearState()->order_type('normal')->limit(5)->limitstart(0)->filter_order('created_on')->filter_order_Dir('DESC')->getList();
require( JModuleHelper::getLayoutPath('mod_j2store_orders') );