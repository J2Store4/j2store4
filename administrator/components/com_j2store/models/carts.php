<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/models/behavior/autoload.php';

class J2StoreModelCarts extends F0FModel {

	protected $default_behaviors = array('filters', 'cartdefault');
	protected  $_rawData = null;
	private $behavior_prefix = 'cart';
	protected $_cartitems = null;
	var $cart_type = 'cart';

	function __construct($config = array()) {

		parent::__construct($config);
	}

	public function addCartItem() {

		$app = JFactory::getApplication();
		$errors = array();
		$json = new JObject();

		//first check if it has product id.
		$product_id = $app->input->get('product_id');

		if(!isset($product_id)) {
			$errors['error'] =  array('general'=>JText::_('J2STORE_PRODUCT_NOT_FOUND'));
			return $errors;
		}

		//check negative quantity... Customers should not be able to add a negative quantity
		$quantity = $app->input->get('product_qty', 1);
		if($quantity <= 0) {
			$errors['error'] = array('general'=>JText::_('J2STORE_PRODUCT_INVALID_QUANTITY'));
			return $errors;
		}


		//product found. Load it
		$product = F0FModel::getTmpInstance('Products', 'J2StoreModel')->getItem($product_id);

		if(($product->visibility !=1) || ($product->enabled !=1) ) {
			$errors['error'] = array('general'=>JText::_('J2STORE_PRODUCT_NOT_ENABLED_CANNOT_ADDTOCART'));
			return $errors;
		}

		if($product->j2store_product_id != $product_id) {
			//so sorry. Data fetched does not match the product id
			$errors['error'] =  array('general'=>JText::_('J2STORE_PRODUCT_NOT_FOUND'));
			return $errors;
		}

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
			// Oops, an exception occurred!
			$this->setError($e->getMessage());
			echo $e->getMessage();
		}

