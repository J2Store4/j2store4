<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/

// No direct access
defined('_JEXEC') or die;

class J2StoreTableTaxprofile extends F0FTable
{
	public function check()
	{
		$result = true;

		// Do we have a taxprofile name?
		if (!$this->taxprofile_name)
		{
			$result = false;
			$this->setError(JText::_('J2STORE_TAXPROFILE_NAME_REQUIRED'));
		}
		return $result;
	}

	/**
	 * The event which runs before deleting a record
	 *
	 * @param   integer  $oid  The PK value of the record to delete
	 *
	 * @return  boolean  True to allow the deletion
	 */
	protected function onBeforeDelete($oid)
	{
		$status = true;
		//check is taxprofile associated with products
		$status =  $this->isProductAssociated($oid);		
		return $status;
	}

	/**
	 * Method to check any product is associated with this taxprofile id
	 * @param int $oid
	 * @return boolean
	 */
	private function isProductAssociated($oid){
		$status =true;
		$model = F0FModel::getTmpInstance('Products', 'J2StoreModel');
		$items = $model->taxprofile_id($oid)->getList();
		if(isset($items) && count($items)){
			$status =false;
			JFactory::getApplication()->enqueueMessage(JText::_('J2STORE_TAXPROFILE_ASSOCIATED_WITH_PRODUCTS'),'warning');
		}
		return $status;
	}

}
