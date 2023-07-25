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

class J2StoreTableFilter extends F0FTable
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
			//make sure that any product using this filters before delete
			if(!$this->isProductAssociated($oid)){
				return false;
			}
		}
		return $status;
	}


	private function isProductAssociated($oid){
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('pf.*');
		$query->from('#__j2store_product_filters as pf');
		$query->where('pf.filter_id='.$db->q($oid));
		$db->setQuery($query);
		$result = $db->loadObjectList();
		//check any product having this product filters then return false
		return (!empty($result))  ? false  : true;
	}
}
