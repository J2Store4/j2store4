<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreModelStoreprofiles extends F0FModel {

	protected function onBeforeSave(&$data, &$table)
	{
		$country = F0FModel::getTmpInstance('Countries','J2StoreModel')->getItem($data['country_id']);
		$data['country_name'] = $country->country_name;
		$zone = F0FModel::getTmpInstance('zones','J2StoreModel')->getItem($data['zone_id']);
		$data['zone_name'] = $zone->zone_name;
		return true;
	}


}

