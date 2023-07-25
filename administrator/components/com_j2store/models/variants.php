<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/

// No direct access to this file
defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/models/behavior/autoload.php';

class J2StoreModelVariants extends F0FModel {

	protected $default_behaviors = array('filters');

	function __construct($config = array()) {

		parent::__construct($config);
	}

	public function buildQuery($overrideLimits = false) {

		$query = parent::buildQuery($overrideLimits);

		$query->select($this->_db->qn('#__j2store_productquantities').'.j2store_productquantity_id ')
			 ->select($this->_db->qn('#__j2store_productquantities').'.quantity')
		->join('LEFT OUTER','#__j2store_productquantities ON #__j2store_productquantities.variant_id = #__j2store_variants.j2store_variant_id');

		// get the weight class
		$query->select ( $this->_db->qn ( '#__j2store_weights' ) . '.weight_title' )
				->select ( $this->_db->qn ( '#__j2store_weights' ) . '.weight_unit' )
				->join ( 'LEFT OUTER', '#__j2store_weights ON #__j2store_weights.j2store_weight_id = #__j2store_variants.weight_class_id' );

		// get the length class
		$query->select ( $this->_db->qn ( '#__j2store_lengths' ) . '.length_title' )
				->select ( $this->_db->qn ( '#__j2store_lengths' ) . '.length_unit' )
				->join ( 'LEFT OUTER', '#__j2store_lengths ON #__j2store_lengths.j2store_length_id = #__j2store_variants.length_class_id' );

		//the following joins run only when the product type is Variable
		$product_type = $this->getState('product_type');
		if($product_type == 'variable' || $product_type == 'advancedvariable' || $product_type == 'variablesubscriptionproduct' || $product_type == 'flexivariable') {
			$query->select('#__j2store_product_variant_optionvalues.product_optionvalue_ids AS variant_name')
			->join('INNER', '#__j2store_product_variant_optionvalues ON #__j2store_product_variant_optionvalues.variant_id = #__j2store_variants.j2store_variant_id');
		}
		return $query;

	}


	public function getTableFields()
	{
		static $sets;

		if ( !is_array( $sets) )
		{
			$sets= array( );
		}

		$tableName = $this->getTable()->getTableName();
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


	/**
	 * Method to return rows in  associative array of table given
	 * @param string $table_name
	 * @param string $column
	 * @param string $key
	 */
	public function getDimesions($table_name,$column ,$key){
		$db= JFactory::getDbo();
		$table = '#__j2store_'.$table_name;
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($table);
		$db->setQuery($query);
		$results =array();
		$results[] = JText::_('J2STORE_SELECT_OPTION');
		$results = $db->loadAssocList($column,$key);
		return $results;
	}

}