<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2storeTableMetafield extends F0FTable
{
	public function __construct($table, $key, &$db)
	{
		$table = "#__j2store_metafields";
		$key = "id";
		parent::__construct($table, $key, $db);

	}


}