<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/models/behavior/autoload.php';
//require_once JPATH_ADMINISTRATOR.'/components/com_j2store/models/carts.php';

class J2StoreModelCartadmins extends F0FModel {
	private $behavior_prefix = 'cart';
	protected $default_behaviors = array('filters', 'cartdefault');
	protected  $_rawData = null;
	
	protected $_cartitems = null;
	var $cart_type = 'cart';

	function __construct($config = array()) {
		parent::__construct($config);
	}

	public function addAdminCartItem() {		
		$app = JFactory::getApplication();
		$errors = array();
		$json = new JObject();

		//first check if it has product id.
		$product_id = $this->input->get('product_id');
		if(!isset($product_id)) {
			$errors['error'] = JText::_('J2STORE_PRODUCT_NOT_FOUND');
			return $errors;
		}
		//product found. Load it
		$product = F0FModel::getTmpInstance('Products', 'J2StoreModel')->getItem($product_id);
		if(($product->visibility !=1) || ($product->enabled !=1) ) {
			$errors['error'] = array('error'=>JText::_('J2STORE_PRODUCT_NOT_ENABLED_CANNOT_ADDTOCART'));
			return $errors;
		}

		if($product->j2store_product_id != $product_id) {
			//so sorry. Data fetched does not match the product id
			$errors['error'] = JText::_('J2STORE_PRODUCT_NOT_FOUND');
			return $errors;
		}

		//print_r($product);exit;
		//all ok. Fire model dispatcher
		if($product->product_type) {
			$this->addBehavior($this->behavior_prefix.$product->product_type);
		}else {
			$this->addBehavior($this->behavior_prefix.'simple');
		}				
		try
		{
		    $ref_model = $this;
			// Call the behaviors
			$result = $this->modelDispatcher->trigger('onBeforeAddCartItem', array(&$ref_model, $product, &$json ));
		}
		catch (Exception $e)
		{
			// Oops, an exception occured!
			$this->setError($e->getMessage());
			echo $e->getMessage();
		}

		return $json->result;
	}


	/* public function addItem($item) {

		$session = JFactory::getSession();
		//$user = JFactory::getUser();
		$user_id = $this->input->get('user_id');
		$cart = $this->getCart(0,$user_id);
		if (!empty($cart))
		{
			$keynames = array ();

			$keynames ['cart_id'] = $cart->j2store_cart_id;
			$keynames ['variant_id'] = $item->variant_id;
			$keynames ['product_options'] = $item->product_options;

			$table = F0FTable::getInstance ( 'Cartitems', 'J2StoreTable' );

			$item->cart_id = $cart->j2store_cart_id;
			$table->product_id = $item->product_id;
			$table->variant_id = $item->variant_id;
			$table->product_type = $item->product_type;

			if ($table->load ( $keynames )) {
				// if an item exists, we just add the quantity. Even if it does not
				$table->product_qty = $table->product_qty + $item->product_qty;
			} else {
				foreach ( $item as $key => $value ) {
					if (property_exists ( $table, $key )) {
						$table->set ( $key, $value );
					}
				}
			}

			if ($table->store ()) {
				try {
					// Call the behaviors
					$result = $this->modelDispatcher->trigger ( 'AfterAddCartItem', array (
							&$this,
							&$table
					) );
				} catch ( Exception $e ) {
					// Oops, an exception occured!
					$this->setError ( $e->getMessage () );
					return false;
				}
				return $cart;
			} else {
				return false;
			}

		} else {
			return false;
		}
		return false;
	} */





