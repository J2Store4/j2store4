<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
JLoader::register( 'J2StoreTable', JPATH_ADMINISTRATOR.'/components/com_j2store/tables/_base.php' );
class J2StoreTableShippingRate extends F0FTable {
	public function __construct($table, $key, &$db, $config = array())
	{
		parent::__construct($table, $key, $db, $config = array());
	}

	/**
	 * Checks row for data integrity.
	 * Assumes working dates have been converted to local time for display,
	 * so will always convert working dates to GMT
	 *
	 * @return boolean
	 */
	function check()
	{
       // if (empty($this->j2store_shippingmethod_id))
       // {
       //     $this->setError( JText::_('J2STORE_SHIPPING_METHOD_REQUIRED') );
       //     return false;
       // }
		return true;
	}
}