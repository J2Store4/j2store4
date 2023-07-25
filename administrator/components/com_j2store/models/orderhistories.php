<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();
class J2StoreModelOrderhistories extends F0FModel {

	
	public function setOrderHistory($order, $comment = '', $notify=0) {
		
		if(!isset($order->order_id)) return;
		
		if(empty($comment)) {
			$comment = JText::_('J2STORE_ORDER_UPDATED'); 	
		}
		
		$history = $this->getTable();
		$history->reset();
		$history->j2store_orderhistory_id = 0;
		$values = array();
		$values['j2store_orderhistory_id'] = null;
		$values['order_id'] = $order->order_id;
		$values['order_state_id'] = $order->order_state_id;
		$values['comment'] = $comment;
		$values['notify_customer'] = $notify;
		$history->save($values);
		
		return true;
	}
}