 public function addItem($item) {
        $platform = J2Store::platform();
        $fof_helper = J2Store::fof();
		$app = $platform->application();
		$oid = $app->input->getInt ( 'oid' );
		// load order table
		$order = $fof_helper->loadTable( 'Order', 'J2StoreTable' );
		$order->load ( $oid );
		if (is_array ( $item )) {
			$item = $platform->toObject ( $item );
		}		
		// cart details
		$cart = $this->getCart ($order->cart_id);		
		if (! empty ( $cart )) {
			$keynames = array ();			
			$keynames ['cart_id'] = $cart->j2store_cart_id;
			$keynames ['variant_id'] = $item->variant_id;
			$keynames ['product_options'] = $item->product_options;
			//cart table update			
			$table = $fof_helper->loadTable( 'Cartitems', 'J2StoreTable' );
			$item->cart_id = $cart->j2store_cart_id;						
			if ($table->load ( $keynames )) {
				$table->product_id = $item->product_id;
				$table->variant_id = $item->variant_id;
				$table->product_type = $item->product_type;
				// if an item exists, we just add the quantity. Even if it does not
				$table->product_qty = $table->product_qty + $item->product_qty;
			} else {
				foreach ( $item as $key => $value ) {
					if (property_exists ( $table, $key )) {
						$table->set ( $key, $value );
					}
				}
			}
			
			if ($table->store ()) {					
				$variantTable = $fof_helper->loadTable( 'Variant', 'J2StoreTable' );
				$variantTable->load($table->variant_id);
				$product_helper = J2Store::product ();
				$item->group_id = implode(',', JAccess::getGroupsByUser($order->user_id));

				$pricing = $product_helper->getPrice($variantTable,$item->product_qty,$item->group_id);

				$item->weight = $variantTable->weight;
				$item->price = $pricing->price;
				$item->j2store_variant_id = $table->variant_id;
				
				if($item->product_type) {
					$this->addBehavior($this->behavior_prefix.$item->product_type);
				}else {
					$this->addBehavior($this->behavior_prefix.'simple');
				}
				
				//run model behaviors
				try
				{
				    $ref_model = $this;
					// Call the behaviors
					$this->modelDispatcher->trigger('onGetCartItems', array(&$ref_model, &$item));
				}
				catch (Exception $e)
				{
					// Oops, an exception occured!
					$this->setError($e->getMessage());
					return false;
				}
			
				
				//orderitem table
				$orderitemTable = $fof_helper->loadTable( 'Orderitem', 'J2StoreTable' );
				$orderitem = new \stdClass();
				// get product
				$product = $product_helper->setId ( $item->product_id )->getProduct ();								
				$fof_helper->getModel( 'Products', 'J2StoreModel' )->runMyBehaviorFlag ( true )->getProduct ( $product );

				$orderitem->cartitem_id = $table->j2store_cartitem_id;
				$orderitem->order_id = $order->order_id;
				$orderitem->cart_id = $item->cart_id;
				$orderitem->product_id = $item->product_id;
				$orderitem->product_type = $item->product_type;
				$orderitem->variant_id = $item->variant_id;
				$orderitem->vendor_id = isset ( $item->vendor_id ) && $item->vendor_id ? $item->vendor_id : '0';
				$orderitem->orderitem_name = $product->product_name;
                $orderitem->product_params = $product->params;
				$orderitem->orderitem_sku = $variantTable->sku;
				$orderitem->orderitem_quantity = J2Store::utilities ()->stock_qty ( $item->product_qty );
				
				$array = array ();
				$array ['thumb_image'] = isset ( $product->thumb_image ) ? $product->thumb_image : '';
				$array ['shipping'] = $variantTable->shipping;
				
				$registry = $platform->getRegistry($array,true);
				$orderitem->orderitem_params = $registry->toString ( 'JSON' );
				$product_option_array = array();
				if($item->product_options){					
					$product_option_array = unserialize ( base64_decode ( $item->product_options ));
				}
				
				//product option 
				$product_option_data = $product_helper->getOptionPrice ( $product_option_array, $product->j2store_product_id );
                if(isset($item->product_type) && $item->product_type == 'flexivariable'){
                    $product_option_data = array();
                    $product_option_data ['option_data'] = $item->options;
                    $product_option_data ['option_price'] = $item->option_price;
                    $product_option_data ['option_weight'] = 0;
                }
				$orderitem->orderitem_option_price = $item->option_price;
				// price which is not processed
				$orderitem->orderitem_price = $item->pricing->price;
				$orderitem->orderitem_baseprice = $item->pricing->base_price;
				//weight
				$orderitem->orderitem_weight = isset($product->variant) && ($product->variant->weight) && ! empty ( $product->variant->weight ) ? $product->variant->weight : 0;
				//tax profile
				$orderitem->orderitem_taxprofile_id = $product->taxprofile_id;				
				// just a placeholder and also used as reference for product options
				$orderitem->orderitem_attributes = $item->product_options;
				$orderitem->orderitem_raw_attributes = $item->product_options;
				// $orderitem->orderitemattributes = $item->product_options;
				//$price = $orderitem->orderitem_price + $orderitem->orderitem_option_price;
				//$line_price = $price * $orderitem->orderitem_quantity;
				//$tax_model = F0FModel::getTmpInstance ( 'Taxprofiles', 'J2StoreModel' );
				//$order_info = $order->getOrderInformation ();
				//set billing and shipping to tax profile model
				//////////
				//echo "<pre>";print_r($orderitem);exit;
				//tax calculation
				////////////////////////
                $insert_item_attributes = true;
				$fof_helper->getModel( 'Orderitem', 'J2StorModel' )->getOrderItemParams ( $orderitem, $item );
				if ($orderitemTable->load ( array (
						'order_id' => $order->order_id,
						'product_id' => $item->product_id,
						'variant_id' => $item->variant_id ,
						'orderitem_attributes' => $orderitem->orderitem_attributes
				) )) {
					$orderitemTable->orderitem_quantity += $item->product_qty;
					$orderitem->orderitem_quantity = $orderitemTable->orderitem_quantity;
                    $insert_item_attributes = false;
				}
				$orderitem->orderitemattributes = array ();
				foreach ( $product_option_data ['option_data'] as $product_option ) {
					$orderitem_attrib = new \stdClass ();
					$orderitem_attrib->productattributeoption_id = $product_option ['product_option_id'];
					$orderitem_attrib->productattributeoptionvalue_id = $product_option ['product_optionvalue_id'];
					$orderitem_attrib->orderitemattribute_name = $product_option ['name'];
					$orderitem_attrib->orderitemattribute_value = $product_option ['option_value'];
					$orderitem_attrib->orderitemattribute_prefix = $product_option ['price_prefix'];
					$orderitem_attrib->orderitemattribute_price = $product_option ['price'];
					$orderitem_attrib->orderitemattribute_code = isset ( $product_option ['option_sku'] ) ? $product_option ['option_sku'] : '';
					$orderitem_attrib->orderitemattribute_type = $product_option ['type'];
					$orderitem->orderitemattributes [] = $orderitem_attrib;
				}
				
				$orderitem->orderitem_weight_total = ($orderitem->orderitem_weight + (! empty ( $product_option_data ['option_weight'] ) ? $product_option_data ['option_weight'] : 0)) * $item->product_qty;
                J2Store::plugin()->event('AfterAddOrderItem', array(&$orderitem));
				if ($orderitemTable->save ( $orderitem )) {
				    if($insert_item_attributes){
                        $order->saveOrderItemAttributes ( $orderitem->orderitemattributes, $orderitemTable );
                    }
					$order->cart_id = $cart->j2store_cart_id;			
					$order->getAdminTotals ( );	
					//return true;
				}else{
					return false;
				}
				return $cart;
			}else{
				return false;
			}
		}else {
			return false;
		}
		
		return false;
	}
	
