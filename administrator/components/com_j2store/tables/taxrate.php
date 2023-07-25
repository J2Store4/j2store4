<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreTableTaxrate extends F0FTable
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
		return $this->deleteChildren($oid);
	}

	private function deleteChildren($oid){
		$status =true;
		$model = F0FModel::getTmpInstance('Taxrules', 'J2StoreModel');
		$items = $model->taxrate_id($oid)->getList();
		$taxrule = F0FTable::getAnInstance('Taxrule','J2StoreTable');
		if(isset($items) && count($items)){
			foreach($items as $item ){
				if(!$taxrule->delete($item->j2store_taxrule_id)){
					$status = false;
				}
			}
		}
		return $status;
	}


}
