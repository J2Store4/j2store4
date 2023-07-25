<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2StoreModelProductsBehaviorDefault extends F0FModelBehavior {

	public function onAfterGetItem(&$model, &$record) {		
		$app = JFactory::getApplication();
		J2Store::plugin()->importCatalogPlugins();
		$app->triggerEvent('onJ2StoreAfterGetProduct', array(&$record));
	}

}
