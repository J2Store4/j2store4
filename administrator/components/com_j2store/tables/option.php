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

class J2StoreTableOption extends F0FTable
{

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
		// Load the post record
		$item = clone $this;
		$item->load($oid);

		if($oid){
			//make sure that any product using this options before delete the
			if(!$this->isProductAssociated($oid)){
				return false;
			}else{
				$this->deleteChildren($oid);
			}
		}
		return $status;
	}

	private function isProductAssociated($oid){
		$status =true;
		$model = F0FModel::getTmpInstance('ProductOptions', 'J2StoreModel');
		$items = $model->option_id($oid)->getList();
		if(isset($items) && count($items)){
			$status =false;
		}
		return $status;
	}

	private function deleteChildren($oid){
		$model = F0FModel::getTmpInstance('OptionValues', 'J2StoreModel');
		$db = $this->getDbo();
		$query = $db->getQuery(true)->select('j2store_optionvalue_id')
		->from('#__j2store_optionvalues')
		->where('option_id='.$db->q($oid));
		$db->setQuery($query);
		$idlist= $db->loadColumn();
		if(count($idlist)) {
			$model->setIds($idlist);
			$model->delete();
		}
		return true;
	}
	public function check()
	{
		$result = true;

		// Do we have a unique name?
		if (!$this->option_unique_name)
		{
			$result = false;
			$this->setError(JText::_('J2STORE_OPTION_UNIQUE_NAME_REQUIRED'));
		}

		// Do we have a option  name?
		if (!$this->option_name)
		{
			$result = false;
			$this->setError(JText::_('J2STORE_OPTION_NAME_REQUIRED'));
		}
		return $result;
	}

}
