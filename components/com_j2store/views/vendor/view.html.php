<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreViewVendors extends F0FViewHtml {
	 protected function onAdd($tpl=null){
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$model = $this->getModel('Vendors');
		$this->item = $model->getItem();

/* 	 	$this->item =  F0FTable::getAnInstance('Vendor','J2StoreTable');

		$this->item->load(array('j2store_user_id'=>$user->id ,'enabled'=>1));

		$this->products = F0FModel::getTmpInstance('Products' ,'J2StoreModel')
					     ->vendor_id($this->item->j2store_vendor_id)
		                 ->enabled(1)
		     	            ->getList(); */

		if(!isset($this->item->j2store_user_id) || $this->item->j2store_user_id != $user->id){
				$app->redirect('index.php',JText::_('J2STORE_ACCESS_FORBIDDEN'),'warning');
			}

		return true;
	}
}