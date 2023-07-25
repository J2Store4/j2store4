<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreModelVendors extends F0FModel {

	protected function onProcessList(&$resultArray)
	{
		foreach($resultArray as &$res){

			$res->name = $res->first_name .' ' .$res->last_name;
		}
	}
	public function buildQuery($overrideLimits = false)
	{
		$db = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$query->select('#__j2store_vendors.*')->from("#__j2store_vendors as #__j2store_vendors");
		$query->select($db->qn('#__j2store_addresses').'.j2store_address_id')
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
		->leftJoin('#__j2store_addresses ON #__j2store_addresses.j2store_address_id = #__j2store_vendors.address_id')
		->leftJoin('#__j2store_countries ON #__j2store_countries.j2store_country_id = #__j2store_addresses.country_id')
		->leftJoin('#__j2store_zones ON #__j2store_zones.j2store_zone_id = #__j2store_addresses.zone_id');
		$this->buildOrderbyQuery($query);
		return $query;
	}

	public function buildOrderbyQuery(&$query){
		$state = $this->getState();
		$app = JFactory::getApplication();
        $db = JFactory::getDbo();
		$filter_order_Dir = $app->input->getString('filter_order_Dir','asc');
		$filter_order = $app->input->getString('filter_order','j2store_vendor_id');
        $search = $app->input->getString('first_name','');
        if(!in_array(strtolower($filter_order_Dir),array('asc','desc'))){
            $filter_order_Dir = 'desc';
        }
		//check filter
		if($filter_order =='j2store_vendor_id' || $filter_order =='enabled' || $filter_order =='ordering' ){
			$query->order('#__j2store_vendors.'.$filter_order.' '.$filter_order_Dir);
		}else if($filter_order =='country_name' ){
			$query->order('#__j2store_countries.'.$filter_order.' '.$filter_order_Dir);
		}else if($filter_order =='zone_name' ){
			$query->order('#__j2store_zones.'.$filter_order.' '.$filter_order_Dir);
		}else if($filter_order == 'first_name'){
			$query->order('#__j2store_addresses.'.$filter_order.' '.$filter_order_Dir);
		}
        $enabled = $this->getState('enabled',null);
        if(!is_null($enabled)){
            $query->where('#__j2store_vendors.enabled = '.$db->q($enabled));
        }
        if ($search){
            $query->where('#__j2store_addresses.first_name LIKE '.$db->q('%'.$search.'%'));
        }
	}

	public function onBeforeSave(&$data, &$table){
		$addressTable = F0FTable::getInstance('Address','J2storeTable')->getClone();
		$addressTable->load($data['address_id']);
		$addressTable->save($data);
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->qn(array('address_id','j2store_user_id')))
            ->from($db->qn('#__j2store_vendors'))
            ->where($db->qn('j2store_vendor_id').'!='. $db->quote($data['j2store_vendor_id']))
            ->where($db->qn('j2store_user_id').'='. $db->quote($data['user_id']));
            $db->setQuery($query);
            $user_id = $db->loadResult();
        $status = false;
        if($addressTable->j2store_address_id > 0 && empty($user_id)){
            $data['address_id'] = $addressTable->j2store_address_id;
            $data['j2store_user_id'] = $addressTable->user_id;
            $status = true;
        }else{
            $errors = $addressTable->getErrors();
            foreach ($errors as $error){
                if(isset($error) && !empty($error)){
                    J2Store::platform()->application()->enqueueMessage($error,'error');
                }else{
                    $this->setError(JText::_("J2STORE_USER_ID_ALREADY_USED"));
                }
            }
        }
        return $status;
	}

}
