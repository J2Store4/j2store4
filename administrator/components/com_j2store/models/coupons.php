<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2StoreModelCoupons extends F0FModel {

	public $code = '';
	public $coupon = false;
	public $limit_usage_to_x_items = '';

	protected function onBeforeSave(&$data, &$table) {
        $status = true ;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->qn(array('coupon_code')))
            ->from($db->qn('#__j2store_coupons'))
            ->where($db->qn('j2store_coupon_id').'!='. $db->quote($data['j2store_coupon_id']))
            ->where($db->qn('coupon_code').'='. $db->quote($data['coupon_code']));
        $db->setQuery($query);
        $coupon_code = $db->loadResult();
        if(isset($coupon_code) && !empty ($coupon_code)){
            $this->setError(JText::_("J2STORE_COUPON_CODE_IS_ALREADY_USED"));
            $status = false;
        }

        if((isset($data['coupon_name']) && empty($data['coupon_name'])) || (isset($data['coupon_code']) && empty($data['coupon_code']))){
            $this->setError(JText::_("J2STORE_REQUIRED_FIELD_EMPTY"));
            $status = false;
        }

        if( isset($data['valid_from']) && ($data['valid_from'] != '0000-00-00 00:00:00') && isset($data['valid_to']) && ($data['valid_to'] != '0000-00-00 00:00:00') && ($data['valid_from'] >= $data['valid_to'] )){
              $this->setError(JText::_("J2STORE_COUPON_VALID_FORM_DATE_NEED_TO_GRATER_THAN_COUPON VALID_TO_DATE"));
              $status = false;
        }

        if( ($data['valid_to'] != '0000-00-00 00:00:00' && $data['valid_from'] == '0000-00-00 00:00:00' )){
            $this->setError(JText::_("J2STORE_COUPON_VALID_FORM_DATE_NEED_TO_GRATER_THAN_COUPON VALID_TO_DATE"));
            $status = false;
        }
        
		if(isset($data['products']) && !empty($data['products'])){
            if(is_string($data['products'])){
                $data['products'] = array($data['products']);
            }
            $data['products'] =implode(',' , $data['products']);

		}else{
			$data['products'] ='';
		}

		if(isset($data['product_category']) && !empty($data['product_category'])){
            if(is_string($data['product_category'])){
                $data['product_category'] = array($data['product_category']);
            }
            $data['product_category'] =implode(',' , $data['product_category']);
        }else{
			$data['product_category']='';
		}

		if(isset($data['brand_ids']) && !empty($data['brand_ids'])){
            if(is_string($data['brand_ids'])){
                $data['brand_ids'] = array($data['brand_ids']);
            }
            $data['brand_ids']   =implode(',' , $data['brand_ids']);
		}else{
			$data['brand_ids']='';
		}

		if(isset($data['user_group']) && !empty($data['user_group'])){
              if(is_string($data['user_group'])){
                    $data['user_group'] = array($data['user_group']);
                }
			$data['user_group'] =implode(',' ,$data['user_group']);
		}else{
			$data['user_group']='';
		}
		return $status;
	}

	protected function onAfterGetItem(&$record)
	{
		$record->product_category = explode(',',$record->product_category);
		$record->brand_ids = explode(',',$record->brand_ids);
		$record->user_group = explode(',',$record->user_group);
	}

	public function init() {

		// sanity check
		$this->code = $this->get_coupon ();
		if (empty ( $this->code ))
			return false;

		static $couponsets;
		if(!is_array($couponsets)) $couponsets = array();

		if (!isset($couponsets[$this->code])) {
			$db = JFactory::getDbo ();
			$query = $db->getQuery ( true )->select ( '*' )->from ( '#__j2store_coupons' )->where ( 'coupon_code = ' . $db->q ( $this->code ) )->where('enabled = 1');
			$db->setQuery ( $query );
			try {
				$row = $db->loadObject ();
			} catch ( Exception $e ) {
				// an error occured
				$row = F0FTable::getInstance ( 'Coupon', 'J2StoreTable' );
			}
			$couponsets[$this->code] = $row;
		}
		$this->coupon = $couponsets[$this->code];
		if(isset($this->coupon->max_quantity)) {
			$this->limit_usage_to_x_items = $this->coupon->max_quantity;
		}
		return true;
	}

	public function get_coupon_history($coupon_id, $user_id='') {

		static $history;
		if(!is_array($history)) $history= array();

		if(!isset($history[$coupon_id][$user_id])) {
			$db = JFactory::getDbo();
			$query = $db->getQuery ( true );
			$query->select ( 'COUNT(*) AS total' )->from ( '#__j2store_orderdiscounts' )
                ->join('LEFT','#__j2store_orders on #__j2store_orderdiscounts.order_id = #__j2store_orders.order_id')
                -> where('#__j2store_orders.order_state_id!=5 ')
                ->where ( '#__j2store_orderdiscounts.discount_entity_id=' . $db->q ( $coupon_id) );
			$query->where('#__j2store_orderdiscounts.discount_type = '.$db->q('coupon'));
			if(!empty($user_id)) {
				$query->where('#__j2store_orderdiscounts.user_id = '.$db->q($user_id));
			}
			$db->setQuery ( $query );
			$history[$coupon_id][$user_id] = $db->loadResult ();
		}
		return $history[$coupon_id][$user_id];
	}


	public function is_valid($order) {
		try {
            $coupon_status = false;
            J2Store::plugin()->event('BeforeCouponIsValid', array($this, $order,&$coupon_status));
            if ($coupon_status) {
                return true;
            }
			$this->validate_enabled();
			$this->validate_exists();
			$this->validate_usage_limit();
			$this->validate_user_logged();
			$this->validate_users();
			$this->validate_user_group();
			$this->validate_user_usage_limit();
			$this->validate_expiry_date();
			$this->validate_minimum_amount($order);
			$this->validate_product_ids();
			//allo plugins to run their own course.
			$results = J2Store::plugin()->event('CouponIsValid', array($this, $order));
			if (in_array(false, $results, false)) {
				throw new Exception( JText::_('J2STORE_COUPON_NOT_APPLICABLE'));
				$this->remove_coupon();
				return false;
			}
		} catch ( Exception $e ) {
			$this->setError($e->getMessage());
			//var_dump($e->getMessage());
			JFactory::getApplication()->enqueueMessage($e->getMessage());
			//clear the coupon code
			$this->remove_coupon();
			return false;
		}
		return true;
	}

	public function is_admin_valid($order){
		try {
			$this->validate_enabled ();
			$this->validate_exists();
			$this->validate_expiry_date();
			$this->validate_minimum_amount($order);
			$this->validate_admin_product_ids($order);
			//allo plugins to run their own course.
			$results = J2Store::plugin()->event('CouponIsValid', array($this, $order));
			if (in_array(false, $results, false)) {
				throw new Exception( JText::_('J2STORE_COUPON_NOT_APPLICABLE'));
				$this->remove_coupon();
				return false;
			}
		}catch (Exception $e){
			$this->setError($e->getMessage());
			//var_dump($e->getMessage());
			JFactory::getApplication()->enqueueMessage($e->getMessage());
			//clear the coupon code
			$this->remove_coupon();
			return false;
		}
		return true;
	}
	
	/**
	 * Check if a coupon is valid
	 *
	 * @return bool
	 */
	public function is_valid_for_cart() {
		return $this->is_type( array( 'fixed_cart', 'percentage_cart' ));
	}


	public function is_valid_for_product($product) {
		if (! $this->is_type ( array (
				'fixed_product',
				'percentage_product' 
		) )) {
			return false;
		}
		
		$valid = false;
		$coupon_products_data = $this->get_selected_products ();
		$coupon_categories_data = array ();
		$product_data = array ();
		if ($this->coupon->product_category) {
			$coupon_categories_data = explode ( ',', $this->coupon->product_category );
		}
		$brands = array();
		if(!empty($this->coupon->brand_ids)) {
			$brands = explode ( ',', $this->coupon->brand_ids);
		}
		
		if (! count ( $coupon_categories_data ) && ! count ( $coupon_products_data ) && !count($brands)) {
			// No product ids - all items discounted
			$valid = true;
		}
		
		if (count ( $coupon_products_data ) > 0) {
			//selected products only
			if (in_array ( $product->product_id, $coupon_products_data )) {
				$valid = true;
			}
		}

		if (count ( $coupon_categories_data ) > 0 && $product->product_source == 'com_content') {

			//selected categories only
			$db = JFactory::getDbo ();
			$query = $db->getQuery ( true );
			$query->select ( '*' )->from ( '#__content' )->where ( 'id=' . $db->q ( $product->product_source_id ) );
			//->where ( 'catid=' . $db->q ( $category_id ) );
			$db->setQuery ( $query );
			$content = $db->loadObject ();
			$cat_ids = explode ( ',', $content->catid );

			foreach ( $coupon_categories_data as $category_id ) {
				if (in_array ( $category_id, $cat_ids )) {
					$valid = true;
					break;
				}
			}

		}
		
		//manufacturers / brands
		if(count($brands)){
			$manufacturer_data = array ();			
				$manufacturer_id = isset($product->cartitem->manufacturer_id) ? $product->cartitem->manufacturer_id : '';
				if(!empty($manufacturer_id ) && in_array($manufacturer_id, $brands)){
					$manufacturer_data[] = $product->product_id;
				}
				if (count ( $manufacturer_data ) > 0) {
					$valid = true;
				}
		}
		
		// allow plugins to modify the output
		J2Store::plugin ()->event ( 'IsCouponValidForProduct', array (
				$valid,
				$product,
				$this 
		) );
		return $valid;
	}

	private function validate_enabled() {
		$params = J2Store::config();
		if($params->get('enable_coupon', 0) == 0) {
			throw new Exception( JText::_('J2STORE_COUPON_DOES_NOT_EXIST') );
		}
	}

	/**
	 * Ensure coupon exists or throw exception
	 */
	private function validate_exists() {
		if ( ! $this->coupon) {
			throw new Exception( JText::_('J2STORE_COUPON_DOES_NOT_EXIST') );
		}
	}

	/**
	 * Ensure coupon usage limit is valid or throw exception
	 */
	private function validate_usage_limit() {
		$total = $this->get_coupon_history($this->coupon->j2store_coupon_id);
		if ($this->coupon->max_uses > 0 && ($total >= $this->coupon->max_uses)) {
			throw new Exception( JText::_('J2STORE_COUPON_USAGE_LIMIT_HAS_REACHED') );
		}

	}

	private function validate_user_logged() {
		$user = JFactory::getUser();
		// is customer loged
		if ($this->coupon->logged && ! $user->id) {
			throw new Exception( JText::_('J2STORE_COUPON_APPLICABLE_ONLY_FOR_LOGGED_IN_CUSTOMERS') );
		}
	}

	private function validate_users() {
		$user = JFactory::getUser ();
		if ($this->coupon->users) {
            if($user->id <= 0){
                throw new Exception ( JText::_ ( 'J2STORE_COUPON_NOT_APPLICABLE' ) );
            }
			$users = explode ( ',', $this->coupon->users );
			if (count ( $users )){
                if (! in_array ( $user->id, $users )) {
                    throw new Exception ( JText::_ ( 'J2STORE_COUPON_NOT_APPLICABLE' ) );
                }
            }

		}
	}
	
	/**
	 * Method to validate the user group of the user as set in the coupon
	 * */
	private function validate_user_group() {
		$user = JFactory::getUser ();
		if ($this->coupon->user_group && count($user->groups) ) {
			if (! count(  array_intersect(explode(',', $this->coupon->user_group), $user->groups) ) ) {
				throw new Exception ( JText::_ ( 'J2STORE_COUPON_NOT_APPLICABLE' ) );
			}
		}
	}

	/**
	 * Ensure coupon user usage limit is valid or throw exception
	 *
	 * Per user usage limit - check here if user is logged in (against user IDs)
	 * Checked again for emails later on in WC_Cart::check_customer_coupons()
	 */
	private function validate_user_usage_limit() {
		$user = JFactory::getUser();
		if ($user->id) {
			$customer_total = $this->get_coupon_history($this->coupon->j2store_coupon_id, $user->id);
			if ($this->coupon->max_customer_uses > 0 && ($customer_total >= $this->coupon->max_customer_uses)) {
				throw new Exception( JText::_('J2STORE_COUPON_INDIVIDUAL_USAGE_LIMIT_HAS_REACHED') );
			}
		}
	}

	/**
	 * Ensure coupon date is valid or throw exception
	 */
	private function validate_expiry_date() {
		$db = JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$tz = JFactory::getConfig()->get('offset');
		$now = JFactory::getDate('now', $tz)->toSql(true);
		$valid_from = JFactory::getDate($this->coupon->valid_from, $tz)->toSql(true);
		$valid_to = JFactory::getDate($this->coupon->valid_to, $tz)->toSql(true);
		if(
		($this->coupon->valid_from == $nullDate || $valid_from <= $now) &&
		 ($this->coupon->valid_to == $nullDate || $valid_to >= $now)
		){
			return true;
		}else {
			throw new Exception( JText::_('J2STORE_COUPON_EXPIRED'));
		}
	}

	/**
	 * Ensure coupon amount is valid or throw exception
	 */
	private function validate_minimum_amount($order) {

		// is subtotal above min subtotal restriction.
		if (isset ( $this->coupon->min_subtotal ) && ( float ) $this->coupon->min_subtotal > 0) {
			$subtotal = $order->subtotal;
			//echo round($row->min_subtotal,0);
			if (!empty($this->coupon->min_subtotal) && (float) $this->coupon->min_subtotal  > (float) $subtotal) {
				throw new Exception( JText::sprintf('J2STORE_COUPON_MINIMUM_SPEND_LIMIT_NOT_REACHED', $this->coupon->min_subtotal));
			}
		}
	}

	/**
	 * Ensure coupon is valid for products in the cart is valid or throw exception
	 */
	private function validate_product_ids() {
		$db = JFactory::getDbo ();

		$coupon_products_data = $this->get_selected_products ();

		$coupon_categories_data = array ();
		if ($this->coupon->product_category) {
			$coupon_categories_data = explode ( ',', $this->coupon->product_category );
		}

		$product_data = array ();
		if (count ( $coupon_categories_data ) || count ( $coupon_products_data ) || !empty($this->coupon->brand_ids)) {
			$valid_for_cart = false;
			$cartitems = F0FModel::getTmpInstance ( 'Carts', 'J2StoreModel' )->getItems ();

			//product categories
			if (count ( $coupon_categories_data ) > 0) {

				if (count ( $cartitems ) > 0) {
					foreach ( $cartitems as $cart_item ) {
						if ($cart_item->product_source == 'com_content') {
							//selected categories only

							$query = $db->getQuery ( true );
							$query->select ( '*' )->from ( '#__content' )->where ( 'id=' . $db->q ( $cart_item->product_source_id ) );
							//->where ( 'catid=' . $db->q ( $category_id ) );
							$db->setQuery ( $query );
							$content = $db->loadObject ();
							$cat_ids = explode ( ',', $content->catid );

							foreach ( $coupon_categories_data as $category_id ) {
								if (in_array ( $category_id, $cat_ids )) {
									$product_data [] = $cart_item->product_id;
									break;
								}
							}
						}
					}
					if (count ( $product_data ) > 0) {
						$valid_for_cart = true;
					}
				}
			}
			
			//products
			if (count ( $coupon_products_data ) > 0) {
				if (count ( $cartitems ) > 0) {
					foreach ( $cartitems as $cart_item ) {
						if (in_array ( $cart_item->product_id, $coupon_products_data )) {
							$valid_for_cart = true;
						}
					}
				}
			}
			
			//manufacturers
			if(!empty($this->coupon->brand_ids)){
				$brand_ids = explode(',' ,$this->coupon->brand_ids);
				$manufacturer_data = array ();
				if(count($brand_ids)) {
					foreach ( $cartitems as $item ) {
						if(isset($item->manufacturer_id) && !empty($item->manufacturer_id) && in_array($item->manufacturer_id , $brand_ids)){
							$manufacturer_data[] = $item->product_id;							
						}
					}
					if (count ( $manufacturer_data ) > 0) {
						$valid_for_cart = true;
					}
				}
			}

			if (! $valid_for_cart) {
				throw new Exception ( JText::_ ( 'J2STORE_COUPON_NOT_VALID_FOR_PRODUCT' ) );
			}
		}
	}

	public function validate_admin_product_ids($order){
		// coupon validation during admin order edit
		$app = JFactory::getApplication();
		$db = JFactory::getDbo ();
        $platform = J2Store::platform();
		$coupon_products_data = $this->get_selected_products ();

		$coupon_categories_data = array ();
		if ($this->coupon->product_category) {
			$coupon_categories_data = explode ( ',', $this->coupon->product_category );
		}
		$product_data = array ();

		if (count ( $coupon_categories_data ) || count ( $coupon_products_data ) || !empty($this->coupon->brand_ids)) {
			if($platform->isClient('administrator')){
				$order_items = $order->getItems();
				//product categories
				if ( count ( $coupon_categories_data ) > 0 ) {

					if ( count ( $order_items ) > 0 ) {
						foreach ( $order_items as $order_item ) {
							$product = J2Store::product()->setId($order_item->product_id)->getProduct();
							if ( $product->product_source == 'com_content' ) {
								$query = $db->getQuery ( true );
								$query->select ( '*' )->from ( '#__content' )->where ( 'id=' . $db->q ( $product->product_source_id ) );
								//->where ( 'catid=' . $db->q ( $category_id ) );
								$db->setQuery ( $query );
								$content = $db->loadObject ();
								$cat_ids = explode ( ',', $content->catid );

								foreach ( $coupon_categories_data as $category_id ) {
									if (in_array ( $category_id, $cat_ids )) {
										$product_data [] = $order_item->product_id;
										break;
									}
								}
								
							}
						}
						if ( count ( $product_data ) > 0 ) {
							$valid_for_cart = true;
						}
					}
				}

				//products
				if ( count ( $coupon_products_data ) > 0 ) {
					if ( count ( $order_items ) > 0 ) {
						foreach ( $order_items as $order_item ) {
							if ( in_array ( $order_item->product_id, $coupon_products_data ) ) {
								$valid_for_cart = true;
							}
						}
					}
				}

				//manufacturers
				if ( !empty( $this->coupon->brand_ids ) ) {
					$brand_ids = explode ( ',', $this->coupon->brand_ids );
					$manufacturer_data = array();
					if ( count ( $brand_ids ) ) {
						foreach ( $order_items as $item ) {
							if ( isset( $item->manufacturer_id ) && !empty( $item->manufacturer_id ) && in_array ( $item->manufacturer_id, $brand_ids ) ) {
								$manufacturer_data[] = $item->product_id;
							}
						}
						if ( count ( $manufacturer_data ) > 0 ) {
							$valid_for_cart = true;
						}
					}
				}

				if ( !$valid_for_cart ) {
					throw new Exception ( JText::_ ( 'J2STORE_COUPON_NOT_VALID_FOR_PRODUCT' ) );
				}
			}
		}
	}

	public function get_selected_products() {
		$products = array();
		if (!empty($this->coupon->products)) {
			$products = explode ( ',', $this->coupon->products);
		}
		return $products;
	}



	public function getCouponByCode($code) {
		$db = JFactory::getDbo ();
		$query = $db->getQuery ( true );
		$query->select ( '*' )->from ( '#__j2store_coupons' )->where ( 'coupon_code=' . $db->q ( $code ) )->where ( 'enabled=1' );
		$db->setQuery ( $query );
		$row = $db->loadObject ();
		return $row;

	}
	
	/**
	 * Checks the coupon type.
	 *
	 * @param string $type Array or string of types
	 * @return bool
	 */
	public function is_type( $type ) {
		return ( $this->coupon->value_type == $type || ( is_array( $type ) && in_array( $this->coupon->value_type, $type ) ) ) ? true : false;
	}

	public function get_discount_amount($discounting_amount, $cartitem, $order, $single = true) {
		$discount = 0;
		$app = JFactory::getApplication ();
		$params = J2Store::config ();
		$session = JFactory::getSession ();
		$cart_item_qty = is_null ( $cartitem ) ? 1 : $cartitem->orderitem_quantity;
		
		if ($this->is_type ( array ('percentage_product','percentage_cart') )) {
			// percentage based discount. This is a very normal calculation
			$discount = $this->coupon->value * ($discounting_amount / 100);

		} elseif ($this->is_type ( 'fixed_cart' ) && ! is_null ( $cartitem ) && $order->subtotal_ex_tax) {
			// A complex calculation. we need to divide the discount between line items based on their price in proportion to the subtotal. This is so line items with different tax rates get a fair discount
			$discount_percent = 0;
			$product_helper = J2Store::product ();
			if ($params->get ( 'config_including_tax', 0 )) {
				if(J2Store::platform()->isClient('administrator')){
					$actual_price = $cartitem->orderitem_finalprice_with_tax;
				}else{
					$actual_price = ($cartitem->orderitem_price + $cartitem->orderitem_option_price);
				}
				$price_for_discount = $product_helper->get_price_including_tax ( ($actual_price * $cart_item_qty), $cartitem->orderitem_taxprofile_id );
				$discount_percent = ($price_for_discount) / $order->subtotal;
			} else {
				$actual_price = ($cartitem->orderitem_price + $cartitem->orderitem_option_price);
				$price_for_discount = $product_helper->get_price_excluding_tax ( ($actual_price * $cart_item_qty), $cartitem->orderitem_taxprofile_id );
				$discount_percent = ($price_for_discount) / $order->subtotal_ex_tax;
			}
			$discount = ($this->coupon->value * $discount_percent) / $cart_item_qty;
		} elseif ($this->is_type ( 'fixed_product' )) {
			
			$discount = min ( $this->coupon->value, $discounting_amount );
			$discount = $single ? $discount : $discount * $cart_item_qty;
			// $discount = $this->coupon->value * ($discounting_amount / $sub_total);
		}
		
		$discount = min ( $discount, $discounting_amount );
		
		// Handle the limit_usage_to_x_items option
		if ($this->is_type ( array ('percentage_product','fixed_product') )) {
			if ($discounting_amount) {
				if ('' === $this->limit_usage_to_x_items || 0 == $this->limit_usage_to_x_items) {
					$limit_usage_qty = $cart_item_qty;
				} else {
					$limit_usage_qty = min ( $this->limit_usage_to_x_items, $cart_item_qty );
					$this->limit_usage_to_x_items = max ( 0, $this->limit_usage_to_x_items - $limit_usage_qty );
				}
				if ($single) {
					$discount = ($discount * $limit_usage_qty) / $cart_item_qty;
				} else {
					$discount = ($discount / $cart_item_qty) * $limit_usage_qty;
				}
			}
		}
		// has free shipping
		if ($this->coupon->free_shipping) {
			$order->allow_free_shipping ();
		}
		
		// allow plugins to modify the amount
		J2Store::plugin ()->event ( 'GetCouponDiscountAmount', array (
				$discount,
				$discounting_amount,
				$cartitem,
				$order,
				$this,
				$single 
		) );
		return $discount;
	}
	
	public function getCouponHistory() {
		$app = JFactory::getApplication();
		$id = $app->input->getInt('coupon_id', 0);
		if($id < 1) return array();
		$coupon_history_model = F0FModel::getTmpInstance('Orderdiscounts', 'J2StoreModel');
		$items = $coupon_history_model->discount_entity_id($id)->discount_type('coupon')->getList();
		if(count($items)) {
			foreach($items as &$item) {
				$order = F0FTable::getAnInstance('Order', 'J2StoreTable')->getClone();
				$order->load(array('order_id'=>$item->order_id));
				$item->order = $order;
			}
		}
		return $items;
	}
	
	/**
	 * Method to get coupon discount types. Third party developers can override by introducing new coupon value types.
	 * @return array A list of key value pair
	 */
	
	
	public function getCouponDiscountTypes() {		
		$list = array (
				'percentage_cart' => JText::_ ( 'J2STORE_VALUE_TYPE_CART_DISCOUNT_PERCENTAGE' ),
				'fixed_cart' => JText::_ ( 'J2STORE_VALUE_TYPE_CART_DISCOUNT_FIXED_PRICE' ),
				'percentage_product' => JText::_ ( 'J2STORE_VALUE_TYPE_PRODUCT_PERCENTAGE' ),
				'fixed_product' => JText::_ ( 'J2STORE_VALUE_TYPE_PRODUCT_FIXED_PRICE' ) 
		);
		//allow plugins to modify
		J2Store::plugin()->event('GetCouponDiscountTypes', array(&$list));
		return $list;	
	}

	public function get_coupon(){
		$cart_model = F0FModel::getTmpInstance('Carts', 'J2StoreModel');
        $cart_table = $cart_model->getCart();
		if(isset( $cart_table->cart_coupon ) && !empty( $cart_table->cart_coupon ) ){
			$session = JFactory::getSession ();
			$session->set('coupon', $cart_table->cart_coupon, 'j2store');
			$coupon_code = $cart_table->cart_coupon;
		}else{
			$session = JFactory::getSession ();
			$coupon_code = $session->get ( 'coupon', '', 'j2store' );
		}

		return $coupon_code;
	}

	public function has_coupon(){
		$cart_model = F0FModel::getTmpInstance('Carts', 'J2StoreModel');
        $cart_table = $cart_model->getCart();
		$session = JFactory::getSession ();
		if(isset( $cart_table->cart_coupon ) && !empty( $cart_table->cart_coupon ) ){
			$session->set('coupon', $cart_table->cart_coupon, 'j2store');
		}
		return $session->has ( 'coupon', 'j2store' );
	}

	public function set_coupon($post_coupon=''){
		$session = JFactory::getSession ();
		$session->set('coupon', $post_coupon, 'j2store');
		$cart_model = F0FModel::getTmpInstance('Carts', 'J2StoreModel');
		$cart_table = $cart_model->getCart();
		if(isset( $cart_table->j2store_cart_id ) && !empty( $cart_table->j2store_cart_id )){
			$cart_table->cart_coupon = $post_coupon;
			$cart_table->store();
		}
	}

	public function remove_coupon() {
		JFactory::getSession()->clear('coupon', 'j2store');
		$cart_model = F0FModel::getTmpInstance('Carts', 'J2StoreModel');
        $cart_table = $cart_model->getCart();

		if(isset( $cart_table->j2store_cart_id ) && !empty( $cart_table->j2store_cart_id )){
			$cart_table->cart_coupon = '';
			$cart_table->store();
		}
		
	}
}