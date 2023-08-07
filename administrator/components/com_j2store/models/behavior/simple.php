<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
use Joomla\Registry\Registry;

defined('_JEXEC') or die;
class J2StoreModelProductsBehaviorSimple extends F0FModelBehavior {

	private $_rawData = array();

		public function onAfterGetItem(&$model, &$record) {
			//sanity check
			if($record->product_type != 'simple') return;
            $platform = J2Store::platform();
			//we just have the products. Get the variants
			if($record->j2store_product_id) {

				$variantModel = F0FModel::getTmpInstance('Variants', 'J2StoreModel');
				$variantModel->clearState()->setState('product_type', $record->product_type);
					//Its a simple product. So we will have only one variant record
					try {
						$variants = $variantModel->product_id($record->j2store_product_id)->getList();
						$record->variants = isset($variants[0]) ? $variants[0]: new stdClass();
					}catch(Exception $e) {
						echo $e->getMessage();
						$record->variants = F0FTable::getInstance('Variants', 'J2StoreTable');
					}

					try {

					//lets load product options as well
					$record->product_options = F0FModel::getTmpInstance('ProductOptions', 'J2StoreModel')
									->product_id($record->j2store_product_id)
									->limit(0)
									->parent_id(null)
									->limitstart(0)
									->getList();
					}catch (Exception $e) {
						echo $e->getMessage();
					}

					$registry = $platform->getRegistry($record->params);
					$record->params = $registry;
				}
		}

