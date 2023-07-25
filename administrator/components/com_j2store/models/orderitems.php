<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();


class J2StoreModelOrderItems extends F0FModel {

	private $_items = array ();

	public function setItems($cartitems) {
		$product_helper = J2Store::product ();		
		$productitems = array ();
		foreach ( $cartitems as $cartitem ) {

			if ($cartitem->product_qty == 0) {
				F0FModel::getTmpInstance('Cartitems', 'J2StoreModel')->setIds(array($cartitem->j2store_cartitem_id))->delete();
				continue;
			}

			if ($product_helper->managing_stock ( $cartitem ) && $product_helper->backorders_allowed ( $cartitem ) === false) {
				
				//this could be wrong. we are not checking the total quantity for product types other than variant type	
				/* if ($cartitem->product_qty > $cartitem->available_quantity && $cartitem->available_quantity >= 1) {
					JFactory::getApplication ()->enqueueMessage ( JText::sprintf ( "J2STORE_CART_QUANTITY_ADJUSTED", $cartitem->product_name, $cartitem->product_qty, $cartitem->available_quantity ) );
					$cartitem->product_qty = $cartitem->available_quantity;
				} */
			
				// removing the product from the cart if it's not available
				$stock_status = ($cartitem->available_quantity == 0) ? true :false;
				J2Store::plugin ()->event('ValidateStockOnSetOrderItems', array(&$stock_status, $cartitem));
				if ($stock_status) {
					F0FModel::getTmpInstance('Cartitems', 'J2StoreModel')->setIds(array($cartitem->j2store_cartitem_id))->delete();
					continue;
				}
			}
			unset($orderItem);
			// TODO Push this into the orders object->addItem() method?
			$orderItem = $this->getTable()->getClone();
			$orderItem->cart_id = $cartitem->cart_id;
			$orderItem->cartitem_id = $cartitem->j2store_cartitem_id;
			$orderItem->product_id = $cartitem->product_id;
			$orderItem->product_source = $cartitem->product_source;
			$orderItem->product_source_id = $cartitem->product_source_id;
			$orderItem->product_type = $cartitem->product_type;
			$orderItem->product_params = $cartitem->product_params;
			$orderItem->variant_id = $cartitem->variant_id;
			$orderItem->orderitem_sku = $cartitem->sku;
			$orderItem->vendor_id = $cartitem->vendor_id;
			$orderItem->orderitem_name = $cartitem->product_name;
			$orderItem->orderitem_quantity = J2Store::utilities()->stock_qty($cartitem->product_qty);
			
			//set the entire cartitem. We can use it later
			$orderItem->cartitem = $cartitem;

			// price which is not processed
			$orderItem->orderitem_price = $cartitem->pricing->price;
			$orderItem->orderitem_baseprice = $cartitem->pricing->base_price;
			$orderItem->orderitem_option_price = $cartitem->option_price;

		// the following four includes the option prices as well
		//	$orderItem->orderitem_price_with_tax = $cartitem->pricing->price_with_tax;
		//	$orderItem->orderitem_price_without_tax = $cartitem->pricing->price_without_tax;

		//	$orderItem->orderitem_baseprice_with_tax = $cartitem->pricing->base_price_with_tax;
		//	$orderItem->orderitem_baseprice_without_tax = $cartitem->pricing->base_price_without_tax;

			$orderItem->orderitem_taxprofile_id = $cartitem->taxprofile_id;

			$orderItem->orderitem_weight = $cartitem->weight;
			$orderItem->orderitem_weight_total = $cartitem->weight_total;

			//just a placeholder and also used as reference for product options
			$orderItem->orderitem_attributes = $cartitem->product_options;
			$orderItem->orderitem_raw_attributes = $cartitem->product_options;

			//prepare options
			$this->getOrderItemOptions($orderItem, $cartitem);

			//prepare orderitem_params and add some data that might be useful
			$this->getOrderItemParams($orderItem, $cartitem);
		
			JPluginHelper::importPlugin ( 'j2store' );
			$results = JFactory::getApplication ()->triggerEvent ( "onJ2StoreAfterAddCartItemToOrder", array (
					$cartitem
			) );
			foreach ( $results as $result ) {
				foreach ( $result as $key => $value ) {
					$orderItem->set ( $key, $value );
				}
			}
			J2Store::plugin()->event('AfterAddOrderItem', array(&$orderItem));
			// TODO When do attributes for selected item get set during admin-side order creation?
			array_push ( $this->_items, $orderItem );

		}
		return $this;
	}

