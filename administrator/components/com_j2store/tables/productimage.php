<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;

class J2StoreTableProductimage extends F0FTable
{
	public function __construct($table, $key, &$db, $config=array())
	{
		$table = "#__j2store_productimages";
		//important
		$key ="j2store_productimage_id";
		parent::__construct($table, $key, $db, $config);
	}
}