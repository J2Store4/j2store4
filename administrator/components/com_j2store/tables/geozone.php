<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
class J2StoreTableGeozone extends F0FTable
{
	public function check()
	{
		$result = true;
		// Do we have a geozone name?
		if (!$this->geozone_name)
		{
			$result = false;
			$this->setError(JText::_('J2STORE_GEOZONE_NAME_REQUIRED'));
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
		//if status is true then you can continue delete
		return $this->deleteChildren($oid);
	}

	public function deleteChildren($oid){
		$status = true;
		//should delete geozonerules
		$geozonerules = F0FModel::getTmpInstance('GeozoneRules','J2StoreModel')->geozone_id($oid)->getList();
		$geozonerule = F0FTable::getAnInstance('GeozoneRule','J2StoreTable');
		if(isset($geozonerules) && count($geozonerules)){
			foreach($geozonerules as $grule){
				if(!$geozonerule->delete($grule->j2store_geozonerule_id)){
					$status = false;
				}
			}
		}
		if($status){
			// delete taxrate and also related taxprofile rules...
			$taxrates = F0FModel::getTmpInstance('Taxrates','J2StoreModel')->geozone_id($oid)->getList();
			$taxrate = F0FTable::getAnInstance('Taxrate','J2StoreTable');
			if(isset($taxrates) && count($taxrates)){
				foreach($taxrates as $trate){
					if(!$taxrate->delete($trate->j2store_taxrate_id)){
						$status = false;
					}
				}
			}
		}
		return $status;
	}


}
