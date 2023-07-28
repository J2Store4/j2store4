<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreControllerOrders extends F0FController
{
	public function __construct($config) {
		parent::__construct($config);
		$this->registerTask('apply', 'save');
		$this->registerTask('saveNew', 'save');
	}


	protected function onBeforeBrowse()
	{
		if(!$this->checkACL('j2store.vieworder'))
		{
			return false;
		}
		return parent::onBeforeBrowse();
	}

	protected function onBeforeEdit() {
		if(!$this->checkACL('j2store.vieworder'))
		{
			return false;
		}
		return true;//parent::onBeforeEdit();
	}

	public function onBeforeCreateOrder(){

		if(!$this->checkACL('core.edit'))
		{
			return false;
		}
		return true;
	}

	public function saveOrderFee(){
		$app = JFactory::getApplication ();
		$data = $app->input->getArray($_POST);
		$order_id = $app->input->getString('order_id','');
		$order = F0FTable::getInstance('Order', 'J2StoreTable');
		$order->load(array('order_id'=>$order_id));
		$json = array();
		if(!empty($order->order_id)) {
			$feeTable = F0FTable::getAnInstance ( 'OrderFee', 'J2StoreTable' )->getClone();
			$data['fee_type'] = 'admin_added';
			$feeTable->bind($data);
			$feeTable->order_id = $order_id;
			if(isset( $data['tax_class_id'] ) && !empty( $data['tax_class_id'] )){
				$feeTable->taxable = 1;
			}
			$tax_data = array();
			$feeTable->tax_data = json_encode($tax_data);
			$feeTable->store();
			$json['success'] = 1;
		}else{
			$json['error'] = JText::_ ( 'J2STORE_ORDER_FEE_ERROR' );
		}
		echo json_encode ( $json );
		$app->close();
	}

	public function removeOrderFee(){
		$app = JFactory::getApplication ();
		$fee_id = $app->input->getInt('fee_id',0);
		if(!empty($fee_id)) {
			$feeTable = F0FTable::getAnInstance ( 'OrderFee', 'J2StoreTable' )->getClone();
			$feeTable->load ($fee_id);
			if($feeTable->j2store_orderfee_id > 0){
				$feeTable->delete ();
			}
			$json['success'] = 1;
		}else{
			$json['error'] = JText::_ ( 'J2STORE_ORDER_FEE_ERROR' );
		}
		echo json_encode ( $json );
		$app->close();
	}

	/*
	 * Method to save Order status
	*/
	public function saveOrderstatus(){

		$data = $this->input->getArray($_POST);
		$id = $this->input->getInt('id');
		$status =false;
		$return = isset($data['return']) ? $data['return'] : '';
		$order_id = $this->input->getString('order_id');
		$order = F0FTable::getInstance('Order', 'J2StoreTable');
		$order->load(array('order_id'=>$order_id));

		if(!empty($order->order_id)) {
		    $is_need_notify = false;
            if(isset($data['notify_customer']) && $data['notify_customer'] == 1){
                $is_need_notify = true;
            }
			//update status
			$order->update_status($data['order_state_id'], $is_need_notify);

			if(isset($data['reduce_stock']) && $data['reduce_stock'] == 1) {
				$order->reduce_order_stock();
			}

			if(isset($data['increase_stock']) && $data['increase_stock'] == 1) {
				$order->restore_order_stock();
			}

			if(isset($data['grant_download_access']) && $data['grant_download_access'] == 1) {
				$order->grant_download_permission();
			}

			if(isset($data['reset_download_expiry']) && $data['reset_download_expiry'] == 1) {
				$order->reset_download_expiry();
			}

			if(isset($data['reset_download_limit']) && $data['reset_download_limit'] == 1){
                $order->reset_download_limit();
            }

		}

		//is it an ajax call
		if($return){
			$json =array();
			$link = 'index.php?option=com_j2store&view=orders';
			$json['success']['link'] = $link;
			echo json_encode($json);
			JFactory::getApplication()->close();
		}else {
			$url ='index.php?option=com_j2store&view=order&task=edit&id='.$id;
			$this->setRedirect($url);
		}

	}

	public function resendEmail(){
		$app = JFactory::getApplication ();
		$id = $app->input->getInt('id',0);
		$message = '';
		if($id > 0 ){
			$order = F0FTable::getAnInstance('Order' ,'J2StoreTable');
			$order->load($id);
			if(!empty( $order->j2store_order_id ) && $order->j2store_order_id == $id ){
				$order->notify_customer();
			}
		}
		$app->redirect ( 'index.php?option=com_j2store&view=order&task=edit&id='.$id);
	}

	/**
	 * Method to save Order Customer Note
	 */
	public function saveOrderCnote(){
		$data = $this->input->getArray($_POST);
		$id = $this->input->getInt('id');
		$order = F0FTable::getAnInstance('Order' ,'J2StoreTable');
		$msg = JText::_('J2STORE_ORDER_SAVE_ERROR');
		$msgType='warning';
		//must check id exists
		if($id){
			//then load the id and confirm row exists
			if($order->load($id)){
				//now assign the customer note to order customer note object
				$order->customer_note = $data['customer_note'];
				$msg = JText::_('J2STORE_ORDER_SAVED_SUCCESSFULLY');
				$msgType ='message';
				if(!$order->save($order)){
					$msg = JText::_('J2STORE_ORDER_SAVE_ERROR');
					$msgType='warning';
				}
			}
		}
		$url ='index.php?option=com_j2store&view=order&task=edit&id='.$id;
		$this->setRedirect($url, $msg,$msgType);

	}

	/**
	 * Method to save shipping tracking id
	 */
	public function saveTrackingId(){
		$data = $this->input->getArray($_POST);
		$id = $this->input->getInt('id');
		$order = F0FTable::getAnInstance('Order' ,'J2StoreTable');
		$msg = '';
		$msgType='warning';

		//must check id exists
		if($order->load($id)){
			//load the shipping
			$ordershipping = F0FTable::getAnInstance('Ordershipping', 'J2StoreTable');

			if($ordershipping->load(array('order_id'=>$order->order_id))){
				$ordershipping->ordershipping_tracking_id = isset($data['ordershipping_tracking_id']) ? $data['ordershipping_tracking_id'] : '';
			}else {
				$ordershipping->order_id = $order->order_id;
				$ordershipping->ordershipping_tracking_id = isset($data['ordershipping_tracking_id']) ? $data['ordershipping_tracking_id'] : '';
			}
			if($ordershipping->store()) {
				$msg = JText::_('J2STORE_ORDER_SAVED_SUCCESSFULLY');
				$msgType ='message';
			}else {
				$msg = JText::_('J2STORE_ORDER_SAVE_ERROR');
				$msgType='warning';
			}

		}
		$url ='index.php?option=com_j2store&view=order&task=edit&id='.$id;
		$this->setRedirect($url, $msg,$msgType);

	}

	/**
	 * Method to edit orderinfo based on the address type
	 *
	 */
	function setOrderinfo(){
		$order_id  = $this->input->getString('order_id');
		$address_type = $this->input->getString('address_type');
		$orderinfo = F0FTable::getAnInstance('Orderinfo','J2StoreTable');
		$orderinfo->load(array('order_id'=>$order_id));
		$type = "all_".$address_type;
		$custom_datas = json_decode($orderinfo->$type);
		
		$processed = $this->removePrefix((array)$orderinfo,$address_type);
		if (!empty($custom_datas)) {
			foreach($custom_datas as $key =>$custom_data){
				$processed->$key = $custom_data->value;
			}
		}
		$model = F0FModel::getTmpInstance('Orders','J2StoreModel');
		$view = $this->getThisView();
		$view->setModel($model, true);
		$view->addTemplatePath(JPATH_ADMINISTRATOR.'/components/com_j2store/views/order/tmpl/');
		$view->set('address_type',$address_type);
		$fieldClass  = J2Store::getSelectableBase();
		$view->set('fieldClass' , $fieldClass);
		$view->set('orderinfo',$processed);
		$view->set('item',$orderinfo);
		$view->setLayout('address');
		$view = $this->display();
	}

	/**
	 * Method to save orderinfo
	 */
	function saveOrderinfo(){
		$data = $this->input->getArray($_POST);
		$order_id = $this->input->getString('order_id');
		$order = F0FTable::getAnInstance('Order','J2StoreTable');
		$order->load(array('order_id'=>$order_id));
		$address_type = $this->input->getString('address_type');
		$orderinfo = F0FTable::getAnInstance('Orderinfo','J2StoreTable');
		$orderinfo->load(array('order_id'=>$order_id));

		//$orderinfo->bind($data);
		$msg =JText::_('J2STORE_ORDERINFO_SAVED_SUCCESSFULLY');
		$msgType='message';
		$data['all_'.$address_type]= $order->processCustomFields($address_type, $data);
		if(!$orderinfo->save($data)){
			$msg =JText::_('J2STORE_ORDERINFO_SAVED_SUCCESSFULLY');
			$msgType='warning';
		}
		$url = "index.php?option=com_j2store&view=orders&task=setOrderinfo&order_id=".$order_id."&address_type=".$address_type."&layout=address&tmpl=component";
		$this->setRedirect($url, $msg,$msgType);

	}

	/**
	 * Method to remove the prefix and return result of address
	 * @param unknown_type $input
	 * @param unknown_type $prefix
	 */
	public function removePrefix($input ,$prefix) {
		$keys = array_keys($input);
		$values =array();
		$return = new JObject();
		foreach($input as $k =>$value){
			if (strpos($k,$prefix.'_') === 0){
				$key =  str_replace($prefix.'_','',$k);
				$return->$key = $value;
			}
		}

		return $return;
	}

	/**
	 * Method to get Countrylist
	 */
	public function getCountry(){
		$app = JFactory::getApplication();
		$country_id = $this->input->getInt('country_id');
		$zone_id = $this->input->getInt('zone_id');
		if($country_id) {
			$zones = F0FModel::getTmpInstance('Zones', 'J2storeModel')->country_id($country_id)->getList();
		}
		$json = array();
		$json['zone'] = $zones ;
		echo json_encode($json);
		$app->close();

	}


	function download() {
		$app = JFactory::getApplication();
		$ftoken = $app->input->getString('ftoken', '');

		if($ftoken) {
			$table = F0FTable::getInstance('Upload', 'J2StoreTable');
			if($table->load(array('mangled_name'=>$ftoken))) {
				$name = $table->original_name;
				$mask = basename($name);
				$file = $table->saved_name;
				jimport('joomla.filesystem.file');
				$path = JPATH_ROOT.'/media/j2store/uploads/'.$file;
				if(JFile::exists($path)) {
					F0FModel::getTmpInstance('Orderdownloads', 'J2StoreModel')->downloadFile($path, $mask);
					$app->close();
				}
			}
		}
	}



	public function printOrder(){
		$app = JFactory::getApplication();
		$order_id = $this->input->getString('order_id');
		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}
		$order = F0FTable::getInstance('Order' ,'J2StoreTable');
		$order->load(array('order_id' => $order_id));
		$error = false;
		$view->assign('order' ,$order );

		$view->assign('error', $error);
		$view->setLayout('print');
		$view->display();
	}


	public function printShipping(){
		$app = JFactory::getApplication();
		$order_id = $this->input->getString('order_id');
		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		$order = F0FTable::getInstance('Order' ,'J2StoreTable');
		$order->load(array('order_id' => $order_id));

		$orderinfo = F0FTable::getAnInstance('Orderinfo','J2StoreTable');
		$orderinfo->load(array('order_id'=>$order_id));

		$error = false;
		$view->assign('orderinfo' ,$orderinfo );
		$view->assign('item' ,$order );
		$view->assign('params' ,J2Store::config() );
		$view->assign('error', $error);
		$view->setLayout('print_shipping');
		$view->display();
	}
	
	/**
	 * Method to create or edit an existing order
	 *
	 */
	public function createOrder(){
		$option = $this->input->getCmd('option', 'com_j2store');
		$componentName = str_replace('com_', '', $option);
		$app = JFactory::getApplication();
		$session  = JFactory::getSession();
		$params = J2Store::config();
		
		$j2store_order_id = $this->input->getInt('oid',0);
		if($j2store_order_id == 0){
			$cid = $this->input->get('cid',array());
			if(isset($cid[0])){
				$j2store_order_id =$cid[0]; 
			}
		}
		$view = $this->getThisView('Orders');
		$sublayout = $app->input->getString('layout','basic');
		
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}
		$order = F0FTable::getInstance('Order' ,'J2StoreTable')->getClone();
		$order->load($j2store_order_id);

		//On before admin edit order
		$app->triggerEvent("onJ2StoreOnBeforeEditOrder", array( $order ) );
		
		//get currency id, value and code and store it
		$currency = J2Store::currency();		
		$view->assign('order' ,$order );
		$view->assign('currency' ,$currency );
		$view->assign('params',$params);
		$order_items = $order->getItems();
		
		if((count($order_items) == 0) && in_array($sublayout, array('payment_shipping_methods','summary'))){
			$sublayout = 'items';
		}
		$fieldsets = array('basic' => JText::_('J2STORE_STORES_GROUP_BASIC'),
				'billing' => JText::_('J2STORE_BILLING_ADDRESS'),
				'shipping' => JText::_('J2STORE_SHIPPING_ADDRESS'),
				'items' => JText::_('J2STORE_ORDER_ITEMS'),
				'payment_shipping_methods'=> JText::_('J2STORE_PAYMENT_SHIPPING_METHODS'),
				'summary' => JText::_('J2STORE_ORDER_SUMMARY'),
		);
		$view->assign('fieldsets',$fieldsets);
		switch($sublayout){
			case 'basic':
				$update_history = 0;
				$native = JLanguageHelper::detectLanguage();
				if (empty($native))
				{
					$native = 'en-GB';
				}
				// Get the list of available languages.
				$languages_list = JLanguageHelper::createLanguageList($native);
				$languages = array();
				foreach ($languages_list as $language){
					$languages[$language['value']] = $language['text'];
				}
				if(empty($order->order_state_id)){
					$order->order_state_id = 5;
				}
				if(empty($order->order_id)){
					$update_history = 1;
				}
				$order_status = J2Html::getOrderStatusHtml($order->order_state_id);
				$view->assign('update_history',$update_history); 
				$view->assign('order_status',$order_status);
				$view->assign('languages',$languages);
				break;
			case 'billing':
				$orderinfo = $order->getOrderInformation();
				$address_model = F0FModel::getTmpInstance('Addresses', 'J2StoreModel');				
				$addresses = $address_model->user_id($order->user_id)->getList();
				$billing_processed = $this->removePrefix((array)$orderinfo,'billing');												
				$view->assign('orderinfo',$orderinfo);
				if($order->user_id) {
					$address = $address_model->user_id($order->user_id)->getFirstItem();
				} else {
					$address = F0FTable::getAnInstance('Address', 'J2StoreTable');
				}
				$view->assign('addresses',$addresses);					
				$view->assign('billing_address_id', $session->get('billing_address_id','','j2store'));
				$view->assign('fieldClass', J2Store::getSelectableBase());
				$view->assign('storeProfile', J2Store::storeProfile());
				$fields = J2Store::getSelectableBase()->getFields('billing',$address,'address');
				$view->assign('fields', $fields);
				$view->assign('address',F0FTable::getAnInstance('Address', 'J2StoreTable'));
				break;
			case 'shipping':
				$orderinfo = $order->getOrderInformation();
				$this->checkBillingInfo ( $order );
				$address_model = F0FModel::getTmpInstance('Addresses', 'J2StoreModel');
                $address_model->clearState();
				$addresses = $address_model->user_id($order->user_id)->getList();
				$shipping_processed = $this->removePrefix((array)$orderinfo,'shipping');				
				$view->assign('orderinfo',$orderinfo);
				$view->assign('addresses',$addresses);
				$view->assign('shipping_address_id', $session->get('shipping_address_id','','j2store'));
				$view->assign('fieldClass', J2Store::getSelectableBase());
				$view->assign('storeProfile', J2Store::storeProfile());
				if($order->user_id) {
					$address = $address_model->user_id($order->user_id)->getFirstItem();
				} else {
					$address = F0FTable::getAnInstance('Address', 'J2StoreTable');
				}
				$fields = J2Store::getSelectableBase()->getFields('shipping',$address,'address');
				$view->assign('fields', $fields);
				$view->assign('address',F0FTable::getAnInstance('Address', 'J2StoreTable'));
				break;
				// let us load items
			case 'items':
				$this->checkBillingInfo ( $order );
				//$taxes = $order->getOrderTaxRates();
				$orderitems = $order->getItems();
				//$view->assign('taxes',$taxes);				
				$view->assign('orderitems',$orderitems);
				break;
			case 'payment_shipping_methods':
				$this->checkBillingInfo ( $order );
				$payment_plugins = J2Store::plugin()->getPluginsWithEvent( 'onJ2StoreGetPaymentPlugins' );						
				$default_method = !empty($order->orderpayment_type) ? $order->orderpayment_type : $params->get('default_payment_method', '');
				$plugins = array();
					
				if ($payment_plugins)
				{
					foreach ($payment_plugins as $plugin)
					{
						$results = $app->triggerEvent("onJ2StoreGetPaymentOptions", array( $plugin->element, $order ) );
						if (!in_array(false, $results, false))
						{
							if(!empty($default_method) && $default_method == $plugin->element) {
								$plugin->checked = true;
							}
							$plugins[] = $plugin;
						}
					}
				}
				$shipping = $order->getOrderShippingRate();
				//$shipping_amount = $order->order_shipping + $order->order_shipping_tax;

				$view->assign('shipping_tracking_id',$shipping->ordershipping_tracking_id);
				$view->assign('shipping_name',$shipping->ordershipping_name);
				$view->assign('shipping_code',$shipping->ordershipping_code);
				$view->assign('shipping_price',$order->order_shipping);
                $view->assign('shipping_tax',$order->order_shipping_tax);
				$view->assign('shipping_plugin',$shipping->ordershipping_type);		
				$view->assign('paymentplugins',$plugins);
				break;
			
			case 'summary':
				$this->checkBillingInfo ( $order );
				$taxes = $order->getOrderTaxrates();
				
				$view->assign('taxes',$taxes);
				$view->assign('vouchers' , $order->getOrderVouchers());
				$view->assign('coupons' , $order->getOrderCoupons());
				$view->assign('shipping',$order->getOrderShippingRate());
				break;
		}
		$view->assign('form_prefix','jform');
		$view->assign('storeProfile',J2Store::storeProfile());
		$view->assign('layout',$sublayout);
		$view->setLayout('order');
		$view->display();
	}

	/**
	 * Method to check billing info is available
	 * @param Order object
	 * @return null
	*/
	function checkBillingInfo($order){
		$app = JFactory::getApplication ();
		$orderinfo = $order->getOrderInformation();
		if(empty( $orderinfo ) || empty( $orderinfo->billing_first_name )){
			//redirect to billing
			$url ='index.php?option=com_j2store&view=orders&task=createOrder&layout=billing&oid='.$order->j2store_order_id;
			$app->redirect ( $url,JText::_ ( 'J2STORE_BILLING_ADDRESS_REQUIRED' ),'warning' );
		}
	}
	/**
	 * Method to save the Order step by step
	 * based on the layout
	 * switch to save function
	 * @return result array()
	 */
	
	public function saveAdminOrder(){
		$app = JFactory::getApplication();
		// get the session object
		$session = JFactory::getSession();
		$sublayout = $app->input->getString('layout','basic');
		$next_layout = $app->input->getString('next_layout','');
		$order_id = $this->input->getInt('oid',0);
		$order = F0FTable::getInstance('Order' ,'J2StoreTable');
		$order->load($order_id);		
		$result =array('msg' => JText::_('J2STORE_SAVE_SUCCESS') ,'msgType'=>'message');
		$data = $app->input->get('jform',array(),'ARRAY');
		J2Store::plugin ()->event ( 'BeforeSaveAdminOrder', array(&$order) );
		switch($sublayout){
			// save basic function
			case 'basic':
				$result = $order->saveAdminOrderBasic($data);
				break;
			// save billing information
			case 'billing':
				//$address_type = $app->input->getString('address_type' ,'billing');
				$data = $app->input->getArray($_REQUEST);							
				$result = $order->saveAdminOrderInfo($data);
				break;
			//save shipping address
			case 'shipping':
				//$address_type = $app->input->getString('address_type' ,'shipping');
				$data = $app->input->getArray($_REQUEST);
				$result = $order->saveAdminOrderInfo($data);
				break;				
			case 'payment_shipping_methods':
				$shipping_name = $app->input->getString('shipping_name','');

				if(!empty($shipping_name)){	
					$post_data = $app->input->getArray($_REQUEST);					
					$values = array();
					$values['shipping_price'] = isset($post_data['shipping_price']) ? $post_data['shipping_price'] : 0;
					$values['shipping_extra'] = isset($post_data['shipping_extra']) ? $post_data['shipping_extra'] : 0;
					$values['shipping_tax'] = isset($post_data['shipping_tax']) ? $post_data['shipping_tax'] : 0;
					$values['shipping_code']= isset($post_data['shipping_code']) ? $post_data['shipping_code'] : 0;
					$values['shipping_name']= isset($post_data['shipping_name']) ? $post_data['shipping_name'] : '';
					$values['shipping_plugin']= isset($post_data['shipping_plugin']) ? $post_data['shipping_plugin'] : 'shipping_admin';

					$session->set('shipping_values',$values,'j2store');
				}
				$shipping_tracking_id = $app->input->getString('shipping_tracking_id','');

				if(isset($shipping_tracking_id)){
					$ordershipping = F0FTable::getAnInstance('Ordershipping', 'J2StoreTable');
					if($ordershipping->load(array('order_id'=>$order->order_id))){
						$ordershipping->ordershipping_tracking_id = $shipping_tracking_id;
						$ordershipping->store();
					}

				}
				$order->orderpayment_type = $app->input->getString('payment_plugin','');
				$order->getAdminTotals();
				break;	
			case 'items':
				$order->getAdminTotals();
				$result['msg'] = JText::_('J2STORE_ORDER_ITEM_CHANGE_SUCCESS');
				break;
			case 'summary':
				$order->getAdminTotals();
				J2Store::plugin()->event('AfterSummarySaveOrder', array($order));
				break;			
		}
		
		$url ='index.php?option=com_j2store&view=orders&task=createOrder&layout='.$sublayout.'&oid='.$order->j2store_order_id;		
		if($next_layout=="summary" && $sublayout=="summary"){
			$url ="index.php?option=com_j2store&view=order&id=".$order->j2store_order_id;
		}elseif($next_layout !=''){
			$url ='index.php?option=com_j2store&view=orders&task=createOrder&layout='.$next_layout.'&oid='.$order->j2store_order_id;
		}
		$this->setRedirect($url ,$result['msg'] , $result['msgType']);
	}
	function calculateTax(){
		$app = JFactory::getApplication();
		$order_id = $app->input->get('oid',0);
		$order = F0FTable::getInstance('Order' ,'J2StoreTable');
		$order->load($order_id);
		$discounts = $order->getOrderDiscounts();
		$session = JFactory::getSession();
		foreach ($discounts as $discount){
		    if(isset($discount->discount_type) && $discount->discount_type == 'voucher'){
                $session->set('voucher', $discount->discount_code, 'j2store');
            }
            if(isset($discount->discount_type) && $discount->discount_type == 'coupon'){
                $session->set('coupon', $discount->discount_code, 'j2store');
            }
        }

		$order->getAdminTotals(true);
		//echo "<pre>";print_r($order);exit;
		$result['msg'] = JText::_('J2STORE_ORDER_ITEM_CHANGE_SUCCESS');
		$url ='index.php?option=com_j2store&view=orders&task=createOrder&layout=summary&oid='.$order->j2store_order_id;
		$json = array();
		$json['success']= 1;
		$json['redirect'] = $url;
		echo json_encode($json);
		$app->close();
	}
	/**
	 * validate order address
	 *   */
	public function validate_address(){
		$app = JFactory::getApplication();
		$data = $app->input->getArray($_POST);		
		$json = array();
		if(isset($data['order_id']) && isset($data['validate_type']) && $data['order_id'] && $data['validate_type']){
			$order = F0FTable::getInstance('Order' ,'J2StoreTable');
			$order->load(array(
					'order_id' => $data['order_id']
			));
			$data['email'] = $order->user_email;
			$data['admin_display_error'] = 1;
			$selectableBase = J2Store::getSelectableBase();			
			$json = $selectableBase->validate($data, $data['validate_type'], 'address');			
		}else{
			$json['error']['validate_type'] = JText::_('J2STORE_INVALID_ADDRESS_TYPE');
		}
		J2Store::plugin()->event('CheckoutValidateBilling',array(&$json));
		if(!$json){
			$json['success'] = 1;
		}
		echo json_encode($json);
		$app->close();
	}

	/**
	 * get product list in search
	 *    */
	public function getproducts(){
		$app = JFactory::getApplication();
		$q = $app->input->post->getString('q');
		$json = array();
		$model = F0FModel::getTmpInstance('Products','J2StoreModel');
		$model->setState('search',$q);
		//$items= $model->getSFProducts();
		$items= $model->getSearchProduct();
		if(count($items)) {
			foreach($items as &$item) {
				F0FModel::getTmpInstance('Products', 'J2StoreModel')->runMyBehaviorFlag(true)->getProduct($item);
			}
		}
		echo json_encode($items);
		$app->close();
	}
		
	function removeOrderitem(){
		$app = JFactory::getApplication();
		$item_ids = $app->input->get('cid',array(),"ARRAY");
		$order_id = $app->input->get('oid',0);
		$json = array();
		if($order_id > 0){
			$url = 'index.php?option=com_j2store&view=orders&task=createOrder&layout=items&oid='.$order_id;
			$order = F0FTable::getInstance('Order' ,'J2StoreTable')->getClone();
			$order->load($order_id);
			$items = $order->getItems();
			if(count($items) == count($item_ids) || count($item_ids)==0){
				$json['error'] = JText::_("J2STORE_ORDER_MUSTHAVE_ATLEAST_ONE_ITEM");				
			}else{				
				foreach ($item_ids as $item_id){				
					$orderItem = F0FTable::getAnInstance('OrderItem','J2StoreTable')->getClone();
					$orderItem->load($item_id);
					$item_name = $orderItem->orderitem_name;
					$item_sku =  $orderItem->orderitem_sku;
					if(!empty($orderItem->cartitem_id)){
						$CartItem = F0FTable::getAnInstance('CartItem','J2StoreTable')->getClone();
						$CartItem->delete($orderItem->cartitem_id);
						$orderitemattribute = F0FTable::getAnInstance('OrderItemAttribute', 'J2StoreTable')->getClone();
						$orderitemattribute->load(array(
								'orderitem_id' => $item_id
						));
						if(isset($orderitemattribute->j2store_orderitemattribute_id) && $orderitemattribute->j2store_orderitemattribute_id >0){
							$orderitemattribute->delete();

						}
					}
					if($orderItem->delete()){
						$msg = JText::sprintf('J2STORE_ORDERITEM_REMOVED',$item_name,$item_sku);
						$order->add_history($msg);
					}
				}
				$json['success'] = 1;
				$order->getAdminTotals();				
			}
		}else{
			$json['error'] = JText::_('J2STORE_ORDER_NOT_FOUND_MISSING');
		}
		echo json_encode($json);
		$app->close();		
	}

	function updateInventry($type){
		$app = JFactory::getApplication();
		$variant_id = $app->input->getInt('variant_id',0);
		$qty = $app->input->getInt('qty',0);
		$order_id = $app->input->get('order_id',0);

		$json = array();
		if(!empty($variant_id)){
			$variant_model = F0FModel::getTmpInstance('Variants', 'J2StoreModel')->getClone();
			$variant = $variant_model->getItem($variant_id);

			//product name
			$product = J2Store::product()->setId($variant->product_id)->getProduct();
			$product_model = F0FModel::getTmpInstance('Products', 'J2StoreModel');
			$product_data = $product_model->getProduct($product);
			$product_name = "";
			if($product_data->j2store_product_id){
				$product_name = $product_data->product_name;
			}

			F0FTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
			$order = F0FTable::getInstance('Order', 'J2StoreTable')->getClone();
			$order->load(array('order_id'=>$order_id));
			if($variant && J2Store::product()->managing_stock($variant)) {
				$old_qty = $product_data->variant->quantity;
				if($type == "add"){
					$new_qty = $variant->increase_stock($qty);
					$msg = JText::sprintf('J2STORE_ORDERITEM_STOCK_ADDED',$product_name,$old_qty,$new_qty);
					$order->add_history($msg);
					$json['success'] = $msg;
				}elseif($type == "remove"){
					$new_qty = $variant->reduce_stock($qty);
					$reduce_msg = JText::sprintf('J2STORE_ORDERITEM_STOCK_REDUCED',$product_name,$old_qty,$new_qty);
					$order->add_history($reduce_msg);
					$json['success'] = $reduce_msg;
				}
			}else{
				$json['error'] = JText::_('J2STORE_PRODUCT_STOCK_INVENTRY_NOT_AVAILABLE');

			}
		}
		return $json;
	}

	function addInventry(){
		$app = JFactory::getApplication();
		$json = $this->updateInventry('add');
		echo json_encode($json);
		$app->close();
	}

	function removeInventry(){
		$app = JFactory::getApplication();
		$json = $this->updateInventry('remove');
		echo json_encode($json);
		$app->close();
	}
}