<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined ( '_JEXEC' ) or die ();
class J2StoreTableCart extends F0FTable {
	protected function onBeforeDelete($oid) {
		$status =  true;
		// load cart items
		$query = $this->_db->getQuery (true);
		$query->select ( '*' )->from ( '#__j2store_cartitems' )->where ( 'cart_id = ' . $this->_db->q( ( int ) $oid ));
		$this->_db->setQuery ( $query );

 		try {
 			$items = $this->_db->loadObjectList ();
			// foreach orderitem
			foreach ( $items as $item ) {
				// remove from user's cart
				if(!F0FTable::getAnInstance ( 'Cartitem', 'J2StoreTable' )->delete ( $item->j2store_cartitem_id )){
					//F0FTable::getAnInstance ( 'Cartitem', 'J2StoreTable' )->getError();
					break;
					return false;
				}else{
					J2Store::plugin ()->event ( 'RemoveCartItem', array (
							$item
					) );

					$status =  true;
				}
			 }

		 } catch ( Exception $e ) {
			// do nothing
		}
		return $status;
	}

	protected function onBeforeStore($updateNulls) {
		if(parent::onBeforeStore($updateNulls)) {
			$tz = JFactory::getConfig()->get('offset');
			$date = F0FPlatform::getInstance()->getDate('now', $tz, false);
			if(!$this->j2store_cart_id) {

				//get the IP of the customer
				$this->customer_ip = $_SERVER['REMOTE_ADDR'];
				jimport('joomla.environment.browser');
				$browser = JBrowser::getInstance();

				$this->cart_browser = $browser->getBrowser();
				$analytics = array();
				$analytics['is_mobile'] = $browser->isMobile();
				$this->cart_analytics = json_encode($analytics);

				$this->created_on = $date->toSql(true);
                $ref_model = $this;
				J2Store::plugin()->event('BeforeStoreCart',array(&$ref_model));
			}else{
				$this->modified_on = $date->toSql(true);
			}
			return true;
		}
		return false;
	}
}