	function deleteItem() {
	
		// TODO we should be removing promotions as well
		$app = JFactory::getApplication();
		$cartitem_id = $this->input->get ( 'cartitem_id' );
		$cartitem = F0FTable::getInstance ( 'Cartitem', 'J2StoreTable' );
	
		// the user wants to remove the item from cart. so remove it
		if ($cartitem->load ( $cartitem_id )) {
	
			if($cartitem->cart_id != $this->getAdminCartId()) {
				$this->setError ( JText::_ ( 'J2STORE_CART_DELETE_ERROR' ) );
				return false;
			}
	
			$item = new JObject ();
			$item->product_id = $cartitem->product_id;
			$item->variant_id = $cartitem->variant_id;
			$item->product_options = $cartitem->product_options;
	
			if ($cartitem->delete ( $cartitem_id )) {
				J2Store::plugin()->event( 'RemoveFromCart', array (
				$item
				) );
				return true;
			} else {
				$this->setError ( JText::_ ( 'J2STORE_CART_DELETE_ERROR' ) );
				return false;
			}
		} else {
			$this->setError ( JText::_ ( 'J2STORE_CART_DELETE_ERROR' ) );
			return false;
		}
	}
	 public function getCart($cart_id=0/*, $user_id */) {	
		$app = JFactory::getApplication();
		$session = JFactory::getSession ();
		$data = $app->input->getArray($_POST);
		$order_id = $this->input->getInt('oid',0);
		$order = F0FTable::getInstance('Order' ,'J2StoreTable');
		$order->load($order_id);
		if(empty($cart_id)){
			$cart_id = $order->cart_id;
		}
		$user_id = $order->user_id;		
		$keynames = array();
	
		if(!$cart_id) {
			$cart_id = $this->getState('filter_cart_id', null);
		}	
		if(!empty($cart_id) && $cart_id > 0) {
			$keynames['j2store_cart_id'] = $cart_id;
		} 	//one more key needs to be added.
	
		$cart = F0FTable::getInstance('Cart', 'J2StoreTable');
		$cart->reset();
		if (!$cart->load( $keynames) )	{
			//new cart
			$cart->is_new = true;
		}else{
			$cart->is_new = false;
		}
	
		$cart->cart_type = $this->getCartType();
		$cart->user_id = $user_id;
		$cart->session_id = $session->getId();	
		$cart->store();		
		//set the cart id to session
		$this->setCartId($cart->j2store_cart_id);
		$order->cart_id = $cart->j2store_cart_id;
		$order->store();		
		return $cart;
	}

