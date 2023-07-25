<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreModelManufacturers extends F0FModel {

	protected function onProcessList(&$resultArray)
	{
		foreach($resultArray as &$res){

			$res->name = $res->first_name .' ' .$res->last_name;
		}
	}
	public function buildQuery($overrideLimits = false)
	{
		$db = JFactory::getDbo();
		$query  = $db->getQuery(true)
		->select('#__j2store_manufacturers.*')->from("#__j2store_manufacturers as #__j2store_manufacturers")
		->select($db->qn('#__j2store_addresses').'.j2store_address_id')
		->select($db->qn('#__j2store_addresses').'.first_name')
		->select($db->qn('#__j2store_addresses').'.last_name')
		->select($db->qn('#__j2store_addresses').'.address_1')
		->select($db->qn('#__j2store_addresses').'.address_2')
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
		->leftJoin('#__j2store_addresses ON #__j2store_addresses.j2store_address_id = #__j2store_manufacturers.address_id');
		$this->buildOrderbyQuery($query);
		return $query;
	}

	public function buildOrderbyQuery(&$query){
		$app = JFactory::getApplication();
        $db = JFactory::getDbo();

        $filter_order_Dir = $this->getState('filter_order_Dir',$app->input->getString('filter_order_Dir','asc'));
        $filter_order = $this->getState('filter_order',$app->input->getString('filter_order','company'));
        $search = $app->input->getString('company','');
        if(!in_array(strtolower($filter_order_Dir),array('asc','desc'))){
            $filter_order_Dir = 'desc';
        }
		if($filter_order =='j2store_manufacturer_id' || $filter_order =='enabled' || $filter_order =='ordering'){
			//$query->order($db->q('#__j2store_manufacturers.'.$filter_order).' '.$db->q($filter_order_Dir));
            $query->order($db->qn('#__j2store_manufacturers').'.'.$db->qn($filter_order).' '.$filter_order_Dir);
		}elseif(in_array($filter_order ,array('company' ,'city'))){
            $query->order($db->qn('#__j2store_addresses').'.'.$db->qn($filter_order).' '.$filter_order_Dir);
			//$query->order('#__j2store_addresses.'.$filter_order.' '.$filter_order_Dir);
		}
        $enabled = $this->getState('filter_enabled',null);
        if(is_null($enabled)){
            $enabled = $this->getState('enabled',null);
        }
        if(!is_null($enabled)){
            $query->where('#__j2store_manufacturers.enabled = '.$db->q($enabled));
        }
        if ($search){
            $query->where('#__j2store_addresses.company LIKE '.$db->q('%'.$search.'%'));
        }
	}

	public function onBeforeSave(&$data, &$table){
		$app = JFactory::getApplication();
		$addressTable = F0FTable::getInstance('Address','J2storeTable');
		$addressTable->load($data['address_id']);
		$addressTable->save($data);
		$data['address_id'] = $addressTable->j2store_address_id;
        $data['brand_desc_id'] = isset($data['brand_desc_id']) && !empty($data['brand_desc_id']) && $data['brand_desc_id'] > 0  ? $data['brand_desc_id'] : 0 ;
		return true;
	}

	public function getManufacturersList($brand_ids){

		$db = JFactory::getDbo();
		$query = $this->buildQuery($overrideLimits = false);
		$query->where('#__j2store_manufacturers.j2store_manufacturer_id IN ('. $brand_ids. ')');
		$db->setQuery($query);

		$results =  $db->loadObjectList();

		return $results;
	}

}
