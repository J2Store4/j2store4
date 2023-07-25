<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
defined ( '_JEXEC' ) or die ();
class J2StoreModelProductOptionvalues extends F0FModel {


	public function buildQuery($overrideLimits = false) {

		$query = parent::buildQuery($overrideLimits);
	/* 	$query = $this->_db->getQuery(true);
		$query->select($this->_db->qn('#__j2store_product_optionvalues').'.*')->from($this->_db->qn('#__j2store_product_optionvalues').' AS '.$this->_db->qn('#__j2store_product_optionvalues')); */
		$query->select('#__j2store_optionvalues.optionvalue_name, #__j2store_optionvalues.optionvalue_image')
			->join('LEFT OUTER','#__j2store_optionvalues ON #__j2store_optionvalues.j2store_optionvalue_id = #__j2store_product_optionvalues.optionvalue_id');
		$query->clear('order');
		$query->order('#__j2store_product_optionvalues.ordering ASC');
		return $query;
	}
	
	public function getTableFields()
	{
		$tableName = $this->getTable()->getTableName();
		static $sets;
		
		if ( !is_array( $sets) )
		{
			$sets= array( );
		}
		
		if(!isset($sets[$tableName])) {
		
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				$sets[$tableName] = $this->getDbo()->getTableColumns($tableName, true);
			}
			else
			{
				$fieldsArray = $this->getDbo()->getTableFields($tableName, true);
				$sets[$tableName] = array_shift($fieldsArray);
			}
		}
		return $sets[$tableName];
	}
	
}