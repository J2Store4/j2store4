<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class J2StoreViewCustomer extends F0FViewHtml
{

	 public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$this->email = $app->input->getString('email_id');
		$this->item = F0FModel::getTmpInstance('Customers','J2StoreModel')->getAddressesByemail($this->email);
		$task = $app->input->getString('task');
		$this->currency = J2Store::currency();
		if($task == 'viewOrder' && $this->email){
			$this->addresses = F0FModel::getTmpInstance('Addresses','J2StoreModel')->email($this->email)->getList();
			$this->orders =F0FModel::getTmpInstance('Orders','J2StoreModel')->order_type('normal')->user_email($this->email)->getList();
		}
		$this->table_fields = F0FModel::getTmpInstance('Customfields','J2StoreModel')->getList();
		JToolbarHelper::title(JTEXT::_('J2STORE_CUSTOMER_VIEW'));
		JToolbarHelper::cancel();
		return parent::display($tpl);
		//return true;
	}

}