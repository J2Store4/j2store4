<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreModelProductsBehaviorVariable extends F0FModelBehavior {

	private $_rawData = array();

	public function onAfterGetItem(&$model, &$record) {
        $platform = J2Store::platform();
		//we just have the products. Get the variants
		$variantModel = F0FModel::getTmpInstance('Variants', 'J2StoreModel');
		$variantModel->setState('product_type', $record->product_type);


		$record->lengths =$variantModel->getDimensions('lengths', 'j2store_length_id','length_title');
		$record->weights = $variantModel->getDimensions('weights', 'j2store_weight_id','weight_title');

		try {
			//first load master variant

			$variant_table = F0FTable::getAnInstance('Variant', 'J2StoreTable');
			$variant_table->load(array('product_id'=>$record->j2store_product_id, 'is_master'=>1));
			$record->variant = $variant_table;
			$global_config = JFactory::getConfig();
			$limit = $global_config->get('list_limit',20);
			//now load variants
			/* 			$record->variants = $variantModel
                        ->product_id($record->j2store_product_id)
                        ->is_master(0)
                        ->getList();
             */
			//now load variants
			$record->variants = $variantModel
				->product_id($record->j2store_product_id)
				->limit($limit)
				->is_master(0)
				->getList();
			//TODO pagination to be set
			$record->variant_pagination = $variantModel->getPagination();

		}catch (Exception $e) {
			//there may not be a variant set.
			echo 'No variant set';
		}

		//lets load product options as well
		$record->product_options = F0FModel::getTmpInstance('ProductOptions', 'J2StoreModel')
			->product_id($record->j2store_product_id)
			->limit(0)
			->parent_id(null)
			->limitstart(0)
			->getList();

		$registry = $platform->getRegistry($record->params);
		$record->params = $registry;

	}

	public function onBeforeSave(&$model, &$data)
	{
		if(!isset($data['product_type']) || $data['product_type'] != 'variable') return;
		$utility_helper = J2Store::utilities();
        $platform = J2Store::platform();
        $app = $platform->application();
		if(!isset( $data['visibility'] )){
			$data['visibility'] = 1;
		}
		if(isset($data['cross_sells'])) {
			$data['cross_sells'] = $utility_helper->to_csv($data['cross_sells']);
		}else{
			$data['cross_sells'] ='';
		}
		if(isset($data['up_sells'])) {
			$data['up_sells'] = $utility_helper->to_csv($data['up_sells']);
		}else{
			$data['up_sells'] ='';
		}

		if(isset($data['shippingmethods']) && !empty($data['shippingmethods'])){
			$data['shippingmethods'] = implode(',',$data['shippingmethods']);
		}
        //check sku already exist
        foreach ($data['variable'] as $variable) {
            if(isset($variable->max_sale_qty) &&  !empty($variable->max_sale_qty) && isset($variable->min_sale_qty) && !empty($variable->min_sale_qty) && ($variable->max_sale_qty < $variable->min_sale_qty)){
                $variable->min_sale_qty= 0 ;
                $app->enqueueMessage(JText::_('J2STORE_MAX_SALE_QTY_NEED_TO_GRATER_THEN_MIN_SALE_QTY'),'warning');
            }
        }
        if(isset($data['item_options']) && is_object($data['item_options'])){
            $data['item_options'] = (array)$data['item_options'];
        }

        if(isset($data['item_options']) && count($data['item_options']) > 0){
            $data['has_options'] = 1;
        }

        $integer_array = array('taxprofile_id','manufacturer_id','vendor_id','isdefault_variant','length_class_id','weight_class_id');
        foreach ($integer_array as $key){
            if(isset($data[$key]) && !empty($data[$key])){
                $data[$key] = (int) $data[$key];
            }else{
                $data[$key] = 0;
            }
        }
        $float_array = array('price','length','width','height','weight','min_sale_qty','max_sale_qty','notify_qty');
        foreach ($float_array as $key){
            if(isset($data[$key]) && !empty($data[$key])){
                $data[$key] = (float) $data[$key];
            }else{
                $data[$key] = 0;
            }
        }

        if(isset($data['quantity']) && is_object($data['quantity']) && (!isset($data['quantity']->product_attributes) || empty($data['quantity']->product_attributes))){
            $data['quantity']->product_attributes = '';
        }
        $quantity_integer_array = array('quantity');
        foreach ($quantity_integer_array as $key){
            if(isset($data['quantity']) && is_object($data['quantity']) && isset($data['quantity']->$key) && !empty($data['quantity']->$key)){
                $data['quantity']->$key = (int) $data['quantity']->$key;
            }elseif(isset($data['quantity']) && is_object($data['quantity'])){
                $data['quantity']->$key = 0;
            }
        }

		//bind existing params
		if($data['j2store_product_id'] ){
			$product = F0FTable::getAnInstance('Product','J2StoreTable');
			$product->load($data['j2store_product_id']);
			if($product->params){
				$product->params  = json_decode($product->params);
				if(!isset($data['params']) || empty($data['params'])) {
					$data['params'] = $platform->getRegistry('{}');
				}else {
					$data['params'] = array_merge((array)$product->params,(array)$data['params']);
				}
				//$data['params'] = array_merge((array)$product->params,(array)$data['params']);
			}
		}

		if(isset($data['params']) && !empty($data['params'])){
			$data['params'] = json_encode($data['params']);
		}

		$this->_rawData = $data;
	}

	public function onAfterSave(&$model) {

		if($this->_rawData) {

			$table = $model->getTable();

			//save variant
			//since post has too much of information, this could do the job
			$variant = F0FTable::getInstance('Variant', 'J2StoreTable');
			$variant->bind($this->_rawData);
			//echo "<pre>";print_r($variant);exit;
			//by default it is treated as master product.
			$variant->is_master = 1;
			$variant->product_id = $table->j2store_product_id;
			$variant->store();

			//save product options
			if(isset($this->_rawData['item_options'])) {

				foreach($this->_rawData['item_options'] as $item){
					$poption = F0FTable::getInstance('Productoption', 'J2StoreTable')->getClone();
					$item->product_id = $table->j2store_product_id;
					try {
						$poption->save($item);
					}catch (Exception $e) {
						throw new Exception($e->getMessage());
					}
				}
			}

            $platform = J2Store::platform();
			//save variable values
			if(isset($this->_rawData['variable'])){
				foreach($this->_rawData['variable'] as $variant_key => $item){

					if(is_array($item)){
						$item = $platform->toObject($item);
					}
                    $integer_array = array('taxprofile_id','manufacturer_id','vendor_id','isdefault_variant','length_class_id','weight_class_id');
                    foreach ($integer_array as $key){
                        if(isset($item->$key) && !empty($item->$key) && $item->$key > 0){
                            $item->$key = (int) $item->$key;
                        }else{
                            $item->$key = 0;
                        }
                    }
                    $float_array = array('price','length','width','height','weight','min_sale_qty','max_sale_qty','notify_qty');
                    foreach ($float_array as $key){
                        if(isset($item->$key) && !empty($item->$key) && $item->$key > 0){
                            $item->$key = (float) $item->$key;
                        }else{
                            $item->$key = 0;
                        }
                    }
					if(isset($item->use_store_config_max_sale_qty) && $item->use_store_config_max_sale_qty =='on'){
						$item->use_store_config_max_sale_qty= 1;
					}else{
						$item->use_store_config_max_sale_qty= 0;
					}

					if(isset($item->use_store_config_min_sale_qty) && $item->use_store_config_min_sale_qty =='on' ){
						$item->use_store_config_min_sale_qty= 1;
					}else{
						$item->use_store_config_min_sale_qty= 0;
					}

					if(isset($item->use_store_config_notify_qty) && $item->use_store_config_notify_qty =='on'){
						$item->use_store_config_notify_qty= 1;
					}else{

						$item->use_store_config_notify_qty= 0;
					}

					if(isset($item->params)){
						$item->params = json_encode($item->params);
					}else{
						$item->params = '{}';
					}


					$variantChild = F0FTable::getInstance('Variant', 'J2StoreTable')->getClone();
					$variantChild->is_master = 0;
					$item->product_id = $table->j2store_product_id;
					$quantity_item = $item->quantity;

					$quantity_item->variant_id = $variant_key;
					$quantity = F0FTable::getAnInstance('Productquantity','J2StoreTable')->getClone();
					$quantity->load(array('variant_id'=>$variant_key));
					try {
						if($variantChild->save($item)){

							if(!$quantity->save($quantity_item)){
								$quantity->getError();
							}
						}
					}catch (Exception $e) {
						throw new Exception($e->getMessage());
					}
				}
			}


			//save product images
			$images = F0FTable::getInstance('ProductImage', 'J2StoreTable');
			if(isset($this->_rawData['additional_images']) && !empty($this->_rawData['additional_images'] )){
				if(is_object($this->_rawData['additional_images'])){
					$this->_rawData['additional_images'] = json_encode($platform->fromObject($this->_rawData['additional_images']));
				}else{
					$this->_rawData['additional_images'] = json_encode($this->_rawData['additional_images']);
				}
                if(is_object($this->_rawData['additional_images_alt'])){
                    $this->_rawData['additional_images_alt'] = json_encode($platform->fromObject($this->_rawData['additional_images_alt']));
                }else{
                    $this->_rawData['additional_images_alt'] = json_encode($this->_rawData['additional_images_alt']);
                }
			}
			$this->_rawData['product_id'] = $table->j2store_product_id;

			//just make sure that we do not have a double entry there
			$images->load(array('product_id'=>$table->j2store_product_id));
			$images->save($this->_rawData);

			//finally run indexes to get the min - max price
			$this->runIndexes($table);
            if(isset($this->_rawData ['productfilter_ids'])){
                //save product filters
                F0FTable::getAnInstance('ProductFilter', 'J2StoreTable' )->addFilterToProduct ( $this->_rawData ['productfilter_ids'], $table->j2store_product_id );
            }

		}
	}

	public function runIndexes($table) {
		//first get all the variants for the product
		$variants = F0FModel::getTmpInstance('variants', 'J2StoreModel')->product_id($table->j2store_product_id)->is_master(0)->getList();
		$min_price            = null;
		$max_price            = null;

		foreach ( $variants as $variant) {
			// Skip non-priced variations
			if ( $variant->price === '' || $variant->price == 0 ) {
				continue;
			}

			// Find min price
			if ( is_null( $min_price ) || $variant->price < $min_price ) {
				$min_price  = $variant->price;
			}

			// Find max price
			if ( $variant->price > $max_price ) {
				$max_price   = $variant->price;
			}
		}
		//load the price index table and set the min - max price
		$db = JFactory::getDbo();
		$values = array();
		$product_id = $table->j2store_product_id;
		$values['product_id'] = $product_id;
		$values['min_price'] = $min_price;
		$values['max_price'] = $max_price;
		$price_index = F0FTable::getInstance('ProductPriceIndex', 'J2StoreTable');
		$object = (object) $values;
		if($price_index->load($table->j2store_product_id)) {
			$db->updateObject('#__j2store_productprice_index', $object , 'product_id');
		} else {
			$db->insertObject('#__j2store_productprice_index', $object);
		}

	}

	public function onBeforeDelete(&$model) {
		$id = $model->getId();
		if(!$id) return;
		$product = F0FTable::getAnInstance('Product', 'J2StoreTable')->getClone();
		if($product->load($id)) {
			if($product->product_type != 'variable') return;
			$variantModel = F0FModel::getTmpInstance('Variants', 'J2StoreModel');

			//get variants
			$variants = $variantModel->limit(0)->limitstart(0)->product_id($id)->getItemList();
			foreach($variants as $variant) {
				$variantModel->setIds(array($variant->j2store_variant_id))->delete();
			}
		}
	}

	public function onAfterGetProduct(&$model, &$product) {
		//sanity check
		if($product->product_type != 'variable') return;

        $platform = J2Store::platform();
		$product_helper = J2Store::product();
		//links
		$product_helper->getAddtocartAction($product);
		$product_helper->getCheckoutLink($product);
		$product_helper->getProductLink( $product );
		//we just have the products. Get the variants
		$variantModel = F0FModel::getTmpInstance('Variants', 'J2StoreModel');
		$variantModel->setState('product_type', $product->product_type);

		try {
			//first load master variant

			$variant_table = F0FTable::getAnInstance('Variant', 'J2StoreTable')->getClone();
			$variant_table->load(array('product_id'=>$product->j2store_product_id, 'is_master'=>1));
			$product->variant = $variant_table;


			//now load variants
			$product->variants = $variantModel
				->product_id($product->j2store_product_id)
				->is_master(0)
				->getList();
		}catch (Exception $e) {
			//there may not be a variant set.
			echo 'No variant set';
		}

		//no variants found. Exit processing
		if(!$product->variants) {
			$product->visibility = 0;
			return false;
		}

		foreach($product->variants as &$variant) {

			if($variant->quantity_restriction && $variant->min_sale_qty > 0) {
				$quantity = $variant->min_sale_qty;
			}else {
				$quantity = 1;
			}

			if($product_helper->check_stock_status($variant,$quantity)) {
				//reset the availability
				$variant->availability = 1;
			}else {
				$variant->availability = 0;
			}
		}
		$all_sold_out = true;
		foreach($product->variants as $single_variant){
			if($single_variant->availability == 1){
				$all_sold_out = false;
				break;
			}
		}
		//check if options match
		$product->all_sold_out = $all_sold_out;
		$product->options = array();
		//only if the product has options and variations
		if($product->has_options && $product->variants) {
			try {

				//lets load product options as well
				$product->product_options = F0FModel::getTmpInstance('ProductOptions', 'J2StoreModel')
					->product_id($product->j2store_product_id)
					->limit(0)
					->parent_id(null)
					->limitstart(0)
					->getList();

				$product->options = $product_helper->getProductOptions($product);

				if ($product_helper->validateVariants($product->variants, $product->options) === false) {
					$product->visibility = 0;
				}
			}catch (Exception $e) {
				$product->visibility = 0;
				return false;
			}
		}
		//validation failed, dont display the product at all.
		if($product->visibility == 0) return false;



		$registry = $platform->getRegistry($product->params);
		$product->params = $registry;

		// process variant
		$product->variant = $product_helper->getDefaultVariant($product->variants);

		$variant_ids = array();
		foreach($product->variants as &$one_variant) {
			//get quantity restrictions
			$product_helper->getQuantityRestriction($one_variant);
			$variant_ids[] = $one_variant->j2store_variant_id;
		}

		if($product->variant->quantity_restriction && $product->variant->min_sale_qty > 0) {
			$product->quantity = $product->variant->min_sale_qty;
		} else {
			$product->quantity = 1;
		}

		//process pricing. returns an object
		$product->pricing = $product_helper->getPrice($product->variant, $product->quantity);
		$param_data = $platform->getRegistry($product->variant->params);
		$main_image = $param_data->get('variant_main_image','');
		$is_main_as_thum = $param_data->get('is_main_as_thum',0);
		$product->main_image = isset( $main_image ) && !empty( $main_image ) ? $main_image: (isset($product->main_image) ? $product->main_image: '');
		if($is_main_as_thum){
			$product->thumb_image = isset( $main_image ) && !empty( $main_image ) ? $main_image: (isset($product->thumb_image) ? $product->thumb_image: '');
		}
		//only if the product has options and variations
		if($product->has_options && $product->variants) {
			try {

				$db = JFactory::getDbo();
				//get all the variants
				$query = $db->getQuery(true)->select('#__j2store_product_variant_optionvalues.variant_id as variant_id, #__j2store_product_variant_optionvalues.product_optionvalue_ids')->from('#__j2store_product_variant_optionvalues')
					->where('variant_id IN ('.implode(',', $variant_ids).')' );

				$db->setQuery($query);
				$csvs = $db->loadAssocList('variant_id');

				$variant_csvs = array();
				foreach($csvs as $variant_id=>$csv) {
					$variant_csvs[$variant_id] = $csv['product_optionvalue_ids'];
				}
				$product->variant_json = json_encode($variant_csvs);

				//get the default variant
				$default_optionvalue_ids = $variant_csvs[$product->variant->j2store_variant_id];

				/* $query = $db->getQuery(true)->select('#__j2store_product_variant_optionvalues.product_optionvalue_ids')->from('#__j2store_product_variant_optionvalues')
				->where('variant_id='.$db->q($product->variant->j2store_variant_id));
				$db->setQuery($query);
				$row = $db->loadObject();
				 */

				if(isset($default_optionvalue_ids)) {
					$value_array = explode(',', $default_optionvalue_ids);
				} else {
					$value_array = array();
				}
				foreach($product->options as &$option) {
					if ($option['type'] == 'select' || $option['type'] == 'radio') {
						foreach($option['optionvalue'] as &$optionvalue) {
							if(in_array($optionvalue['product_optionvalue_id'], $value_array)) {
								$optionvalue['product_optionvalue_default'] = 1;
							}
						}
					}
				}

			}catch (Exception $e) {
				//do nothing
			}
		}

	}

	public function onUpdateProduct(&$model, &$product) {
        $platform = J2Store::platform();
		$app = $platform->application();
		$product_helper = J2Store::product();
		$params = J2Store::config();
		//first get the correct variant
		$options = $app->input->get('product_option', array(0), 'ARRAY');
		if (isset($options )) {
			$options =  array_filter($options );
		} else {
			$options = array();
		}

		//no options found. so just return an empty array
		if(count($options) < 1) return false;

		//options found. Get the correct variant

		$variant = $product_helper->getVariantByOptions($options, $product->j2store_product_id);



		if($variant === false) return false;

		//now we have the variant. Process.

		//get quantity restrictions
		$product_helper->getQuantityRestriction($variant);

		$actual_quantity = $quantity = $app->input->getFloat('product_qty', 1);

		if($variant->quantity_restriction && $variant->min_sale_qty > 0 ) {
			$quantity = ($variant->min_sale_qty > $quantity) ? $variant->min_sale_qty : $quantity;
			//do one more check
			$quantity = ($quantity > $variant->max_sale_qty) ? $variant->max_sale_qty : $quantity;
			if($quantity == 0 || !$quantity) $quantity = $actual_quantity;
		}

		//check stock status
		if($product_helper->check_stock_status($variant, $quantity)) {
			//reset the availability
			$variant->availability = 1;
		}else {
			$variant->availability = 0;
		}

		//process pricing. returns an object
		$variant->pricing = $product_helper->getPrice($variant, $quantity);
        J2Store::plugin()->event('BeforeUpdateProductReturn',array(&$params,$product));
		//prepare return values
		$return = array();
		$return['variant_id'] = $variant->j2store_variant_id;
		$param_data = $platform->getRegistry($variant->params);
		$main_image = $param_data->get('variant_main_image','');
		$image_path = JUri::root();
		$return['main_image'] = isset( $main_image ) && !empty( $main_image ) ? $image_path.$main_image : $image_path.$product->main_image;
		$return['sku'] = $variant->sku;
		$return['quantity'] = floatval($quantity);
		$return['price'] = $variant->price;
		$return['availability'] = $variant->availability;
		$return['manage_stock'] = $variant->manage_stock;
		$return['allow_backorder'] = $variant->allow_backorder;

        if($product_helper->managing_stock($variant)){
            if($variant->availability) {
                $return['stock_status'] = $product_helper->displayStock($variant, $params);
            }else {
                $return['stock_status'] = JText::_('J2STORE_OUT_OF_STOCK');
            }
        }else{
            $return['stock_status'] = '';
        }
		//print_r($return);exit;
		$return['pricing'] = array();
		$return['pricing']['base_price'] = J2Store::product()->displayPrice($variant->pricing->base_price, $product, $params);
		$return['pricing']['price'] = J2Store::product()->displayPrice($variant->pricing->price, $product, $params);
		$return ['pricing'] ['original'] = array();
		$return ['pricing'] ['original']['base_price'] = $variant->pricing->base_price;
		$return ['pricing'] ['original']['price'] = $variant->pricing->price;
        if($variant->pricing->base_price != $variant->pricing->price){
            $return['pricing']['class'] = 'show';
        }else{
            $return['pricing']['class'] = 'hide';
        }
        $return['pricing']['discount_text'] = '';
        if( isset($variant->pricing->is_discount_pricing_available)) {
            $discount = (1 - ($variant->pricing->price / $variant->pricing->base_price)) * 100;
            if ($discount > 0){
                $return['pricing']['discount_text'] = JText::sprintf('J2STORE_PRODUCT_OFFER',round($discount).'%');
            }
        }
		//dimensions
		$return['dimensions'] = round($variant->length,2).' x '.round($variant->width,2).' x '.round($variant->height,2).' '.$variant->length_title;
		$return['weight'] = round($variant->weight,2).' '.$variant->weight_title;
		$return['weight_raw'] = round($variant->weight,2);
		$return['weight_unit'] = $variant->weight_unit;
        J2Store::plugin()->event('AfterUpdateProductReturn',array(&$return,$product,$params));
		return $return;

	}

}
