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

class J2StoreTableProductoption extends F0FTable
{
	public function __construct($table, $key, &$db, $config=array())
	{
		$table = "#__j2store_product_options";
		$query = $db->getQuery(true)
			->select($db->qn('#__j2store_options').'.option_name', $db->qn('#__j2store_options').'.type')
			->leftJoin('#__j2store_options ON #__j2store_options.j2store_option_id = #__j2store_product_options.option_id');
			$this->setQueryJoin($query);
		parent::__construct($table, $key, $db, $config);
	}

	/**
	 * Method to delete product optionvalues when product option is deleted
	 * (non-PHPdoc)
	 * @see F0FTable::delete()
	 * @result boolean
	 */
	public function delete($oid = null)
	 {
	 		$status =true;

	 		//get all the children of product options
			$productoptions = F0FModel::getTmpInstance('Productoptionvalues','J2StoreModel')
							->productoption_id($oid)
							->getList();
			if(isset($productoptions) && !empty($productoptions)){
				//loop the productoptions to load and delete the
				foreach($productoptions as $poption){
						$productoption = F0FTable::getAnInstance('Productoptionvalue','J2StoreTable');
						$productoption->load($poption->j2store_product_optionvalue_id);
						if(!$productoption->delete($poption->j2store_product_optionvalue_id)){
							$status = false;
						}
				}
			}

		$status = parent::delete($oid);
		return $status;
	}

}
