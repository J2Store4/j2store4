<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
class J2StoreTableCustomer extends F0FTable
{
	protected $is_vat_exempt = false;
	
	public function __construct($table, $key, &$db, $config=array())
	{
		$table ='#__j2store_addresses';
		$key ='j2store_address_id';

		parent::__construct($table, $key, $db, $config);
		$session = JFactory::getSession();
		if($session->has('is_vat_valid', 'j2store')) {
			$vat_result = $session->get('is_vat_valid', 'invalid', 'j2store');			
			if(strtolower($vat_result == 'valid')) {
				$this->set_is_vat_exempt(1);
			}
		}
		
	}
	
	
	public function set_is_vat_exempt($exempt) {
		$this->is_vat_exempt = $exempt;
	}
	
	public function is_vat_exempt() {
		return $this->is_vat_exempt;
	}
}