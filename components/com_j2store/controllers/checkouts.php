<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2StoreControllerCheckouts extends F0FController
{

	protected $cacheableTasks = array();

	function execute($task) {
		if(in_array($task, array('add', 'edit', 'read', 'browse'))) {
			$task='browse';
		}
		return parent::execute($task);
	}

	protected function onBeforeGenericTask($task)
	{
		$format = JFactory::getApplication()->input->getString('format', '');
		$forbidden = array('json', 'csv', 'pdf');
		if(in_array(strtolower($format), $forbidden)) {
			return false;
		}

		return parent::onBeforeGenericTask($task);
	}

	protected function onBeforeBrowse() {

		$format = JFactory::getApplication()->input->getString('format', '');
		$forbidden = array('json', 'csv', 'pdf');
		if(in_array(strtolower($format), $forbidden)) {
			return false;
		}

		return parent::onBeforeBrowse();
	}

	public function display($cachable = false, $urlparams = array(), $tpl=null) {

		$document = F0FPlatform::getInstance()->getDocument();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();

		if ($document instanceof JDocument)
		{
			$viewType = $document->getType();
		}
		else
		{
			$viewType = $this->input->getCmd('format', 'html');
		}

		$view = $this->getThisView();

		// Get/Create the model

		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		// Set the layout
		$view->setLayout(is_null($this->layout) ? 'default' : $this->layout);

		$isLogged = 0;
		if($user->id) {
			$isLogged = 1;
		}
		$view->assign('logged', $isLogged);

		$order = F0FModel::getTmpInstance('Orders', 'J2StoreModel')->initOrder()->getOrder();
		$items = $order->getItems();
		$session = JFactory::getSession ();
		$is_mobile = $session->get('is_mobile','','j2store');
        $cart_params = array();
        if ($is_mobile){
            $cart_params['mobile'] = 'mobile';
        }
		$link = J2Store::platform()->getCartUrl($cart_params);//JRoute::_('index.php?option=com_j2store&view=carts'.$mobile);

		if(count($items) < 1) {
			$app->enqueueMessage(JText::_('J2STORE_CART_NO_ITEMS'), 'notice');
			$app->redirect($link);

		}

		//validate stock
		if($order->validate_order_stock() == false) {
			$app->redirect($link);
		}
		//prepare shipping
		// Checking whether shipping is required
		$showShipping = false;

		if(J2Store::config()->get('show_shipping_address', 0)) {
			$showShipping = true;
		}

		if ($isShippingEnabled = $order->isShippingEnabled())
		{
			$showShipping = true;
		}

		$view->assign('showShipping', $showShipping);

		//trigger on before checkout event
		J2Store::plugin()->event('BeforeCheckout', array($order,&$view));

		// Display without caching
		$view->display();

	}


	function login() {
		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$view = $this->getThisView();

		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}
		//$model		= $this->getModel('checkouts');
		//check session
		$account = $session->get('account', 'register', 'j2store');
		if (isset($account)) {
			$view->assign('account', $account);
		} else {
			$view->assign('account', 'register');
		}

		$view->assign('params', J2Store::config());
		$view->setLayout('default_login');
		$html = '';

		ob_start();
		$view->display();
		$html .= ob_get_contents();
		ob_end_clean();
		echo $html;
		$app->close();
	}


	function login_validate() {

		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$session = JFactory::getSession();
		$params = J2Store::config();
		$session->set('uaccount', 'login', 'j2store');

		$view = $this->getThisView();

		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		$redirect_url = J2Store::platform()->getCheckoutUrl();

		$json = array();

		if ($user->id) {
			$json['redirect'] = $redirect_url;
		}
		J2Store::plugin()->eventWithArray('CheckoutBeforeLogin', array(&$json));

		if (!$json) {

			$userHelper = J2Store::user();
			//now login the user
			if ( !$userHelper->login(
					array('username' => $app->input->getString('email'), 'password' => $app->input->getRaw('password'))
			))
			{
				$json['error']['warning'] = JText::_('J2STORE_CHECKOUT_ERROR_LOGIN');
			}

		}

		if (!$json) {
			$session->clear('guest', 'j2store');
            $user = JFactory::getUser();
			// Default Addresses
			$address_info = F0FModel::getTmpInstance('Addresses', 'J2StoreModel')->user_id($user->id)->getFirstItem();

			if ($address_info) {
				if ($params->get('config_tax_default') == 'shipping') {
					$session->set('shipping_country_id', $address_info->country_id, 'j2store');
					$session->set('shipping_zone_id',$address_info->zone_id, 'j2store');
					$session->set('shipping_postcode',$address_info->zip, 'j2store');
				}

				if ($params->get('config_tax_default') == 'billing') {
					$session->set('billing_country_id', $address_info->country_id, 'j2store');
					$session->set('billing_zone_id',$address_info->zone_id, 'j2store');
					$session->set('billing_postcode',$address_info->zip, 'j2store');
				}
			} else {
				$session->clear('shipping_country_id', 'j2store');
				$session->clear('shipping_zone_id', 'j2store');
				$session->clear('shipping_postcode', 'j2store');
				$session->clear('billing_country_id', 'j2store');
				$session->clear('billing_zone_id', 'j2store');
				$session->clear('billing_postcode', 'j2store');
			}

			$json['redirect'] = $redirect_url;
		}

		J2Store::plugin()->eventWithArray('CheckoutAfterLogin', array(&$json));

		echo json_encode($json);
		$app->close();
	}

	function register() {
		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$params = J2Store::config();

		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}
		$order = F0FModel::getTmpInstance('Orders', 'J2StoreModel')->initOrder()->getOrder();
		//$link = JRoute::_('index.php?option=com_j2store&view=carts');

		$session->set('uaccount', 'register', 'j2store');

		$selectableBase = J2Store::getSelectableBase();
		$view->assign('fieldsClass', $selectableBase);
		$address = F0FTable::getAnInstance('address', 'J2StoreTable');
		$fields = $selectableBase->getFields('billing',$address,'address');
		J2Store::plugin ()->event ( 'BeforeCheckoutRegister', array(&$address,$order) );
		$view->assign('fields', $fields);
		$view->assign('address', $address);

		//get layout settings
		$view->assign('storeProfile', J2Store::storeProfile());



		$showShipping = false;
		if($params->get('show_shipping_address', 0)) {
			$showShipping = true;
		}

		if ($isShippingEnabled = $order->isShippingEnabled())
		{
			$showShipping = true;
		}

		$this->showShipping = $showShipping;

		$view->assign( 'showShipping', $showShipping );
        $view->assign( 'privacyconsent_enabled',JPluginHelper::isEnabled('system', 'privacyconsent'));
		$view->setLayout( 'default_register');
		$html = '';

		ob_start();
		$view->display();
		$html .= ob_get_contents();
		ob_end_clean();
		echo $html;
		$app->close();
	}

	function register_validate() {
        $platform = J2Store::platform();
		$app = $platform->application();
		$user = JFactory::getUser();
		$session = $app->getSession();
		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

        $redirect_url = $platform->getCheckoutUrl();
		$data = $app->input->getArray($_POST);
		$address_model = F0FModel::getTmpInstance('Addresses', 'J2StoreModel');
		$store_address = J2Store::storeProfile();
		$userHelper = J2Store::user();
        $privacy_plugin_enabled = JPluginHelper::isEnabled('system', 'privacyconsent');
		$json = array();

		// Validate if customer is already logged out.
		if ($user->id) {
			$json['redirect'] = $redirect_url;
		}

		if (!$json) {

			$selectableBase = J2Store::getSelectableBase();
			$json = $selectableBase->validate($data, 'billing', 'address');

			//validate the password fields
			$userHelper->validatePassword($app->input->post->getString('password'),$app->input->post->getString('confirm'),$json);

			//check email
			if ((strlen($app->input->post->get('email')) < 4)) {
				$json['error']['email'] = JText::_('J2STORE_EMAIL_REQUIRED');
			}

			//check email
			if($userHelper->emailExists($app->input->post->getString('email') )){
				$json['error']['email'] = JText::_('J2STORE_EMAIL_EXISTS');
			}
            $privacy_plugin = $app->input->post->get('privacyconsent',0);

			if($privacy_plugin_enabled && !$privacy_plugin){
                $privacy_plugin = JPluginHelper::getPlugin('system', 'privacyconsent');
                $privacy_params = $platform->getRegistry($privacy_plugin->params);//Joomla 1.6 Onward
                $json['error']['privacyconsent'] = JText::_($privacy_params->get('messageOnRedirect','PLG_SYSTEM_PRIVACYCONSENT_REDIRECT_MESSAGE_DEFAULT'));
            }
		}

		J2Store::plugin()->event('CheckoutValidateRegister', array(&$json));

		if (!$json) {
			$post = $app->input->getArray($_POST);

			//now create the user
			// create the details array with new user info
			$details = array(
					'email' =>  $app->input->getString('email'),
					'name' => $app->input->getString('first_name').' '.$app->input->getString('last_name'),
					'username' =>  $app->input->getString('email'),
					'password' => $app->input->getString('password'),
					'password2'=> $app->input->getString('confirm')
			);
			$msg = '';
			$user = $userHelper->createNewUser($details, $msg);

			$session->set('account', 'register', 'j2store');

			//now login the user
			if ( $userHelper->login(
					array('username' => $user->username, 'password' => $details['password'])
			)
			) {
                if($privacy_plugin_enabled){
                    //save privacy consent
                    $userHelper->savePrivacyConsent();
                }
				//$billing_address_id = $userHelper->addCustomer($post);
				$billing_address_id = $address_model->addAddress('billing');


				//check if we have a country and zone id's. If not use the store address
				$country_id = $app->input->post->getInt('country_id', '');
				if(empty($country_id)) {
					$country_id = $store_address->get('country_id');
				}

				$zone_id = $app->input->post->getInt('zone_id', '');
				if(empty($zone_id)) {
					$zone_id = $store_address->get('zone_id');
				}

				$postcode = $app->input->post->getString('zip');
				if(empty($postcode)) {
					$postcode = $store_address->get('zip');
				}

				$session->set('billing_address_id', $billing_address_id , 'j2store');
				$session->set('billing_country_id', $country_id, 'j2store');
				$session->set('billing_zone_id', $zone_id, 'j2store');
				$session->set('billing_postcode', $postcode, 'j2store');

				$shipping_address = $app->input->post->get('shipping_address');
				if (!empty($shipping_address )) {
					$session->set('shipping_address_id', $billing_address_id, 'j2store');
					$session->set('shipping_country_id', $country_id, 'j2store');
					$session->set('shipping_zone_id', $zone_id, 'j2store');
					$session->set('shipping_postcode', $postcode, 'j2store');
				}
				if(!$json) {
					$json = J2Store::plugin()->eventWithArray('CheckoutAfterRegister');
				}

			} else {
				$json['redirect'] = $redirect_url;
			}

			$session->clear('guest', 'j2store');
			$session->clear('shipping_method', 'j2store');
			$session->clear('shipping_methods', 'j2store');
			$session->clear('payment_method', 'j2store');
			$session->clear('payment_methods', 'j2store');
		}

		echo json_encode($json);
		$app->close();
	}

	function guest() {
		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$cart_model = F0FModel::getTmpInstance('Carts', 'J2StoreModel');
		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}


		$is_mobile = $session->get('is_mobile','','j2store');
        $cart_params = array();
        if ($is_mobile){
            $cart_params['mobile'] = 'mobile';
        }
        $link = J2Store::platform()->getCartUrl($cart_params);//$link = JRoute::_('index.php?option=com_j2store&view=carts'.$mobile);

		$session->set('uaccount', 'guest', 'j2store');


		//initialise order
		$order = F0FModel::getTmpInstance('Orders', 'J2StoreModel')->initOrder()->getOrder();
		if(count($order->getItems()) < 1) {
			$app->redirect($link, JText::_('J2STORE_CART_NO_ITEMS'));
		}

		//validate stock
		if($order->validate_order_stock() == false) {
			$app->redirect($link);
		}

		//set guest variable to session as the array, if it does not exist
		if(!$session->has('guest', 'j2store')) {
			$session->set('guest', array(), 'j2store');
		}
		$guest = $session->get('guest', array(), 'j2store');

		$data = array();

		$selectableBase = J2Store::getSelectableBase();
		$view->assign('fieldsClass', $selectableBase);

		$address = F0FTable::getAnInstance('address', 'J2StoreTable');

		if (empty($guest['billing']['zip']) && $session->has('billing_postcode', 'j2store') ) {
			$guest['billing']['zip'] = $session->get('billing_postcode', '', 'j2store');
		}

		if (empty($guest['billing']['country_id']) && $session->has('billing_country_id', 'j2store')) {
			$guest['billing']['country_id'] = $session->get('billing_country_id', '', 'j2store');
		}

		if (empty($guest['billing']['zone_id']) && $session->has('billing_zone_id', 'j2store')) {
			$guest['billing']['zone_id'] = $session->get('billing_zone_id', '', 'j2store');
		}

		//bind the guest data to address table if it exists in the session

		if(isset($guest['billing']) && count($guest['billing'])) {
			$address->bind($guest['billing']);
		}

		$fields = $selectableBase->getFields('billing',$address,'address');
		$view->assign('fields', $fields);
		$view->assign('address', $address);

		//get layout settings
		$view->assign('storeProfile', J2Store::storeProfile());


		$showShipping = false;
		if(J2Store::config()->get('show_shipping_address', 0)) {
			$showShipping = true;
		}

		if ($isShippingEnabled = $order->isShippingEnabled())
		{
			$showShipping = true;
		}
		$view->assign( 'showShipping', $showShipping );

		$data['shipping_required'] = $showShipping;

		if (isset($guest['shipping_address'])) {
			$data['shipping_address'] = $guest['shipping_address'];
		} else {
			$data['shipping_address'] = true;
		}
		$view->assign( 'data', $data);

		$view->setLayout( 'default_guest');

		$html = '';

		ob_start();
		$view->display();
		$html .= ob_get_contents();
		ob_end_clean();
		echo $html;
		$app->close();

	}

	function guest_validate() {

		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$address_model = F0FModel::getTmpInstance('Addresses', 'J2StoreModel');
		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

        $redirect_url = J2Store::platform()->getCheckoutUrl();
		$data = $app->input->getArray($_POST);
		$store_address = J2Store::storeProfile();
		//initialise guest value from session
		$guest = $session->get('guest', array(), 'j2store');
		$params = J2Store::config();

		$json = array();

		// Validate if customer is logged in.
		if (JFactory::getUser()->id) {
			$json['redirect'] = $redirect_url;
		}

		// Validate order
		$order = F0FModel::getTmpInstance('Orders', 'J2StoreModel')->initOrder()->getOrder();
		if(count($order->getItems()) < 1) {
			$json['redirect'] = $redirect_url;
		}

		// Check if guest checkout is available.
		//TODO prevent if products have downloads also
		if (!$params->get('allow_guest_checkout')) {
			$json['redirect'] = $redirect_url;
		}

		if (!$json) {

			$selectableBase =J2Store::getSelectableBase();
			$json = $selectableBase->validate($data, 'billing', 'address');

			//check email
			if ((strlen($app->input->post->get('email')) < 4)) {
				$json['error']['email'] = JText::_('J2STORE_EMAIL_REQUIRED');
			}

		}

		J2Store::plugin()->event('CheckoutValidateGuest',array(&$json,&$data));

		if (!$json) {
			//now assign the post data to the guest billing array.
			foreach($data as $key=>$value) {
				$guest['billing'][$key] = $value;
			}

			//check if we have a country and zone id's. If not use the store address
			$country_id = $app->input->post->getInt('country_id', '');
			if(empty($country_id)) {
				$country_id = $store_address->get('country_id');
			}

			$zone_id = $app->input->post->getInt('zone_id', '');
			if(empty($zone_id)) {
				$zone_id = $store_address->get('zone_id');
			}

			$postcode = $app->input->post->get('zip');
			if(empty($postcode)) {
				$postcode = $store_address->get('zip');
			}
			///returns an object
			$country_info = F0FModel::getTmpInstance('Countries', 'J2StoreModel')->getItem($country_id);

			//save to address table before you proceed.
			$address_model->addAddress('billing', $guest['billing']);

			if ($country_info) {
				$guest['billing']['country_name'] = $country_info->country_name;
				$guest['billing']['iso_code_2'] = $country_info->country_isocode_2;
				$guest['billing']['iso_code_3'] = $country_info->country_isocode_3;
			} else {
				$guest['billing']['country_name'] = '';
				$guest['billing']['iso_code_2'] = '';
				$guest['billing']['iso_code_3'] = '';
			}

			$zone_info = F0FModel::getTmpInstance('Zones', 'J2StoreModel')->getItem($zone_id);

			if ($zone_info) {
				$guest['billing']['zone_name'] = $zone_info->zone_name;
				$guest['billing']['zone_code'] = $zone_info->zone_code;
			} else {
				$guest['billing']['zone_name'] = '';
				$guest['billing']['zone_code'] = '';
			}

			if ($app->input->getInt('shipping_address')) {
				$guest['shipping_address'] = true;
			} else {
				$guest['shipping_address'] = false;
			}

			// Default billing address
			$session->set('billing_country_id', $country_id, 'j2store');
			$session->set('billing_zone_id', $zone_id, 'j2store');
			$session->set('billing_postcode', $postcode, 'j2store');

			if ($guest['shipping_address']) {

				foreach($data as $key=>$value) {
					$guest['shipping'][$key] = $value;
				}

				//save to address table before you proceed.
				$address_model->addAddress('shipping', $guest['shipping']);

				if ($country_info) {
					$guest['shipping']['country_name'] = $country_info->country_name;
					$guest['shipping']['iso_code_2'] = $country_info->country_isocode_2;
					$guest['shipping']['iso_code_3'] = $country_info->country_isocode_3;
				} else {
					$guest['shipping']['country_name'] = '';
					$guest['shipping']['iso_code_2'] = '';
					$guest['shipping']['iso_code_3'] = '';
				}

				if ($zone_info) {
					$guest['shipping']['zone_name'] = $zone_info->zone_name;
					$guest['shipping']['zone_code'] = $zone_info->zone_code;
				} else {
					$guest['shipping']['zone_name'] = '';
					$guest['shipping']['zone_code'] = '';
				}
				// Default Shipping Address
				$session->set('shipping_country_id', $country_id, 'j2store');
				$session->set('shipping_zone_id', $zone_id, 'j2store');
				$session->set('shipping_postcode', $postcode, 'j2store');
			}
			//now set the guest values to the session
			$session->set('guest', $guest, 'j2store');
			$session->set('account', 'guest', 'j2store');

			$session->clear('shipping_method', 'j2store');
			$session->clear('shipping_methods', 'j2store');
			$session->clear('payment_method', 'j2store');
			$session->clear('payment_methods', 'j2store');
		}
		echo json_encode($json);
		$app->close();

	}

	function guest_shipping() {

		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		$guest = $session->get('guest', array(), 'j2store');

		$data = array();

		$selectableBase = J2Store::getSelectableBase();
		$view->assign('fieldsClass', $selectableBase);

		$address = F0FTable::getAnInstance('address', 'J2StoreTable');

		if (empty($guest['shipping']['zip']) && $session->has('shipping_postcode', 'j2store') ) {
			$guest['shipping']['zip'] = $session->get('shipping_postcode', '', 'j2store');
		}

		if (empty($guest['shipping']['country_id']) && $session->has('shipping_country_id', 'j2store')) {
			$guest['shipping']['country_id'] = $session->get('shipping_country_id', '', 'j2store');
		}

		if (empty($guest['shipping']['zone_id']) && $session->has('shipping_zone_id', 'j2store')) {
			$guest['shipping']['zone_id'] = $session->get('shipping_zone_id', '', 'j2store');
		}

		//bind the guest data to address table if it exists in the session

		if(isset($guest['shipping']) && count($guest['shipping'])) {
			$address->bind($guest['shipping']);
		}
		$fields = $selectableBase->getFields('shipping',$address,'address');
		$view->assign('fields', $fields);
		$view->assign('address', $address);

		//get layout settings

		$view->assign('storeProfile', J2Store::storeProfile());

		$view->assign( 'data', $data);

		$view->setLayout( 'default_guest_shipping');

		$html = '';

		ob_start();
		$view->display();
		$html .= ob_get_contents();
		ob_end_clean();
		echo $html;
		$app->close();

	}

	function guest_shipping_validate() {
		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$address_model = F0FModel::getTmpInstance('Addresses', 'J2StoreModel');
		$params = J2Store::config();
		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

        $redirect_url = J2Store::platform()->getCheckoutUrl();
		$data = $app->input->getArray($_POST);
		$store_address = J2Store::storeProfile();
		//initialise guest value from session
		$guest = $session->get('guest', array(), 'j2store');
		$json = array();

		// Validate if customer is logged in.
		if (JFactory::getUser()->id) {
			$json['redirect'] = $redirect_url;
		}

		// Check if guest checkout is available.
		//TODO prevent if products have downloads also
		if (!$params->get('allow_guest_checkout')) {
			$json['redirect'] = $redirect_url;
		}

		if (!$json) {
			$selectableBase = J2Store::getSelectableBase();
			$json = $selectableBase->validate($data, 'shipping', 'address');
		}


		J2Store::plugin()->event('CheckoutValidateGuestShipping',array(&$json,&$data));


		if(!$json) {

			//now assign the post data to the guest billing array.
			foreach($data as $key=>$value) {
				$guest['shipping'][$key] = $value;
			}

			//check if we have a country and zone id's. If not use the store address
			$country_id = $app->input->post->getInt('country_id', '');
			if(empty($country_id)) {
				$country_id = $store_address->get('country_id');
			}

			$zone_id = $app->input->post->getInt('zone_id', '');
			if(empty($zone_id)) {
				$zone_id = $store_address->get('zone_id');
			}

			$postcode = $app->input->post->get('zip');
			if(empty($postcode)) {
				$postcode = $store_address->get('zip');
			}

			//save to address table before you proceed.
			$address_model->addAddress('shipping', $guest['shipping']);

			//now get the country info
			//returns an object
			$country_info = F0FModel::getTmpInstance('Countries', 'J2StoreModel')->getItem($country_id);

			if ($country_info) {
				$guest['shipping']['country_name'] = $country_info->country_name;
				$guest['shipping']['iso_code_2'] = $country_info->country_isocode_2;
				$guest['shipping']['iso_code_3'] = $country_info->country_isocode_3;
			} else {
				$guest['shipping']['country_name'] = '';
				$guest['shipping']['iso_code_2'] = '';
				$guest['shipping']['iso_code_3'] = '';
			}

			$zone_info = F0FModel::getTmpInstance('Zones', 'J2StoreModel')->getItem($zone_id);

			if ($zone_info) {
				$guest['shipping']['zone_name'] = $zone_info->zone_name;
				$guest['shipping']['zone_code'] = $zone_info->zone_code;
			} else {
				$guest['shipping']['zone_name'] = '';
				$guest['shipping']['zone_code'] = '';
			}
			// Default Shipping Address
            $session->set('shipping_country_id', $country_id, 'j2store');
            $session->set('shipping_zone_id', $zone_id, 'j2store');
            $session->set('shipping_postcode', $postcode, 'j2store');

			//now set the guest values to the session
			$session->set('guest', $guest, 'j2store');

			$session->clear('shipping_method', 'j2store');
			$session->clear('shipping_methods', 'j2store');

		}
		echo json_encode($json);
		$app->close();
	}

	function billing_address() {

		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$address_model = F0FModel::getTmpInstance('Addresses', 'J2StoreModel');
		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}
		$user = JFactory::getUser();
		$address = F0FTable::getAnInstance('Address', 'J2StoreTable');
		if($user->id) {
			//$address = $address_model->user_id($user->id)->getFirstItem();
			if(isset( $address->j2store_address_id ) && empty($address->j2store_address_id)){
				$userProfile = JUserHelper::getProfile( $user->id );
				$address->address_1 = isset($userProfile->profile['address1']) ? $userProfile->profile['address1']:'';
				$address->address_2 = isset($userProfile->profile['address2']) ? $userProfile->profile['address2']:'';
				$address->city = isset($userProfile->profile['city']) ? $userProfile->profile['city']:'';
				$address->zip = isset($userProfile->profile['postal_code']) ? $userProfile->profile['postal_code']:'';
				//
				$address->phone_1 = isset($userProfile->profile['phone']) ? $userProfile->profile['phone']:'';
				$address->phone_2 = isset($userProfile->profile['mobilephone']) ? $userProfile->profile['mobilephone']:'';
				$address->first_name = isset($userProfile->profile['first_name']) ? $userProfile->profile['first_name']:'';
				$address->last_name = isset($userProfile->profile['last_name']) ? $userProfile->profile['last_name']:'';
			}

		}
		$is_mobile = $session->get('is_mobile','','j2store');

        $cart_params = array();
        if ($is_mobile){
            $cart_params['mobile'] = 'mobile';
        }
        $link = J2Store::platform()->getCartUrl($cart_params);
		/*$mobile = ($is_mobile) ? '&mobile=mobile' : '';
		$link = JRoute::_('index.php?option=com_j2store&view=carts'.$mobile);*/

		$order = F0FModel::getTmpInstance('Orders', 'J2StoreModel')->initOrder()->getOrder();
		if(count($order->getItems()) < 1 ) {
			$app->redirect($link, $order->getError());
		}

		//validate stock
		if($order->validate_order_stock() == false) {
			$app->redirect($link);
		}

		//get the billing address id from the session
		if ($session->has('billing_address_id', 'j2store')) {
			$billing_address_id = $session->get('billing_address_id', '', 'j2store');
		} else {
			$billing_address_id = isset($address->j2store_address_id)?$address->j2store_address_id:'';
		}

		$view->assign('address_id', $billing_address_id);

		if ($session->has('billing_country_id', 'j2store')) {
			$billing_country_id = $session->get('billing_country_id', '', 'j2store');
		} else {
			$billing_country_id = isset($address->country_id)?$address->country_id:'';
		}

		if ($session->has('billing_zone_id', 'j2store')) {
			$billing_zone_id = $session->get('billing_zone_id', '', 'j2store');
		} else {
			$billing_zone_id = isset($address->zone_id)?$address->zone_id:'';
		}
		$view->assign('zone_id', $billing_zone_id);

		//get all address
		if($user->id) {
			$addresses = $address_model->user_id($user->id)->getList();
		}else {
			$addresses = array();
		}
		J2Store::plugin ()->event ( 'BeforeCheckoutBilling', array(&$address,$addresses,$order) );
		$view->assign('addresses', $addresses);
		$selectableBase = J2Store::getSelectableBase();
		$view->assign('fieldsClass', $selectableBase);
		$fields = $selectableBase->getFields('billing',$address,'address');
        // if any custom field, need to add address object
        $default_address = F0FTable::getAnInstance('Address', 'J2StoreTable')->getClone();
        $selectableBase->getFields('billing',$default_address,'address');

		$view->assign('fields', $fields);
		$view->assign('address', empty($addresses) ? $address :$default_address);//

		//get layout settings
		$view->assign('storeProfile', J2Store::storeProfile());
		$view->setLayout( 'default_billing');

		$html = '';

		ob_start();
		$view->display();
		$html .= ob_get_contents();
		ob_end_clean();
		echo $html;
		$app->close();
	}

	//validate billing address

	function billing_address_validate() {

		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$user = JFactory::getUser();
		$address_model = F0FModel::getTmpInstance('Addresses', 'J2StoreModel');

		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

        $redirect_url = J2Store::platform()->getCheckoutUrl();
		$data = $app->input->getArray($_POST);
		$json = array();
		$store_address = J2Store::storeProfile();

		$selectableBase = J2Store::getSelectableBase();

		// Validate if customer is logged or not.
		if (!$user->id) {
			$json['redirect'] = $redirect_url;
		}
		J2Store::plugin()->event('BeforeCheckoutValidateBilling',array(&$json));
		//Has the customer selected an existing address?
		$selected_billing_address = $app->input->getString('billing_address');
		if (isset($selected_billing_address ) && $app->input->getString('billing_address') == 'existing') {
			$selected_address_id =	$app->input->getInt('address_id');
			if (empty($selected_address_id)) {
				$json['error']['warning'] = JText::_('J2STORE_ADDRESS_SELECTION_ERROR');
			} elseif (!in_array($app->input->getInt('address_id'), array_keys($address_model->getAddresses('j2store_address_id')))) {
				$json['error']['warning'] = JText::_('J2STORE_ADDRESS_SELECTION_ERROR');
			} else {
				// Default Payment Address
				$address_info = $address_model->getItem($app->input->getInt('address_id'));
			}

			if (!$json) {
				$session->set('billing_address_id', $app->input->getInt('address_id'), 'j2store');

				if ($address_info) {

					//if country id is empty set it to the store country id
					if(empty($address_info->country_id)) {
						$session->set('billing_country_id',$store_address->get('country_id'), 'j2store');

					} else {
						$session->set('billing_country_id',$address_info->country_id, 'j2store');
					}
					$session->set('billing_zone_id',$address_info->zone_id, 'j2store');
					$session->set('billing_postcode',$address_info->zip, 'j2store');
				} else {
					$session->clear('billing_country_id', 'j2store');
					$session->clear('billing_zone_id', 'j2store');
					$session->clear('billing_postcode', 'j2store');
				}
				$session->clear('payment_method', 'j2store');
				$session->clear('payment_methods', 'j2store');
			}
		} else {

			if (!$json) {

				$json = $selectableBase->validate($data, 'billing', 'address');

				//J2Store::plugin()->event('CheckoutValidateBilling',array(&$json));

				if(!$json) {
					$address_id = $address_model->addAddress('billing');
					//now get the address and save to session
					$address_info = $address_model->getItem($address_id);

					//check if we have a country and zone id's. If not use the store address
					$country_id = $app->input->post->getInt('country_id', '');
					if(empty($country_id)) {
						$country_id = $store_address->get('country_id');
					}

					$zone_id = $app->input->post->getInt('zone_id', '');
					if(empty($zone_id)) {
						$zone_id = $store_address->get('zone_id');
					}

					$postcode  = $app->input->post->getString('zip');
					if(empty($postcode)) {
						$postcode = $store_address->get('zip');
					}

					$session->set('billing_address_id', $address_info->j2store_address_id, 'j2store');
					$session->set('billing_country_id', $country_id, 'j2store');
					$session->set('billing_zone_id',$zone_id, 'j2store');
					$session->set('billing_postcode',$postcode, 'j2store');
					$session->clear('payment_method', 'j2store');
					$session->clear('payment_methods', 'j2store');
				}

			}

		}
		J2Store::plugin()->event('CheckoutValidateBilling',array(&$json));
		echo json_encode($json);
		$app->close();

	}

	//shipping address

	function shipping_address() {

		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$session = JFactory::getSession();
		$address_model = F0FModel::getTmpInstance('Addresses', 'J2StoreModel');

		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}
		//address variable only for new user, only prefile data to prefile
		$address = F0FTable::getAnInstance('Address', 'J2StoreTable');
		if($user->id) {
			//$address = $address_model->user_id($user->id)->getFirstItem();
			if(isset( $address->j2store_address_id ) && empty($address->j2store_address_id)){
				$userProfile = JUserHelper::getProfile( $user->id );
				$address->address_1 = isset($userProfile->profile['address1']) ? $userProfile->profile['address1']:'';
				$address->address_2 = isset($userProfile->profile['address2']) ? $userProfile->profile['address2']:'';
				$address->city = isset($userProfile->profile['city']) ? $userProfile->profile['city']:'';
				$address->zip = isset($userProfile->profile['postal_code']) ? $userProfile->profile['postal_code']:'';
				//
				$address->phone_1 = isset($userProfile->profile['phone']) ? $userProfile->profile['phone']:'';
				$address->phone_2 = isset($userProfile->profile['mobilephone']) ? $userProfile->profile['mobilephone']:'';
				$address->first_name = isset($userProfile->profile['first_name']) ? $userProfile->profile['first_name']:'';
				$address->last_name = isset($userProfile->profile['last_name']) ? $userProfile->profile['last_name']:'';
			}

		}

		//get the billing address id from the session
		if ($session->has('shipping_address_id', 'j2store')) {
			$shipping_address_id = $session->get('shipping_address_id', '', 'j2store');
		} else {
			$shipping_address_id = $address->j2store_address_id;
		}

		$view->assign('address_id', $shipping_address_id);

		if ($session->has('shipping_postcode', 'j2store')) {
			$shipping_postcode = $session->get('shipping_postcode', '', 'j2store');
		} else {
			$shipping_postcode = $address->zip;
		}

		if ($session->has('shipping_country_id', 'j2store')) {
			$shipping_country_id = $session->get('shipping_country_id', '', 'j2store');
		} else {
			$shipping_country_id = $address->country_id;
		}

		if ($session->has('shipping_zone_id', 'j2store')) {
			$shipping_zone_id = $session->get('shipping_zone_id', '', 'j2store');
		} else {
			$shipping_zone_id = $address->zone_id;
		}
		$view->assign('zone_id', $shipping_zone_id);

		//get all address
		//$addresses = $address_model->user_id($user->id)->getList();
		//get all address
		if($user->id) {
			$addresses = $address_model->user_id($user->id)->getList();
		}else {
			$addresses = array();
		}
		J2Store::plugin ()->event ( 'BeforeCheckoutShipping', array(&$address,$addresses) );
		$view->assign('addresses', $addresses);

		$selectableBase = J2Store::getSelectableBase();
		$view->assign('fieldsClass', $selectableBase);

		$fields = $selectableBase->getFields('shipping',$address,'address');
		// if any custom field, need to add address object
        $default_address = F0FTable::getAnInstance('Address', 'J2StoreTable')->getClone();
        $selectableBase->getFields('shipping',$default_address,'address');
		$view->assign('fields', $fields);
		$view->assign('address', empty($addresses) ? $address :$default_address);

		//get layout settings
		$view->assign('storeProfile', J2Store::storeProfile());


		$view->setLayout( 'default_shipping');

		$html = '';

		ob_start();
		$view->display();
		$html .= ob_get_contents();
		ob_end_clean();
		echo $html;
		$app->close();
	}

	function shipping_address_validate() {

		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$session = JFactory::getSession();
		$address_model = F0FModel::getTmpInstance('Addresses', 'J2StoreModel');
		$params = J2Store::config();
		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

        $redirect_url = J2Store::platform()->getCheckoutUrl();
		$data = $app->input->getArray($_POST);
		$json = array();
		$store_address = J2Store::storeProfile();

		$selectableBase = J2Store::getSelectableBase();

		// Validate if customer is logged or not.
		if (!$user->id) {
			$json['redirect'] = $redirect_url;
		}

		$order = F0FModel::getTmpInstance('Orders', 'J2StoreModel')->initOrder()->getOrder();

		// Validate if shipping is required. If not the customer should not have reached this page.
		$showShipping = false;

		if($params->get('show_shipping_address', 0)) {
			$showShipping = true;
		}

		if ($isShippingEnabled = $order->isShippingEnabled())
		{
			$showShipping = true;
		}

		if ($showShipping == false) {
			$json['redirect'] = $redirect_url;
		}

		// Validate cart has products and has stock.
		if (count($order->getItems()) < 1) {
			$json['redirect'] = $redirect_url;
		}
		J2Store::plugin()->event('BeforeCheckoutValidateShipping',array(&$json));
		//Has the customer selected an existing address?
		$selected_shipping_address =$app->input->getString('shipping_address');
		if (isset($selected_shipping_address ) && $app->input->getString('shipping_address') == 'existing') {
			$selected_address_id =	$app->input->getInt('address_id');
			if (empty($selected_address_id)) {
				$json['error']['warning'] = JText::_('J2STORE_ADDRESS_SELECTION_ERROR');
			} elseif (!in_array($app->input->getInt('address_id'), array_keys($address_model->getAddresses('j2store_address_id')))) {
				$json['error']['warning'] = JText::_('J2STORE_ADDRESS_SELECTION_ERROR');
			} else {
				// Default shipping Address. returns associative list of single record
				$address_info = $address_model->getItem($app->input->getInt('address_id'));
			}

			if (!$json) {
				$session->set('shipping_address_id', $app->input->getInt('address_id'), 'j2store');

				if ($address_info) {

					//if country id is empty set it to the store country id
					if(empty($address_info->country_id)) {
						$session->set('shipping_country_id',$store_address->get('country_id'), 'j2store');
					} else {
						$session->set('shipping_country_id',$address_info->country_id, 'j2store');
					}


					$session->set('shipping_zone_id',$address_info->zone_id, 'j2store');
					$session->set('shipping_postcode',$address_info->zip, 'j2store');
				} else {
					$session->clear('shipping_country_id', 'j2store');
					$session->clear('shipping_zone_id', 'j2store');
					$session->clear('shipping_postcode', 'j2store');
				}
				$session->clear('shipping_method', 'j2store');
				$session->clear('shipping_methods', 'j2store');
			}
		} else {
			if (!$json) {
				$json = $selectableBase->validate($data, 'shipping', 'address');

				//J2Store::plugin()->event('CheckoutValidateShipping',array(&$json));

				if(!$json) {

					$address_id = $address_model->addAddress('shipping');
					//now get the address and save to session
					$address_info = $address_model->getItem($address_id);

					//check if we have a country and zone id's. If not use the store address
					$country_id = $app->input->post->getInt('country_id', '');
					if(empty($country_id)) {
						$country_id = $store_address->get('country_id');
					}

					$zone_id = $app->input->post->getInt('zone_id', '');
					if(empty($zone_id)) {
						$zone_id = $store_address->get('zone_id');
					}

					$postcode= $app->input->post->get('zip');
					if(empty($postcode)) {
						$postcode = $store_address->get('zip');
					}

					$session->set('shipping_address_id', $address_info->j2store_address_id, 'j2store');
					$session->set('shipping_country_id',$country_id, 'j2store');
					$session->set('shipping_zone_id',$zone_id, 'j2store');
					$session->set('shipping_postcode',$postcode, 'j2store');
					$session->clear('shipping_method', 'j2store');
					$session->clear('shipping_methods', 'j2store');
				}

			}

		}
		J2Store::plugin()->event('CheckoutValidateShipping',array(&$json));
		echo json_encode($json);
		$app->close();
	}

	//shipping and payment method

	function shipping_payment_method() {
		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$user = JFactory::getUser();
		$params = J2Store::config();
		$address_model = F0FModel::getTmpInstance('Addresses', 'J2StoreModel');

		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}
		$profile_order_id = $session->get('profile_order_id',null,'j2store');
		$order = F0FModel::getTmpInstance('Orders', 'J2StoreModel')->initOrder($profile_order_id)->getOrder();

		if ($order->getItemCount() < 1)
		{
			$is_mobile = $session->get('is_mobile','','j2store');
            $cart_params = array();
            if ($is_mobile){
                $cart_params['mobile'] = 'mobile';
            }
            $link = J2Store::platform()->getCartUrl($cart_params);
			$msg = JText::_('J2STORE_NO_ITEMS_IN_CART');
            J2Store::platform()->redirect($link, $msg);
		}

		JPluginHelper::importPlugin ('j2store');

		//custom fields
		$selectableBase = J2Store::getSelectableBase();
		$view->assign('fieldsClass', $selectableBase);
		$address_table = F0FTable::getAnInstance('Address', 'J2StoreTable');
		$fields = $selectableBase->getFields('payment',$address_table,'address');
		$view->assign('fields', $fields);
		$view->assign('address', $address_table);

		//get layout settings
		$view->assign('storeProfile', J2Store::storeProfile());

		//shipping
		$showShipping = false;

		if($params->get('show_shipping_address', 0)) {
			$showShipping = true;
		}

		if ($isShippingEnabled = $order->isShippingEnabled())
		{
			$showShipping = true;
		}
		$view->assign( 'showShipping', $showShipping );

		if($showShipping)
		{
			$shipping_layout = "shipping_yes";
			$shipping_method_form = $this->getShippingHtml(  $order );
			$view->assign( 'showShipping', $showShipping );
			$view->assign( 'shipping_method_form', $shipping_method_form );

			//$view->assign( 'rates', $rates );
		}
		//process payment plugins
		$showPayment = true;
		if ((float)$order->order_total == (float)'0.00'  )
		{
			if(isset($order->show_payment_method) && $order->show_payment_method == 1){
				$showPayment = true;
			}else{
				$showPayment = false;
			}
            $app->triggerEvent("onJ2StoreChangeShowPaymentOnTotalZero", array( $order, &$showPayment ) );
		}
		$view->assign( 'showPayment', $showPayment );

		$payment_plugins = J2Store::plugin()->getPluginsWithEvent( 'onJ2StoreGetPaymentPlugins' );
		$default_method = $params->get('default_payment_method', '');
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
						$html = $this->getPaymentForm( $plugin->element, true);
						$view->assign( 'payment_form_div', $html);
					}
					$plugins[] = $plugin;
				}
			}
		}

		if (count($plugins) == 1)
		{
			$plugins[0]->checked = true;
			$html = $this->getPaymentForm( $plugins[0]->element, true);
			$view->assign( 'payment_form_div', $html);
		}

		$view->assign('plugins', $plugins);

		$view->assign( 'order', $order );
		$view->assign('params', $params);
		$view->setLayout( 'default_shipping_payment');

		$html = '';

		ob_start();
		$view->display();
		$html .= ob_get_contents();
		ob_end_clean();
		echo $html;
		$app->close();
	}

	function shipping_payment_method_validate() {

		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$user = JFactory::getUser();
		$params = J2Store::config();
		$address_model = F0FModel::getTmpInstance('Addresses', 'J2StoreModel');

		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}
		$profile_order_id = $session->get('profile_order_id',null,'j2store');
		$order = F0FModel::getTmpInstance('Orders', 'J2StoreModel')->initOrder($profile_order_id)->getOrder();

        $redirect_url = J2Store::platform()->getCheckoutUrl();
		//now get the values posted by the plugin, if any
		$values = $app->input->getArray($_POST);
		$json = array();

		//first validate custom fields
		$selectableBase = J2Store::getSelectableBase();
		$json = $selectableBase->validate($values, 'payment', 'address');

		if(!$json) {
			$json = J2Store::plugin()->eventWithArray('CheckoutValidateShippingPayment',array($values, $order));
		}

		if (!$json) {
			//validate weather the customer is logged in
			$billing_address = '';
			if ($user->id && $session->has('billing_address_id', 'j2store')) {
				$billing_address = $address_model->getItem($session->get('billing_address_id', '', 'j2store'));
			} elseif ($session->has('guest', 'j2store')) {
				$guest = $session->get('guest', array(), 'j2store');
				$billing_address = $guest['billing'];
			}

			if (empty($billing_address)) {
				$json['redirect'] = $redirect_url;
			}

			//cart has products?
			if ($order->getItemCount() < 1) {
				$json['redirect'] = $redirect_url;
			}

			if (!$json) {

				$isShippingEnabled = $order->isShippingEnabled();
				//validate selection of shipping methods and set the shipping rates
				if($params->get('show_shipping_address', 0) || $isShippingEnabled ) {
					//shipping is required.

					if ($user->id && $session->has('shipping_address_id', 'j2store')) {
						$shipping_address = $address_model->getItem($session->get('shipping_address_id', '', 'j2store'));
					} elseif ($session->has('guest', 'j2store')) {
						$guest = $session->get('guest', array(), 'j2store');
						$shipping_address = $guest['shipping'];
					}

					//check if shipping address id is set in session. If not, redirect
					if(empty($shipping_address)) {
						$json['error']['shipping'] = JText::_('J2STORE_CHECKOUT_ERROR_SHIPPING_ADDRESS_NOT_FOUND');
						$json['redirect'] = $redirect_url;
					}

					try {
						$this->validateSelectShipping($values, $order);
					} catch (Exception $e) {
						$json['error']['shipping'] = $e->getMessage();
					}

					if(!$json) {


						$shipping_values = array();
						$shipping_values['shipping_price']    = isset($values['shipping_price']) ? $values['shipping_price'] : 0;
						$shipping_values['shipping_extra']   = isset($values['shipping_extra']) ? $values['shipping_extra'] : 0;
						$shipping_values['shipping_code']     = isset($values['shipping_code']) ? $values['shipping_code'] : '';
						$shipping_values['shipping_name']     = isset($values['shipping_name']) ? $values['shipping_name'] : '';
						$shipping_values['shipping_tax']      = isset($values['shipping_tax']) ? $values['shipping_tax'] : 0;
						$shipping_values['shipping_plugin']     = isset($values['shipping_plugin']) ? $values['shipping_plugin'] : '';
						//set the shipping method to session
						$session->set('shipping_method',$shipping_values['shipping_plugin'], 'j2store');
						$session->set('shipping_values',$shipping_values, 'j2store');


					}

				}

			}

			if (!$json) {
				// is shipping mandatory
				if($params->get('shipping_mandatory', 0)) {
					//yes it is. Check if session has shipping values
					$shipping_values = $session->get('shipping_values', array(), 'j2store');
					$shipping_method = $session->get('shipping_method', null, 'j2store');
					if(count($shipping_values) < 1 || empty($shipping_method)) {
						//now value selected
						$json['error']['shipping'] = JText::_('J2STORE_CHECKOUT_SHIPPING_METHOD_SELECTION_MANDATORY');
					}
				}
			}

			//validate selection of payment methods
			if (!$json) {
				$profile_order_id = $session->get('profile_order_id',null,'j2store');
				//re initialise the order
				$order = F0FModel::getTmpInstance('Orders', 'J2StoreModel')->initOrder($profile_order_id)->getOrder();

				$showPayment = true;
				if ((float)$order->order_total == (float)'0.00')
				{
					$showPayment = false;
                    $app->triggerEvent("onJ2StoreChangeShowPaymentOnTotalZero", array( $order, &$showPayment ) );
				}

				if($showPayment) {
					$payment_plugin = $app->input->getString('payment_plugin');
					if (!isset($payment_plugin)) {
						$json['error']['warning'] = JText::_('J2STORE_CHECKOUT_ERROR_PAYMENT_METHOD');
					} elseif (!isset($payment_plugin )) {
						$json['error']['warning'] = JText::_('J2STORE_CHECKOUT_ERROR_PAYMENT_METHOD');
					}
					//validate the selected payment
					try {
						$this->validateSelectPayment($payment_plugin, $values);
					} catch (Exception $e) {
						$json['error']['warning'] = $e->getMessage();
					}

				}

				if($params->get('show_terms', 0) && $params->get('terms_display_type', 'link') =='checkbox' ) {
					$tos_check = $app->input->get('tos_check');
					if (!isset($tos_check)) {
						$json['error']['tos_check'] = JText::_('J2STORE_CHECKOUT_ERROR_AGREE_TERMS');
					}
				}

				if (!$json) {

					$payment_plugin = $app->input->getString('payment_plugin');
					//set the payment plugin form values in the session as well.
					$session->set('payment_values', $values, 'j2store');
					$session->set('payment_method', $payment_plugin, 'j2store');
					$session->set('customer_note', strip_tags($app->input->getString('customer_note')), 'j2store');
				}
			}
		}
		echo json_encode($json);
		$app->close();
	}
	/**
	 * display expressconfirm layout
	 *   */
	function expressconfirm(){
		$app = JFactory::getApplication();
		$data = $app->input->getArray($_REQUEST);
		$session = JFactory::getSession();
		$view = $this->getThisView();
		$order = '';
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}
		if($session->has('order_id','j2store')){
			$data['order_id'] = $session->get('order_id','','j2store');
		}
		if(isset($data['order_id']) ){
			$order = F0FTable::getInstance('Order', 'J2StoreTable')->getClone();
			$order->load(array('order_id'=>$data['order_id']));

		}else{
			$order_model = F0FModel::getTmpInstance('Orders', 'J2StoreModel');
			$order = $order_model->initOrder()->getOrder();

		}
		$view->setLayout('default_expressconfirm');
		$view->assign('ec_html', J2Store::plugin()->eventWithHtml('ExpressCheckoutConfirmPayment',array($data)));

		$data['order']=$order;
		$view->assign('order', $order);
		// Display without caching
		$view->display();
	}

	function confirm() {

		//no cache
		J2Store::utilities()->nocache();

		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$user = JFactory::getUser();
		$params = J2Store::config();
		$address_model = F0FModel::getTmpInstance('Addresses', 'J2StoreModel');
		JPluginHelper::importPlugin ('j2store');

		$errors = array();

		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		//get the payment plugin form values set in the session.
		if($session->has('payment_values', 'j2store')) {
			$values = $session->get('payment_values', array(), 'j2store');
			//backward compatibility. TODO: change the way the plugin gets its data
			foreach($values as $name=>$value) {
				$app->input->set($name, $value);
			}
		}

		//validate the order
		try {
			$orders_model = F0FModel::getTmpInstance('Orders', 'J2StoreModel');

			//if we already have the order in the session, then it might be an update. So pass it
			$order_id = $app->getUserState( 'j2store.order_id', null);
			$order = $orders_model->initOrder($order_id)->getOrder();
			$orders_model->validateOrder($order);
			//plugin trigger
			$app->triggerEvent( "onJ2StoreAfterOrderValidate", array(&$order) );
		}catch (Exception $e) {
			$errors[]= $e->getMessage();
		}


		//Extra watch fix
		if(!$session->has('payment_method', 'j2store')) {
			$payment_values = $session->get('payment_values', array(), 'j2store');
			$payment_method = isset($payment_values['payment_plugin']) ? $payment_values['payment_plugin'] : '';
			$session->set('payment_method', $payment_method, 'j2store');
		}
		$orderpayment_type = $session->get('payment_method', '', 'j2store');
		//showPayment
		$showPayment = true;
		if ((float)$order->order_total == (float)'0.00')
		{
			$showPayment = false;
			$orderpayment_type = JText::_('PAYMENT_FREE');
            $app->triggerEvent("onJ2StoreChangeShowPaymentOnTotalZero", array( $order, &$showPayment ) );
            if($showPayment === true){
                $orderpayment_type = $session->get('payment_method', '', 'j2store');
            } else {
                // in the case of orders with a value of 0.00, we redirect to the confirmPayment page
                $free_redirect = J2Store::platform()->getCheckoutUrl(array('task' => 'confirmPayment'));
                $view->assign('free_redirect', $free_redirect);
            }
		}
		$view->assign( 'showPayment', $showPayment );


		// Validate if payment method has been set.
		$orderpayment_type = trim($orderpayment_type);
		if (($showPayment == true && !$session->has('payment_method', 'j2store')) || empty($orderpayment_type)) {
			$errors[] = JText::_('J2STORE_CHECKOUT_ERROR_PAYMENT_METHOD_NOT_SELECTED');
		}

		if(!$errors) {
			//$orderpayment_type = $session->get('payment_method', '', 'j2store');

			//trigger onJ2StoreBeforePayment event
			if ($showPayment == true && !empty($orderpayment_type)) {
				//Since 3.2, this is not required. Fees API is implemented.
			//	$results = $app->triggerEvent( "onJ2StoreBeforePayment", array($orderpayment_type, $order) );
			}

			// in the case of orders with a value of 0.00, use custom values
//			if ( (float) $order->order_total == (float)'0.00' )
//			{
//				$orderpayment_type = 'free';
//				$transaction_status = JText::_( "J2STORE_COMPLETE" );
//			}

			$order->orderpayment_type = $orderpayment_type;

			try {
				$order = $order->saveOrder();
				// IMPORTANT: Store the order_id in the user's session for the postPayment "View Invoice" link
				$view->assign('order', $order);

				$app->setUserState( 'j2store.order_id', $order->order_id );
				$app->setUserState( 'j2store.orderpayment_id', $order->j2store_order_id );
				$app->setUserState( 'j2store.order_token', $order->token);
				// in the case of orders with a value of 0.00, we redirect to the confirmPayment page
//				if ( (float) $order->order_total == (float)'0.00' )
//				{
//					$free_redirect = JRoute::_( 'index.php?option=com_j2store&view=checkout&task=confirmPayment' );
//					$view->assign('free_redirect', $free_redirect);
//				}

				$values = array();
				$values['order_id'] = $order->order_id;
				$values['orderpayment_id'] = $order->j2store_order_id;
				$values['orderpayment_amount'] = $order->order_total;
				$values['order'] = $order;

				$results = $app->triggerEvent( "onJ2StorePrePayment", array( $orderpayment_type, $values));

				// Display whatever comes back from Payment Plugin for the onPrePayment
				$html = "";
				for ($i=0; $i<count($results); $i++)
				{
				$html .= $results[$i];
				}

				$view->assign('plugin_html', $html);

			} catch (Exception $e) {
				$errors[] = $e->getMessage();
			}

		}

		if(count($errors)) {
			$view->assign('error', implode('/n', $errors));
		}

		// Set display
		$view->setLayout('default_confirm');


		$html = '';

		ob_start();
		$view->display();
		$html .= ob_get_contents();
		ob_end_clean();
		echo $html;
		$app->close();
	}

	function getShippingHtml(&$order) {
        $layout = 'shipping_yes';
		$html = '';
		$view = $this->getThisView ();
		if ($model = $this->getThisModel ()) {
			// Push the model into the view (as default)
			$view->setModel ( $model, true );
		}

		$view->setLayout ( $layout );
		$rates = array ();

		switch (strtolower ( $layout )) {
			case "shipping_calculate" :
				break;
			case "shipping_no" :
				break;
			case "shipping_yes" :
			default :
				$rates = F0FModel::getTmpInstance ( 'Shippings', 'J2StoreModel' )->getShippingRates ( $order );
				$default_rate = array ();
				$session = JFactory::getSession ();
				$shipping_values = $session->get ( 'shipping_values', array (), 'j2store' );
				if(count($rates)){
					$order->show_payment_method = 1;
				}else{
					$order->show_payment_method = 0;
				}
				if (count ( $rates ) == 1) {
					$default_rate = $rates [0];
				} elseif (count ( $shipping_values )) {
					foreach ( $rates as $rate ) {
						if ($rate ['name'] == $shipping_values ['shipping_name']) {
							$shipping_values ['name'] = $shipping_values ['shipping_name'];
							$shipping_values ['price'] = $shipping_values ['shipping_price'];
							$shipping_values ['code'] = $shipping_values ['shipping_code'];
							$shipping_values ['tax'] = $shipping_values ['shipping_tax'];
							$shipping_values ['extra'] = $shipping_values ['shipping_extra'];
							$shipping_values ['element'] = $rate ['element'];
							$default_rate = $shipping_values;
						}
					}
				}
				$view->assign ( 'rates', $rates );
				$view->assign ( 'default_rate', $default_rate );
				break;
		}
		ob_start ();
		$view->display ();
		$html = ob_get_contents ();
		ob_end_clean ();
		return $html;
	}

	function getPaymentForm($element = '', $plain_format = false) {
		$app = JFactory::getApplication ();
		$values = $app->input->getArray ( $_REQUEST );
		$html = '';
		$text = "";
		$user = JFactory::getUser ();
		if (empty ( $element )) {
			$element = $app->input->getString ( 'payment_element' );
		}
		$results = array ();

		JPluginHelper::importPlugin ( 'j2store' );

		$results = $app->triggerEvent ( "onJ2StoreGetPaymentForm", array (
				$element,
				$values
		) );
		for($i = 0; $i < count ( $results ); $i ++) {
			$result = $results [$i];
			$text .= $result;
		}

		$html = $text;
		if ($plain_format) {
			return $html;
		} else {

			// set response array
			$response = array ();
			$response ['msg'] = $html;

			// encode and echo (need to echo to send back to browser)
			echo json_encode ( $response );
			$app->close ();
		}
		// return;
	}

	function validateSelectPayment($payment_plugin, $values) {
		$response = array ();
		$response ['msg'] = '';
		$response ['error'] = '';

		$app = JFactory::getApplication ();
		JPluginHelper::importPlugin ( 'j2store' );

		// verify the form data
		$results = array ();
		$results = $app->triggerEvent ( "onJ2StoreGetPaymentFormVerify", array (
				$payment_plugin,
				$values
		) );

		for($i = 0; $i < count ( $results ); $i ++) {
			$result = $results [$i];
			if (! empty ( $result->error )) {
				$response ['msg'] = $result->message;
				$response ['error'] = '1';
			}
		}
		if ($response ['error']) {
			throw new Exception ( $response ['msg'] );
			return false;
		} else {
			return true;
		}
		return false;
	}

	function validateSelectShipping($values, $order) {
		$error = 0;

		if (isset ( $values ['shippingrequired'] )) {
			if ($values ['shippingrequired'] == 1 && empty ( $values ['shipping_plugin'] )) {
				throw new Exception ( JText::_ ( 'J2STORE_CHECKOUT_SELECT_A_SHIPPING_METHOD' ) );
				return false;
			}
		}

		if (( float ) $order->order_total == ( float ) '0.00') {
			return true;
		}

		// trigger the plugin's validation function
		// no matter what, fire this validation plugin event for plugins that extend the checkout workflow
		$results = array ();
		$results = JFactory::getApplication ()->triggerEvent ( "onValidateSelectShipping", array (
				$values
		) );

		for($i = 0; $i < count ( $results ); $i ++) {
			$result = $results [$i];
			if (! empty ( $result->error )) {
				throw new Exception ( $result->message );
				return false;
			}
		}

		if ($error == '1') {
			return false;
		}

		return true;
	}

	/**
	 * This method occurs after payment is attempted,
	 * and fires the onPostPayment plugin event
	 *
	 * @return unknown_type
	 */
	function confirmPayment() {

		J2Store::utilities()->nocache();

		$app = JFactory::getApplication ();
		$user = JFactory::getUser();
		$session = JFactory::getSession();
		$params = J2Store::config();
		$order_model = F0FModel::getTmpInstance('Orders', 'J2StoreModel');
		$orderpayment_type = $app->input->getString ( 'orderpayment_type' );
		$view = $this->getThisView();
		if ($model = $this->getThisModel())
		{
			// Push the model into the view (as default)
			$view->setModel($model, true);
		}

		// Get post values
		$values = $app->input->getArray ( $_POST );
		// backward compatibility for payment plugins
		foreach ( $values as $name => $value ) {
			$app->input->set ( $name, $value );
		}


		// set the guest mail to null if it is present
		// check if it was a guest checkout
		$account = $session->get ( 'account', 'register', 'j2store' );

		// get the order_id from the session set by the prePayment
		$orderpayment_id = ( int ) $app->getUserState ( 'j2store.orderpayment_id' );

		$order_id = $app->getUserState ( 'j2store.order_id' );


		$order = F0FTable::getAnInstance('Order', 'J2StoreTable')->getClone();
		$order->load ( array (
				'order_id' => $order_id
		) );

		$clear_cart = $params->get('clear_cart', 'order_placed');
		if($clear_cart == 'order_placed') {
			$order->empty_cart();
		}

		$order_link = J2Store::platform()->getMyprofileUrl();//JRoute::_('index.php?option=com_j2store&view=myprofile');
		if ($session->has ( 'guest', 'j2store' ) && !$user->id) {
			$guest = $session->get ( 'guest', array (), 'j2store' );
			$session->set ( 'guest_order_email', $guest ['billing'] ['email'], 'j2store' );
			$session->set ( 'guest_order_token', $order->token, 'j2store');
		}

		JPluginHelper::importPlugin ( 'j2store' );
		$html = "";

        $showPayment = false;
        $app->triggerEvent("onJ2StoreChangeShowPaymentOnTotalZero", array( $order, &$showPayment ) );

		// free product? set the state to confirmed and save the order.
		if ((! empty ( $order_id )) && ( float ) $order->order_total == ( float ) '0.00' && !$showPayment) {
			$order->payment_complete();

			// After confirm free product
			J2Store::plugin()->event( "AfterConfirmFreeProduct", array ($order) );

			//free product. So clear cart.
			if($clear_cart == 'order_confirmed') {
				$order->empty_cart();
			}
		} else {

			$values = array();
			$values['order_id'] = $order_id;
			$values['order_state_id'] = 1;

			// get the payment results from the payment plugin
			$results = $app->triggerEvent ( "onJ2StorePostPayment", array (
					$orderpayment_type,
					$values
			) );

			// Display whatever comes back from Payment Plugin for the onPrePayment
			for($i = 0; $i < count ( $results ); $i ++) {
				$html .= $results [$i];
			}

			// re-load the order in case the payment plugin updated it
			$order->load ( array (
					'order_id' => $order_id
			) );
		}

		// $order_id would be empty on posts back from Paypal, for example
		if (isset ( $order->order_id) && !empty($order->order_id)) {

			//fail-safe
			if($clear_cart == 'order_placed') {
				$order->empty_cart();
			}
			// unset a few things from the session.
			$session->clear ( 'shipping_method', 'j2store' );
			$session->clear ( 'shipping_methods', 'j2store' );
			$session->clear ( 'payment_method', 'j2store' );
			$session->clear ( 'payment_methods', 'j2store' );
			$session->clear ( 'payment_values', 'j2store' );
			$session->clear ( 'guest', 'j2store' );
			$session->clear ( 'customer_note', 'j2store' );
			$session->clear ( 'profile_order_id', 'j2store' );

			// clear coupon and voucher
			F0FModel::getTmpInstance ( 'Coupons', 'J2StoreModel' )->remove_coupon();
			F0FModel::getTmpInstance ( 'Vouchers', 'J2StoreModel' )->remove_voucher();

			// trigger onAfterOrder plugin event
			$results = $app->triggerEvent ( "onJ2StoreAfterPayment", array (
					$order
			) );

			foreach ( $results as $result ) {
				$html .= $result;
			}
		}

		$app->setUserState ( 'j2store.order_id', null);
		$app->setUserState ( 'j2store.orderpayment_id', null);

		$is_mobile = $session->get('is_mobile','','j2store');
		if($is_mobile){
			$app->redirect('index.php?option=com_j2store&view=myprofile&mobile=mobile');
		}

		$params = J2Store::config();
		if ($params->get ( 'show_postpayment_orderlink', 1 )) {
			$view->assign ( 'order_link', $order_link );
		} else {
			$view->assign ( 'order_link', '' );
		}
		if(isset($order)) {
			$view->assign ( 'order', $order);
		}
		$view->assign ( 'plugin_html', $html );
		$view->setLayout ( 'postpayment' );
		$view->display ();
		return;
	}
}