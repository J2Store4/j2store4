<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreTableVendoruser extends F0FTable
{

	public function __construct($table, $key, &$db)
	{

		$table ='#__users';
		$key ='id';

		$query = $db->getQuery(true)
		->select($db->qn('#__j2store_vendors').'.j2store_vendor_id')
		->select($db->qn('#__j2store_vendors').'.j2store_user_id')
		->select($db->qn('#__j2store_vendors').'.address_id')
		->select($db->qn('#__j2store_addresses').'.j2store_address_id')
		->select($db->qn('#__j2store_addresses').'.first_name')
		->select($db->qn('#__j2store_addresses').'.last_name')
		->select($db->qn('#__j2store_addresses').'.address_1')
		->select($db->qn('#__j2store_addresses').'.address_2')
		->select($db->qn('#__j2store_addresses').'.user_id')
		->select($db->qn('#__j2store_addresses').'.email')
		->select($db->qn('#__j2store_addresses').'.city')
		->select($db->qn('#__j2store_addresses').'.zip')
		->select($db->qn('#__j2store_addresses').'.zone_id')
		->select($db->qn('#__j2store_addresses').'.country_id')
		->select($db->qn('#__j2store_addresses').'.phone_1')
		->select($db->qn('#__j2store_addresses').'.phone_2')
		->select($db->qn('#__j2store_addresses').'.fax')
		->select($db->qn('#__j2store_addresses').'.type')
		->select($db->qn('#__j2store_addresses').'.company')
		->select($db->qn('#__j2store_addresses').'.tax_number')
		->select($db->qn('#__j2store_countries').'.country_name')
		->select($db->qn('#__j2store_zones').'.zone_name')
		->leftJoin('#__j2store_vendors ON #__j2store_vendors.j2store_user_id = #__users.id')
		->leftJoin('#__j2store_addresses ON #__j2store_addresses.j2store_address_id = #__j2store_vendors.address_id')
		->leftJoin('#__j2store_countries ON #__j2store_countries.j2store_country_id = #__j2store_addresses.country_id')
		->leftJoin('#__j2store_zones ON #__j2store_zones.j2store_zone_id = #__j2store_addresses.zone_id');
		$this->setQueryJoin($query);
		parent::__construct($table, $key, $db);
	}




}
