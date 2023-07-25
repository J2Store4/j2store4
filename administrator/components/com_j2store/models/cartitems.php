<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/models/behavior/autoload.php';

class J2StoreModelCartitems extends F0FModel {

	protected $cart_id = '';
	
	protected function _buildQueryWhere(&$query)
	{
		$filter_cart   = $this->getState('filter_cart');
		$filter_product   = $this->getState('filter_product');
		$filter_variant   = $this->getState('filter_variant');
		$filter_name	= $this->getState('filter_name');
		
		if (!empty($filter_cart))
		{
			$query->where('tbl.cart_id= '.$this->_db->q((int) $filter_cart));
		}
		
		if (!empty($filter_product))
		{
			$query->where('tbl.product_id = '.$this->_db->q((int) $filter_product));
		}
	
		if (!empty($filter_variant))
		{
			$query->where('tbl.variant_id = '.$this->_db->q((int) $filter_variant));
		}
		if ( !empty ($filter_name) && strlen($filter_name) )
		{
			$key	= $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_name ) ) ).'%');
			$query->where('LOWER(product.product_name) LIKE '.$key);
		}

		//Do we really need the following checks?
		$query->where('product.enabled =1');
		$query->where('product.visibility=1');
	
	}
	
	protected function _buildQueryFields(&$query)
	{
		$field = array();
	
		$field[] = " variant.j2store_variant_id";
		$field[] = " variant.sku";
		$field[] = " variant.price";
		$field[] = " variant.pricing_calculator";
		$field[] = " variant.shipping";
		$field[] = " variant.weight";
		$field[] = " variant.length";
		$field[] = " variant.width";
		$field[] = " variant.height";
		$field[] = " variant.length_class_id";
		$field[] = " variant.weight_class_id";
		$field[] = " variant.quantity_restriction ";
		$field[] = " variant.min_sale_qty";
		$field[] = " variant.max_sale_qty";
		$field[] = " variant.use_store_config_min_sale_qty";
		$field[] = " variant.use_store_config_max_sale_qty";
		$field[] = " variant.use_store_config_notify_qty";
		$field[] = " variant.manage_stock";
		$field[] = " variant.allow_backorder";
	
		$field[] = " product.product_type";
		$field[] = " product.product_source";
		$field[] = " product.product_source_id";
		$field[] = " product.has_options";
		$field[] = " product.up_sells";
		$field[] = " product.cross_sells";
		$field[] = " product.taxprofile_id";
		$field[] = " product.vendor_id";
		$field[] = " product.manufacturer_id";
		$field[] = " product.params as product_params";
	
		$field[] = " product.visibility";
	
		$field[] = " productimage.thumb_image";
		$field[] = " stock.on_hold as quantity_on_hold";
		$field[] = " stock.quantity as available_quantity";
	
		$query->select('tbl.*');
		$query->select( $field );
	}
	
	protected function _buildQueryJoins(&$query)
	{
		$query->join('INNER', '#__j2store_variants AS variant ON tbl.variant_id = variant.j2store_variant_id');
		$query->join('INNER', '#__j2store_products AS product ON tbl.product_id = product.j2store_product_id');
		$query->join('LEFT OUTER', '#__j2store_productimages AS productimage ON product.j2store_product_id = productimage.product_id');
		$query->join('LEFT OUTER', '#__j2store_productquantities AS stock ON variant.j2store_variant_id = stock.variant_id');
	}
	
	public function buildQuery($overrideLimits=false) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)->from('#__j2store_cartitems as tbl');
		$this->_buildQueryFields($query);
		$this->_buildQueryJoins($query);
		$this->_buildQueryWhere($query);
		J2Store::plugin()->event('CartItemsAfterBuildQuery', array(&$query, $overrideLimits));
		return $query;
	}
	
	public function onProcessList(&$cartitems) {
		foreach ($cartitems as &$cartitem) {
			$this->validate_item($cartitem);	
		}
	}
	 
	
	function validate_item(&$cartitem) {
		$product_helper = J2Store::product();
		if(!$product_helper->setId($cartitem->product_id)->exists()) {
			$this->setIds(array($cartitem->j2store_cartitem_id))->delete();
			JFactory::getApplication()->enqueueMessage(JText::_('J2STORE_CART_ITEM_UNAVAILABLE_REMOVED'), 'warning');
			unset($cartitem);
		}
		
	}
	
	
}