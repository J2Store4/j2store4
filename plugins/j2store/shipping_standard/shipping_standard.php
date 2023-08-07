<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

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

class plgJ2StoreShipping_Standard extends J2StoreShippingPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
    var $_element   = 'shipping_standard';
    function onJ2StoreIsJ2Store4($element){
        if (!$this->_isMe($element)) {
            return null;
        }
        return true;
    }
    /**
     * Overriding
     *
     * @param $row
     * @return string|null
     * @throws Exception
     */
    function onJ2StoreGetShippingView( $row )
    {
    	if (!$this->_isMe($row))
    	{
    		return null;
    	}

    	return $this->viewList();
    }

    /**
     * Validates the data submitted based on the suffix provided
     * A controller for this plugin, you could say
     *
     * @return string
     * @throws Exception
     */
    function viewList()
    {
        $platform = J2Store::platform();
        $fof_helper = J2Store::fof();
    	$app = $platform->application();
    	JToolBarHelper::title(JText::_('J2STORE_SHIPM_SHIPPING_METHODS').'-'.JText::_('plg_j2store_'.$this->_element),'j2store-logo');
    	JToolBarHelper::cancel( 'cancel', 'JTOOLBAR_CLOSE' );
    	$vars = new \stdClass();
    	$vars->state = $this->_getState();
        $model = $fof_helper->getModel('ShippingMethods', 'J2StoreModel');
        $vars->list = $model->getList();
    	$id = $app->input->getInt('id', '0');
    	$form = array();
    	$form['action'] = "index.php?option=com_j2store&view=shipping&task=view&id={$id}";
    	$vars->form = $form;
    	$vars->sid = $id;
    	return $this->_getLayout('default', $vars);
    }
    /**
     *
     * @param $element
     * @param $order
     * @return array|null
     */
    function onJ2StoreGetShippingRates($element, $order)
    {
    	// Check if this is the right plugin
    	if (!$this->_isMe($element))
    	{
    		return null;
    	}
        $fof_helper = J2Store::fof();
        $platform = J2Store::platform();
    	$vars = array();
		//set the address
		$order->setAddress();
		$geozones_taxes = array();
    	$geozones = $order->getShippingGeoZones();
    	$gz_array = array();
    	foreach ($geozones as $geozone)
    	{
    		$gz_array[] = $geozone->geozone_id;
    	}

    	$subtotal = $order->order_subtotal;
    	$rates = array();
    	$model = $fof_helper->getModel('ShippingMethods', 'J2StoreModel');
    	$model->setState( 'filter_enabled', '1' );
    	$model->setState( 'filter_subtotal', $subtotal );
    	if ($methods = $model->getList())
    	{
    		foreach( $methods as $method )
    		{
    			//check if there is an override
    			if($method->address_override == 'store') {
    				//there is an override.
    				//so set the shipping address to store and get the geozones afresh
    				$order->setAddress('store');
    			} else {
    				$order->setAddress();
    			}
    			$geozones = $order->getShippingGeoZones();
    			$gz_array = array();
    			foreach ($geozones as $geozone)
    			{
    				$gz_array[] = $geozone->geozone_id;
    			}
    			// filter the list of methods according to geozone
    			$ratemodel = $fof_helper->getModel('ShippingRates', 'J2StoreModel');
    			$ratemodel->setState('filter_shippingmethod', $method->j2store_shippingmethod_id);
    			$ratemodel->setState('filter_geozones', $gz_array);
    			if ($ratesexist = $ratemodel->getList())
    			{
    				$total = $this->getTotal($method->j2store_shippingmethod_id, $geozones, $order, $geozones_taxes );
    				if ($total)
    				{
    					$total->shipping_method_type = $method->shipping_method_type;
						$params = $platform->getRegistry($method->params);
						$total->shipping_select_text = $params->get('shipping_select_text','');
    					$rates[] = $total;
    				}
    			}
    		}
    	}

    	$i = 0;
    	foreach( $rates as $rate )
    	{
    		$vars[$i]['element'] = $this->_element;
    		$vars[$i]['name'] = addslashes(JText::_($rate->shipping_method_name));
    		$vars[$i]['type'] = $rate->shipping_method_type;
    		$vars[$i]['code'] = $rate->j2store_shippingrate_id;
    		$vars[$i]['price'] = $rate->shipping_rate_price;
    		$vars[$i]['tax'] = round($rate->shipping_tax_total, 2);
    		$vars[$i]['extra'] = $rate->shipping_rate_handling;
			$vars[$i]['select_text'] = $rate->shipping_select_text;
    		$vars[$i]['total'] = $rate->shipping_rate_price + $rate->shipping_rate_handling + round($rate->shipping_tax_total, 2);
    		$i++;
    	}
    	return $vars;

    }

    /**
     *
     * Returns an object with the total cost of shipping for this method and the array of geozones
     *
     * @param $shipping_method_id
     * @param $geozones
     * @param $order
     * @param $geozones_taxes
     * @return stdClass
     */
    protected function getTotal( $shipping_method_id, $geozones, $order, $geozones_taxes )
    {
        $platform = J2Store::platform();
        $fof_helper = J2Store::fof();
    	$return = new \stdClass();
    	$return->j2store_shippingrate_id         = '0';
    	$return->shipping_rate_price      = '0.00000';
    	$return->shipping_rate_handling   = '0.00000';
    	$return->shipping_tax_rates        = '0.00000';
    	$return->shipping_tax_total       = '0.00000';

    	$rate_exists = false;
    	$geozone_rates = array();
		$orderItems = $order->getItems();
    	// cast product_id as an array
    	$orderItems = (array) $orderItems;

    	$shippingmethod = $fof_helper->loadTable('ShippingMethods', 'J2StoreTable');
    	$shippingmethod->load( $shipping_method_id );

    	if (empty($shippingmethod->j2store_shippingmethod_id))
    	{
    		return $return;
    	}

    	//initialise cart model
    	switch($shippingmethod->shipping_method_type)
    	{
    		case "2":
    			// 2 = per order - price based
    			// Get the total of the order, and find the rate for that
    			$total = 0;
    			$order_ships = false;
    			$params = J2Store::config();
    			if($params->get('config_including_tax', 0)) {
    				$final_price = 'orderitem_finalprice_with_tax';
    			}else{
    				$final_price = 'orderitem_finalprice_without_tax';
    			}
    			
    			foreach($orderItems as $product) {
                    $registry = $platform->getRegistry($product->orderitem_params);
    				if($registry->get('shipping', 0)) {
    					$order_ships = true;
    					$total += $product->$final_price; // product total
    				}
    			}
                $ship_params = $platform->getRegistry($shippingmethod->params);
				$price_based_on = $ship_params->get('shipping_price_based_on',0);
				if($price_based_on){
					$discount_array = $order->getCartDiscounts();
					$discount = $discount_array['order_discount']+$discount_array['order_discount_tax'];
					if( $discount > 0 ){
						$total -= $discount;
					}
				}
				
    			if($order_ships) {
	    			foreach ($geozones as $geozone)
	    			{
	    				unset($rate);

	    				$geozone_id = $geozone->geozone_id;
	    				if (empty($geozone_rates[$geozone_id]) || !is_array($geozone_rates[$geozone_id]))
	    				{
	    					$geozone_rates[$geozone_id] = array();
	    				}

	    				$model = $fof_helper->getModel('ShippingRates', 'J2StoreModel');
	    				$model->setState('filter_shippingmethod', $shipping_method_id);
	    				$model->setState('filter_geozone', $geozone_id);
	    				$model->setState('filter_weight', $total); // Use weight as total

	    				$items = $model->getList();

	    				if (count($items) > 0) {
                            $rate = $items[0];
                            $geozone_rates[$geozone_id]['0'] = $rate;
                            // if $rate->j2store_shippingrate_id is empty, then no real rate was found
                            if (!empty($rate->j2store_shippingrate_id))
                            {
                                $rate_exists = true;
                            }
                            $geozone_rates[$geozone_id]['0']->qty = '1';
                            $geozone_rates[$geozone_id]['0']->shipping_method_type = $shippingmethod->shipping_method_type;
	    				}
	    			}
    			}
    			break;
    		case "1":
    			// 1 = per order - quantity based
    			// first, get the total quantity of shippable items for the entire order
    			// then, figure out the rate for this number of items (use the weight range field) + geozone
    		case "0":
    			// 0 = per order - flat rate
    		case "5":
    			// 5 = per order - weight based

    			// if any of the products in the order require shipping
    			$sum_weight = 0;
    			$count_shipped_items = 0;
    			$order_ships = false;

    			foreach($orderItems as $product) {
                    $registry = $platform->getRegistry($product->orderitem_params);
    				if($registry->get('shipping', 0)) {
    					$order_ships = true;
    					$product_id = $product->variant_id;
    					$sum_weight += $product->orderitem_weight_total;
    					$count_shipped_items += $product->orderitem_quantity;
    				}
    			}

    			if ($order_ships)
    			{
    				foreach ($geozones as $geozone)
    				{
    					unset($rate);
    					$geozone_id = $geozone->geozone_id;
    					if (empty($geozone_rates[$geozone_id]) || !is_array($geozone_rates[$geozone_id]))
    					{
    						$geozone_rates[$geozone_id] = array();
    					}

    					switch( $shippingmethod->shipping_method_type )
    					{
    						case "0":
    							// don't use weight, just do flat rate for entire order
    							// regardless of weight and regardless of the number of items
    							$rate = $this->getRate( $shipping_method_id, $geozone_id, $product_id );
    							break;
    						case "1":
    							// get the shipping rate for the entire order using the count of all products in the order that ship
    							$rate = $this->getRate( $shipping_method_id, $geozone_id, $product_id, '1', $count_shipped_items );
    							break;
    						default:
    							// get the shipping rate for the entire order using the sum weight of all products in the order that ship
    							$rate = $this->getRate( $shipping_method_id, $geozone_id, $product_id, '1', $sum_weight );
    							break;
    					}
    					$geozone_rates[$geozone_id]['0'] = $rate;

    					// if $rate->j2store_shippingrate_id is empty, then no real rate was found
    					if (!empty($rate->j2store_shippingrate_id))
    					{
    						$rate_exists = true;
    					}

    					$geozone_rates[$geozone_id]['0']->qty = '1';
    					$geozone_rates[$geozone_id]['0']->shipping_method_type = $shippingmethod->shipping_method_type;
    				}
    			}
    			break;
    		case "6":
    		case "4":
    		case "3":
    			// 6 = per item - price based, a percentage of the product's price
    			// 4 = per item - weight based
    			// 3 = per item - flat rate

    			$rates = array();

    			foreach($orderItems as $hash=>$product) {

    				$registry = $platform->getRegistry($product->orderitem_params);
    				if($registry->get('shipping', 0)) {
    					$pid  = $product->variant_id;
    					$qty  = $product->orderitem_quantity;
		    				foreach ($geozones as $geozone)
		    				{
		    					unset($rate);

		    					$geozone_id = $geozone->geozone_id;
		    					if (!isset($geozone_rates[$geozone_id]) || empty($geozone_rates[$geozone_id]) || !is_array($geozone_rates[$geozone_id]))
		    					{
		    						$geozone_rates[$geozone_id] = array();
		    					}
		    					// $geozone_rates[$geozone_id][$pid] contains the shipping rate object for ONE product_id at this geozone.
		    					// You need to multiply by the quantity later
		    					$rate = $this->getRate( $shipping_method_id, $geozone_id, $pid, $shippingmethod->shipping_method_type );

		    					//price per item
		    					if ($shippingmethod->shipping_method_type == '6')
		    					{
		    						// the rate is a percentage of the product's price
		    						$rate->shipping_rate_price = ($rate->shipping_rate_price/100) * $product->orderitem_finalprice;

		    						$geozone_rates[$geozone_id][$hash] = $rate;
		    						$geozone_rates[$geozone_id][$hash]->shipping_method_type = $shippingmethod->shipping_method_type;
		    						$geozone_rates[$geozone_id][$hash]->qty = '1'; // If the method_type == 6, qty should be 1 (we don't need to multiply later, in the "calc for the entire method", since this is a percentage of the orderitem_final_price)

		    						//weight per item

		    						//if weight based per item, we need to use weight.
		    						//Per product weight (including the option weight) is already present in the products array. So pass it.
		    					}elseif($shippingmethod->shipping_method_type == '4')
		    					{
		    						$rate = $this->getRate( $shipping_method_id, $geozone_id, $pid, '1', $product->orderitem_weight);
		    						$geozone_rates[$geozone_id][$hash] = $rate;
		    						$geozone_rates[$geozone_id][$hash]->shipping_method_type = $shippingmethod->shipping_method_type;
		    						$geozone_rates[$geozone_id][$hash]->qty = $qty;
		    					}
		    					else
		    					{
		    						//obviously, this is flat rate per item
		    						$geozone_rates[$geozone_id][$hash] = $rate;
		    						$geozone_rates[$geozone_id][$hash]->shipping_method_type = $shippingmethod->shipping_method_type;
		    						$geozone_rates[$geozone_id][$hash]->qty = $qty;
		    					}

		    					// if $rate->j2store_shippingrate_id is empty, then no real rate was found
		    					if (!empty($rate->j2store_shippingrate_id))
		    					{
		    						$rate_exists = true;
		    					}
		    				}
    				}
    			}

    			break;
    		default:
    			return false;
    			break;
    	}

    	if (!$rate_exists)
    	{
    		return false;
    	}

    	$shipping_tax_rates = array();
    	$shipping_method_price = 0;
    	$shipping_method_handling = 0;
    	$shipping_method_tax_total = 0;
		$taxModel =  $fof_helper->getModel('TaxProfiles', 'J2StoreModel');
	    	// now calc tax for the entire method
	    	foreach ($geozone_rates as $geozone_id=>$geozone_rate_array)
	    	{

	    		foreach ($geozone_rate_array as $geozone_rate)
	    		{
					if($shippingmethod->tax_class_id) {

						$value = ($geozone_rate->shipping_rate_price * $geozone_rate->qty ) + $geozone_rate->shipping_rate_handling;
						/* $shipping_tax_rates[$geozone_id] = 0;
						foreach ($tax_rates as $tax_rate) {
							$shipping_tax_rates[$geozone_id] += $tax_rate['rate'];
						} */
						$taxrates = $taxModel->getTaxwithRates($value, $shippingmethod->tax_class_id,false,'shipping');
						if(isset($taxrates->taxtotal)) {
							$shipping_method_tax_total += $taxrates->taxtotal;
						}

	    			}

	    			$shipping_method_price += ($geozone_rate->shipping_rate_price * $geozone_rate->qty);
	    			$shipping_method_handling += $geozone_rate->shipping_rate_handling;
	    		}
    		}

    	// return formatted object
	    $return->shipping_rate_price    = $shipping_method_price;
	    $return->shipping_rate_handling = $shipping_method_handling;
	    $return->shipping_tax_rates     = $shipping_tax_rates;
	    $return->shipping_tax_total     = $shipping_method_tax_total;
	    $return->shipping_method_id     = $shipping_method_id;
	    $return->shipping_method_name   = $shippingmethod->shipping_method_name;
    	return $return;
    }

    /**
     * Returns the shipping rate for an item
     * Going through this helper enables product-specific flat rates in the future...
     *
     * @param int $shipping_method_id
     * @param int $geozone_id
     * @param string $variant_id
     * @param string $use_weight
     * @param string $weight
     * @return object
     */
    public function getRate( $shipping_method_id, $geozone_id, $variant_id='', $use_weight='0', $weight='0' )
    {
        $fof_helper = J2Store::fof();
    	$model = $fof_helper->getModel('ShippingRates', 'J2StoreModel');
    	$model->setState('filter_shippingmethod', $shipping_method_id);
    	$model->setState('filter_geozone', $geozone_id);

    	if (empty($variant_id))
    	{
    		// product doesn't require shipping, therefore cannot impact shipping costs
    		return $fof_helper->loadTable('ShippingRates', 'J2StoreTable');
    	}

    	$variant = $fof_helper->getModel('Variants', 'J2StoreModel')->getItem($variant_id);

    	if (empty($variant->shipping))
    	{
    		// product doesn't require shipping, therefore cannot impact shipping costs
    		return $fof_helper->loadTable('ShippingRates', 'J2StoreTable');
    	}

    	if (!empty($use_weight) && $use_weight == '1')
    	{
    		$model->setState('filter_weight', $weight);

    	}
    	$items = $model->getList();

    	if (empty($items))
    	{
    		return $fof_helper->loadTable('ShippingRates', 'J2StoreTable');
    	}

    	return $items[0];
    }

	function onJ2StoreShippingParamsValidate(&$ship_message){
        $standard_message = array(
            'shipping_name' => '',
            'standard_gezone' => 'J2STORE_ENABLE_GEOZONE',
            'standard_shipping' => 'J2STORE_ENABLE_SHIPPING_METHOD',
            'standard_rate' => 'J2STORE_ENABLE_SHIPPING_METHOD_RATE'
        );
		$final_message = array();
        foreach ( $standard_message as $key => $value ){
            $data = $this->params->get($key);
            $final_message[$key] = array(
                'name' => JText::_($value),
                'value' => empty($data) ? "<span class=' icon-unpublish'></span>" : "<span class='icon-publish'></span>"
            );
        }
        //echo "<pre>";print_r($final_message);exit;
        $ship_message[$this->_element] = array(
            'name' => JText::_('COM_J2STORE_TITLE_SHIPPINGS'),
            'message' => $final_message,
        );

		/*
		 * 1. GeoZOne is there and enabled
		 * 2. check Shipping method and enable
		 * 3. check rate is there or not
		 * */
//		$error_message[] = "<span class=' icon-unpublish'></span>";
//		if($this->validateGeozone()){
//			$error_message[JText::_('J2STORE_ENABLE_GEOZONE')] = "<span class='icon-publish'></span>";
//		}
//		$error_message[JText::_('J2STORE_ENABLE_SHIPPING_METHOD')] = "<span class=' icon-unpublish'></span>";
//		if($this->validateShippingMethodEnable()){
//			$error_message[JText::_('J2STORE_ENABLE_SHIPPING_METHOD')] = "<span class='icon-publish'></span>";
//		}
//		$error_message[JText::_('J2STORE_ENABLE_SHIPPING_METHOD_RATE')] = "<span class=' icon-unpublish'></span>";
//		if($this->validateShippingMethodRate()){
//			$error_message[JText::_('J2STORE_ENABLE_SHIPPING_METHOD_RATE')] = "<span class='icon-publish'></span>";
//		}
//		return $error_message;
	}
	function validateGeozone(){
        $fof_helper = J2Store::fof();
		$geozone =  $fof_helper->getModel('Geozones','J2StoreModel')->enabled(1)->getList();
		if(count($geozone) > 0){
			return true;
		}
		return false;
	}

	function validateShippingMethodEnable(){
        $fof_helper = J2Store::fof();
		$shipping_methods = $fof_helper->getModel('ShippingMethods', 'J2StoreModel');
		$shipping_methods->setState('filter_enabled','1');
		$data = $shipping_methods->getList();
		if(count($data) > 0){
			return true;
		}
		return false;
	}

	function validateShippingMethodRate(){
        $fof_helper = J2Store::fof();
		$shipping_rates = $fof_helper->getModel('ShippingRates', 'J2StoreModel')->getList();
		if(count($shipping_rates) > 0 ){
			return true;
		}
		return false;
	}
}

