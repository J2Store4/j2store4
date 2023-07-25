<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2StoreModelCartsBehaviorCartVariable extends F0FModelBehavior {


	public function onBeforeAddCartItem(&$model, $product, &$json) {

		$app = JFactory::getApplication();
		$values = $app->input->getArray($_REQUEST);
		$errors = array();

		//run quantity check
			$quantity = $app->input->get('product_qty');
		if (isset($quantity )) {
			$quantity = $quantity;
		} else {
			$quantity = 1;
		}

		//get options
		//get the product options
		$options = $app->input->get('product_option', array(0), 'ARRAY');
		if (isset($options )) {
			$options =  array_filter($options );
		} else {
			$options = array();
		}

		//iterate through stored options for this product and validate
		foreach($product->product_options as $product_option) {
			if (empty($options[$product_option->j2store_productoption_id])) {
				$errors['error']['option'][$product_option->j2store_productoption_id] = JText::sprintf('J2STORE_ADDTOCART_PRODUCT_OPTION_REQUIRED', JText::_($product_option->option_name));
			}
		}

		if(!$errors) {

			$productHelper = J2Store::product();

			//get variant by product options. This is not an effective method. Adds a load on the system
//			

			//js based method implemented.
			$variant_id = $app->input->getInt('variant_id', 0);
			if($variant_id) {
				$variant = F0FModel::getTmpInstance('Variants', 'J2StoreModel')->getItem($variant_id);
				if($variant->j2store_variant_id != $variant_id || $variant->product_id != $product->j2store_product_id) {
					$errors['error']['general'] = JText::_('J2STORE_VARIANT_NOT_FOUND');
				}

				//double check if the chosen variant is correct. We cannot trust the javascript alone.
				$verify_variant = $productHelper->getVariantByOptions($options, $product->j2store_product_id);
				if($verify_variant->j2store_variant_id != $variant_id) {
					//somehow we got the wrong variant. Use the variant by options because that is always correct.
					$variant = $verify_variant;
				}
				if($variant === false) {
					$errors['error']['general'] = JText::_('J2STORE_VARIANT_NOT_FOUND');
				}
				
			} else {
				//variant id not found. fall back
				$variant = $productHelper->getVariantByOptions($options, $product->j2store_product_id);				
			}
			
			$cart = $model->getCart();
			if(!$errors && $cart->cart_type != 'wishlist') {
				//before validating, get the total quantity of this variant in the cart
				$cart_total_qty = $productHelper->getTotalCartQuantity($variant->j2store_variant_id);
	
				//validate minimum / maximum quantity
				 $error = $productHelper->validateQuantityRestriction($variant, $cart_total_qty, $quantity);
				 if(!empty($error)) {
				 	$errors['error']['stock'] = $error;
				 }
	
				//validate inventory
				if($productHelper->check_stock_status($variant, $cart_total_qty+$quantity) === false ) {
					if ( $variant->quantity > 0 ) {
						$errors['error']['stock'] = JText::sprintf ( 'J2STORE_LOW_STOCK_WITH_QUANTITY', $variant->quantity ); 
					}else{
						$errors['error']['stock'] = JText::_('J2STORE_OUT_OF_STOCK'); 
					}
				}
			}	

		}

		if(!$errors) {
			//all good. Add the product to cart

			// create cart object out of item properties
			$item = new JObject;
			$item->user_id     = JFactory::getUser()->id;
			$item->product_id  = (int) $product->j2store_product_id;
			$item->variant_id  = (int) $variant->j2store_variant_id;
			$item->product_qty = J2Store::utilities()->stock_qty($quantity);
			$item->product_type = $product->product_type;
			$item->product_options = base64_encode(serialize($options));
			$item->vendor_id   = isset($product->vendor_id) ? $product->vendor_id : '0';

			// onAfterCreateItemForAddToCart: plugin can add values to the item before it is being validated /added
			// once the extra field(s) have been set, they will get automatically saved

			$results = J2Store::plugin()->event("AfterCreateItemForAddToCart", array( $item, $values ) );
			foreach ($results as $result)
			{
				foreach($result as $key=>$value)
				{
					$item->set($key,$value);
				}
			}

			// no matter what, fire this validation plugin event for plugins that extend the checkout workflow
			$results = array();
			$results =  J2Store::plugin()->event("BeforeAddToCart", array( $item, $values, $product, $product->product_options) );
			foreach($results as $result) {
				if (! empty ( $result['error'] )) {
					$errors['error']['general'] = $result['error'];
				}
			}

			// when there is some error from the plugin then the cart item should not be added
			if(!$errors){
				//add item to cart
				$cartTable = $model->addItem($item);

				if($cartTable === false) {
					//adding to cart is failed
					$errors['success'] = 0;
				} else {
					//adding cart is successful
					$errors['success'] = 1;
					$errors['cart_id'] = $cartTable->j2store_cart_id;
				}
			}
		}

		$json->result = $errors;

	}

	
	public function onGetCartItems(&$model, &$item) {

		//sanity check
		if($item->product_type != 'variable') return;
		
		$product_helper = J2Store::product();
        $platform = J2Store::platform();

		// Options
		if (isset($item->product_options)) {
			$options = unserialize(base64_decode($item->product_options));
		} else {
			$options = array();
		}

		$option_price = 0;
		$option_weight = 0;
		$option_data = array();

		//get the variant by options
		$variant = F0FModel::getTmpInstance('Variants', 'J2StoreModel')->getItem($item->variant_id);

		foreach ($options as $product_option_id => $option_value) {

			$product_option = $product_helper->getCartProductOptions($product_option_id, $item->product_id);

			if ($product_option) {
				if ($product_option->type == 'select' || $product_option->type == 'radio') {

					//ok now get product option values
					$product_option_value = $product_helper->getCartProductOptionValues($product_option_id, $option_value);

					if ($product_option_value) {

						//option price
						if ($product_option_value->product_optionvalue_prefix == '+') {
							$option_price += $product_option_value->product_optionvalue_price;
						} elseif ($product_option_value->product_optionvalue_prefix == '-') {
							$option_price -= $product_option_value->product_optionvalue_price;
						}

						//options weight
						if ($product_option_value->product_optionvalue_weight_prefix == '+') {
							$option_weight += $product_option_value->product_optionvalue_weight;
						} elseif ($product_option_value->product_optionvalue_weight_prefix == '-') {
							$option_weight -= $product_option_value->product_optionvalue_weight;
						}


						$option_data[] = array(
								'product_option_id'       => $product_option_id,
								'product_optionvalue_id' => $option_value,
								'option_id'               => $product_option->option_id,
								'optionvalue_id'         => $product_option_value->optionvalue_id,
								'name'                    => $product_option->option_name,
								'option_value'            => $product_option_value->optionvalue_name,
								'type'                    => $product_option->type,
								'price'                   => $product_option_value->product_optionvalue_price,
								'price_prefix'            => $product_option_value->product_optionvalue_prefix,
								'weight'                   => $product_option_value->product_optionvalue_weight,
								'option_sku'               => $product_option_value->product_optionvalue_sku,
								'weight_prefix'            => $product_option_value->product_optionvalue_weight_prefix
						);
					}
				}
			}
		} // option loop

		$product = $product_helper->setId($item->product_id)->getProduct();
		$param_data = $platform->getRegistry($variant->params);
		$main_image = $param_data->get('variant_main_image','');
		$is_main_as_thum = $param_data->get('is_main_as_thum',0);
		$item->main_image = isset( $main_image ) && !empty( $main_image ) ? $main_image: '';
		if($is_main_as_thum){
			$item->thumb_image = isset( $main_image ) && !empty( $main_image ) ? $main_image: '';
		}

		$item->product_name = $product->product_name;
		$item->product_view_url = $product->product_view_url;
		$item->options = $option_data;
		$item->option_price = 0;
		$item->weight = $variant->weight;
		$item->weight_total = ($variant->weight) * $item->product_qty;
		$group_id = '';
		if(isset($item->group_id) && !empty($item->group_id)){
			$group_id = $item->group_id;
		}
		$item->pricing = $product_helper->getPrice($item, $item->product_qty,$group_id);
	}

	public function onValidateCart(&$model, $cartitem, $quantity) {

		//sanity check
		if($cartitem->product_type != 'variable') return;
		
		$productHelper = J2Store::product();
		$errors = array();

		$variant = F0FModel::getTmpInstance('Variants', 'J2StoreModel')->getItem($cartitem->variant_id);

		//before validating, get the total quantity of this variant in the cart
		$cart_total_qty  = $productHelper->getTotalCartQuantity($variant->j2store_variant_id);

		//get the quantity difference. Because we are going to check the total quantity
		$difference_qty = $quantity - $cartitem->product_qty;

		//validate minimum / maximum quantity
		$error = $productHelper->validateQuantityRestriction($variant, $cart_total_qty, $difference_qty);
		if(!empty($error)) {
			$errors[] = $error;
		}

		//validate inventory
		if($productHelper->check_stock_status($variant, ($cart_total_qty+$difference_qty)) === false) {
				$errors[] = JText::_('J2STORE_OUT_OF_STOCK');
		}

		if(count($errors)) {
			throw new Exception(implode('/n', $errors));
			return false;
		}
		return true;
	}
}

