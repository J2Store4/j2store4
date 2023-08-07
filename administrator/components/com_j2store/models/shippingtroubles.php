<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2StoreModelShippingtroubles extends F0FModel {
	/**
	 * Method to buildQuery to return list of data
	 * @see F0FModel::buildQuery()
	 * @return query
	 */
	public function buildQuery($overrideLimits = false) {
	
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('#__j2store_products.*')->from('#__j2store_products');
		$this->_buildQueryJoin($query);
        $this->_buildQueryGroup($query);
		$this->_buildQueryWhere($query);
        if(!empty($this->state->filter_order) && in_array($this->state->filter_order,array('j2store_product_id','created_on'))) {
            if(!in_array(strtolower($this->state->filter_order_Dir),array('asc','desc'))){
                $this->state->filter_order_Dir = 'desc';
            }
            $query->order($db->qn('#__j2store_products').'.'.$db->qn($this->state->filter_order).' '.$this->state->filter_order_Dir);
			//$query->order('#__j2store_products.'.$this->state->filter_order.' '.$this->state->filter_order_Dir);
		}else{
			$query->order('#__j2store_products.created_on DESC');
		}
        $shipping_model = $this;
		J2Store::plugin()->event('AfterShippingTroubleListQuery', array(&$query, &$shipping_model));
		return $query;
	}
	
	function _buildQueryJoin($query){
		$query->select('#__j2store_variants.sku,#__j2store_variants.price,#__j2store_variants.shipping,#__j2store_variants.length,#__j2store_variants.width,#__j2store_variants.height,#__j2store_variants.length_class_id,#__j2store_variants.weight_class_id,#__j2store_variants.weight');
		$query->join('INNER','#__j2store_variants ON #__j2store_products.j2store_product_id = #__j2store_variants.product_id');
    }
    function _buildQueryGroup($query){
        $query->group('#__j2store_products.j2store_product_id');
    }
	/**
	 * Method to get Filter Values for SFBuildWhereQuery
	 * @return StdClass
	 */
	private function getFilterValues()
	{
		return (object)array(
				'search' 			=>	$this->getState('search',null,'string'),				
		);
	}
	
	function _buildQueryWhere($query){
		$db = $this->_db;
		$state = $this->getFilterValues();
		$query->where('#__j2store_products.enabled='.$this->_db->q(1));
		$query->where('#__j2store_variants.is_master='.$this->_db->q(1).' AND (#__j2store_variants.shipping='.$this->_db->q(0).' OR #__j2store_variants.length='.$this->_db->q(0).' OR #__j2store_variants.width='.$this->_db->q(0).' OR
				#__j2store_variants.height='.$this->_db->q(0).' OR #__j2store_variants.weight='.$this->_db->q(0).' OR #__j2store_variants.length_class_id='.$this->_db->q(0).' OR #__j2store_variants.weight_class_id='.$this->_db->q(0).')');
		
		if($state->search){
			$query->where('('.
					$db->qn('#__j2store_products').'.'.$db->qn('j2store_product_id').' LIKE '.$db->q('%'.$state->search.'%').'OR '.
					$db->qn('#__j2store_products').'.'.$db->qn('product_source').' LIKE '.$db->q('%'.$state->search.'%').'OR '.
					$db->qn('#__j2store_variants').'.'.$db->qn('sku').' LIKE '.$db->q('%'.$state->search.'%').'OR '.
					$db->qn('#__j2store_variants').'.'.$db->qn('price').' LIKE '.$db->q('%'.$state->search.'%').'OR '.
					$db->qn('#__j2store_products').'.'.$db->qn('product_type').' LIKE '.$db->q('%'.$state->search.'%').')'
					) ;
		}
	}

	function getShippingMethods(){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select("shipping.extension_id,shipping.name,shipping.type,shipping.folder,shipping.element,shipping.params,shipping.enabled,shipping.ordering,shipping.manifest_cache")
		->from("#__extensions as shipping");
		$query->where("shipping.type=".$db->q('plugin'));
		$query->where("shipping.element LIKE 'shipping_%'");
		$query->where("shipping.folder=".$db->q('j2store'));
		$query->where("shipping.enabled=".$db->q(1));
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	function getShippingDetails(){		
		$shipping_data = $this->getShippingMethods();		
		if(count($shipping_data)){
			return true;
		}
		return false;		
	}

	function getShippingValidate(){
		$shipping_versions = array(
			"shipping_usps" => "1.9.0",
			"shipping_dhl" => "1.2",
			"shipping_fedex" => "3.0",
			"shipping_ups" => "1.12",			
			"shipping_canadapost" => "1.0",
			"shipping_postcode" => "1.10",
			"shipping_bring" => "1.2"			
		);
		$shipping_methods = $this->getShippingMethods();
        $shipping_message = array();
		J2Store::plugin()->event('ShippingParamsValidate',array(&$shipping_message));
        $platform = J2Store::platform();
		foreach ($shipping_methods as $shipping_method){			
			$manifest = $platform->getRegistry($shipping_method->manifest_cache);
			$version =  $manifest->get('version',0);
			if(isset($shipping_versions[$shipping_method->element]) && version_compare($shipping_versions[$shipping_method->element],$version,'>=')){
				$message = JText::sprintf('J2STORE_SHIPPING_NEED_TO_BE_UPDATE',$shipping_method->name);
				$shipping_message[] = array(
					$shipping_method->name => 	$message
				);
			}
		}		
		
		return $shipping_message;
	}
}