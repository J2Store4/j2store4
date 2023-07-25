<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
if(!defined('DS')){
	define('DS',DIRECTORY_SEPARATOR);
}

if (!defined('F0F_INCLUDED'))
{
	include_once JPATH_LIBRARIES . '/f0f/include.php';
}
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/shipping.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/toolbar.php');

class plgJ2StoreShipping_Free extends J2StoreShippingPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
	var $_element   = 'shipping_free';
	var $_order;

    /**
     * Overriding
     *
     * @param $row
     * @return string
     */
	function onJ2StoreGetShippingView( $row )
	{
		if (!$this->_isMe($row))
		{
			return null;
		}
		return '';
	}

    function onJ2StoreIsJ2Store4($element){
        if (!$this->_isMe($element)) {
            return null;
        }
        return true;
    }

	function onJ2StoreGetShippingOptions($element, $order)
	{
		// Check if this is the right plugin
		if (!$this->_isMe($element))
		{
			return null;
		}
		$found = true;
		$order->setAddress();
		// $this->_order = $order;
		$geozones = $this->params->get('geozones');
		//return true if we have empty geozones
        if(!empty($geozones))
        {
        	//incase All Geozone is selected
        	if(in_array('*',array_values($geozones))){
        		$found = true;
        	}else{
	        	$found = false;
				if(!is_array($geozones)){
	          		$geozones = explode(',', $geozones);
				}
	          	$orderGeoZones = $order->getShippingGeoZones();
	          	//loop to see if we have at least one geozone assigned
	          	foreach( $orderGeoZones as $orderGeoZone )
	          	{
	          		if(in_array($orderGeoZone->geozone_id, $geozones))
	          		{
	          			$found = true;
	          			break;
	          		}
	          	}
        	}
        }
        if($this->params->get('requires_coupon', 0)) {
        	if($order->has_free_shipping() == false) {
        		$found = false;        		 
        	} 
        }
		return $found;
	}

    /**
     * Method to get shipping rates
     * @param $element
     * @param $order
     * @return array|null $results
     */
    function onJ2StoreGetShippingRates($element, $order)
    {
        // Check if this is the right plugin
        if (!$this->_isMe($element))
        {
            return null;
        }

        $vars = array();
        //set the address
        $order->setAddress();
        $subtotal = $order->order_subtotal;

        $min_subtotal = (float) $this->params->get('min_subtotal', 0);
        $max_subtotal = (float) $this->params->get('max_subtotal', -1);

        $status = true;
        $check_shipping_product = $this->params->get('check_shipping_product', 1);
        if($check_shipping_product && $status){
            $products = $order->getItems();
            $subtotal = 0;
            $status = false;
            foreach($products as $product) {
                if (isset($product->cartitem->shipping) && $product->cartitem->shipping && isset($product->cartitem->pricing->price)) {
                    $subtotal += $product->cartitem->pricing->price * $product->cartitem->product_qty;
                    $status = true;
                }
            }
        }
        if($min_subtotal > 0 && $min_subtotal > $subtotal) {
            $status = false;
        }
        if($max_subtotal > 0 && $subtotal > $max_subtotal ) {
            $status = false;
        }



        if(!$status) return $vars;

        $geozones_taxes = array();
        $params_geozones = $this->params->get('geozones');
        $i=0;
        $name = addslashes(JText::_($this->params->get('display_name', $this->_element)));
        $vars[$i]['element'] = $this->_element;
        $vars[$i]['name'] = $name;
        $vars[$i]['code'] = '';
        $vars[$i]['price'] = 0;
        $vars[$i]['tax'] = 0;
        $vars[$i]['extra'] = 0;
        $vars[$i]['total'] = 0;

        return $vars;
    }

	/**
	 * Method to exclude free shipping based on conditions
	 * @param $order
	 * @param $rates
	 *
	 */
	function onJ2StoreAfterGetShippingRate($order,&$rates){

		$shipping_array = $this->params->get('shipping_method',array());
		$user_group = $this->params->get('usergroup',array());

		$user = JFactory::getUser ();
		$exclude = false;

		// Exclusion based on user groups

		if($user_group){
			foreach ($user_group as $group){
				if(in_array ( $group, $user->groups )){
					$exclude = true;
				}
			}
		}

		//get
		$methods_with_children = array(
			'shipping_standard',
			'shipping_postcode',
			'shipping_additional',
			'shipping_incremental',
			'shipping_flatrate_advanced'
		);

		// exclude based on shipping
		if(!$exclude){
			foreach ($shipping_array as $shipping){

				foreach ($rates as $rate){
					if($rate['element'] == $shipping){
						$exclude = true;
						break;
					}

					if(in_array($rate['element'], $methods_with_children)) {
						//we have to compare the name
						if($rate['name'] == $shipping) {
							$exclude = true;
							//exclusion found. Break the loop
							break;
						}
					}
				}
			}
		}

		if($exclude){
			//exclusion found. Unset free shipping from the shipping rates array.
			foreach ($rates as $key => $rate){
				if($rate['element'] == $this->_element){
					unset( $rates[$key] );
					break;
				}
			}
		}

	}
}