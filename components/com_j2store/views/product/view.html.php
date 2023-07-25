<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;

class J2StoreViewProduct extends F0FViewHtml
{	
	
	protected function onRead($tpl = null) {
		JFactory::getLanguage ()->load ('com_j2store', JPATH_ADMINISTRATOR);
		/* JRequest::setVar('hidemainmenu', true);
		$model = $this->getModel('Products');
		$product_id = $this->input->getInt('id', 0);
		if(!$product_id) return false;
		
		$product = F0FTable::getAnInstance('Product', 'J2StoreTable')->get_product_by_id($product_id);
		if($product === false) return false;
				
		$model->getProduct($product);
		$this->product = $product;
		//process tax
		$this->taxModel = F0FModel::getTmpInstance('TaxProfiles', 'J2StoreModel');
		$this->params = J2Store::config();
 */
		$this->params = J2Store::config();
		return true;
	}

}

