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

class J2StoreTableProductquantity extends F0FTable
{

	public function __construct($table, $key, &$db)
	{
		parent::__construct($table, $key, $db);
	}

	public function check()
	{
		$return  = true;
		if(empty($this->variant_id)){
			$this->setError(JText::_('J2STORE_PRODUCT_VARIANT_ID_MISSING'));
			$return = false;
		}

		return $return;
	}
}
