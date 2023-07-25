<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreTableVendor extends F0FTable
{

	public function __construct($table, $key, &$db)
	{
		$query = $db->getQuery(true)
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
		->leftJoin('#__j2store_addresses ON #__j2store_addresses.j2store_address_id = #__j2store_vendors.address_id')
		->leftJoin('#__j2store_countries ON #__j2store_countries.j2store_country_id = #__j2store_addresses.country_id')
		->leftJoin('#__j2store_zones ON #__j2store_zones.j2store_zone_id = #__j2store_addresses.zone_id');
		$this->setQueryJoin($query);
		parent::__construct($table, $key, $db);
	}


	/**
	 * The event which runs before deleting a record
	 *
	 * @param   integer  $oid  The PK value of the record to delete
	 *
	 * @return  boolean  True to allow the deletion
	 */
	protected function onBeforeDelete($oid)
	{
		//if status is true then you can continue delete
		$status =  $this->isProductAssociated($oid);

		if($status){
			//SHOULD ALSO DELETE THE ADDRESS
			// Load the post record
			$item = clone $this;
			$item->load($oid);
			if($item->address_id){
				if(F0FTable::getAnInstance('Address','J2StoreTable')->load($item->address_id)){
					if(!F0FTable::getAnInstance('Address','J2StoreTable')->delete($item->address_id))
					{
						$status = false;
					}
				}
			}
		}
		return $status;
	}

	private function isProductAssociated($oid){
        $platform = J2Store::platform();
        $fof_helper = J2Store::fof();
        $status =true;
        $model = $fof_helper->getModel('Products', 'J2StoreModel');
		$items = $model->vendor_id($oid)->getList();
		if(isset($items) && count($items)){
			// will return null and stop deleting vendor
            $product_name = '';
            foreach ($items as $key => $item){
                if($key < 5){
                    $product_name .= $item->product_name.',';
                }else{
                    break;
                }
            }
			$status =false;
            $platform->application()->enqueueMessage(JText::sprintf('J2STORE_CAN_NOT_DELETE_VENDOR_ASSIGN_TO_PRODUCT',$product_name.'etc.'),'warning');
		}
		return $status;
	}
}
