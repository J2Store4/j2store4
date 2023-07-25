<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreTableCountry extends F0FTable
{
	public function check()
	{
		$result = true;

		if(!$this->country_name)
		{
			$this->setError(JText::_('COM_J2STORE_COUNTRY_MISSING'));
			$result = false;
		}
		if(!$this->country_isocode_2)
		{
			$this->setError(JText::_('COM_J2STORE_COUNTRY_ISOCODE2_MISSING'));
			$result = false;
		}
		if(!$this->country_isocode_3)
		{
			$this->setError(JText::_('COM_J2STORE_COUNTRY_ISOCODE3_MISSING'));
			$result = false;
		}

		return parent::check() && $result;
	}
}	
