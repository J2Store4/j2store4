<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;

class J2StoreViewCarts extends F0FViewHtml
{

    protected function onDisplay($tpl = null)
    {
        $platform = J2Store::platform();
        $app = $platform->application();
        $session = $app->getSession();
        $view = $app->input->getCmd('view', 'cpanel');
        $params = J2Store::config();
        $utility = J2Store::utilities();
        $utility->nocache();
        $this->currency = J2Store::currency();
        $this->store = J2Store::storeProfile();

        if (in_array($view, array('cpanel', 'cpanels')))
        {
            return;
        }

        $country_id = $this->input->getInt('country_id');

        if (isset($country_id)) {
            $session->set('billing_country_id', $country_id, 'j2store');
            $session->set('shipping_country_id', $country_id, 'j2store');
        } elseif ($session->has('shipping_country_id', 'j2store')) {
            $country_id = $session->get('shipping_country_id', '', 'j2store');
        } else {
            $country_id = $this->store->get('country_id');
        }

        $zone_id = $this->input->getInt('zone_id');
        if (isset($zone_id)) {
            $session->set('billing_zone_id', $zone_id, 'j2store');
            $session->set('shipping_zone_id', $zone_id, 'j2store');
        } elseif($session->has('shipping_zone_id', 'j2store')) {
            $zone_id = $session->get('shipping_zone_id', '', 'j2store');
        } else {
            $zone_id = $this->store->get('zone_id');
        }
        $postcode = $this->input->getAlnum('postcode','');
        $postcode = $utility->text_sanitize($postcode);

        if (isset($postcode ) && !empty($postcode)) {
            $session->set('shipping_postcode', $postcode, 'j2store');
        } elseif ($session->has('shipping_postcode', 'j2store')) {
            $postcode = $session->get('shipping_postcode', '', 'j2store');
        } else {
            $postcode = $this->store->get('zip');
        }
        $coupon = F0FModel::getTmpInstance ( 'Coupons', 'J2StoreModel' );
        $coupon->get_coupon();

        $voucher = F0FModel::getTmpInstance ( 'Vouchers', 'J2StoreModel' );
        $voucher->get_voucher();

        $this->country_id = $country_id;
        $this->zone_id = $zone_id;
        $this->postcode = $postcode;

        if($params->get('hide_shipping_until_address_selection', 1) == 0) {
            $session->set('billing_country_id', $country_id, 'j2store');
            $session->set('shipping_country_id', $country_id, 'j2store');
            $session->set('billing_zone_id', $zone_id, 'j2store');
            $session->set('shipping_zone_id', $zone_id, 'j2store');
            $session->set('shipping_postcode', $postcode, 'j2store');
            $session->set('force_calculate_shipping', 1, 'j2store');
        }
        // Pass page params on frontend only
        if (F0FPlatform::getInstance()->isFrontend())
        {
            $this->params = $params;
        }
        // Load the model
        $model = $this->getModel();

        $items  = $model->getItems();
        if(empty( $items )){
            $cart_empty_redirect_url = $model->getEmptyCartRedirectUrl();
            if($cart_empty_redirect_url){
                $app->redirect ( $cart_empty_redirect_url );
            }
        }

        //plugin trigger
        $this->before_display_cart = '';
        $before_results = J2Store::plugin()->event('BeforeDisplayCart', array( &$items) );
        foreach ($before_results  as $result) {
            $this->before_display_cart .= $result;
        }
        //trigger plugin events
        $i=0;
        $onDisplayCartItem = array();
        foreach( $items as $item)
        {
            ob_start();
            J2Store::plugin()->event('DisplayCartItem', array( $i, $item ) );
            $cartItemContents = ob_get_contents();
            ob_end_clean();
            if (!empty($cartItemContents))
            {
                $onDisplayCartItem[$i] = $cartItemContents;
            }
            $i++;
        }

        $this->onDisplayCartItem =  $onDisplayCartItem;

        $order = F0FModel::getTmpInstance('Orders', 'J2StoreModel')->populateOrder($items)->getOrder();
        $order->validate_order_stock();
        $this->order = $order;

        $this->items = $order->getItems();

        foreach($this->items as $item) {
            if(isset($item->orderitemattributes) && count($item->orderitemattributes)) {
                foreach($item->orderitemattributes as &$attribute) {
                    if($attribute->orderitemattribute_type == 'file') {
                        unset($table);
                        $table = F0FTable::getInstance('Upload', 'J2StoreTable');
                        if($table->load(array('mangled_name'=>$attribute->orderitemattribute_value))) {
                            $attribute->orderitemattribute_value = $table->original_name;
                        }
                    }
                }
            }
        }

        $this->taxes = $order->getOrderTaxrates();
        $this->shipping = $order->getOrderShippingRate();
        $this->coupons = $order->getOrderCoupons();
        $this->vouchers = $order->getOrderVouchers();

        $this->taxModel = F0FModel::getTmpInstance('TaxProfiles', 'J2StoreModel');

        //do we have shipping methods
        $this->shipping_methods = $session->get('shipping_methods', array(), 'j2store');

        $this->shipping_values = $session->get('shipping_values', array(), 'j2store');

        $this->checkout_url = $model->getCheckoutUrl();
        $this->continue_shopping_url = $model->getContinueShoppingUrl();

        $this->after_display_cart = '';
        $results = J2Store::plugin()->event('AfterDisplayCart', array( $order) );
        foreach ($results as $result) {
            $this->after_display_cart .= $result;
        }

        $menu = $app->getMenu();
        $active = $menu->getActive();
        $this->menuItemParams = is_object($active) ? $active->getParams(): $platform->getRegistry('{}');
        $cart_view = $this;
        J2Store::plugin()->event('BeforeCartView', array( &$cart_view ) );
        return true;
    }

}