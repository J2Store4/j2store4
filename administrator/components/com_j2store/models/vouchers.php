<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreModelVouchers extends F0FModel {
	
	public $code = '';
	public $voucher = false;
	public $history = array();
	
	public function init($voucher_code = '') {
	
		//get the coupon from the session and assign it to the coupon variable
        if(empty($voucher_code)){
            $this->code = $this->get_voucher ();
        }else{
            $this->code = $voucher_code;
        }

		if(empty($this->code)) return false;
	
		//load the coupon
		$table = F0FTable::getInstance('Voucher', 'J2StoreTable')->getClone();
		$table->load(array('voucher_code'=>$this->code));
		$status = false;
		if($table->enabled){
			$this->voucher = $table;
			$status = true;
		}
		if(!$status) {
			JFactory::getApplication()->enqueueMessage(JText::_('J2STORE_VOUCHER_DOES_NOT_EXIST'),'warning');
			$this->remove_voucher();
		}
		return $status;
	}
	
	public function get_voucher_history($voucher_id) {
	
		if(!isset($this->history[$voucher_id])) {
			$db = JFactory::getDbo();
			$query = $db->getQuery (true);
			$j2config = J2Store::config ();
			if($j2config->get('config_including_tax', 0)) {
				$query->select('ROUND(SUM(discount_amount) + SUM(discount_tax), 2) AS total');
			}else {
				$query->select('ROUND(SUM(discount_amount), 2) AS total');
			}
			$query->from('#__j2store_orderdiscounts')
			->join('LEFT','#__j2store_orders o on #__j2store_orderdiscounts.order_id = o.order_id')
			-> where(' o.order_state_id!=5 ')
			-> where('discount_entity_id='.$db->q($voucher_id))
			->group('discount_entity_id');
			$query->where('discount_type ='.$db->q('voucher'));
			$db->setQuery ( $query );
			$this->history[$voucher_id] = $db->loadResult ();
		}
		return $this->history[$voucher_id];
	}

    public function get_admin_voucher_history($voucher_id,$order_id = '') {

        if(!isset($this->history[$voucher_id])) {
            $db = JFactory::getDbo();
            $query = $db->getQuery (true);
            $j2config = J2Store::config ();
            if($j2config->get('config_including_tax', 0)) {
                $query->select('ROUND(SUM(discount_amount) + SUM(discount_tax), 2) AS total');
            }else {
                $query->select('ROUND(SUM(discount_amount), 2) AS total');
            }
            $query->from('#__j2store_orderdiscounts')
                ->join('LEFT','#__j2store_orders o on #__j2store_orderdiscounts.order_id = o.order_id')
                -> where(' o.order_state_id!=5 ')
                -> where('discount_entity_id='.$db->q($voucher_id));
            if($order_id){
                $query-> where('o.order_id !='.$db->q($order_id));
            }
                $query->group('discount_entity_id');
            $query->where('discount_type ='.$db->q('voucher'));
            $db->setQuery ( $query );
            $this->history[$voucher_id] = $db->loadResult ();
        }
        return $this->history[$voucher_id];
    }

	public function is_valid() {
		try {
			$this->validate_enabled();
			$this->validate_exists();
			$this->validate_usage_limit();
			$this->validate_expiry_date();
			//allo plugins to run their own course.
			$results = J2Store::plugin()->eventWithArray('VoucherIsValid', array($this));
			if (in_array(false, $results, false)) {
				throw new Exception( JText::_('J2STORE_VOUCHER_NOT_APPLICABLE'));
			}
		} catch ( Exception $e ) {
			$this->setError($e->getMessage());
			JFactory::getApplication()->enqueueMessage($e->getMessage(),'warning');
			$this->remove_voucher();
			return false;
		}

		return true;
	}

	public function is_admin_valid($order){
        try {
            $this->validate_enabled();
            $this->validate_exists();
            $this->validate_admin_usage_limit($order);
            $this->validate_expiry_date();
            //allo plugins to run their own course.
            $results = J2Store::plugin()->eventWithArray('VoucherIsValid', array($this));
            if (in_array(false, $results, false)) {
                throw new Exception( JText::_('J2STORE_VOUCHER_NOT_APPLICABLE'));
            }
        } catch ( Exception $e ) {
            $this->setError($e->getMessage());
            JFactory::getApplication()->enqueueMessage($e->getMessage(),'warning');
            $this->remove_voucher();
            return false;
        }

        return true;
    }
	
	private function validate_enabled() {
		$params = J2Store::config();
		if($params->get('enable_voucher', 0) == 0) {
			throw new Exception( JText::_('J2STORE_VOUCHER_NOT_ENABLED') );
		}
	}
	
	/**
	 * Ensure coupon exists or throw exception
	 */
	private function validate_exists() {
		if ( ! $this->voucher) {
			throw new Exception( JText::_('J2STORE_VOUCHER_DOES_NOT_EXIST') );
		}
	}
	
	/**
	 * Ensure coupon usage limit is valid or throw exception
	 */
	private function validate_usage_limit() {
		$total = $this->get_voucher_history($this->voucher->j2store_voucher_id);
		$amount = $this->voucher->voucher_value - $total;
		if ($amount <= 0) {
			throw new Exception( JText::_('J2STORE_VOUCHER_USAGE_LIMIT_HAS_REACHED') );
		}
	
	}

	private function validate_admin_usage_limit($order){
	    $order_id = isset($order->order_id) ? $order->order_id: '';
        $total = $this->get_admin_voucher_history($this->voucher->j2store_voucher_id,$order_id);
        $amount = $this->voucher->voucher_value - $total;
        if ($amount <= 0) {
            throw new Exception( JText::_('J2STORE_VOUCHER_USAGE_LIMIT_HAS_REACHED') );
        }
    }

    /**
     * Ensure voucher date is valid or throw exception
     */
    private function validate_expiry_date() {
        $db = JFactory::getDbo();
        $nullDate = $db->getNullDate();
        $tz = JFactory::getConfig()->get('offset');
        $now = JFactory::getDate('now', $tz)->format('Y-m-d', true);
        $valid_from = JFactory::getDate($this->voucher->valid_from, $tz)->format('Y-m-d', true);
        $valid_to = JFactory::getDate($this->voucher->valid_to, $tz)->format('Y-m-d', true);

        if(
            ($this->voucher->valid_from == $nullDate || $valid_from <= $now) &&
            ($this->voucher->valid_to == $nullDate || $valid_to >= $now)
        ){
            return true;
        }else {
            throw new Exception( JText::_('J2STORE_VOUCHER_EXPIRED'));
        }
    }
	
	public function get_discount_amount($price, $cartitem, $order, $single=true)  {
        $platform = J2Store::platform();
        if ( $platform->isClient('administrator') ) {
            $voucher_history_total = $this->get_admin_voucher_history($this->voucher->j2store_voucher_id,$order->order_id);
        }else{
            $voucher_history_total = $this->get_voucher_history($this->voucher->j2store_voucher_id);
        }

		if ($voucher_history_total) {
			$amount = $this->voucher->voucher_value - $voucher_history_total;
		} else {
			$amount = $this->voucher->voucher_value;
		}
		
		/**
		 * This is the most complex discount - we need to divide the discount between rows based on their price in
		 * proportion to the subtotal. This is so rows with different tax rates get a fair discount, and so rows
		 * with no price (free) don't get discounted.
		 *
		 * Get item discount by dividing item cost by subtotal to get a %
		 */
		$params = J2Store::config ();
		$product_helper = J2Store::product ();
		$cart_item_qty = $cartitem->orderitem_quantity;

		$discount_percent = 0;		
		if ($params->get ( 'config_including_tax', 0 )) {
			$actual_price = ($cartitem->orderitem_price + $cartitem->orderitem_option_price);
			$price_for_discount = $product_helper->get_price_including_tax ( ($actual_price * $cart_item_qty), $cartitem->orderitem_taxprofile_id );
			$discount_percent = ($price_for_discount) / $order->subtotal;
		} else {
			$actual_price = ($cartitem->orderitem_price + $cartitem->orderitem_option_price);
			$price_for_discount = $product_helper->get_price_excluding_tax ( ($actual_price * $cart_item_qty), $cartitem->orderitem_taxprofile_id );
			$discount_percent = ($price_for_discount) / $order->subtotal_ex_tax;
		}
		$discount = ($amount * $discount_percent) / $cart_item_qty;		
		//allow plugins to modify the discount
		J2Store::plugin()->event('GetVoucherDiscountAmount', array($discount, $price, $cartitem, $order, $this, $single));
		return $discount;
	}

	function get_admin_discount_amount($price){
		$voucher_history_total = $this->get_voucher_history($this->voucher->j2store_voucher_id);
		if ($voucher_history_total) {
			$amount = $this->voucher->voucher_value - $voucher_history_total;
		} else {
			$amount = $this->voucher->voucher_value;
		}
		if($price > $amount){
			$discount = $amount;
		}else{
			$discount = $amount - $price;
		}
		return $discount;
	}


	public function getVoucherByCode($code) {
		$db = JFactory::getDbo ();
		$query = $db->getQuery ( true );
		$query->select ( '*' )->from ( '#__j2store_vouchers' )->where ( 'voucher_code=' . $db->q ( $code ) )->where ( 'enabled=1' );
		$db->setQuery ( $query );
		$row = $db->loadObject ();
		return $row;
	
	}
	
	public function getVoucher($code) {
		$status = true;

		$vouchers = $this->enabled(1)->voucher_code($code)->getList();		
		if(count($vouchers) > 1) {
			//duplicate vouchers found. 
			$status = false;
			return $status; 
		}
		
	
		if (isset($vouchers[0]) && $vouchers[0]) {
			$voucher = $vouchers[0];
			$db = JFactory::getDbo();
			$params = J2Store::config();

			//sum of voucher history
			/*$query = $db->getQuery(true)->select('SUM(discount_amount) as total')->from('#__j2store_orderdiscounts')
														-> where('discount_entity_id='.$db->q($voucher->j2store_voucher_id))
														->group('discount_entity_id');
			$query->where('discount_type ='.$db->q('voucher'));
			$voucher_history = $db->setQuery($query)->loadAssoc();*/
			$voucher_total = $this->get_voucher_history ( $voucher->j2store_voucher_id );
			if ($voucher_total) {
				$amount = $voucher->voucher_value - $voucher_total;
			} else {
				$amount = $voucher->voucher_value;
			}
				
			if ($amount <= 0) {
				$status = false;
			}
		} else {
			$status = false;
		}
	
		if ($status) {
			$return = array(
					'voucher_id'       => $voucher->j2store_voucher_id,
					'voucher_code'     => $voucher->voucher_code,
					'voucher_to_email'  => $voucher->email_to,
					'message'          => $voucher->email_body,
					'amount'           => $amount,
					'enabled'           => $voucher->enabled,
					'created_on'       => $voucher->created_on
			);
			
			return (object) $return;
		}

		return $status;
	}
	
	public function sendVouchers($cids) {
		$app = JFactory::getApplication ();
		$config = JFactory::getConfig ();
		$params = J2Store::config ();
		
		$sitename = $config->get ( 'sitename' );
		
		$emailHelper = J2Store::email ();
		
		$mailfrom = $config->get ( 'mailfrom' );
		$fromname = $config->get ( 'fromname' );
		
		$failed = 0;
		foreach ( $cids as $cid ) {
			$voucherTable = F0FTable::getAnInstance ( 'Voucher', 'J2StoreTable' )->getClone ();
			$voucherTable->load ( $cid );			
			
			$mailer = JFactory::getMailer ();
			$mailer->setSender ( array (
					$mailfrom,
					$fromname 
			) );
			$mailer->isHtml(true);
			$mailer->addRecipient ( $voucherTable->email_to );
			$mailer->setSubject ( $voucherTable->subject );
			// parse inline images before setting the body
			$emailHelper->processInlineImages ( $voucherTable->email_body, $mailer );
			$mailer->setBody ( $voucherTable->email_body );			
			//Allow plugins to modify
			J2Store::plugin ()->event ( 'BeforeSendVoucher', array ($voucherTable,&$mailer));			
			if($mailer->Send () !== true) {
				$this->setError(JText::sprintf('J2STORE_VOUCHERS_SENDING_FAILED_TO_RECEIPIENT', $voucherTable->email_to));
				$failed++;
			}
			
			J2Store::plugin ()->event ( 'AfterSendVoucher', array ($voucherTable,&$mailer));
			$mailer = null;
		}
		
		if($failed > 0) return false;
		
		return true;
	}
	
	public function getVoucherHistory($id) {
		
		$app = JFactory::getApplication();
		
		if($id < 1) return array();
		
		$voucher_history_model = F0FModel::getTmpInstance('Orderdiscounts', 'J2StoreModel');
		$items = $voucher_history_model->discount_entity_id($id)->discount_type('voucher')->getList();

		if(count($items)) {
			foreach($items as $k => &$item) {
				$order = F0FTable::getAnInstance('Order', 'J2StoreTable')->getClone();
				$order->load(array('order_id'=>$item->order_id));
				
				if( $order->order_state_id == 5 ) {
					// skip new order discounts
					unset($items[$k]); continue;	
				}
				$item->order = $order;
			}
		}
		return $items;
	}

	/**
	 * Clears the voucher from session
	 * */
	public function remove_voucher() {
		JFactory::getSession()->clear('voucher', 'j2store');
		$cart_model = F0FModel::getTmpInstance('Carts', 'J2StoreModel');
        $cart_table = $cart_model->getCart();
		if(isset( $cart_table->j2store_cart_id ) && !empty( $cart_table->j2store_cart_id )){
			$cart_table->cart_voucher = '';
			$cart_table->store();
			
		}

	}

	public function set_voucher($post_voucher){
        J2Store::plugin()->event('BeforeSetVoucher',array(&$post_voucher));
		$session = JFactory::getSession ();
		$session->set('voucher', $post_voucher, 'j2store');

		$cart_model = F0FModel::getTmpInstance('Carts', 'J2StoreModel');
		$cart_table = $cart_model->getCart();
		if(isset( $cart_table->j2store_cart_id ) && !empty( $cart_table->j2store_cart_id )){
			$cart_table->cart_voucher = $post_voucher;
			$cart_table->store();

		}
        J2Store::plugin()->event('AfterSetVoucher',array(&$post_voucher,&$cart_table));
	}

	public function get_voucher(){
		$cart_model = F0FModel::getTmpInstance('Carts', 'J2StoreModel');
        $cart_table = $cart_model->getCart();
		if(isset( $cart_table->cart_voucher ) && !empty( $cart_table->cart_voucher ) ){
			$session = JFactory::getSession ();
			$session->set('voucher', $cart_table->cart_voucher, 'j2store');
			$voucher_code = $cart_table->cart_voucher;

		}else{
			$session = JFactory::getSession ();
			$voucher_code = $session->get ( 'voucher', '', 'j2store' );
		}
		return $voucher_code;
	}

	public function has_voucher(){
		$session = JFactory::getSession ();
		$cart_model = F0FModel::getTmpInstance('Carts', 'J2StoreModel');
        $cart_table = $cart_model->getCart();
		if(isset( $cart_table->cart_voucher ) && !empty( $cart_table->cart_voucher ) ){
			$session->set('voucher', $cart_table->cart_voucher, 'j2store');
		}
		return $session->has ( 'voucher', 'j2store' );
	}
}