<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreModelConfigurations extends F0FModel {

	public function &getItemList($overrideLimits = false, $group = '')
	{
		$query = $this->_db->getQuery(true)->select('*')->from('#__j2store_configurations');
		$this->_db->setQuery($query);
		$items = $this->_db->loadObjectList('config_meta_key');
		return $items; 
	} 
	
 	public function onBeforeLoadForm(&$name, &$source, &$options) {
		$app = JFactory::getApplication();
		$data1 = $this->_formData;
		$data = $this->getItemList();
		
		$params = array();
		foreach($data as $namekey=>$singleton) {
			if ($namekey == 'limit_orderstatuses') {
				$params[$namekey] = explode(',', $singleton->config_meta_value);
			}else {
				$params[$namekey] = $singleton->config_meta_value;
			}
		}
		$this->_formData = $params;	
	}


}