	public function setCartId($cart_id=0) {
		
		$session = JFactory::getSession();
		$session->set('admin_cart_id.'.$this->getCartType(), $cart_id, 'j2store');
	}
	public function getAdminCartId() {
		$session = JFactory::getSession();
		$cart_id = $session->get('admin_cart_id.'.$this->getCartType(), 0, 'j2store');
		return $cart_id;
	}
	public function setCartType($type='cart') {
		$this->cart_type = $type;
	}
	
	public function getCartType() {
		return $this->cart_type;
	}
	function getCartUrl($order_id) {
		$app = JFactory::getApplication();
		$url = 'administrator/index.php?option=com_j2store&view=orders&task=createOrder&layout=summary&oid='.$order_id;		
		/* if($app->isAdmin()){
			$url = 'index.php?option=com_j2store&view=orders&task=createOrder&layout=summary&oid='.$order_id;
		} */
		J2Store::plugin()->event('GetCartLink', array(&$url));
		return $url;
	}
	
	function update(){
		$app = JFactory::getApplication ();
		$post = $app->input->getArray ( $_POST );
		$order = F0FTable::getInstance ( 'Order', 'J2StoreTable' );
		$order->load($post['oid']);
		$productHelper = J2Store::product ();
		$json = array ();
		if(isset($post['jform']['orderitem'])){
			foreach ( $post['jform']['orderitem'] as $orderitem_id => $orderitem ) {				
				$cartitem = F0FModel::getTmpInstance ( 'Cartitem', 'J2StoreModel' )->getItem ( $orderitem['cartitem_id']);
				//sanity check
				if($cartitem->cart_id != $orderitem['cart_id']) continue;
				
				if ($this->validate ($cartitem, $orderitem ) === false) {
					// an error occured. Return it
					$json ['error'] = $this->getError();
					continue; // exit from the loop
				}
				// validation successful. Update cart
				$cartitem2 = F0FTable::getInstance ( 'Cartitem', 'J2StoreTable' )->getClone();
				$cartitem2->load ( $orderitem['cartitem_id']);
				if (empty ( $orderitem['orderitem_quantity'] ) || $orderitem['orderitem_quantity'] < 1) {
					// the user wants to remove the item from cart. so remove it
					
					$item = new JObject ();
					$item->product_id = $cartitem->product_id;
					$item->variant_id = $cartitem->variant_id;
					$item->product_type = $cartitem->product_type;
					$item->product_options = $cartitem->product_options;
					
					$cartitem2->delete ( );
					J2Store::plugin()->event( 'RemoveFromCart', array (
					$item
					) );
						
				}else {
					$cartitem2->product_qty = J2Store::utilities()->stock_qty($orderitem['orderitem_quantity']);
					$cartitem2->store ();
					$orderitem_table = F0FTable::getInstance ( 'Orderitem', 'J2StoreTable' )->getClone();
					$orderitem_table->load($orderitem['j2store_orderitem_id']);
					if(!empty($orderitem_table->variant_id)){
						$variant_table = F0FTable::getInstance ( 'Variant', 'J2StoreTable' )->getClone();
						$variant_table->load($orderitem_table->variant_id);
						$group_id = implode(',', JAccess::getGroupsByUser($order->user_id));
						$pricing = J2Store::product()->getPrice($variant_table,$orderitem['orderitem_quantity'],$group_id);
						$orderitem_table->orderitem_price = $pricing->price;
						$orderitem_table->orderitem_baseprice = $pricing->base_price;
					}
					$orderitem_table->orderitem_quantity = $orderitem['orderitem_quantity'];
					$orderitem_table->store();
				}
			}						
		}		
		return $json;
	}
	
	function validate($cartitem, $orderitem) {
	
		$json = new JObject();
	
		$cart = $this->getCart($orderitem['cart_id']);
		if($cart->cart_type != 'cart') return true;
	
		if($cartitem->product_type) {
			$this->addBehavior($this->behavior_prefix.$cartitem->product_type);
		}else {
			$this->addBehavior($this->behavior_prefix.'simple');
		}
	
		try
		{
            $ref_model = $this;
			// Call the behaviors
			$result = $this->modelDispatcher->trigger('onValidateCart', array(&$ref_model, $cartitem, $orderitem['orderitem_quantity']));
		}
		catch (Exception $e)
		{
			// Oops, an exception occured!
			$result = false;
			$this->setError($e->getMessage());
		}
		return $result;
	}	
}
