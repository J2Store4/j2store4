<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;

class J2StoreTableMyprofile extends F0FTable
{
	public function __construct($table, $key, &$db)
	{
		$table = '#__j2store_orders';
		$key = 'j2store_order_id';
		parent::__construct($table, $key, $db);
	}
}
