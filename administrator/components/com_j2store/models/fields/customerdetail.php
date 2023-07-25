<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
/* class JFormFieldFieldtypes extends JFormField */

class JFormFieldCustomerdetail extends F0FFormFieldText
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Customerdetail';

	public function getRepeatable()
	{
		$orderinfo = F0FTable::getAnInstance('Orderinfo','J2StoreTable');
		$orderinfo->load(array('order_id' => $this->item->order_id));
		$customer_name = $orderinfo->billing_first_name .' '. $orderinfo->billing_last_name;
		$html ='';
		$html .= $customer_name;
		$html .='<br>';
		$html .='<small>';
		$html .=$this->item->user_email;
		$html .='</small>';
		return $html;
	}
}
