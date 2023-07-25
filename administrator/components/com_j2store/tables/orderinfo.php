<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2storeTableOrderinfo extends F0FTable
{
  public function __construct($table, $key, &$db)
  {
 	 	$query = JFactory::getDbo()->getQuery(true);
  		$query->leftJoin('#__j2store_countries as billingcountry ON billingcountry.j2store_country_id = #__j2store_orderinfos.billing_country_id');
  		$query->select('billingcountry.country_name as billing_country_name');
  		
  		$query->leftJoin('#__j2store_countries as shippingcountry ON shippingcountry.j2store_country_id = #__j2store_orderinfos.shipping_country_id');
  		$query->select('shippingcountry.country_name as shipping_country_name');
  		
  		$query->leftJoin('#__j2store_zones as billingzone ON billingzone.j2store_zone_id = #__j2store_orderinfos.billing_zone_id');
  		$query->select('billingzone.zone_name as billing_zone_name');
  		
  		$query->leftJoin('#__j2store_zones as shippingzone ON shippingzone.j2store_zone_id = #__j2store_orderinfos.shipping_zone_id');
  		$query->select('shippingzone.zone_name as shipping_zone_name');
  		
  	$this->setQueryJoin($query);
  	 
	parent::__construct($table, $key, $db);

  }  

}