		public function onBeforeSave(&$model, &$data)
		{
			if(!isset($data['product_type']) || $data['product_type'] != 'simple') return;

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

            if(is_object($data['quantity']) && (!isset($data['quantity']->product_attributes) || empty($data['quantity']->product_attributes))){
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
            if( isset($data['max_sale_qty']) && !empty($data['max_sale_qty'])  && isset($data['min_sale_qty']) && !empty($data['min_sale_qty']) && ($data['max_sale_qty'] < $data['min_sale_qty']) ){
                $data['min_sale_qty'] = 0 ;
                $app->enqueueMessage(JText::_('J2STORE_MAX_SALE_QTY_NEED_TO_GRATER_THEN_MIN_SALE_QTY'),'warning');
            }

			//bind existing params
			if($data['j2store_product_id'] ){
				$product = F0FTable::getAnInstance('Product', 'J2StoreTable')->getClone();
				$product->load($data['j2store_product_id']);
				if(isset($product->params)){
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
			if($table->product_type != 'simple') return;

			//since post has too much of information, this could do the job
			$variant = F0FTable::getInstance('Variant', 'J2StoreTable')->getClone();
			$variant->bind($this->_rawData);
			//by default it is treated as master product.
			$variant->is_master = 1;
			$variant->product_id = $table->j2store_product_id;
			$variant->store();

			//get the item options
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
			if(isset($this->_rawData['quantity'] )) {
				$inventory = $this->_rawData['quantity'];
				$productQuantity = F0FTable::getInstance('Productquantity', 'J2StoreTable')->getClone();
				$productQuantity->load(array('variant_id'=>$variant->j2store_variant_id));
				$productQuantity->variant_id = $variant->j2store_variant_id;
				try {
					$productQuantity->save($inventory);
				}catch (Exception $e) {
					throw new Exception($e->getMessage());
				}
			}

            $platform = J2Store::platform();
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
            if(isset($this->_rawData ['productfilter_ids'])){
                //save product filters
                F0FTable::getAnInstance('ProductFilter', 'J2StoreTable' )->addFilterToProduct ( $this->_rawData ['productfilter_ids'], $table->j2store_product_id );
            }
            
		}

	}

	public function onBeforeDelete(&$model) {
		$id = $model->getId();
		if(!$id) return;
		$product = F0FTable::getAnInstance('Product', 'J2StoreTable')->getClone();
		if($product->load($id)) {
			if($product->product_type != 'simple') return;
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
		if(!in_array($product->product_type, array('simple'))) return;
        $platform = J2Store::platform();
		$product_helper = J2Store::product ();
		// links
		$product_helper->getAddtocartAction ( $product );
		$product_helper->getCheckoutLink ( $product );
		$product_helper->getProductLink( $product );

		$variantModel = F0FModel::getTmpInstance ( 'Variants', 'J2StoreModel' );
		$variantModel->clearState ()->setState ( 'product_type', $product->product_type );
		// Its a simple product. So we will have only one variant record
		try {
			$variants = $variantModel->product_id ( $product->j2store_product_id )->getList ();
			$product->variants = $variants [0];
		} catch ( Exception $e ) {
			$model->setError ( $e->getMessage () );
			$product->variants = F0FTable::getInstance ( 'Variants', 'J2StoreTable' );
		}

		$registry = $platform->getRegistry($product->params);
		$product->params = $registry;

		// process variant
		$product->variant = $product->variants;

		// get quantity restrictions
		$product_helper->getQuantityRestriction ( $product->variant );

		// now process the quantity

		if ($product->variant->quantity_restriction && $product->variant->min_sale_qty > 0) {
			$product->quantity = $product->variant->min_sale_qty;
		} else {
			$product->quantity = 1;
		}

		// check stock status
		if ($product_helper->check_stock_status ( $product->variant, $product->quantity )) {
			// reset the availability
			$product->variant->availability = 1;
		} else {
			$product->variant->availability = 0;
		}

		// process pricing. returns an object
		$product->pricing = $product_helper->getPrice ( $product->variant, $product->quantity );

		$product->options = array ();
		if ($product->has_options) {

			// load product options

			try {

				// lets load product options as well
				$product->product_options = F0FModel::getTmpInstance ( 'ProductOptions', 'J2StoreModel' )->product_id ( $product->j2store_product_id )->limit ( 0 )->parent_id ( null )->limitstart ( 0 )->getList ();
			} catch ( Exception $e ) {
				$model->setError ( $e->getMessage () );
			}

			try {
				$product->options = $product_helper->getProductOptions ( $product );
				$default_selected_options = $product_helper->getDefaultProductOptions ( $product->options );
				// now get the price
				$product_option_data = $product_helper->getOptionPrice ( $default_selected_options, $product->j2store_product_id );
				$product->pricing->base_price = $product->pricing->base_price + $product_option_data ['option_price'];
				$product->pricing->price = $product->pricing->price + $product_option_data ['option_price'];
			} catch ( Exception $e ) {
				// do nothing
			}
		}		
	}

	public function onUpdateProduct(&$model, &$product) {
		if($product->product_type != 'simple') return;
		$app = JFactory::getApplication ();
		$params = J2Store::config ();
		$product_helper = J2Store::product ();

		$product_id = $app->input->getInt ( 'product_id', 0 );

		if (! $product_id)
			return false;

		// get variant
		$variants = F0FModel::getTmpInstance ( 'Variants', 'J2StoreModel' )->product_id ( $product->j2store_product_id )->is_master ( 1 )->getList ();
		$product->variants = $variants [0];

		// process variant
		$product->variant = $product->variants;

		// get quantity restrictions
		$product_helper->getQuantityRestriction ( $product->variant );

		// now process the quantity
		$product->quantity = $app->input->getFloat ( 'product_qty', 1 );

		if ($product->variant->quantity_restriction && $product->variant->min_sale_qty > 0) {
			$product->quantity = $product->variant->min_sale_qty;
		}

		// process pricing. returns an object
		$pricing = $product_helper->getPrice ( $product->variant, $product->quantity );

		$selected_product_options = $app->input->get ( 'product_option', array (), 'ARRAY' );

		// get the selected option price
		if (count ( $selected_product_options )) {
			$product_option_data = $product_helper->getOptionPrice ($selected_product_options, $product->j2store_product_id );

			$base_price = $pricing->base_price + $product_option_data ['option_price'];
			$price = $pricing->price + $product_option_data ['option_price'];
		} else {
			$base_price = $pricing->base_price;
			$price = $pricing->price;
		}
        J2Store::plugin()->event('BeforeUpdateProductReturn',array(&$params,$product));
		$return = array ();
		$return ['pricing'] = array ();
		$return ['pricing'] ['base_price'] = $product_helper->displayPrice ( $base_price, $product, $params );
		$return ['pricing'] ['price'] = $product_helper->displayPrice ( $price, $product, $params );
		$return ['pricing'] ['original'] = array();
		$return ['pricing'] ['original']['base_price'] = $base_price;
		$return ['pricing'] ['original']['price'] = $price;
        J2Store::plugin()->event('AfterUpdateProductReturn',array(&$return,$product,$params));
		return $return;
	}

}