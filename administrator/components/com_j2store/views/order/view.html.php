<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class J2StoreViewOrder extends F0FViewHtml
{
	/**
	 * Executes before rendering the page for the Add task.
	 *
	 * @param   string  $tpl  Subtemplate to use
	 *
	 * @return  boolean  Return true to allow rendering of the page
	 */
	protected function onAdd($tpl = null)
	{
		$id = $this->input->getInt('id');

		$this->order = F0FTable::getAnInstance('Order' ,'J2StoreTable');
		$this->order->load($id);
		$this->item = $this->order;
		$this->fieldClass = J2Store::getSelectableBase();

		$this->params = J2Store::config();
		$this->currency = J2Store::currency();
		$this->taxes = $this->order->getOrderTaxrates();
		$this->shipping = $this->order->getOrderShippingRate();
		$this->coupons = $this->order->getOrderCoupons();
		$this->vouchers = $this->order->getOrderVouchers();
		$this->orderinfo = $this->order->getOrderInformation();
		$this->orderhistory = $this->order->getOrderHistory();
		parent::onAdd();
	}


}