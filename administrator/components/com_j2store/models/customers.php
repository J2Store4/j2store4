<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();
class J2StoreModelCustomers extends F0FModel {


	public function buildQuery($overrideLimits=false) {
		$query = parent::buildQuery($overrideLimits);
		//$query->select($this->_db->qn('#__j2store_orders.user_email'));
		$query->select('CONCAT(#__j2store_addresses.first_name," ",#__j2store_addresses.last_name )AS customer_name');
		//$query->join('INNER','#__j2store_orders ON  #__j2store_addresses.email = #__j2store_orders.user_email');
		$query->select('#__j2store_countries.country_name as country_name');
		$query->join('LEFT OUTER', '#__j2store_countries ON #__j2store_addresses.country_id = #__j2store_countries.j2store_country_id');
		$query->select('#__j2store_zones.zone_name as zone_name');
		$query->join('LEFT OUTER', '#__j2store_zones ON #__j2store_addresses.zone_id = #__j2store_zones.j2store_zone_id');
		$query->group('#__j2store_addresses.email');
		//$subquery = $this->_db->getQuery(true)->select('COUNT(*)')->from('#__j2store_orders AS o')->where('o.user_email=#__j2store_orders.user_email')->where('o.order_type = \'normal\'');
		//$query->select('('.$subquery.') AS totalorders');
		$name =  $this->getState('customer_name');
		if(!empty($name)){
               $query->where(
                   '('.$this->_db->qn('#__j2store_addresses').'.'.$this->_db->qn('first_name').' LIKE '.$this->_db->q('%'.$name.'%').' OR '.
                   ' CONCAT ('.$this->_db->qn('#__j2store_addresses').'.'.$this->_db->qn('first_name').', " ", '.$this->_db->qn('#__j2store_addresses').'.'.$this->_db->qn('last_name').') LIKE '.$this->_db->q($name).'OR '.
                   $this->_db->qn('#__j2store_addresses').'.'.$this->_db->qn('last_name').' LIKE '.$this->_db->q('%'.$name.'%').')'
               );
		}
		$country_name = $this->getState('country_name');
		if(!empty($country_name)){
            $query->where(
                $this->_db->qn('#__j2store_countries').'.'.$this->_db->qn('country_name').' LIKE '.$this->_db->q('%'.$country_name.'%')
            );
        }
        $query->where('#__j2store_addresses.email != ""');
        $query->where('#__j2store_addresses.first_name != ""');
		return $query;
	}


	public function getAddressesByemail($email){
		$db = JFactory::getDbo();
		$query = parent::buildQuery($overrideLimits=false);
		$query->where($this->_db->qn('#__j2store_addresses.email').' LIKE '.$db->q('%'.$email.'%'));
		$db->setQuery($query);
		return $db->loadObject();
	}


	public function savenewEmail(){
		$app = JFactory::getApplication();
		$data = $app->input->getArray($_POST);
		$email = $app->input->getString('email');
		$new_email =  $app->input->getString('new_email');
		j2STORE::user();
		$status = true;
		if(!$this->updateAlladdressesByemail($email,$new_email)){
			$status = false;
		}

		if($status){
			if(!$this->updateOrdersbyEmail($email,$new_email)){
				$status = false;
			}
		}

		if($status){
			if(!$this->updateOrderDownloadsbyEmail($email ,$new_email)){
				$status = false;
			}
		}

		if($status){
			if(!$this->updateOrderCouponsbyEmail($email ,$new_email)){
				$status = false;
			}
		}

		if($status){
			if(!$this->updateUsersbyEmail($email ,$new_email)){
				$status = false;
			}
		}
		return $status;
	}

	public function updateAlladdressesByemail($email ,$new_email){
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		// Fields to update.
		$fields = array($db->quoteName('email') . ' = ' . $db->quote($new_email));

		// Conditions for which records should be updated.
		$conditions = array(
				$db->quoteName('email') . ' = ' . $db->quote($email)
		);
		$query->update($db->quoteName('#__j2store_addresses'))->set($fields)->where($conditions);
		$db->setQuery($query);
		return $db->execute();
	}


	function updateOrdersbyEmail($email ,$new_email) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Fields to update.
		$fields = array($db->quoteName('user_email') . ' = ' . $db->quote($new_email));

		// Conditions for which records should be updated.
		$conditions = array(
				$db->quoteName('user_email') . ' = ' . $db->quote($email)
		);
		$query->update($db->quoteName('#__j2store_orders'))->set($fields)->where($conditions);
		$db->setQuery($query);
		return $db->execute();
	}

	function updateOrderDownloadsbyEmail($email ,$new_email) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Fields to update.
		$fields = array($db->quoteName('user_email') . ' = ' . $db->quote($new_email));
		// Conditions for which records should be updated.
		$conditions = array(
				$db->quoteName('user_email') . ' = ' . $db->quote($email)
		);

		$query->update($db->quoteName('#__j2store_orderdownloads'))->set($fields)->where($conditions);
		$db->setQuery($query);
		return $db->execute();
	}


	function updateOrderCouponsbyEmail($email ,$new_email) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Fields to update.
		$fields = array($db->quoteName('discount_customer_email') . ' = ' . $db->quote($new_email));
		// Conditions for which records should be updated.
		$conditions = array(
				$db->quoteName('discount_customer_email') . ' = ' . $db->quote($email)
		);
		$query->update($db->quoteName('#__j2store_orderdiscounts'))->set($fields)->where($conditions);
		$db->setQuery($query);
		return $db->execute();
	}

	function updateUsersbyEmail($email ,$new_email) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Fields to update.
		$fields = array($db->quoteName('email') . ' = ' . $db->quote($new_email));
		// Conditions for which records should be updated.
		$conditions = array(
				$db->quoteName('email') . ' = ' . $db->quote($email)
		);
		$query->update($db->quoteName('#__users'))->set($fields)->where($conditions);
		$db->setQuery($query);
		return $db->execute();
	}
}