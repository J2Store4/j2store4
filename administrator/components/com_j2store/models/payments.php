<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreModelPayments extends F0FModel {

	/**
	 * Method to buildQuery to return list of data
	 * @see F0FModel::buildQuery()
	 * @return query
	 */
	public function buildQuery($overrideLimits = false) {

		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$this->getSelectQuery($query);
		$this->getWhereQuery($query);
		$this->buildOrderbyQuery($query);
		return $query;
	}

	/**
	 * Method to getSelect query
	 * @param unknown_type $query
	 */
	protected function getSelectQuery(&$query)
	{
		$query->select("payment.extension_id,payment.name,payment.type,payment.folder,payment.element,payment.params,payment.enabled,payment.ordering,payment.manifest_cache")
		->from("#__extensions as payment");
	}

	protected function getWhereQuery(&$query)
	{
		$query->where("payment.type='plugin'");
		$query->where("payment.element LIKE 'payment_%'");
		$query->where("payment.folder='j2store'");

		$name = $this->getState('name');
		if(isset($name) && !empty($name)) {
			$query->where('payment.name LIKE '. $this->_db->q('%'.$name.'%') );
		}
		
	}

	public function buildOrderbyQuery(&$query){
		$state = $this->getState();
		$app = JFactory::getApplication();
		$filter_order_Dir = $app->input->getString('filter_order_Dir','asc');
        if(!in_array(strtolower($filter_order_Dir),array('asc','desc'))){
            $filter_order_Dir = 'desc';
        }
		$filter_order = $app->input->getString('filter_order','extension_id');
		if($filter_order != 'version' && $filter_order != 'id' && !empty($filter_order)){
			if(in_array ( $filter_order, array('name','version','enabled') )){
				$query->order('payment.'.$filter_order.' '.$filter_order_Dir);
			}

		}

	}
	protected function onProcessList(&$resultArray){
		foreach($resultArray as &$res){
			$res->view = JText::_('J2STORE_VIEW');
            $manifest_cache = json_decode($res->manifest_cache);
            $res->version = isset($manifest_cache->version) && !empty($manifest_cache->version) ? $manifest_cache->version: '1.0';
		}
	}

}
