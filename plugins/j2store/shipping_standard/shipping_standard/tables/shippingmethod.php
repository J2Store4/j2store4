<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2StoreTableShippingMethod extends F0FTable
{

	public function __construct($table, $key, &$db, $config = array())
	{
		parent::__construct($table, $key, $db, $config = array());
	}

    function check()
    {
    	if(empty($this->shipping_method_name)) {
    		throw new Exception(JText::_('J2STORE_SHIPPING_METHOD_NAME_REQUIRED'));
    		return false;
    	}
        if ((float) $this->subtotal_maximum == (float) '0.00000')
        {
            $this->subtotal_maximum = '-1';
        }
        return true;
    }

}