		return $json->result;
	}

	public function addItem($item) {
        $platform = J2Store::platform();
        $fof_helper = J2Store::fof();
		$cart = $this->getCart();
		if (!empty($cart))
		{
			$keynames = array ();

			$keynames ['cart_id'] = $cart->j2store_cart_id;
			$keynames ['variant_id'] = $item->variant_id;
			$keynames ['product_options'] = $item->product_options;

			$table = $fof_helper->loadTable( 'Cartitems', 'J2StoreTable' );

			$item->cart_id = $cart->j2store_cart_id;
			$table->product_id = $item->product_id;
			$table->variant_id = $item->variant_id;
			$table->product_type = $item->product_type;
			$item_params = $platform->getRegistry('{}');
			if(isset($item->cartitem_params)) {
				if (is_array($item->cartitem_params)) {
					$item_params->loadArray($item->cartitem_params);
				} else {
					$item_params->loadString($item->cartitem_params);
				}
			}
			$item->cartitem_params = $item_params->toString('JSON');

			J2Store::plugin()->event("BeforeLoadCartItemForAdd", array( &$keynames, $item ) );

			if ($table->load ( $keynames )) {
				// if an item exists, we just add the quantity. Even if it does not
				$table->product_qty = $table->product_qty + $item->product_qty;

				$table_params = $platform->getRegistry($table->cartitem_params);
				$table_params->merge($item_params);
				$table->cartitem_params = $table_params->toString('JSON');
			} else {
				foreach ( $item as $key => $value ) {
					if (property_exists ( $table, $key )) {
						$table->set ( $key, $value );
					}
				}
			}

			if ($table->store ()) {
				try {
                    $ref_model = $this;
					// Call the behaviors
					$result = $this->modelDispatcher->trigger ( 'AfterAddCartItem', array (
							&$ref_model,
							&$table
					) );
				} catch ( Exception $e ) {
					// Oops, an exception occurred!
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
	}

	protected function _buildQueryWhere(&$query)
	{
		$filter_user      = $this->getState('filter_user');
		$filter_user_leq  = $this->getState('filter_user_leq');
		$filter_session   = $this->getState('filter_session');
		$filter_date_from	= $this->getState('filter_date_from');
		$filter_date_to		= $this->getState('filter_date_to');
		$filter_cart_id		= $this->getState('filter_cart_id', null);
		$filter_cart_type	= $this->getState('filter_cart_type', $this->cart_type);
		$filter_search = $this->getState('filter_search', null);
		$filter_user_join = $this->getState('filter_usertable_join', null);

		if (strlen($filter_user))
		{
			$query->where('tbl.user_id = '.$this->_db->Quote($filter_user));
		}

		if (strlen($filter_user_leq))
		{
			$query->where('tbl.user_id <= '.$this->_db->Quote($filter_user_leq));
		}


		if (strlen($filter_session))
		{
			$query->where( "tbl.session_id = ".$this->_db->Quote($filter_session));
		}

		if (strlen($filter_date_from))
		{
			$query->where("tbl.created_on >= ".$filter_date_from);
		}

		if (strlen($filter_date_to))
		{
			$query->where("tbl.created_on <= ".$filter_date_to);
		}

		if (strlen($filter_cart_type))
		{
			$query->where("tbl.cart_type = ".$this->_db->q($filter_cart_type));
		}

		if($filter_search && $filter_user_join) {
			 $query->where(
					' ( '.
					'u.'.$this->_db->qn('username').' LIKE '.$this->_db->q('%'.$filter_search.'%').' OR '.
					'u.'.$this->_db->qn('email').' LIKE '.$this->_db->q('%'.$filter_search.'%').' OR '.
					'u.'.$this->_db->qn('name').' LIKE '.$this->_db->q('%'.$filter_search.'%').' OR '.
			 		'tbl.'.$this->_db->qn('j2store_cart_id').' LIKE '.$this->_db->q('%'.$filter_search.'%').' OR '.
					'tbl.'.$this->_db->qn('user_id').' LIKE '.$this->_db->q('%'.$filter_search.'%')
				.' ) '
			) ;
		}

	}

	protected function _buildQueryFields(&$query)
	{
		$query->select('tbl.*');
		$subquery = $this->_db->getQuery(true)->select('COUNT(*)')->from('#__j2store_cartitems AS cartitem')
		->where('cartitem.cart_id = tbl.j2store_cart_id');
		$query->select('('.$subquery.') AS totalitems');
	}

	 public function buildQuery($overrideLimits=false) {
		$db = JFactory::getDbo();
		$filter_order = $this->getState('filter_order','tbl.j2store_cart_id');
		$filter_order_Dir = $this->getState('filter_order_Dir','ASC');

		$query = $db->getQuery(true)->from('#__j2store_carts as tbl');
		$this->_buildQueryFields($query);

		$filter_user_join = $this->getState('filter_usertable_join', null);
		if($filter_user_join) {
			$query->select('u.username, u.name, u.email');
			$query->join('LEFT OUTER', '#__users AS u ON tbl.user_id=u.id');
		}
		$this->_buildQueryWhere($query);

		if(!in_array($filter_order_Dir,array('ASC','DESC'))){
		    $filter_order_Dir = "ASC";
		}
		if(!empty($filter_order)  && in_array($filter_order,array('tbl.j2store_cart_id'))){
			$query->order($filter_order .' '. $filter_order_Dir);
		}else{
            $query->order('tbl.j2store_cart_id '. $filter_order_Dir);
        }
		return $query;
	}

	public function loadCart($cart_id=0) {

		$query = $this->buildQuery();
		$query->order('tbl.modified_on DESC LIMIT 1');
		$this->_db->setQuery($query);
		return $this->_db->loadObject();
	}

	public function getCart($cart_id=0,$need_create_cart = true) {

		$app = JFactory::getApplication();
		$session = JFactory::getSession ();
		$user = JFactory::getUser ();
		if (! $user->id) 			// saves session id (will be needed after logging in)
		{
			$session->set ( 'old_sessionid', $session->getId (), 'j2store' );
		}

		$keynames = array();

		if(!$cart_id) {
			$cart_id = $this->getState('filter_cart_id', null);
		}

		if(!empty($cart_id) && $cart_id > 0) {
			$keynames['j2store_cart_id'] = $cart_id;
		} else {
			$keynames['user_id'] = !empty($user->id) ? $user->id : '0';
			if (empty ( $user->id )) {
				$keynames['session_id'] = $session->getId ();
			}
		}

		//one more key needs to be added.
		$keynames['cart_type'] = $this->getCartType();

		static $carts = array();
		$filter = '';
		if(!empty($keynames)) $filter = "(".implode(' OR ',$keynames).")";

		if(!isset($carts[$filter]) || !isset($carts[$filter]->j2store_cart_id) || empty($carts[$filter]->j2store_cart_id)) {
			$cart = F0FTable::getInstance('Cart', 'J2StoreTable')->getClone();
			if (!$cart->load( $keynames) )	{
				//new cart
				$cart->is_new = true;
				//only when new, we need to assign the user id and the session id.
				$cart->user_id = $user->id;
				$cart->session_id = $session->getId();
			}else{
				$cart->is_new = false;
			}

			$cart->cart_type = $this->getCartType();
			if($need_create_cart){
                $cart->store();
            }


			//set the cart id to session
			$this->setCartId($cart->j2store_cart_id);
			//$session->set('cart_id', $cart->j2store_cart_id, 'j2store');
            $carts[$filter] = $cart;
		}
		return $carts[$filter];

	}

	public function getItems($focrce = false) {

		$app = JFactory::getApplication();
		$session = JFactory::getSession ();
		$user = JFactory::getUser ();
        $cart = $this->getCart(0,false);
        if(!isset($cart->j2store_cart_id) || empty($cart->j2store_cart_id)){
            return array();
        }
			// now process the items
			static $cartsets;
			if(!is_array($cartsets)) $cartsets = array();
			if($focrce){
                $cartsets = array();
            }
			if(!isset($cartsets[$cart->j2store_cart_id])) {
				//we have the cart. Now get items for this cart
				$cartitem_model = F0FModel::getTmpInstance('Cartitems', 'J2StoreModel');
				$cartitem_model->setState('filter_cart', $cart->j2store_cart_id);
				$items = $cartitem_model->getList();
	
				$params = J2Store::config();
				foreach($items as &$item) {
	
					//all ok. Fire model dispatcher
	
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
						// Oops, an exception occurred!
						$this->setError($e->getMessage());
						return array();
					}
	
				} // cart item loops
	
				J2Store::plugin()->event('AfterGetCartItems', array(&$items));
				$cartsets[$cart->j2store_cart_id] = $items;
			}	

		
		return $cartsets[$cart->j2store_cart_id];
	}

	function update() {
		$app = JFactory::getApplication ();
		$post = $app->input->getArray ( $_POST );
		$productHelper = J2Store::product ();

		$cart_id = $this->getCartId();
		$json = array ();

		foreach ( $post ['quantities'] as $cartitem_id => $quantity ) {

			$cartitem = F0FModel::getTmpInstance ( 'Cartitem', 'J2StoreModel' )->getItem ( $cartitem_id);
			//sanity check
			if($cartitem->cart_id != $cart_id) continue;
			// get the difference quantity

			if ($this->validate ($cartitem, $quantity ) === false) {
				// an error occurred. Return it
				$json ['error'] = $this->getError();
				continue; // exit from the loop
			}

			// validation successful. Update cart
			$cartitem2 = F0FTable::getInstance ( 'Cartitem', 'J2StoreTable' );
			$cartitem2->load ( $cartitem_id);
			if (empty ( $quantity ) || $quantity < 1) {
				// the user wants to remove the item from cart. so remove it

				$item = new JObject ();
				$item->product_id = $cartitem->product_id;
				$item->variant_id = $cartitem->variant_id;
				$item->product_type = $cartitem->product_type;
				$item->product_options = $cartitem->product_options;

				$cartitem2->delete ( $cartitem_id );
				J2Store::plugin()->event( 'RemoveFromCart', array (
						$item
				) );
			} else {
				$cartitem2->product_qty = J2Store::utilities()->stock_qty($quantity);
				$cartitem2->store ();
			}
		}
		J2Store::plugin()->event('AfterUpdateCart', array($cart_id, $post));
		return $json;
	}

	function deleteItem() {

		// TODO we should be removing promotions as well
		$app = JFactory::getApplication();
		$cartitem_id = $app->input->get ( 'cartitem_id' );
		$cartitem = F0FTable::getInstance ( 'Cartitem', 'J2StoreTable' );

		// the user wants to remove the item from cart. so remove it
		if ($cartitem->load ( $cartitem_id )) {

			if($cartitem->cart_id != $this->getCartId()) {
				$this->setError ( JText::_ ( 'J2STORE_CART_DELETE_ERROR' ) );
				return false;
			}

			$item = new JObject ();
			$item->product_id = $cartitem->product_id;
			$item->variant_id = $cartitem->variant_id;
			$item->product_options = $cartitem->product_options;
			$item->j2store_cartitem_id = $cartitem->j2store_cartitem_id;

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

	function validate($cartitem, $quantity) {

		$json = new JObject();

		$cart = $this->getCart($this->getCartId());
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
			$result = $this->modelDispatcher->trigger('onValidateCart', array(&$ref_model, $cartitem, $quantity));
		}
		catch (Exception $e)
		{
			// Oops, an exception occurred!
			$result = false;
			$this->setError($e->getMessage());
		}
		return $result;
	}

	function getCartUrl() {

		$url = J2Store::platform()->getCartUrl();//JRoute::_('index.php?option=com_j2store&view=carts',false);
		//allow plugins to alter the cart link.
		//This allows 3rd-party developers to create different checkout steps for j2store
		//Example. The plugin can return a completely different link: index.php?option=com_example&view=myview
		J2Store::plugin()->event('GetCartLink', array(&$url));
		return $url;
	}

	function getCheckoutUrl() {

		$url = J2Store::platform()->getCheckoutUrl();//JRoute::_('index.php?option=com_j2store&view=checkout',false);
		//allow plugins to alter the checkout link.
		//This allows 3rd-party developers to create different checkout steps for j2store
		//Example. The plugin can return a completely different link: index.php?option=com_example&view=myview

		J2Store::plugin()->event('GetCheckoutLink', array(&$url));
		return $url;
	}



	function getContinueShoppingUrl() {

		$params = J2Store::config();
		$type = $params->get('config_continue_shopping_page', 'previous');

		$item = new JObject();
		$item->type = $type;

		switch($type) {
			case 'previous':
			default:
				$item->url = '';
				break;

			case 'menu':
				$url = '';
				//get the menu item id
				$menu_itemid = $params->get('continue_shopping_page_menu', '');
				if(empty($menu_itemid)) {
					$item->url = '';
					$item->type = 'previous';
				} else {

					$application = JFactory::getApplication();
					$menu = $application->getMenu('site');
					$menu_item = $menu->getItem($menu_itemid);

					if(is_object($menu_item)) {

						// we have the menu item. See if language associations are there
						JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');
						try {
							$associations = MenusHelper::getAssociations($menu_item->id);
						}catch (Exception $e) {
							$associations = array();
						}

						//get the current language code
						$tag = JFactory::getLanguage()->getTag();
						if(isset($associations[$tag])) {
							$cmenu = $menu->getItem($associations[$tag]);
						}else {
							$cmenu = $menu_item;
						}
						
						if (JURI::isInternal($cmenu->link)) {
							$url = $link = JRoute::_($cmenu->link.'&Itemid='.$cmenu->id, false);
						}
					}
					if(empty($url)) {
						$item->url = '';
						$item->type = 'previous';
					} else {
						$item->url =$url;
					}
				}

				break;

				case 'url':

					$custom_url = $params->get('config_continue_shopping_page_url', '');
					if(empty($custom_url)) {
						$item->url = '';
						$item->type = 'previous';
					} else {
						$item->url = $custom_url;
					}
					break;

		}

		//allow plugins to alter the checkout link.
		//This allows 3rd-party developers to create different checkout steps for j2store
		//Example. The plugin can return a completely different link

		J2Store::plugin()->event('GetContinueShoppingUrl', array(&$item));
		return $item;
	}


	public function validate_files($files = array()) {
		$app = JFactory::getApplication();
		$json = array();
		if(count($files) < 1) $files = $app->input->files->get('file');

		$upload_result = $this->uploadFile($files);
		if($upload_result == false) {
			$json['error'] = $this->getError();
		} else {
            J2Store::plugin()->event('AfterValidateFiles',array(&$json,&$upload_result));
            if(!$json){
                $upload = F0FTable::getInstance('Upload', 'J2StoreTable');
                $upload->reset();
                $upload->j2store_upload_id = null;


                $jdate = new JDate();

                $upload_result['created_by'] = JFactory::getUser()->id;
                $upload_result['created_on'] = $jdate->toSql();
                $upload_result['enabled']    = 1;

                if(!$upload->save($upload_result)) {
                    $json['error'] = JText::sprintf('J2STORE_UPLOAD_ERR_GENERIC_ERROR');
                }
            }
		}

		if (!$json) {
			$json['name'] = $upload_result['original_name'];
			$json['code'] = $upload_result['mangled_name'];
			$json['success'] = JText::_('J2STORE_UPLOAD_SUCCESSFUL');
		}
		return $json;
	}

	protected function uploadFile($file, $checkUpload = true)
	{
		if (isset($file['name']))
        {
            JLoader::import('joomla.filesystem.file');

			// Can we upload this file type?
			if ($checkUpload)
			{

				if(!class_exists('MediaHelper')) {
					require_once(JPATH_ADMINISTRATOR.'/components/com_media/helpers/media.php');
				}

                $err   = '';
				$paths = array(JPATH_ROOT, JPATH_ADMINISTRATOR);
				$jlang = JFactory::getLanguage();
				$jlang->load('com_media', $paths[0], 'en-GB', true);
				$jlang->load('com_media', $paths[0], null, true);
				$jlang->load('com_media', $paths[1], 'en-GB', true);
				$jlang->load('com_media', $paths[1], null, true);

				if (!MediaHelper::canUpload($file, $err))
                {
					if (!empty($err))
					{
						$err = JText::_($err);
					}
					else
					{
						$app = JFactory::getApplication();
						$errors = $app->getMessageQueue();

						if (count($errors))
						{
							$error = array_pop($errors);
							$err = $error['message'];
						}
						else
						{
							$err = '';
						}
					}

					$content = file_get_contents($file['tmp_name']);
					if (preg_match('/\<\?php/i', $content)) {
						$err = JText::_('J2STORE_UPLOAD_FILE_PHP_TAGS');
					}

					if (!empty($err))
					{
						$this->setError(JText::_('J2STORE_UPLOAD_ERR_MEDIAHELPER_ERROR').' '.$err);
					}
					else
					{
						$this->setError(JText::_('J2STORE_UPLOAD_ERR_GENERIC_ERROR'));
					}

					return false;
				}
			}

			// Get a (very!) randomised name
            $serverkey = JFactory::getConfig()->get('secret','');
			$sig       = $file['name'].microtime().$serverkey;

			if(function_exists('sha256'))
            {
				$mangledname = sha256($sig);
			}
            elseif(function_exists('sha1'))
            {
				$mangledname = sha1($sig);
			}
            else
            {
				$mangledname = md5($sig);
			}
			$upload_folder_path = JPATH_ROOT.'/media/j2store/uploads';
			if(!JFolder::exists($upload_folder_path)) {
				if(!JFolder::create($upload_folder_path)) {
					$this->setError(JText::_('J2STORE_UPLOAD_ERROR_FOLDER_PERMISSION_ERROR'));
				}
			}
			//sanitize file name
			$filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($file['name'], ENT_QUOTES, 'UTF-8')));

			$name = $filename . '.' . md5(mt_rand());
			// ...and its full path
			$filepath = JPath::clean(JPATH_ROOT.'/media/j2store/uploads/'.$name);

			// If we have a name clash, abort the upload
			if (JFile::exists($filepath))
            {
				$this->setError(JText::_('J2STORE_UPLOAD_ERR_NAMECLASH'));
				return false;
			}

			// Do the upload
			if ($checkUpload)
			{
				if (!JFile::upload($file['tmp_name'], $filepath))
                {
					$this->setError(JText::_('J2STORE_UPLOAD_ERR_CANTJFILEUPLOAD'));
					return false;
				}
			}
			else
			{
				if (!JFile::copy($file['tmp_name'], $filepath))
				{
					$this->setError(JText::_('J2STORE_UPLOAD_ERR_CANTJFILEUPLOAD'));
					return false;
				}
			}

			// Get the MIME type
			if(function_exists('mime_content_type'))
            {
				$mime = mime_content_type($filepath);
			}
            elseif(function_exists('finfo_open'))
            {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mime = finfo_file($finfo, $filepath);
			}
            else
            {
				$mime = 'application/octet-stream';
			}

			// Return the file info
			return array(
				'original_name'	=> $file['name'],
				'mangled_name'	=> $mangledname,
				'saved_name'	=> $name,
				'mime_type'			=> $mime
			);
		}
        else
        {
			$this->setError(JText::_('J2STORE_ATTACHMENTS_ERR_NOFILE'));

			return false;
		}
	}

	public function setCartId($cart_id=0) {
		$session = JFactory::getSession();
		$session->set('cart_id.'.$this->getCartType(), $cart_id, 'j2store');
	}

	public function getCartId() {
		$session = JFactory::getSession();
		$cart_id = $session->get('cart_id.'.$this->getCartType(), 0, 'j2store');
		return $cart_id;
	}

	public function setCartType($type='cart') {
		$this->cart_type = $type;
	}

	public function getCartType() {
		return $this->cart_type;
	}

	public function getEmptyCartRedirectUrl(){
		$params = J2Store::config();
		$type = $params->get('config_cart_empty_redirect', 'cart');
		$url = '';
		switch($type) {
			case 'cart':
			default:
				$url = '';
				break;

			case 'menu':
				//get the menu item id
				$menu_itemid = $params->get('continue_cart_redirect_menu', '');
				if(empty($menu_itemid)) {
					$url = '';
				} else {

					$application = JFactory::getApplication();
					$menu = $application->getMenu('site');
					$menu_item = $menu->getItem($menu_itemid);

					if(is_object($menu_item)) {

						// we have the menu item. See if language associations are there
						JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');
						try {
							$associations = MenusHelper::getAssociations($menu_item->id);
						}catch (Exception $e) {
							$associations = array();
						}

						//get the current language code
						$tag = JFactory::getLanguage()->getTag();
						if(isset($associations[$tag])) {
							$cmenu = $menu->getItem($associations[$tag]);
						}else {
							$cmenu = $menu_item;
						}

						if (JURI::isInternal($cmenu->link)) {
							$url = JRoute::_($cmenu->link.'&Itemid='.$cmenu->id, false);
						}
					}
				}

				break;

			case 'url':

				$custom_url = $params->get('config_cart_redirect_page_url', '');
				if(empty($custom_url)) {
					$url = '';
				} else {
					$url = $custom_url;
				}
				break;

		}

		return $url;
	}
}
