<?php
/**
 * @package		Paypal Standard Plugin for J2Store
 * @subpackage	J2Store
 * @author    	Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
 * @copyright	Copyright (c) 2014 Weblogicx India Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * --------------------------------------------------------------------------------
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/payment.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');

class plgJ2StorePayment_paypal extends J2StorePaymentPlugin
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element    = 'payment_paypal';
    var $_isLog      = false;
    var $_j2version = null;
    var $username = null;
    var $password = null;
    var $signature = null;

    /**
     * Constructor
     *
     * For php4 compatibility we must not use the __constructor as a constructor for plugins
     * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
     * This causes problems with cross-referencing necessary for the observer design pattern.
     *
     * @param object $subject The object to observe
     * @param 	array  $config  An array that holds the plugin configuration
     * @since 1.5
     */
    function __construct(& $subject, $config) {
        parent::__construct($subject, $config);
        $this->loadLanguage( '', JPATH_ADMINISTRATOR );
        if($this->params->get('debug', 0)) {
            $this->_isLog = true;
        }

        $mode = $this->params->get ( 'sandbox', 0 );
        if($mode){
            $this->username = $this->params->get('sandbox_api_username','');
            $this->password = $this->params->get('sandbox_api_password','');
            $this->signature = $this->params->get('sandbox_api_signature','');
        } else {
            $this->username = $this->params->get('api_username','');
            $this->password = $this->params->get('api_password','');
            $this->signature = $this->params->get('api_signature','');
        }
    }
    function onJ2StoreIsJ2Store4($element){
        if (!$this->_isMe($element)) {
            return null;
        }
        return true;
    }
    /**
     * verify Accept Subscription payment before payment
     * */
    function onJ2StoreAcceptSubscriptionPayment($element){
        if ($this->_isMe($element)){
            return true;
        } else {
            return null;
        }
    }

    /**
     * verify Accept Subscription payment with trial before payment
     * */
    function onJ2StoreAcceptSubscriptionPaymentWithTrial($element){
        if ($this->_isMe($element)){
            return true;
        } else {
            return null;
        }
    }

    /**
     * verify Accept Subscription card update
     * */
    function onJ2StoreAcceptSubscriptionCardUpdate($element)
    {
        if ($this->_isMe($element)) {
            return true;
        } else {
            return null;
        }
    }



    /**
     * Pre payment for subscription product
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    function _prePaymentForSubscriptionProduct( $data )
    {
        $expresCheckout = PaypalExpressCheckoutForJ2StoreSubscriptionProduct::getInstance($this->params);

        $setAPI = $expresCheckout->checkSetAPI();
        if(!$setAPI){
            return JText::_('J2STORE_PAYMENT_PAYPALSUBSCRIPTION_SOMETHING_WENT_WRONG_IN_CREATING_TOKEN');
        }
        // get component params
        $params = J2Store::config();
        $currency = J2Store::currency();
        $platform = J2Store::platform();
        $app = $platform->application();
        $vars = new \stdClass();
        $vars->order_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];
        $vars->post_url = '';
        $fof_helper = J2Store::fof();
        $order = $fof_helper->loadTable('Order','J2StoreTable',array('order_id'=>$data['order_id']));
        $currency_values= $this->getCurrency($order);
        $line_items = $order->get_line_items();
        $order_info = $order->getOrderInformation();
        $vars->invoice = $order->getInvoiceNumber();
        $vars->cart_session_id = $app->getSession()->getId();
        $rootURL = $platform->getRootUrl();

        $returnUrl = $rootURL.$platform->getCheckoutUrl(array('task' => 'confirmPayment','orderpayment_type' => $this->_element,'paction' => 'display'));
        $cancelUrl = $rootURL.$platform->getCheckoutUrl(array('task' => 'confirmPayment','orderpayment_type' => $this->_element,'paction' => 'cancel'));

        $currencyCode = $currency_values['currency_code'];

        $postFields = array();
        $postFields['METHOD'] = 'SetExpressCheckout';
        $postFields['RETURNURL'] = $returnUrl;
        $postFields['CANCELURL'] = $cancelUrl;
        $postFields['BILLINGTYPE'] = 'MerchantInitiatedBillingSingleAgreement';
        $showBillingAgree = $this->_getParam('show_billing_aggrement_text', 0);
        if($showBillingAgree){
            $postFields['BILLINGAGREEMENTDESCRIPTION'] = strip_tags($this->_getParam('billing_aggrement_text', 'Automatic payments for subscription product'));
        }
        $postFields['BILLINGAGREEMENTCUSTOM'] = $vars->order_id.'|'.$vars->cart_session_id;

        if(isset($order_info->billing_city))
            $postFields['PAYMENTREQUEST_0_SHIPTOCITY'] = $order_info->billing_city;
        if(isset($order_info->billing_first_name)){
            if(isset($order_info->billing_last_name))
                $postFields['PAYMENTREQUEST_0_SHIPTONAME'] = $order_info->billing_first_name.' '.$order_info->billing_last_name;
            else
                $postFields['PAYMENTREQUEST_0_SHIPTONAME'] = $order_info->billing_first_name;
        }

        if(isset($order_info->billing_address_1))
            $postFields['PAYMENTREQUEST_0_SHIPTOSTREET'] = $order_info->billing_address_1;
        if(isset($order_info->billing_zone_id))
            $postFields['PAYMENTREQUEST_0_SHIPTOSTATE'] = $this->getZoneById($order_info->billing_zone_id)->zone_name;
        if(isset($order_info->billing_zip))
            $postFields['PAYMENTREQUEST_0_SHIPTOZIP'] = $order_info->billing_zip;
        if(isset($order_info->billing_country_id))
            $postFields['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = $this->getCountryById($order_info->billing_country_id)->country_isocode_2;
        if(isset($order_info->billing_phone_1))
            $postFields['PAYMENTREQUEST_0_SHIPTOPHONENUM'] = $order_info->billing_phone_1;

        $itemTotalValue = 0;
        $taxTotalValue = 0;

        $postFields['PAYMENTREQUEST_0_PAYMENTACTION'] = urlencode("Sale");
        $postFields['NOSHIPPING'] = 0;
        $postFields['ADDROVERRIDE'] = 0;
        $postFields['REQCONFIRMSHIPPING'] = 0;
        $postFields['ALLOWNOTE'] = 1;
        $postFieldsItems = array();
        $card_update = false;
        if ($order->order_type == 'subscription') {
            $card_update = true;
        }
        $vars->is_card_update = $card_update;
        for($i=0; $i < count($line_items); $i++) {
            $postFieldsItems['L_PAYMENTREQUEST_0_NAME'.$i] = $line_items[$i]['name'];
//			$postFieldsItems['L_PAYMENTREQUEST_0_NUMBER'.$i] = urlencode($i);
            $postFieldsItems['L_PAYMENTREQUEST_0_AMT'.$i] = ($card_update === true)? urlencode(0): urlencode($line_items[$i]['amount']);
            $postFieldsItems['L_PAYMENTREQUEST_0_QTY'.$i] = urlencode($line_items[$i]['quantity']);

            $itemTotalValue += $line_items[$i]['amount'] * $line_items[$i]['quantity'];
            $taxTotalValue += $line_items[$i]['tax'] * $line_items[$i]['quantity'];
        }

        if ($params->get ( 'checkout_price_display_options', 1 ) == 0) {
            if ($order->order_tax > 0) {
                $tax = round ( $currency->format ( $order->order_tax, $currency_values ['currency_code'], $currency_values ['currency_value'], false ), 2 );
                $postFieldsItems['L_PAYMENTREQUEST_0_NAME'.$i] = JText::_ ( 'J2STORE_CART_TAX' );
//			$postFields['L_PAYMENTREQUEST_0_NUMBER'.$i] = urlencode($i);
                $postFieldsItems['L_PAYMENTREQUEST_0_AMT'.$i] = ($card_update === true)? urlencode(0): urlencode($tax);
                $postFieldsItems['L_PAYMENTREQUEST_0_QTY'.$i] = urlencode(1);
                $i++;
                $itemTotalValue += $tax;
            }
        }

        $discount = round ( $currency->format ( $order->get_total_discount ( $params->get ( 'checkout_price_display_options', 1 ) ), $currency_values ['currency_code'], $currency_values ['currency_value'], false ), 2 );
        if ($discount > 0) {
            $postFieldsItems['L_PAYMENTREQUEST_0_NAME'.$i] = JText::_ ( 'J2STORE_CART_DISCOUNT' );
//			$postFieldsItems['L_PAYMENTREQUEST_0_NUMBER'.$i] = urlencode($i);
            $postFieldsItems['L_PAYMENTREQUEST_0_AMT'.$i] = ($card_update === true)? urlencode(0): urlencode(- $discount);
            $postFieldsItems['L_PAYMENTREQUEST_0_QTY'.$i] = urlencode(1);
            $i++;
            $itemTotalValue -= $discount;
        }

        $orderTotalValue = round($itemTotalValue, 2);

        $amount = round ( $currency->format ( $order->order_total, $currency_values ['currency_code'], $currency_values ['currency_value'], false ), 2 );
        if($orderTotalValue != $amount){
            $description = JText::_ ( "J2STORE_PAYMENT_PAYPALSUBSCRIPTION_ORDER_DESCRIPTION" ) . ": " . $order->order_id;
            //get invoice number
            $invoice_number = $order->getInvoiceNumber ();

            $postFields['L_PAYMENTREQUEST_0_NAME0'] = $description;
            $postFields['L_PAYMENTREQUEST_0_NUMBER0'] = urlencode($invoice_number);
            $postFields['L_PAYMENTREQUEST_0_AMT0'] = ($card_update === true)? urlencode(0): urlencode($amount);
            $postFields['L_PAYMENTREQUEST_0_QTY0'] = 1;
            $orderTotalValue = $amount;
        } else {
            $postFields = array_merge($postFields, $postFieldsItems);
        }
        if($card_update === true){
            $orderTotalValue = 0;
        }
        $postFields['PAYMENTREQUEST_0_ITEMAMT'] = urlencode($orderTotalValue);
        $postFields['PAYMENTREQUEST_0_TAXAMT'] = urlencode(0);
        $postFields['PAYMENTREQUEST_0_AMT'] = urlencode($orderTotalValue);
        $postFields['PAYMENTREQUEST_0_CURRENCYCODE'] = urlencode($currencyCode);
        $postFields['PAYMENTREQUEST_0_INVNUM'] = urlencode($vars->invoice);
        $postFields['PAYMENTREQUEST_0_CUSTOM'] = $vars->order_id.'|'.$vars->cart_session_id;

        if($cpp_header_image = $this->_getParam('cpp_header_image',''))
            $postFields['HDRIMG'] = urlencode($cpp_header_image);
        if($cpp_headerborder_color = $this->_getParam('cpp_headerback_color',''))
            $postFields['HDRBACKCOLOR'] = urlencode(substr($cpp_headerborder_color, 0,6));
        if($cpp_headerback_color = $this->_getParam('cpp_headerborder_color',''))
            $postFields['HDRBORDERCOLOR'] = urlencode(substr($cpp_headerback_color, 0,6));

        J2Store::plugin()->event('AfterPrepaymentForSubscriptionProduct', array(&$postFields, $this->_element));

        $result = $expresCheckout->sendRequest($postFields);
        $this->_log($result);
        if(isset($result['ACK']) && $result['ACK'] == 'Success'){
            $token = $result['TOKEN'];
            //To update token
            $order = J2Store::fof()->loadTable('Order', 'J2StoreTable',array ('order_id' => $vars->order_id));
            $order->transaction_id = $token;
            if($order->store()){
                // Redirect to paypal.com here
                $vars->post_url = $expresCheckout->auth_url.$token;
            }
        } else if(isset($result['ACK']) && $result['ACK'] == 'Failure'){
            if(isset($result['L_SHORTMESSAGE0'])){
                $vars->errorMessage = $result['L_SHORTMESSAGE0'];
            }
        } else {
            $vars->errorMessage = JText::_('J2STORE_PAYMENT_PAYPALSUBSCRIPTION_FAILED_TOCREATE_TOKEN');
        }

        $vars->display_name = $this->params->get('display_name', 'PAYMENT_PAYPAL');
        $vars->onbeforepayment_text = $this->params->get('onbeforepayment', '');
        $vars->button_text = $this->params->get('button_text', 'J2STORE_PLACE_ORDER');
        $vars->card_update_button_text = $this->params->get('card_update_button_text', 'J2STORE_PLACE_ORDER');
        $html = $this->_getLayout('prepayment_subscription', $vars);
        return $html;
    }


    /**
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    function _prePayment( $data )
    {
        $fof_helper = J2Store::fof();
        $platform = J2Store::platform();
        $order = $fof_helper->loadTable('Order', 'J2StoreTable',array ('order_id' => $data['order_id']));
        if(isset($data['order_id']) && $data['order_id']){
            $hasSubscriptionProduct = $this->checkHasSubscriptionProductFromOrderID($data['order_id']);
            if($hasSubscriptionProduct){
                //To check has recurring product
                $order_items =  $order->getItems();
                $isRecurring = 0;
                foreach($order_items as $order_item) {
                    if($order_item->product_type == 'subscriptionproduct' || $order_item->product_type == 'variablesubscriptionproduct'){
                        $variant_table = $fof_helper->loadTable('Variant','J2StoreTable',array('j2store_variant_id' => $order_item->variant_id));
                        $registry = $platform->getRegistry($variant_table->params);
                        $subscriptionproduct = $registry->get('subscriptionproduct',array());
                        $recurring_type = isset($subscriptionproduct->recurring_type)? $subscriptionproduct->recurring_type: 'multiple';
                        if($recurring_type == 'multiple'){
                            $isRecurring = 1;
                        }
                    }
                }
                if($isRecurring){
                    return $this->_prePaymentForSubscriptionProduct($data);
                }

            }
        }

        // get component params
        $params = J2Store::config();
        $currency = J2Store::currency();
        $app = $platform->application();
        // prepare the payment form

        $vars = new \stdClass();
        $vars->order_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];

        $currency_values= $this->getCurrency($order);
        $line_items = $order->get_line_items();
        foreach($line_items as &$line_item) {
            $line_item['price'] = $currency->format($line_item['amount'], $currency_values['currency_code'], $currency_values['currency_value'], false);
        }

        $vars->currency_code =$currency_values['currency_code'];
        $vars->orderpayment_amount = $currency->format($order->order_total, $currency_values['currency_code'], $currency_values['currency_value'], false);

        $vars->orderpayment_type = $this->_element;

        $vars->cart_session_id = $app->getSession()->getId();

        $vars->display_name = $this->params->get('display_name', 'PAYMENT_PAYPAL');
        $vars->onbeforepayment_text = $this->params->get('onbeforepayment', '');
        $vars->button_text = $this->params->get('button_text', 'J2STORE_PLACE_ORDER');

        //$vars->products = $products;
        $vars->products = $line_items;
        if($params->get('checkout_price_display_options', 1) == 0) {
            $vars->tax_cart = $currency->format($order->order_tax, $currency_values['currency_code'], $currency_values['currency_value'], false);
        }

        $vars->discount_amount_cart = $currency->format($order->get_total_discount($params->get('checkout_price_display_options', 1)), $currency_values['currency_code'], $currency_values['currency_value'], false);

        // set payment plugin variables
        // set payment plugin variables
        if($this->params->get('sandbox', 0)) {
            $vars->merchant_email = trim($this->_getParam( 'sandbox_merchant_email' ));
        }else {
            $vars->merchant_email = trim($this->_getParam( 'merchant_email' ));
        }
        $rootURL = $platform->getRootUrl();
        $vars->post_url = $this->_getPostUrl();
        $return_url = $this->getReturnUrl();

        $vars->return_url = $rootURL.$return_url;
        $vars->cancel_url = $rootURL.$platform->getCheckoutUrl(array('task' => 'confirmPayment', 'orderpayment_type' => $this->_element, 'paction' => 'cancel'));
        $notify_type = $this->params->get('notify_url_type','default');
        if($notify_type == 'alternative'){
            $vars->notify_url = rtrim(JURI::root(),'/')."/plugins/j2store/".$this->_element."/".$this->_element."/tmpl/notify.php";
        }elseif($notify_type == 'callback'){
            $vars->notify_url = JURI::root()."index.php?option=com_j2store&view=callback&method=".$this->_element."&paction=process";
        }else{
            $vars->notify_url = JURI::root()."index.php?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=process&tmpl=component";
        }

        $orderinfo = $order->getOrderInformation();

        // set variables for user info
        $vars->first_name   = $orderinfo->billing_first_name;
        $vars->last_name    = $orderinfo->billing_last_name;
        $vars->email        = $order->user_email;
        $vars->address_1    = $orderinfo->billing_address_1;
        $vars->address_2    = $orderinfo->billing_address_2;
        $vars->city         = $orderinfo->billing_city;
        $vars->country      = $this->getCountryById($orderinfo->billing_country_id)->country_isocode_2;
        $vars->region       = $this->getZoneById($orderinfo->billing_zone_id)->zone_name;
        $vars->postal_code  = $orderinfo->billing_zip;


        $vars->invoice = $order->getInvoiceNumber();
        J2Store::plugin()->event('AfterPrepayment', array(&$vars, $this->_element));
        $html = $this->_getLayout('prepayment', $vars);
        return $html;
    }

    /**
     * Get payment for renewal using billing ID
     * */
    protected function byReference($subscription, $order){
        $expressCheckout = PaypalExpressCheckoutForJ2StoreSubscriptionProduct::getInstance($this->params);
        $hasAPI = $expressCheckout->checkSetAPI();
        if(!$hasAPI){
            return false;
        }
        $currencyId = $order->currency_code;
        $amount = round($order->order_total, 2);
        $j2StorePlugin = J2Store::plugin();
        if(isset($subscription->meta['billing_agreement_id']['metavalue']) && $subscription->meta['billing_agreement_id']['metavalue'] != ''){
            $postFields = array();
            $postFields['REFERENCEID'] = $subscription->meta['billing_agreement_id']['metavalue'];
            $postFields['METHOD'] = "DoReferenceTransaction";
            $postFields['CURRENCYCODE'] = $currencyId;
            $postFields['PAYMENTACTION'] = urlencode('Sale');
            $postFields['INVNUM'] = $order->getInvoiceNumber();
            $postFields['CUSTOM'] = $order->order_id.'|';

            $orderItemsForPayment = $this->getItemsFromOrderForReferenceTransaction($order);
            $postFields['AMT'] = $orderItemsForPayment['total'];
            $postFields['ITEMAMT'] = $orderItemsForPayment['total'];
            $item_count = 0;
            foreach ($orderItemsForPayment['items'] as $order_item){
                $postFields['L_NAME'.$item_count] = substr($this->clean_title($order_item['L_NAME']), 0,126);
                $postFields['L_AMT'.$item_count] = $order_item['L_AMT'];
                $postFields['L_QTY'.$item_count] = $order_item['L_QTY'];
                $item_count++;
            }

            $paymentResponse = $expressCheckout->sendRequest($postFields);

            if(is_array($paymentResponse) || is_object($paymentResponse)){
                $transaction_details = json_encode($paymentResponse);
            } else {
                $transaction_details = $paymentResponse;
            }
            $order->transaction_details = $transaction_details;
            $order->store();

            if (isset($paymentResponse['ACK']) && $paymentResponse['ACK'] == 'Success') {
                $order->transaction_id = $paymentResponse['TRANSACTIONID'];
                if($order->store()){
                    $j2StorePlugin->event('SuccessRenewalPayment', array($subscription, $order));
                }
                $order->payment_complete();
            } else if(isset($paymentResponse['ACK']) && $paymentResponse['ACK'] == 'Failure' && isset($paymentResponse['L_ERRORCODE0']) && $paymentResponse['L_ERRORCODE0'] == '10412'){
                $returnVal = "Payment failed due to duplicate ID";
                $this->_log($returnVal);
                if(isset($paymentResponse['L_SHORTMESSAGE0']) && !empty($paymentResponse['L_SHORTMESSAGE0'])){
                    $j2StorePlugin->event('AddSubscriptionHistory', array($subscription->j2store_subscription_id, $subscription->status, $paymentResponse['L_SHORTMESSAGE0']));
                }
                $j2StorePlugin->event('NoResponseForRenewalPayment', array($subscription, $order));
            } else {
                if(isset($paymentResponse['L_SHORTMESSAGE0']) && !empty($paymentResponse['L_SHORTMESSAGE0'])){
                    $j2StorePlugin->event('AddSubscriptionHistory', array($subscription->j2store_subscription_id, $subscription->status, $paymentResponse['L_SHORTMESSAGE0']));
                }
                $j2StorePlugin->event('NoResponseForRenewalPayment', array($subscription, $order));
//                $order->update_status ( 3 );
//                $j2StorePlugin->event('FailedRenewalPayment', array($subscription, $order));
                $this->_log($paymentResponse);
            }
        } else {
            $order->update_status ( 3 );
            $j2StorePlugin->event('FailedRenewalPayment', array($subscription, $order));
            $returnVal = "Payment failed due to empty billing ID";
            $this->_log($returnVal);
        }
    }

    /**
     * Process first payment
     * */
    protected function processCheckout(){
        $fof_helper = J2Store::fof();
        $app = J2Store::platform()->application();
        $token = $app->input->get('token', '');
        $PayerID = $app->input->get('PayerID', '');
        if($token != '' && $PayerID != ''){
            $token = urlencode($token);
            /*
             *  Unique PayPal buyer account identification number as returned in the GetExpressCheckoutDetails response
            */
            $payerId = urlencode($PayerID);
            $paymentAction = urlencode('Sale');

            $expressCheckout = PaypalExpressCheckoutForJ2StoreSubscriptionProduct::getInstance($this->params);

            $getDetails = array ();
            $getDetails['TOKEN'] = $token;
            $getDetails['METHOD'] = "GetExpressCheckoutDetails";

            $responseGetData = $expressCheckout->sendRequest($getDetails);
            if (isset($responseGetData['ACK']) && $responseGetData['ACK'] == 'Success') {
//				if (isset($responseGetData['CUSTOM'] ) && $responseGetData['CUSTOM'] != '') {
                if (isset($responseGetData['TOKEN'] ) && $responseGetData['TOKEN'] != '') {
//					$custom_array = explode ( '|', $responseGetData['CUSTOM'] );
//					$order_id = $custom_array [0];
                    $token_id = $responseGetData['TOKEN'];
                    // load the orderpayment record and set some values
                    $order = $fof_helper->loadTable('Order', 'J2StoreTable', array('transaction_id' => $token_id));
                    if(isset($order->order_id) && $order->order_id != ''){
                        if (isset($order->order_type) && $order->order_type == 'subscription') {
                            $this->processSubscriptionCheckoutForUpdateCard($expressCheckout, $order, $token, $payerId, $responseGetData);
                        } else {
                            $postFields = array();
                            $postFields['TOKEN'] = $token;
                            $postFields['PAYERID'] = $payerId;
                            $postFields['METHOD'] = "DoExpressCheckoutPayment";
                            $postFields['PAYMENTREQUEST_0_CURRENCYCODE'] = $responseGetData ['PAYMENTREQUEST_0_CURRENCYCODE'];
                            $postFields['PAYMENTREQUEST_0_PAYMENTACTION'] = $paymentAction;

                            $orderItemsForPayment = $this->getItemsFromOrderForReferenceTransaction($order);
                            $postFields['PAYMENTREQUEST_0_AMT'] = $orderItemsForPayment['total'];
                            $item_count = 0;
                            foreach ($orderItemsForPayment['items'] as $order_item){
                                $postFields['L_PAYMENTREQUEST_0_NAME'.$item_count] = substr($this->clean_title($order_item['L_NAME']), 0,126);
                                $postFields['L_PAYMENTREQUEST_0_AMT'.$item_count] = $order_item['L_AMT'];
                                $postFields['L_PAYMENTREQUEST_0_QTY'.$item_count] = $order_item['L_QTY'];
                                $item_count++;
                            }

                            $paymentResponse = $expressCheckout->sendRequest($postFields);
                            $this->_log($paymentResponse);
                            if(is_array($paymentResponse) || is_object($paymentResponse)){
                                $transaction_details = json_encode($paymentResponse);
                            } else {
                                $transaction_details = $paymentResponse;
                            }
                            $order->transaction_details = $transaction_details;
                            $order->store();
                            if (isset($paymentResponse['ACK']) && $paymentResponse['ACK'] == 'Success') {
                                $order->transaction_id = $paymentResponse['PAYMENTINFO_0_TRANSACTIONID'];
                                if($order->store()){
                                    if(isset($paymentResponse['BILLINGAGREEMENTID'])){
                                        $billingAgreementID = $paymentResponse['BILLINGAGREEMENTID'];
                                        $this->updateBillingAgreementId($order->order_id, $billingAgreementID);
                                    }
                                }
                                $order->payment_complete();
                                // clear cart
                                $order->empty_cart();
                                return true;
                            } else if(isset($paymentResponse['ACK']) && $paymentResponse['ACK'] == 'SuccessWithWarning' && $paymentResponse['L_ERRORCODE0'] == '11607'){
                                //[ShortMessage] => Duplicate Request
                                //[LongMessage] => A successful transaction has already been completed for this token.
                                return true;
                            } else {
                                $this->updateSubscriptionPaymentFailed($order->order_id);
                                $order->update_status ( 3 );
                            }
                        }
                    }
                }
            }
        } else if($token != ''){
            $expressCheckout = PaypalExpressCheckoutForJ2StoreSubscriptionProduct::getInstance($this->params);

            $getDetails = array ();
            $getDetails['TOKEN'] = $token;
            $getDetails['METHOD'] = "GetExpressCheckoutDetails";

            $responseGetData = $expressCheckout->sendRequest($getDetails);
            if (isset($responseGetData['ACK']) && $responseGetData['ACK'] == 'Success') {
//				if (isset($responseGetData['CUSTOM'] ) && $responseGetData['CUSTOM'] != '') {
                if (isset($responseGetData['TOKEN'] ) && $responseGetData['TOKEN'] != '' && isset($responseGetData['AMT'] ) && $responseGetData['AMT'] <= 0) {
//					$custom_array = explode ( '|', $responseGetData['CUSTOM'] );
//					$order_id = $custom_array [0];
                    $token_id = $responseGetData['TOKEN'];
                    // load the orderpayment record and set some values
                    $order = $fof_helper->loadTable('Order', 'J2StoreTable', array('transaction_id' => $token_id));
                    if(isset($order->order_id) && $order->order_id != ''){
                        $postFields = array();
                        $postFields['TOKEN'] = $token;
                        $postFields['METHOD'] = "CreateBillingAgreement";
                        $paymentResponse = $expressCheckout->sendRequest($postFields);
                        $this->_log($paymentResponse);
                        if(is_array($paymentResponse) || is_object($paymentResponse)){
                            $transaction_details = json_encode($paymentResponse);
                        } else {
                            $transaction_details = $paymentResponse;
                        }
                        $order->transaction_details = $transaction_details;
                        $order->store();
                        if (isset($order->order_type) && $order->order_type == 'subscription') {
                            $subscription = array();
                            $process = false;
                            $j2StorePlugin = J2Store::plugin();
                            $j2StorePlugin->event('ProcessSubscriptionCardUpdateCheckout', array($order->subscription_id, &$subscription, &$process));
                            if (isset($paymentResponse['ACK']) && $paymentResponse['ACK'] == 'Success') {
                                $order->transaction_id = $paymentResponse['CORRELATIONID'];
                                if($order->store()){
                                    if(isset($paymentResponse['BILLINGAGREEMENTID'])){
                                        $billingAgreementID = $paymentResponse['BILLINGAGREEMENTID'];
                                        $this->updateBillingAgreementId($order->order_id, $billingAgreementID, 1, $subscription);
                                        $j2StorePlugin->event('CardUpdateSuccess', array($subscription));
                                    }
                                }
                                // clear cart
                                $order->empty_cart();
                                return true;
                            }
                        } else {
                            if (isset($paymentResponse['ACK']) && $paymentResponse['ACK'] == 'Success') {
                                $order->transaction_id = $paymentResponse['CORRELATIONID'];
                                if($order->store()){
                                    if(isset($paymentResponse['BILLINGAGREEMENTID'])){
                                        $billingAgreementID = $paymentResponse['BILLINGAGREEMENTID'];
                                        $this->updateBillingAgreementId($order->order_id, $billingAgreementID);
                                    }
                                }
                                $order->payment_complete();
                                // clear cart
                                $order->empty_cart();
                                return true;
                            } else if(isset($paymentResponse['ACK']) && $paymentResponse['ACK'] == 'SuccessWithWarning' && $paymentResponse['L_ERRORCODE0'] == '11607'){
                                //[ShortMessage] => Duplicate Request
                                //[LongMessage] => A successful transaction has already been completed for this token.
                                return true;
                            } else {
                                $this->updateSubscriptionPaymentFailed($order->order_id);
                                $order->update_status ( 3 );
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    protected function processSubscriptionCheckoutForUpdateCard($expressCheckout, $order, $token, $payerId, $responseGetData){
        $subscription = array();
        $process = false;
        $j2StorePlugin = J2Store::plugin();
        $j2StorePlugin->event('ProcessSubscriptionCardUpdateCheckout', array($order->subscription_id, &$subscription, &$process));
        $paymentAction = urlencode('Sale');
        $postFields = array();
        $postFields['TOKEN'] = $token;
        $postFields['PAYERID'] = $payerId;
        $postFields['METHOD'] = "DoExpressCheckoutPayment";
        $postFields['PAYMENTREQUEST_0_CURRENCYCODE'] = $responseGetData ['PAYMENTREQUEST_0_CURRENCYCODE'];
        $postFields['PAYMENTREQUEST_0_PAYMENTACTION'] = $paymentAction;

        $orderItemsForPayment = $this->getItemsFromOrderForReferenceTransaction($order);
        $postFields['PAYMENTREQUEST_0_AMT'] = 0;
        $item_count = 0;
        foreach ($orderItemsForPayment['items'] as $order_item){
            $postFields['L_PAYMENTREQUEST_0_NAME'.$item_count] = substr($this->clean_title($order_item['L_NAME']), 0,126);
            $postFields['L_PAYMENTREQUEST_0_AMT'.$item_count] = 0;
            $postFields['L_PAYMENTREQUEST_0_QTY'.$item_count] = $order_item['L_QTY'];
            $item_count++;
        }

        $paymentResponse = $expressCheckout->sendRequest($postFields);
        $this->_log($paymentResponse);
        if (isset($paymentResponse['ACK']) && $paymentResponse['ACK'] == 'Success') {
            if(isset($paymentResponse['BILLINGAGREEMENTID'])){
                $billingAgreementID = $paymentResponse['BILLINGAGREEMENTID'];
                $this->updateBillingAgreementId($order->order_id, $billingAgreementID, 1, $subscription);
                $j2StorePlugin->event('CardUpdateSuccess', array($subscription));
            }
            // clear cart
            $order->empty_cart();
            return true;
        }
    }

    /**
     * Get item list for reference transaction
     *
     * @param object $order
     * @return array
     * */
    public function getItemsFromOrderForReferenceTransaction($order){
        $currency = J2Store::currency();
        $params = J2Store::config();
        $currency_values = $this->getCurrency ( $order,false );
        $items = $order->get_line_items($params->get('checkout_price_display_options', 1));
        $itemamt = 0;
        // add item details
        $OrderItems = array ();

        foreach ( $items as $item ) {

            $desc = $item['name'];

            if (count($item['options'])) {
                foreach ($item['options'] as $option) {
                    $desc .= ' | ' .$option['name']. ':' .$option['value'];
                }
            }
            $desc = str_replace("'", '', $desc);
            $price = round($currency->format ($item['amount'], $currency_values ['currency_code'], $currency_values ['currency_value'], $currency_values ['convert'] ),2);

            $itemamt += round($currency->format( ($price*$item['quantity']), $currency_values ['currency_code'], $currency_values ['currency_value'], $currency_values ['convert'] ),2);
            $orderItem = array (
                'L_NAME' => $desc,
                'L_NUMBER' => $item['item_number'],
                'L_QTY' => $item['quantity'],
                'L_AMT' => $price
            )
            ;
            array_push ( $OrderItems, $orderItem );
        }

        //if excluding tax, then we should be sending the tax for the cart as an item as well
        if($params->get('checkout_price_display_options', 1) == 0 ) {
            if($order->order_tax > 0) {
                $tax = round($currency->format ($order->order_tax, $currency_values ['currency_code'], $currency_values ['currency_value'], $currency_values ['convert'] ),2);
                $orderItem = array (
                    'L_NAME' => JText::_('J2STORE_CART_TAX'),
                    'L_NUMBER' => 'tax',
                    'L_QTY' => 1,
                    'L_AMT' => $tax
                )
                ;
                array_push ( $OrderItems, $orderItem );

                $itemamt += $tax;
            }

        }

        //add discount
        $discount_amount_cart = round($currency->format ( $order->get_total_discount($params->get('checkout_price_display_options', 1)), $currency_values ['currency_code'], $currency_values ['currency_value'], $currency_values ['convert'] ),2);

        if ($discount_amount_cart > 0) {
            $orderItem = array (
                'L_NAME' => JText::_('J2STORE_CART_DISCOUNT'),
                'L_NUMBER' => 'discount',
                'L_QTY' => 1,
                'L_AMT' => - $discount_amount_cart
            )
            ;
            array_push ( $OrderItems, $orderItem );

            $itemamt -= $discount_amount_cart;
        }

        $description = JText::_ ( "J2STORE_PAYPAL_ORDER_DESCRIPTION" ) . ": " . $order->order_id;
        //get invoice number
        $invoice_number = $order->getInvoiceNumber ();

        $amount = round($currency->format ( $order->order_total, $currency_values ['currency_code'], $currency_values ['currency_value'], $currency_values ['convert'] ),2);
        if(round($amount,2) != round($itemamt,2)) {
            //line item totals do not match. Send the order as the line item
            $OrderItems = array ();
            $orderItem = array (
                'L_NAME' => $description,
                'L_NUMBER' => $invoice_number,
                'L_QTY' => 1,
                'L_AMT' => $amount
            )
            ;
            array_push ( $OrderItems, $orderItem );
            $itemamt = $amount;
        }

        return array('total' => $itemamt, 'items' => $OrderItems);
    }

    /**
     * To process renewal payment
     * */
    function onJ2StoreProcessRenewalPayment($paymentType, $subscription, $order){
        if($paymentType == $this->_element){
            $this->byReference($subscription, $order);
        }
    }

    /**
     * To process renewal payment
     * */
    function onJ2StoreAfterSubscriptionCanceled($paymentType, $subscription_id, $subscriptionMeta){
        if($paymentType == $this->_element){
            if(isset($subscriptionMeta['billing_agreement_id']['metavalue']) && $subscriptionMeta['billing_agreement_id']['metavalue'] != ''){
                $billingAgreementId = $subscriptionMeta['billing_agreement_id']['metavalue'];
                $billingIdInUse = $this->checkAnyActiveSubscriptionHasSameBillingId($billingAgreementId, $subscription_id);
                if(!$billingIdInUse){
                    $this->removeBillingAgreementIdFromPaypal($billingAgreementId);
                }
            }
        }
    }

    /**
     * Cancel Billing Agreement Id from paypal
     * */
    protected function removeBillingAgreementIdFromPaypal($billingAgreementId)
    {
        $expressCheckout = PaypalExpressCheckoutForJ2StoreSubscriptionProduct::getInstance($this->params);

        $cancelAgreement = array();
        $cancelAgreement['METHOD'] = "BillAgreementUpdate";
        $cancelAgreement['REFERENCEID'] = $billingAgreementId;
        $cancelAgreement['BILLINGAGREEMENTSTATUS'] = 'Canceled';

        $responseGetData = $expressCheckout->sendRequest($cancelAgreement);

        $this->_log($responseGetData, 'Cancel billing agreement response');
    }
    /**
     * Processes the payment form
     * and returns HTML to be displayed to the user
     * generally with a success/failed message
     *
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    function _postPayment( $data )
    {
        // Process the payment
        $app = J2Store::platform()->application();
        $paction = $app ->input->getString('paction');

        $vars = new \stdClass();

        switch ($paction)
        {
            case "display":
                // To process checkout / payment for subscription product
                $this->processCheckout();
                $this->onJ2StoreProcessCron('paypalcollation', 900);
                $vars->message = JText::_($this->params->get('onafterpayment', ''));
                $html = $this->_getLayout('message', $vars);
                $html .= $this->_displayArticle();
                break;
            case "process":
                $vars->message = $this->_process();
                $html = $this->_getLayout('message', $vars);
                echo $html; // TODO Remove this
                $app->close();
                break;
            case "cancel":
                $vars->message = JText::_($this->params->get('oncancelpayment', ''));
                $html = $this->_getLayout('message', $vars);
                break;
            default:
                $vars->message = JText::_($this->params->get('onerrorpayment', ''));
                $html = $this->_getLayout('message', $vars);
                break;
        }

        return $html;
    }

    /************************************
     * Note to 3pd:
     *
     * The methods between here
     * and the next comment block are
     * specific to this payment plugin
     *
     ************************************/

    /**
     * Gets the Paypal gateway URL
     *
     * @param boolean $full
     * @return string
     * @access protected
     */
    function _getPostUrl($full = true)
    {
        $url = $this->params->get('sandbox') ? 'www.sandbox.paypal.com' : 'www.paypal.com';

        if ($full)
        {
            $url = 'https://' . $url . '/cgi-bin/webscr';
        }

        return $url;
    }


    /**
     * Gets the value for the Paypal variable
     *
     * @param string $name
     * @return string
     * @access protected
     */
    function _getParam( $name, $default='' )
    {
        $return = $this->params->get($name, $default);

        $sandbox_param = "sandbox_$name";
        $sb_value = $this->params->get($sandbox_param);
        if ($this->params->get('sandbox') && !empty($sb_value))
        {
            $return = $this->params->get($sandbox_param, $default);
        }

        return $return;
    }

    function getIPNurl(){
        $url = $this->params->get('sandbox',0) ? 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr' : 'https://ipnpb.paypal.com/cgi-bin/webscr';
        return $url;
    }

    /**
     * Validates the IPN data
     *
     * @param array $data
     * @return string Empty string if data is valid and an error message otherwise
     * @access protected
     */
    function _validateIPN( $data, $order)
    {
        $paypal_url = $this->getIPNurl();

        $request = 'cmd=_notify-validate';

        foreach ($data as $key => $value) {
            $request .= '&' . $key . '=' . urlencode(html_entity_decode($value, ENT_QUOTES, 'UTF-8'));
        }

        $curl = curl_init($paypal_url);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);

        if (!$response) {
            $this->_log('CURL failed ' . curl_error($curl) . '(' . curl_errno($curl) . ')');
        }

        $this->_log('IPN Validation REQUEST: ' . $request);
        $this->_log('IPN Validation RESPONSE: ' . $response);

        if ((strcmp($response, 'VERIFIED') == 0 || strcmp($response, 'UNVERIFIED') == 0)) {
            return '';
        }elseif (strcmp ($response, 'INVALID') == 0) {
            return JText::_('J2STORE_PAYPAL_ERROR_IPN_VALIDATION');
        }
        return '';
    }

    /**
     *
     * @return string
     */
    function _process()
    {
        $fof_helper = J2Store::fof();
        $app = J2Store::platform()->application();
        $data = $app->input->getArray($_REQUEST);
        $this->_log ( $this->_getFormattedTransactionDetails ( $data ), 'notify raw response' );

        $error = '';

        // prepare some data
        $validate_ipn = $this->params->get('validate_ipn', 1);
        if($validate_ipn) {
            $custom = $data['custom'];
            $custom_array = explode('|', $custom);

            $order_id  = $custom_array[0];

            // load the orderpayment record and set some values
            $order = $fof_helper->loadTable('Order', 'J2StoreTable',array('order_id'=>$order_id));
            if(!empty($order->order_id) && ($order->order_id == $order_id) ) {
                // validate the IPN info
                $errorV = $this->_validateIPN($data, $order);
                if (!empty($errorV))
                {
                    //ipn api validation
                    if(!$this->checkStatusOfPaypal($data)){
                        // ipn Validation failed
                        $data['ipn_validation_results'] = $errorV;
                    }
                }

            }
        }

        $data['transaction_details'] = $this->_getFormattedTransactionDetails( $data );

        $this->_log($data['transaction_details']);

        // process the payment based on its type
        if ( !empty($data['txn_type']) )
        {
            $payment_error = '';

            if ($data['txn_type'] == 'cart') {
                // Payment received for multiple items; source is Express Checkout or the PayPal Shopping Cart.
                $payment_error = $this->_processSale( $data, $error );
            }
            else {
                // other methods not supported right now
                //$payment_error = JText::_( "J2STORE_PAYPAL_ERROR_INVALID_TRANSACTION_TYPE" ).": ".$data['txn_type'];
            }

            if ($payment_error) {
                // it seems like an error has occurred during the payment process
                $error .= $error ? "\n" . $payment_error : $payment_error;
            }
        }

        if ($error) {
            $sitename = J2Store::platform()->application()->getConfig()->get('sitename');
            //send error notification to the administrators
            $subject = JText::sprintf('J2STORE_PAYPAL_EMAIL_PAYMENT_NOT_VALIDATED_SUBJECT', $sitename);

            $receivers = $this->_getAdmins();
            foreach ($receivers as $receiver) {
                $body = JText::sprintf('J2STORE_PAYPAL_EMAIL_PAYMENT_FAILED_BODY', $receiver->name, $sitename, JURI::root(), $error, $data['transaction_details']);
                //J2Store::email()->sendErrorEmails($receiver->email, $subject, $body);
            }
            return $error;
        }


        // if here, all went well
        $error = 'processed';
        return $error;
    }

    function _getTransactionUrl()
    {
        $url = $this->params->get('sandbox',0) ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';
        return $url;
    }

    public function checkStatusOfPaypal($data){
        if(empty( $data ['txn_id'] ) || empty( $this->username ) || empty( $this->password ) || empty( $this->signature )){
            return false;
        }

        $transactionId = $data ['txn_id'];
        $rawResponse = $this->getTransactionDetails($transactionId);
        // We get a long query string as a response. We need to decode it to a hash array
        $fakeURI    = new JUri('http://localhost/index.php?' . $rawResponse);
        $transaction = $fakeURI->getVar('TRANSACTIONID', '');
        $payment_status = $fakeURI->getVar('PAYMENTSTATUS', '');
        $status = false;

        if($transaction == $transactionId && in_array(strtoupper($payment_status),array('PENDING','COMPLETED'))){
            $status = true;
        }
        return $status;
    }


    function getTransactionDetails($transactionId){
        $payment_url = $this->_getTransactionUrl();
        $transaction_params = array(
            'USER' => $this->username,
            'PWD' => $this->password,
            'SIGNATURE' => $this->signature,
            'METHOD' => 'GetTransactionDetails',
            'VERSION' => '106.0',
            'TRANSACTIONID' => $transactionId
        );
        /*$targetURL = new JUri($payment_url);
        $targetURL->setVar('USER', $this->username);
        $targetURL->setVar('PWD', $this->password);
        $targetURL->setVar('SIGNATURE', $this->signature);
        $targetURL->setVar('METHOD', 'GetTransactionDetails');
        $targetURL->setVar('VERSION', '106.0');
        $targetURL->setVar('TRANSACTIONID', $transactionId);*/

        $useAgent = "J2Store/".$this->_j2version;
        // Set up the request through cURL
        $ch = curl_init($payment_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($transaction_params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $useAgent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        //  curl_setopt($ch, CURLOPT_CAINFO, JPATH_LIBRARIES . '/fof30/Download/Adapter/cacert.pem');
        //  curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        // Force the use of TLS (therefore SSLv3 is not used, mitigating POODLE; see https://github.com/paypal/merchant-sdk-php)
        //    curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
        // This forces the use of TLS 1.x
        //  curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);

        $rawResponse = curl_exec($ch);
        curl_close($ch);
        return $rawResponse;
    }

    /**
     * Processes the sale payment
     *
     * @param array $data IPN data
     * @return boolean Did the IPN Validate?
     * @access protected
     */
    function _processSale($data, $ipnValidationFailed = '') {
        /*
         * validate the payment data
         */
        $errors = array ();
        $fof_helper = J2Store::fof();
        if (! empty ( $ipnValidationFailed )) {
            $errors [] = $ipnValidationFailed;
        }

        if ($this->params->get ( 'sandbox', 0 )) {
            $merchant_email = trim ( $this->_getParam ( 'sandbox_merchant_email' ) );
        } else {
            $merchant_email = trim ( $this->_getParam ( 'merchant_email' ) );
        }
        // is the recipient correct?
        if (empty ( $data ['receiver_email'] ) || strtolower ( $data ['receiver_email'] ) != strtolower ( trim ( $merchant_email ) )) {
            $errors [] = JText::_ ( 'J2STORE_PAYPAL_MESSAGE_RECEIVER_INVALID' );
        }

        $custom = $data ['custom'];
        $custom_array = explode ( '|', $custom );

        $order_id = $custom_array [0];

        // load the orderpayment record and set some values
        $order = $fof_helper->loadTable('Order', 'J2StoreTable',array('order_id' => $order_id));
        if (! empty ( $order->order_id ) && ($order->order_id == $order_id)) {

            $order->add_history(JText::_('J2STORE_PAYPAL_CALLBACK_IPN_RESPONSE_RECEIVED'));

            $order->transaction_details = $data ['transaction_details'];
            $order->transaction_id = $data ['txn_id'];
            $order->transaction_status = $data ['payment_status'];

            // check the stored amount against the payment amount

            // check the payment status
            if (empty ( $data ['payment_status'] ) || ($data ['payment_status'] != 'Completed' && $data ['payment_status'] != 'Pending')) {
                $errors [] = JText::sprintf ( 'J2STORE_PAYPAL_MESSAGE_STATUS_INVALID', @$data ['payment_status'] );
            }

            $currency = J2Store::currency();
            $currency_values= $this->getCurrency($order);
            $gross = $currency->format($order->order_total, $currency_values['currency_code'], $currency_values['currency_value'], false);

            $mc_gross = floatval($data['mc_gross']);
            if ($mc_gross > 0)
            {
                // A positive value means "payment". The prices MUST match!
                // Important: NEVER, EVER compare two floating point values for equality.
                $isValid = ($gross - $mc_gross) < 0.05;
                if(!$isValid) {
                    $errors[] = 'Paid amount does not match the order total';
                }
            }

            // save the data
            if (! $order->store ()) {
                // $errors [] = $order->getError ();
            }

            // set the order's new status
            if (count ( $errors )) {
                // mark as failed
                $order->update_status ( 3 );
                $this->updateSubscriptionPaymentFailed($order->order_id);

            } elseif (strtoupper($data ['payment_status']) == 'PENDING') {

                // set order to pending. Also notify the customer that it is pending
                $order->update_status ( 4, true );
                // reduce the order stock. Because the status is pending.
                $order->reduce_order_stock ();

            } elseif(strtoupper($data ['payment_status']) == 'COMPLETED') {
                $order->payment_complete ();
                $this->updateBillingAgreementId($order->order_id);
            }

            //clear cart
            $order->empty_cart();
        }

        return count ( $errors ) ? implode ( "\n", $errors ) : '';
    }

    function getCurrency($order, $convert=false) {
        $results = array();
        $convert = false;

        $currencyObject = J2Store::currency ();

        $currency_code = $order->currency_code;
        $currency_value = $order->currency_value;

        //accepted currencies
        $currencies = $this->getAcceptedCurrencies();
        if(!in_array($order->currency_code, $currencies)) {
            $default_currency = 'USD';
            if($currencyObject->has($default_currency)) {
                $currencyObject->set($default_currency);
                $currency_code = $default_currency;
                $currency_value = $currencyObject->getValue($default_currency);
                $convert = true;
            }
        }

        $results['currency_code'] = $currency_code;
        $results['currency_value'] = $currency_value;
        $results['convert'] = $convert;

        return $results;
    }

    function getAcceptedCurrencies() {
        $currencies = array(
            'AUD',
            'BRL',
            'CAD',
            'CZK',
            'DKK',
            'EUR',
            'HKD',
            'HUF',
            'ILS',
            'JPY',
            'MYR',
            'MXN',
            'NOK',
            'NZD',
            'PHP',
            'PLN',
            'GBP',
            'RUB',
            'SGD',
            'SEK',
            'CHF',
            'TWD',
            'THB',
            'TRY',
            'USD'
        );
        return $currencies;
    }

    public function clean_title($text){
        $text =  str_replace ( '"','' ,  $text);
        $text =  str_replace ( "'",'' ,  $text);
        return $text;
    }

    /**
     * Update billing agreement
     * */
    protected function updateBillingAgreementId($order_id, $billingAgreementID = '', $updateCard = 0, $subscription = array()){
        if ($updateCard) {
            $subscriptions[] = $subscription;
        } else {
            $subscriptions = $this->getSubscriptionByOrderId($order_id);
        }
        $j2StorePlugin = J2Store::plugin();
        if(is_array($subscriptions) && count($subscriptions)){
            foreach ($subscriptions as $susb){
                if ($updateCard) {
                    $comment = JText::_('J2STORE_SUBSCRIPTION_HISTORY_PAYMENT_COMPLETED_FOR_UPDATE_CARD');
                } else {
                    $comment = JText::_('J2STORE_SUBSCRIPTION_HISTORY_PAYMENT_COMPLETED');
                }
                $j2StorePlugin->event('AddSubscriptionHistory', array($susb->j2store_subscription_id, $susb->status, $comment));
                $j2StorePlugin->event('ChangeSubscriptionStatus', array($susb->j2store_subscription_id, 'active'));
                $j2StorePlugin->event('RefreshUserGroups', array($susb->user_id));
                if($billingAgreementID != ''){
                    $this->addSubscriptionMeta($susb->j2store_subscription_id, 'billing_agreement_id', $billingAgreementID);
                }
            }
        }

    }

    /**
     * Update Payment Failed
     * */
    protected function updateSubscriptionPaymentFailed($order_id){
        $subscriptions = $this->getSubscriptionByOrderId($order_id);
        $j2StorePlugin = J2Store::plugin();
        if(is_array($subscriptions) && count($subscriptions)){
            foreach ($subscriptions as $susb){
                $comment = JText::_('J2STORE_SUBSCRIPTION_HISTORY_PAYMENT_FAILED');
                $j2StorePlugin->event('AddSubscriptionHistory', array($susb->j2store_subscription_id, $susb->status, $comment));
                $j2StorePlugin->event('ChangeSubscriptionStatus', array($susb->j2store_subscription_id, 'failed'));
            }
        }
    }

    /**
     * Check subscription product available for an order
     * */
    protected function checkHasSubscriptionProductFromOrderID($order_id){
        $j2StorePlugin = J2Store::plugin();
        $hasSubProduct = 0;
        $j2StorePlugin->event('CheckHasSubscriptionProductFromOrderID', array('app_subscriptionproduct', $order_id, &$hasSubProduct));
        return $hasSubProduct;
    }

    /**
     * To update subscription meta
     * */
    protected function addSubscriptionMeta($subscription_id, $key, $value){
        $j2StorePlugin = J2Store::plugin();
        $j2StorePlugin->event('AddSubscriptionMeta', array($subscription_id, $key, $value));
    }

    /**
     * Get subscriptions based on order id
     * */
    protected function getSubscriptionByOrderId($order_id){
        $is_enabled = JPluginHelper::isEnabled('j2store', 'app_subscriptionproduct');
        if(!$is_enabled) return array();
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__j2store_subscriptions');
        $query->where('order_id = '.$db->quote($order_id));

        $db->setQuery($query);
        $result = $db->loadObjectList();

        return $result;
    }

    /**
     * Get subscriptions meta
     * */
    protected function getSubscriptionMetaData($subscription_id, $key){
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__j2store_metafields');
        $query->where('owner_id = '.$db->quote($subscription_id));
        $query->where('metakey = '.$db->quote($key));
        $query->where('namespace = '.$db->quote('subscription'));

        $db->setQuery($query);
        $result = $db->loadObject();

        return $result;
    }

    /**
     * Check subscription has same billing Id
     * */
    protected function checkAnyActiveSubscriptionHasSameBillingId($billingId, $subscription_id){
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('COUNT(mf.id)');
        $query->from('#__j2store_metafields as mf');
        $query->where('mf.metakey = '.$db->quote('billing_agreement_id'));
        $query->where('mf.owner_resource = '.$db->quote('subscriptions'));
        $query->where('mf.namespace = '.$db->quote('subscription'));
        $query->where('mf.metavalue = '.$db->quote($billingId));
        $query->where('mf.owner_id <> '.$db->quote($subscription_id));
        $query->join('LEFT', '#__j2store_subscriptions as s ON mf.owner_id = s.j2store_subscription_id');
        $query->where('s.status = '.$db->quote('active'));
        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Handles the CRON task of
     *
     * @param       $task
     * @param array $options
     */
    public function onJ2StoreProcessCron($task, $timeperiod=''){
        $app = J2Store::platform()->application();
        $option = $app->input->get('option','');
        if($task == 'paypal_tls_check'){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://tlstest.paypal.com');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            //curl_setopt($ch, CURLOPT_SSLVERSION, 6); // CURL_SSLVERSION_TLSv1_2
            $result = curl_exec($ch);
            $err_no = curl_errno($ch);
            $error_msg = curl_error($ch);
            curl_close($ch);
            $json = array();
            if(strtolower($result) == 'paypal_connection_ok'){
                $json['success'] = 1;
                $json['msg'] = 'Ok';
            }else{
                $json['error'] = $err_no.' :'.$error_msg;
            }
            echo json_encode($json);
            $app->close();

        }elseif($task == 'paypal_api_check'){

            $json = array();
            $now = new JDate();
            $timePeriod = 18400;
            $mode = $app->input->get('mode','');
            $fromDate = new JDate($now->toUnix() - $timePeriod);
            if($mode == 'sandbox'){
                $payment_url = 'https://api-3t.sandbox.paypal.com/nvp';
            }else{
                $payment_url = 'https://api-3t.paypal.com/nvp';
            }

            $transaction_params = array(
                'USER' => $this->username,
                'PWD' => $this->password,
                'SIGNATURE' => $this->signature,
                'METHOD' => 'TransactionSearch',
                'VERSION' => '106.0',
                'TRANSACTIONCLASS' => 'All',
                'STARTDATE' => $fromDate->format('Y-m-d\TH:i:s\Z', false, false)
            );
            $rawResponse = $this->sendCurlRequest($payment_url,$transaction_params);
            $fakeURI    = new JUri('http://localhost/index.php?' . $rawResponse);
            $ack = $fakeURI->getVar('ACK','');
            if(strtoupper($ack) == 'FAILURE'){
                $json['error'] = $fakeURI->getVar('L_LONGMESSAGE0','');
            }elseif(empty($ack)){
                $json['error'] = JText::_('J2STORE_PAYPAL_NO_RESPONSE_FROM_PAYPAL');
            }else{
                $json['success'] = 1;
            }
            echo json_encode($json);
            $app->close();
        }
        if ($task != 'paypalcollation' && $option != 'com_j2store')
        {
            return;
        }

        if (empty($this->username) || empty($this->password) || empty($this->signature))
        {
            return;
        }
        if(empty($timeperiod)) {
            $timeperiod = $app->input->get('timeperiod',3600);
        }

        // Load a list of latest PayPal sales
        $allSales = $this->getLatestSales($timeperiod);

        if (!is_array($allSales))
        {
            return;
        }

        // Loop through each sale and make a list of which ones do not correspond to an active subscription
        $db             = JFactory::getDbo();
        $needProcessing = array();
        $protoQuery     = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->qn('#__j2store_orders'))
            ->where($db->qn('orderpayment_type') . '=' . $db->q($this->_element));
        //->where($db->qn('order_state_id') . '=' . $db->q(1));

        foreach ($allSales as $sale)
        {
            $transactionId    = $sale['L_TRANSACTIONID'];
            $query = clone $protoQuery;
            $query->where($db->qn('transaction_id') . ' = ' . $db->q($transactionId));
            $db->setQuery($query);
            $countRows = $db->loadResult();

            if ($countRows < 1)
            {
                $needProcessing[] = $transactionId;
            }
        }

        // If there are no pending sales I don't have to do anything.
        if (empty($needProcessing))
        {
            return;
        }

        $thresholdTime = time() + 0.7 * 15;

        // Loop all pending sales, figure out which subscription they are referring to and activate the subscription
        foreach ($needProcessing as $transactionId)
        {
            if (time() > $thresholdTime)
            {
                return;
            }
            $rawResponse = $this->getTransactionDetails($transactionId);
            // We get a long query string as a response. We need to decode it to a hash array
            $fakeURI    = new JUri('http://localhost/index.php?' . $rawResponse);
            $custom = $fakeURI->getVar('CUSTOM', '');
            $tran_status = $fakeURI->getVar('PAYMENTSTATUS', '');
            if (empty($custom))
            {
                continue;
            }


            $custom_array = explode('|', $custom);

            $order_id  = isset($custom_array[0]) ? $custom_array[0]: '';

            if(empty($order_id)){
                continue;
            }
            $fof_helper = J2Store::fof();
            $order = $fof_helper->loadTable('Order', 'J2StoreTable',array('order_id'=>$order_id));
            if(!empty($order->order_id) && $order_id == $order->order_id){
                $currency = J2Store::currency();
                $currency_values= $this->getCurrency($order);
                $gross = $currency->format($order->order_total, $currency_values['currency_code'], $currency_values['currency_value'], false);

                // If the price paid doesn't match we don't accept the transaction
                if (abs($gross - $allSales[$transactionId]['L_AMT']) >= 0.05)
                {
                    continue;
                }
                $order->transaction_id = $transactionId;
                if (strtoupper($tran_status) == 'PENDING') {

                    // set order to pending. Also notify the customer that it is pending
                    $order->update_status ( 4, true );
                    // reduce the order stock. Because the status is pending.
                    $order->reduce_order_stock ();

                } elseif(strtoupper($tran_status) == 'COMPLETED') {
                    $order->payment_complete ();
                    $this->updateBillingAgreementId($order->order_id);
                }
                //$order->payment_complete ();

            }else{
                continue;
            }

        }
        return true;
    }

    /**
     * get available success transactions
     * @param $timePeriod - time period of transaction list
     * @return array
     */
    function getLatestSales($timePeriod = 86400){
        JLoader::import('joomla.utilities.date');
        $now = new JDate();

        $fromDate = new JDate($now->toUnix() - $timePeriod);

        $payment_url = $this->_getTransactionUrl();
        $transaction_params = array(
            'USER' => $this->username,
            'PWD' => $this->password,
            'SIGNATURE' => $this->signature,
            'METHOD' => 'TransactionSearch',
            'VERSION' => '106.0',
            'TRANSACTIONCLASS' => 'All',
            'STATUS' => 'Success',
            'STARTDATE' => $fromDate->format('Y-m-d\TH:i:s\Z', false, false)
        );
        $rawResponse = $this->sendCurlRequest($payment_url,$transaction_params);

        $this->_log($rawResponse,'CRON TRANS SEARCH RESPONSE');
        // We get a long query string as a response. We need to decode it to a hash array
        $fakeURI    = new JUri('http://localhost/index.php?' . $rawResponse);
        $array_resp = array();
        for ($transactionId = 0; $transactionId < 100; $transactionId++)
        {
            if (is_null(($fakeURI->getVar('L_TIMESTAMP' . $transactionId, null))))
            {
                break;
            }

            $transaction = array(
                'L_TIMESTAMP'     => $fakeURI->getVar('L_TIMESTAMP' . $transactionId, null),
                'L_TIMEZONE'      => $fakeURI->getVar('L_TIMEZONE' . $transactionId, null),
                'L_TYPE'          => $fakeURI->getVar('L_TYPE' . $transactionId, null),
                'L_EMAIL'         => $fakeURI->getVar('L_EMAIL' . $transactionId, null),
                'L_NAME'          => $fakeURI->getVar('L_NAME' . $transactionId, null),
                'L_TRANSACTIONID' => $fakeURI->getVar('L_TRANSACTIONID' . $transactionId, null),
                'L_STATUS'        => $fakeURI->getVar('L_STATUS' . $transactionId, null),
                'L_AMT'           => $fakeURI->getVar('L_AMT' . $transactionId, null),
                'L_CURRENCYCODE'  => $fakeURI->getVar('L_CURRENCYCODE' . $transactionId, null),
                'L_FEEAMT'        => $fakeURI->getVar('L_FEEAMT' . $transactionId, null),
                'L_NETAMT'        => $fakeURI->getVar('L_NETAMT' . $transactionId, null),
            );

            $array_resp[$transaction['L_TRANSACTIONID']] = $transaction;
        }
        return $array_resp;
    }

    public function sendCurlRequest($targetURL,$transaction_params){
        //url-ify the data for the POST
        $useAgent = "J2Store/".$this->_j2version;
        // Set up the request through cURL
        $ch = curl_init($targetURL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($transaction_params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $useAgent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        //  curl_setopt($ch, CURLOPT_CAINFO, JPATH_LIBRARIES . '/fof30/Download/Adapter/cacert.pem');
        //    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
        // Force the use of TLS (therefore SSLv3 is not used, mitigating POODLE; see https://github.com/paypal/merchant-sdk-php)
        //curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
        // This forces the use of TLS 1.x
        //curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        $rawResponse = curl_exec($ch);
        $errors = curl_error($ch);
        curl_close($ch);
        if(empty($rawResponse)){
            $rawResponse = 'ACK=FAILURE&L_LONGMESSAGE0='.$errors;
        }
        return $rawResponse;
    }

    /**
     * To load subscription card update form
     * */
    function onJ2StoreLoadSubscriptionPaymentCardUpdateForm($subscription, $order, $app_id)
    {
        if ($subscription->payment_method == $this->_element) {
            $document = J2Store::platform()->application()->getDocument();
            $script = '(function($) {';
            $script .= ' $(document).ready(function(){';
            $script .= '$("#button-subscription_update_card").val("'.JText::_('J2STORE_PAYPAL_LOADING_PLEASE_WAIT').'");';
            $script .= '$("#button-subscription_update_card").trigger("click").attr("disabled", "disabled");';
            $script .= ' });';
            $script .= '})(j2store.jQuery);';
            $document->addScriptDeclaration($script);
            $paypalForm = $this->_renderForm(array());

            return $paypalForm;
        }
    }
}

class PaypalExpressCheckoutForJ2StoreSubscriptionProduct{
    public static $instance = null;
    protected $username = null;
    protected $password = null;
    protected $signature = null;
    protected $api_url = null;
    public $auth_url = null;

    public function __construct($params = null) {
        $mode = $params->get ( 'sandbox', 0 );
        if($mode){
            $this->username = $params->get('sandbox_api_username','');
            $this->password = $params->get('sandbox_api_password','');
            $this->signature = $params->get('sandbox_api_signature','');
            $this->api_url = "https://api-3t.sandbox.paypal.com/nvp";
            $this->auth_url = 'https://www.sandbox.paypal.com/checkoutnow?token=';
        } else {
            $this->username = $params->get('api_username','');
            $this->password = $params->get('api_password','');
            $this->signature = $params->get('api_signature','');
            $this->api_url = "https://api-3t.paypal.com/nvp";
            $this->auth_url = 'https://www.paypal.com/checkoutnow?token=';
        }
    }

    public static function getInstance($config)
    {
        if (!self::$instance) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    /**
     * send request for SetExpressCheckout, GetExpressCheckoutDetails, DoExpressCheckoutPayment,
     *
     */
    public function sendRequest($data) {
        $data['USER'] = $this->username;
        $data['PWD'] = $this->password;
        $data['SIGNATURE'] = $this->signature;
        $data['VERSION'] = '86';
        $resp_data = array ();
        $ch = curl_init ();
        curl_setopt( $ch, CURLOPT_POST, true);
        curl_setopt ( $ch, CURLOPT_URL, $this->api_url );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, http_build_query ( $data ) );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        $resp = curl_exec ( $ch );
        $resp_data = $this->dataDecode ( urldecode ( $resp ) );
        return $resp_data;
    }

    /**
     * decode string and generate array
     *
     * @param string $resp
     * @return array $resp_data
     */
    protected function dataDecode($resp) {
        $resp_data = array ();
        $value = array ();
        if (isset ( $resp ) && ! empty ( $resp )) {
            $exploded = explode ( '&', $resp );
            foreach ( $exploded as $exp ) {
                $value = explode ( '=', $exp );
                $resp_data [$value [0]] = $value [1];
            }
        }
        return $resp_data;
    }

    /**
     * To check API is set
     * */
    public function checkSetAPI(){
        if($this->username != '' && $this->password != '' && $this->signature != ''){
            return true;
        } else {
            return false;
        }
    }
}

/* TYPICAL RESPONSE FROM PAYPAL INCLUDES:
 * mc_gross=49.99
* &protection_eligibility=Eligible
* &address_status=confirmed
* &payer_id=Q5HTJ93G8FQKC
* &tax=0.00
* &address_street=10101+Some+Street
* &payment_date=12%3A13%3A19+Dec+05%2C+2008+PST
* &payment_status=Completed
* &charset=windows-1252
* &address_zip=11259
* &first_name=John
* &mc_fee=1.75
* &address_country_code=US
* &address_name=John+Doe
* &custom=some+custom+value
* &payer_status=verified
* &business=receiver%40domain.com
* &address_country=United+States
* &address_city=Some+City
* &quantity=1
* &payer_email=sender%40emaildomain.com
* &txn_id=3JK16594EX581780W
* &payment_type=instant
* &payer_business_name=John+Doe
* &last_name=Doe
* &address_state=CA
* &receiver_email=receiver%40domain.com
* &payment_fee=1.75
* &receiver_id=YG9UDRP6DE45G
* &txn_type=web_accept
* &item_name=Name+of+item
* &mc_currency=USD
* &item_number=Number+of+Item
* &residence_country=US
* &handling_amount=0.00
* &transaction_subject=Subject+of+Transaction
* &payment_gross=49.99
* &shipping=0.00
* &=
*/

/**
 * VALID PAYMENT_STATUS VALUES returned from Paypal
 *
 * Canceled_Reversal: A reversal has been canceled. For example, you won a dispute with the customer, and the funds for the transaction that was reversed have been returned to you.
 * Completed: The payment has been completed, and the funds have been added successfully to your account balance.
 * Created: A German ELV payment is made using Express Checkout.
 * Denied: You denied the payment. This happens only if the payment was previously pending because of possible reasons described for the pending_reason variable or the Fraud_Management_Filters_x variable.
 * Expired: This authorization has expired and cannot be captured.
 * Failed: The payment has failed. This happens only if the payment was made from your customer�s bank account.
 * Pending: The payment is pending. See pending_reason for more information.
 * Refunded: You refunded the payment.
 * Reversed: A payment was reversed due to a chargeback or other type of reversal. The funds have been removed from your account balance and returned to the buyer. The reason for the reversal is specified in the ReasonCode element.
 * Processed: A payment has been accepted.
 * Voided: This authorization has been voided.
 */
