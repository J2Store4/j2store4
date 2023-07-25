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

// No direct access
defined('_JEXEC') or die;

class J2StoreTableVariant extends F0FTable
{
	public function __construct($table, $key, &$db, $config=array())
	{
		$query = $db->getQuery(true)
			->select($db->qn('#__j2store_productquantities').'.j2store_productquantity_id ')
			->select($db->qn('#__j2store_productquantities').'.quantity')
			->join('LEFT OUTER', '#__j2store_productquantities ON #__j2store_productquantities.variant_id = #__j2store_variants.j2store_variant_id')
		
			->select($db->qn('#__j2store_lengths').'.length_title')
			->select($db->qn('#__j2store_lengths').'.length_unit')
			->select($db->qn('#__j2store_lengths').'.length_value')
			->join('LEFT OUTER', '#__j2store_lengths ON #__j2store_lengths.j2store_length_id = #__j2store_variants.length_class_id')
		
			->select($db->qn('#__j2store_weights').'.weight_title')
			->select($db->qn('#__j2store_weights').'.weight_unit')
			->select($db->qn('#__j2store_weights').'.weight_value')
			->join('LEFT OUTER', '#__j2store_weights ON #__j2store_weights.j2store_weight_id = #__j2store_variants.weight_class_id');
		
		$this->setQueryJoin($query);
		parent::__construct($table, $key, $db, $config);
	}

	public function reduce_stock($qty = 1) {
		return $this->set_stock( $qty, 'subtract' );
	}

	public function increase_stock($qty = 1) {
		return $this->set_stock( $qty, 'add' );
	}

	public function set_stock($qty, $mode='set') {

		if ( ! is_null( $qty ) && J2Store::product()->managing_stock($this) ) {

			$productquantity = F0FTable::getAnInstance('ProductQuantity', 'J2StoreTable')->getClone();
			$productquantity->load(array('variant_id' => $this->j2store_variant_id));

			// Update stock in DB directly
			switch ( $mode ) {
				case 'add' :
					$productquantity->quantity = $productquantity->quantity + $qty;
					break;
				case 'subtract' :
					if($productquantity->quantity > 0 && $productquantity->quantity >= $qty ) {
						$productquantity->quantity = $productquantity->quantity - $qty;
					} else {
						$productquantity->quantity = 0;
					}

					break;
				default :
					$productquantity->quantity = $qty;
					break;
			}

			$productquantity->store();
			$this->quantity = $productquantity->quantity;
			
			//set the availability to true. Otherwise validateStock method will return true or false. 
			//This will not harm any other process because the following check is itself for seting the very same availability.
			$this->availability = 1;
			if(!J2Store::product()->check_stock_status($this, 1)) {
				$this->set_stock_status(0);
			}else{
				$this->set_stock_status(1);
			}
		}
		return $this->quantity;
	}

	public function set_stock_status($status) {

		$product_helper = J2Store::product();
		$status = ( '0' == $status ) ? '0' : '1';

		// Sanity check
		if ( $product_helper->managing_stock($this) ) {

			$product_helper->getQuantityRestriction($this);
			if ( ! $product_helper->backorders_allowed($this) && $this->quantity < $this->min_sale_qty) {
				$status = '0';
			}
		}

		$this->availability = $status;
		$this->store();
	}
	
	protected function onBeforeStore($updateNulls = false) {		
		if(!isset($this->sku) || empty($this->sku)) {
			//sku is empty. Auto generate it based on product name
			$product_helper = J2Store::product();
			$this->sku = $product_helper->generateSKU($this);
		}
		
		return parent::onBeforeStore($updateNulls);
	}

	protected function onBeforeDelete($id) {
		$db = JFactory::getDbo();
		//delete all related records
		try {
				//inventory
			$query = $db->getQuery(true)->delete('#__j2store_productquantities')->where($db->qn('variant_id').' = '.$db->q($id));
			$db->setQuery($query);
			try {
				$db->execute();
			}catch (Exception $e) {
				$this->setError($e->getMessage);
				return false;
			}
				
			/* $productQuantity = F0FTable::getInstance('ProductQuantity', 'J2StoreTable')->load(array('variant_id'=>$id));
			if(isset($productQuantity->j2store_productquantity_id)) {
				F0FModel::getTmpInstance('ProductQuantities', 'J2StoreModel')->setId($productQuantity->j2store_productquantity_id)->delete();
			}
 			*/
			//prices
			$productPrices = F0FModel::getTmpInstance('ProductPrices', 'J2StoreModel')->limit(0)->limitstart(0)->variant_id($id)->getItemList();
			foreach ($productPrices as $price) {
				if($price->variant_id == $id) {
					F0FTable::getAnInstance('ProductPrice', 'J2StoreTable')->delete($price->j2store_productprice_id);					
				}
			}

			//variant product option values
			
			$query = $db->getQuery(true)->delete('#__j2store_product_variant_optionvalues')->where($db->qn('variant_id').' = '.$db->q($id));
			$db->setQuery($query);
			try {
				$db->execute();
			}catch (Exception $e) {
				$this->setError($e->getMessage);
				return false;
			}

		}catch (Exception $e) {
			$this->setError($e->getMessage);
			return false;
		}
		return true;
	}

}