	public function getOrderItemParams(&$orderItem, $cartitem) {

		$array = array();
        $thumb_image = isset($cartitem->thumb_image) ? $cartitem->thumb_image : '';
		if(isset($cartitem->product_type) && in_array($cartitem->product_type,array('variable'))){
            if(!empty($cartitem->main_image) && !empty($cartitem->main_image)){
                $thumb_image = $cartitem->main_image;
            }
        }
		$array['thumb_image'] = $thumb_image;
		$array['shipping'] = $cartitem->shipping;
		$product_helper = J2Store::product();
		
        if($product_helper->managing_stock($cartitem) && $product_helper->backorders_allowed($cartitem) &&
            isset($cartitem->available_quantity) && isset($cartitem->product_qty) && $cartitem->product_qty > $cartitem->available_quantity){
            $array['back_order_item'] = 'J2STORE_BACK_ORDER_ITEM';
        }

		$registry = J2Store::platform()->getRegistry($array,true);
		$orderItem->orderitem_params = $registry->toString('JSON');

	}

	public function getOrderItemOptions(&$orderItem, $cartitem) {

		if(isset($cartitem->options) && is_array($cartitem->options)) {

			$orderitemattributes = array();
            $utility = J2Store::utilities();
			foreach ($cartitem->options as $option) {
				unset($orderitemattribute);
				$orderitemattribute = F0FTable::getAnInstance('OrderItemAttribute', 'J2StoreTable')->getClone();

            //this is the product option id
                $orderitemattribute->productattributeoption_id =  isset($option['product_option_id']) && !empty($option['product_option_id']) ? $option['product_option_id'] : 0 ;
                $orderitemattribute->productattributeoptionvalue_id =  isset($option['product_optionvalue_id']) && !empty($option['product_optionvalue_id']) ? $option['product_optionvalue_id'] : 0 ;


            //product option name. Dont confuse this with the option value name

                $orderitemattribute->orderitemattribute_name =  isset($option['name']) && !empty($option['name']) ? $option['name'] : '' ;
                $orderitemattribute->orderitemattribute_value = $utility->text_sanitize($option['option_value']);


                $orderitemattribute->orderitemattribute_price = isset($option['price']) && !empty($option['price']) ? $option['price'] : 0 ;

                $orderitemattribute->orderitemattribute_prefix = isset($option['price_prefix']) && !empty($option['price_prefix']) ? $option['price_prefix'] : '' ;
                $orderitemattribute->orderitemattribute_type = isset($option['type']) && !empty($option['type']) ? $option['type'] : '' ;
                $orderitemattribute->orderitemattribute_code = isset($option['option_sku']) ? $option['option_sku'] : '';
                $orderitemattributes[] = $orderitemattribute;
            }

			$orderItem->orderitemattributes = $orderitemattributes;
		}


	}

	public function getItems() {
		//var_dump($this->_items);
		return $this->_items;
	}

	protected function onProcessList(&$resultArray) {
		foreach($resultArray as &$result) {
			$result->orderitemattributes = F0FModel::getTmpInstance('OrderitemAttributes', 'J2StoreModel')->orderitem_id($result->j2store_orderitem_id)->getItemList();
		}
	}

	public function getItemsByOrder($order_id) {
		if(empty($order_id)) return array();
		
		$query = $this->_db->getQuery(true);
		$query->select('*')->from('#__j2store_orderitems')->where('order_id = '.$this->_db->q($order_id));
		$this->_db->setQuery($query);
		return  $this->_db->loadObjectList();
	}
	
}