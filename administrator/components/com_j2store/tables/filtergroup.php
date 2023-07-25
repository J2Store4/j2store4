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

class J2StoreTableFiltergroup extends F0FTable
{

	/**
	 * Method to delete product optionvalues when product option is deleted
	 * (non-PHPdoc)
	 * @see F0FTable::delete()
	 * @result boolean
	 */
	protected function onBeforeDelete($oid){
		$status =true;
		//get all the children of product options
		$values = F0FModel::getTmpInstance('Filters','J2StoreModel')
		->group_id($oid)
		->getList();
		$app = JFactory::getApplication();
		if(count($values)){
			//loop the productfilters to load and delete the
			foreach($values as $pfilter){
				$filter = F0FTable::getAnInstance('Filter','J2StoreTable')->getClone();
				if(!$filter->delete($pfilter->j2store_filter_id)){
					$app->enqueueMessage(JText::_('J2STORE_FILTER_GROUP_DELETE_ERROR'),'warning');
					$status = false;
					break;
				}
			}
			return $status;
		}
	}
}