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

class J2StoreTableOrderItem extends F0FTable
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
		//make sure that any product using this options before delete the
		$this->deleteChildren($oid);
		return true;
	}

	function deleteChildren($oid){		
		$query = $this->_db->getQuery(true)->delete('#__j2store_orderitemattributes')->where('orderitem_id = '.$this->_db->q($oid));		
		try {
			$this->_db->setQuery($query)->execute();
		}catch (Exception $e) {			
			//do nothing. Because this is not harmful even if it fails.			
		}
		return true;
	}
}