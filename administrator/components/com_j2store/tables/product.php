<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/strapper.php');
class J2StoreTableProduct extends F0FTable
{

	protected $_product = array();

	public function __construct($table, $key, &$db, $config=array())
	{
		$query = $db->getQuery(true)
        ->select($db->qn('#__j2store_productimages').'.'.$db->qn('j2store_productimage_id'))
        ->select($db->qn('#__j2store_productimages').'.'.$db->qn('main_image'))
        ->select($db->qn('#__j2store_productimages').'.'.$db->qn('main_image_alt'))
        ->select($db->qn('#__j2store_productimages').'.'.$db->qn('thumb_image'))
        ->select($db->qn('#__j2store_productimages').'.'.$db->qn('thumb_image_alt'))
        ->select($db->qn('#__j2store_productimages').'.'.$db->qn('additional_images'))
        ->select($db->qn('#__j2store_productimages').'.'.$db->qn('additional_images_alt'))
		->join('LEFT OUTER', $db->qn('#__j2store_productimages').' ON '.
				$db->qn('#__j2store_products').'.'.$db->qn('j2store_product_id').' = '.
				$db->qn('#__j2store_productimages').'.'.$db->qn('product_id')
		);

		$query->select($db->qn('#__j2store_manufacturers').'.'.$db->qn('brand_desc_id'));
		//manufacturer
		$query
		->join('LEFT OUTER', $db->qn('#__j2store_manufacturers').' ON '.
				$db->qn('#__j2store_products').'.'.$db->qn('manufacturer_id').' = '.
				$db->qn('#__j2store_manufacturers.j2store_manufacturer_id')
		);

		//vendors
		$query
		->join('LEFT OUTER', $db->qn('#__j2store_vendors').' ON '.
				$db->qn('#__j2store_products').'.'.$db->qn('vendor_id').' = '.
				$db->qn('#__j2store_vendors.j2store_vendor_id')
		);

		//now join manufacturer address id with address table
		$query
		->select($db->qn('#__j2store_addresses').'.'.$db->qn('first_name').' AS manufacturer_first_name')
		->select($db->qn('#__j2store_addresses').'.'.$db->qn('last_name').' AS manufacturer_last_name')
		->select($db->qn('#__j2store_addresses').'.'.$db->qn('company').' AS manufacturer')
		->join('LEFT OUTER', $db->qn('#__j2store_addresses').' ON '.
				$db->qn('#__j2store_manufacturers').'.'.$db->qn('address_id').' = '. $db->qn('#__j2store_addresses').'.'.$db->qn('j2store_address_id')
				)
		;

		//now join vendor address id with address table
	/* 	$query
		->select('vendoraddress.first_name as vendor_first_name')
		->select('vendoraddress.last_name as vendor_last_name')
		->select('vendoraddress.company as vendor')
		->join('LEFT_OUTER', '#__j2store_addresses AS vendoraddress ON #__j2store_vendors.address_id = vendoraddress.j2store_address_id'); */


		$this->setQueryJoin($query);

		parent::__construct($table, $key, $db, $config);
	}

	public function check() {

		if(!isset($this->product_source) || empty($this->product_source)) {
			$this->setError(JText::_('J2STORE_PRODUCT_SOURCE_NOT_FOUND'));
			return false;
		}

		if(!isset($this->product_source_id) || empty($this->product_source_id)) {
			$this->setError(JText::_('J2STORE_PRODUCT_SOURCE_ID_NOT_FOUND'));
			return false;
		}
		return parent::check();
	}

	/**
	 * Overrides the load method and passes product source as keys
	 * @see F0FTable::load()
	 */
	public function load($keys=null, $reset=true) {

			$product_source_id = $this->input->getInt('product_source_id', '0');
			$product_source = $this->input->getString('product_source', null);
			if($product_source_id && !is_null($product_source)) {
				$keys = array('product_source_id'=>$product_source_id, 'product_source'=>$product_source);
			}
		return parent::load($keys, $reset);
	}

	public function store($updateNulls = false) {

		if(!isset($this->vendor_id) && empty($this->vendor_id)) {
			$this->vendor_id = F0FModel::getTmpInstance('Vendors', 'J2StoreModel')->enabled(1)->getItem()->j2store_vendor_id;
		}

		return parent::store($updateNulls);

	}

	/**
	 * The event which runs before deleting a record
	 *
	 * @param   integer  $oid  The PK value of the record to delete
	 *
	 * @return  boolean  True to allow the deletion
	 */
	protected function onBeforeDelete($oid)
	{

		$status = true;
		// Load the post record
		$item = clone $this;
		$item->load($oid);
		if($oid){
			$status = $this->deleteChildren($oid, $item->product_type);
		}
        J2Store::plugin()->event('AfterProductDeletion', array($item, $oid, $status));
		return $status;
	}


	public function deleteChildren($product_id,$product_type){
		$status = true;
		$productimages = F0FModel::getTmpInstance('Productimages','J2StoreModel')->product_id($product_id)->getList();
		$productoptions = F0FModel::getTmpInstance('Productoptions','J2StoreModel')->product_id($product_id)->getList();
			if(isset($productoptions) && !empty($productoptions)){
				if(!$this->getDeleteChildren('Productoption' , $productoptions)){
					$status = false;
				}
			}

		if(isset($productimages) && !empty($productimages)){
			if($status){
				if(!$this->getDeleteChildren('Productimage' , $productimages)){
					$status = false;
				}
			}
		}

		//if downloadable delete productfile
		if($product_type =='downloadable'){
			if($status){
				$productFiles = F0FModel::getTmpInstance('Productfiles','J2StoreModel')->product_id($product_id)->getList();
				if($productFiles && !empty($productFiles)){
					if(!$this->getDeleteChildren('Productfile',$productFiles)){
						$status = false;
					}
				}
			}
		}

		//to delete all variants
		$variant = F0FTable::getAnInstance('Variant' ,'J2StoreTable');
			$variants = F0FModel::getTmpInstance('Variants','J2StoreModel')->product_id($product_id)->getList();
			foreach($variants as $singleitem){
				if($variant->load($singleitem->j2store_variant_id)){
					if(!$variant->delete()){
						$status = false;
					}else{
						$status = true;
					}
				}
			}
		$product_filter = F0FTable::getAnInstance('ProductFilter' ,'J2StoreTable')->getClone();
		if(!$product_filter->deleteProductFilterList($product_id)){
			$status = false;
		}	
		return $status;
	}


	public function getDeleteChildren($tablename , $items){

		$status = true;
		foreach ($items as $single){
			$table = F0FTable::getAnInstance(ucfirst($tablename) ,'J2StoreTable');
			$key = $table->getKeyName();
			if(!$table->delete($single->$key)){
				$status = false;
			}
		}
		return $status;
	}


	public function get_product_by_source($product_source, $product_source_id) {

		if(!isset($product_source) || empty($product_source) || !isset($product_source_id) || !is_numeric($product_source_id)) return false;
		if(!isset($this->_product[$product_source][$product_source_id])) {
			if(!$this->load(array('product_source'=>$product_source, 'product_source_id'=>$product_source_id, 'enabled'=>1, 'visibility'=>1))) {
				return false;
			}
			$this->_product[$product_source][$product_source_id] = $this;
		}
		return $this->_product[$product_source][$product_source_id];
	}

	public function get_product_by_id($product_id) {

		if(!isset($product_id) || !is_numeric($product_id)) return false;

		if(!isset($this->_product[$product_id])) {
			if(!$this->load(array('j2store_product_id'=>$product_id, 'enabled'=>1, 'visibility'=>1))) {
				return false;
			}

			//sanity check
			if($this->j2store_product_id != $product_id) return false;

			if($this->enabled != 1 || $this->visibility != 1 )  return false;

			$this->_product[$product_id] = $this;
		}

		return $this->_product[$product_id];
	}

	/**
	 * Method to get full product block
	 * @return string Html of the product
	 */

	public function get_product_html($sublayout = null) {
		return $this->get_html($sublayout);
	}

	/**
	 * Loads product block
	 * @param string Sublayout lets to load an independent layout which can any of the single product item blocks like sku price
	 * @return string Html of the product and cart block
	 */

	public function get_html($sublayout = null) {
		if(!$this->is_valid_product()) return false;

		$app = JFactory::getApplication();
		$html = '';
		$html .= J2Store::plugin ()->eventWithHtml ( 'BeforeRenderingProductHtml' , array($this) );

		//ok. We have a product.
		$controller = F0FController::getTmpInstance('com_j2store', 'products', $this->get_view_config());
		$view = $controller->getView('product', 'html');
		$model = F0FModel::getTmpInstance('Products', 'J2StoreModel');
		$view->setModel($model, true);
		$model->setState('task', 'read');
		//now get the product
		$product = $this->get_product();

        $user = JFactory::getUser();
        //access
        $access_groups = $user->getAuthorisedViewLevels();
        
		if($this->is_visible($product) && ((isset($product->source->access) && !empty($product->source->access) && in_array($product->source->access,$access_groups)) || !isset($product->source->access))){
            J2StoreStrapper::addJS();
			J2StoreStrapper::addCSS();
			$params = J2Store::config();
            $session = JFactory::getSession();
            $is_admin_request = $session->get('is_admin_request',0,'j2store');
            if($is_admin_request){
                $params->set('isregister',0);
                $view->setLayout('adminitem');
            }else{
                $view->setLayout('item');
            }

			$taxModel = F0FModel::getTmpInstance('TaxProfiles', 'J2StoreModel');
			$view->assign('product', $product);
			$view->assign('params', $params);
			$view->assign('taxModel', $taxModel);
			if($sublayout) {
				$view->assign('sublayout', $sublayout);
			}

			J2Store::plugin ()->event ( 'ViewItemProduct' , array(&$product,&$view) );
			ob_start();
			$view->display();
			$html .= ob_get_contents();
			ob_end_clean();
			$html .= J2Store::plugin ()->eventWithHtml ( 'AfterRenderingProductHtml' , array($this) );
		}
		return $html;
	}

	public function get_product_cart_html() {
		if(!$this->is_valid_product()) return false;
		//ok. We have a product.
		$view = $this->get_view();
		$model = F0FModel::getTmpInstance('Products', 'J2StoreModel');
		$view->setModel($model, true);
		$model->setState('task', 'read');
		//now get the product
		$product = $this->get_product();
		if($this->is_visible($product)) {
			J2StoreStrapper::addJS();
			J2StoreStrapper::addCSS();
			$params = J2Store::config();

			$view->assign('singleton_product', $product);
			$view->assign('singleton_params', $params);

			$view->setLayout('cart');

			ob_start();
			$view->display();
			$html = ob_get_contents();
			ob_end_clean();
		}
		return $html;
	}

	public function get_product_images_html($type, $plugin_params=array()) {

		if(!$this->is_valid_product()) return false;

		$app = JFactory::getApplication();
		$html = '';
		$html .= J2Store::plugin ()->eventWithHtml ( 'BeforeRenderingProductImages' , array($this) );
		//now get the product
		$product = $this->get_product();
		if($this->is_visible($product)) {


			//ok. We have a product.
			$controller = F0FController::getTmpInstance('com_j2store', 'products', $this->get_view_config());
			$view = $controller->getView('product', 'html');


			$model = F0FModel::getTmpInstance('Products', 'J2StoreModel');
			$view->setModel($model, true);
			$model->setState('task', 'read');
			J2StoreStrapper::addJS();
			J2StoreStrapper::addCSS();
			$params = J2Store::config();
			$new_params = array();
			if($plugin_params instanceof JRegistry) {
				$new_params = $plugin_params->toArray();
			}
			if(is_array($new_params) && count($new_params)) {
				foreach($new_params as $key=>$value) {
					$params->set($key, $value);
				}
			}

			$params->set('show_thumbnail_image', 0);
			$params->set('show_main_image', 0);
			$params->set('show_additional_image', 0);

			if($type == 'thumbnail') {
				$params->set('show_thumbnail_image', 1);
			}

			if($type == 'main') {
				$params->set('show_main_image', 1);
			}

			if($type == 'mainadditional') {
				$params->set('show_main_image', 1);
				$params->set('show_additional_image', 1);
			}
			J2Store::plugin()->event('ItemView',array(&$product,&$params));
			$view->assign('product', $product);
			$view->assign('params', $params);
			$view->setLayout('item_images');
			ob_start();
			$view->display();
			$html .= ob_get_contents();
			ob_end_clean();
			J2Store::plugin()->event('BeforeDisplayImages', array(&$html, $view, 'com_j2store.products.view.default'));
			$html .= J2Store::plugin ()->eventWithHtml ( 'AfterRenderingProductImages' , array($this) );
			
		}
		return $html;

	}

	public function get_product_brand_html() {
		if (! $this->is_valid_product ()) return false;

		$product = $this->get_product ();
		$html = '';

		if (isset ( $product->manufacturer ) && ! empty ( $product->manufacturer )) {
			$html .= '<span class="manufacturer-brand-text">' . JText::_ ( 'J2STORE_PRODUCT_MANUFACTURER_NAME' ) . ' </span>';
			$html .= '<span class="manufacturer-brand">' . $product->manufacturer . '</span>';
		}
		return $html;
	}

	protected function get_product() {

		static $sets;

		if(!is_array($sets)) {
			$sets = array();
		}
		if(!isset($sets[$this->j2store_product_id])) {
			//first trigger the relevant catalog source plugins
			$app = JFactory::getApplication();
			J2Store::plugin()->importCatalogPlugins();
            $product_obj = $this;
			$app->triggerEvent('onJ2StoreAfterGetProduct', array(&$product_obj));

			$model = F0FModel::getTmpInstance('Products', 'J2StoreModel');
			$sets[$this->j2store_product_id] = $model->getProduct($this);
		}
		return $sets[$this->j2store_product_id];
	}

	public function is_valid_product() {
		if(!isset($this->j2store_product_id) || $this->j2store_product_id < 1) return false;
		return true;
	}

	public function is_visible($product) {
		if(!isset($product->visibility) || $product->visibility != 1) return false;
		return true;
	}

	protected function get_view_config() {
		$config = array();
		$config['input']['format'] = 'raw';
		return $config;
	}

	/**
	 * Returns Vendor company name
	 * @return string
	 */

	public function get_vendor() {
        $product_obj = $this;
		JFactory::getApplication()->triggerEvent('onJ2StoreGetVendor', array(&$product_obj));
		return $this->vendor;
	}

	/**
	 * Returns cross sell product ids
	 * @return array Array of product ids
	 */

	public function get_cross_sells() {
		return explode(',', $this->cross_sells);
	}

	/**
	 * Returns up sell product ids
	 * @return array
	 */
	public function get_upsells() {
		return explode(',', $this->up_sells);
	}

	public function get_product_upsells_html() {
		$html = '';
		if(empty($this->j2store_product_id)) return $html;

		$product_helper = J2Store::product();
		$upsells = $product_helper->getUpsells($this);

		//ok. We have a product.
		$view = $this->get_view();

		$model = F0FModel::getTmpInstance('Products', 'J2StoreModel');
		$view->setModel($model, true);
		$model->setState('task', 'read');
		J2StoreStrapper::addJS();
		J2StoreStrapper::addCSS();
		$params = J2Store::config();

		$view->assign('up_sells', $upsells);
		$view->assign('params', $params);
		$view->setLayout('item_upsells');
		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		$html .= J2Store::plugin()->eventWithHtml('AfterRenderingProductUpsells', array($this, $upsells));

		return $html;
	}

	public function get_product_cross_sells_html() {
		$html = '';
		if(empty($this->j2store_product_id)) return $html;

		//ok. We have a product.
		$view = $this->get_view();

		$product_helper = J2Store::product();
		$cross_sells = $product_helper->getCrossSells($this);

		$model = F0FModel::getTmpInstance('Products', 'J2StoreModel');
		$view->setModel($model, true);
		$model->setState('task', 'read');
		J2StoreStrapper::addJS();
		J2StoreStrapper::addCSS();
		$params = J2Store::config();

		$view->assign('cross_sells', $cross_sells);
		$view->assign('params', $params);
		$view->setLayout('item_crosssells');
		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		$html .= J2Store::plugin()->eventWithHtml('AfterRenderingProductCrossSells', array($this, $cross_sells));

		return $html;
	}

	public function get_product_price_html($type='price') {
		$html = '';
		if(empty($this->j2store_product_id)) return $html;

		//ok. We have a product.
		$view = $this->get_view();
		$product = $this->get_product();

		$model = F0FModel::getTmpInstance('Products', 'J2StoreModel');
		$view->setModel($model, true);
		$model->setState('task', 'read');
		J2StoreStrapper::addJS();
		J2StoreStrapper::addCSS();
		$params = J2Store::config();
		switch($type) {

			case 'price':
			default:
				$params->set('show_base_price', 1);
				$params->set('show_price_field', 1);
				break;

			case 'saleprice':
				$params->set('show_base_price', 0);
				$params->set('show_price_field', 1);
				break;

			case 'regularprice':
				$params->set('show_base_price', 1);
				$params->set('show_price_field', 0);
				break;
		}


		$view->assign('product', $product);
		$view->assign('params', $params);
		$view->setLayout('item_price');
		ob_start();
		$view->display();
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	public function get_view() {
		$controller = F0FController::getTmpInstance('com_j2store', 'products', $this->get_view_config());
		$view = $controller->getView('product', 'html');
		return $view;
	}


	/**
	 * Checks if a product is downloadable
	 *
	 * @return bool
	 */
	public function is_downloadable() {
		$status = $this->product_type == 'downloadable' ? true : false;
		J2Store::plugin ()->event ( 'IsDownloadableProduct', array($this, &$status) );
		return $status;
	}

	/**
	 * Check if downloadable product has a file attached.
	 *
	 * @param string $download_id file identifier
	 * @return bool Whether downloadable product has a file attached.
	 */
	public function has_file( $download_id = '' ) {
		return ( $this->is_downloadable() && $this->get_file( $download_id ) ) ? true : false;
	}


	/**
	 * Get a file by $download_id
	 *
	 * @param string $download_id file identifier
	 * @return array|false if not found
	 */
	public function get_file( $download_id = '' ) {

		if($this->is_valid_product() === false) return false;

		$files = $this->get_files();

		if ( '' === $download_id ) {
			$file = sizeof( $files ) ? current( $files ) : false;
		} elseif ( isset( $files[ $download_id ] ) ) {
			$file = $files[ $download_id ];
		} else {
			$file = false;
		}

		// allow overriding based on the particular file being requested
		J2Store::plugin()->event('ProductFile',  array($file, $this, $download_id));
		return $file;
	}

	/**
	 * Gets an array of downloadable files for this product.
	 *
	 * @return array
	 */
	public function get_files() {

		static $downloadable_files;
		if(!is_array($downloadable_files)) {
			$downloadable_files = array();
		}

		if (!isset($downloadable_files[$this->j2store_product_id])) {

			$downloadable_files[$this->j2store_product_id] = F0FModel::getTmpInstance('ProductFiles', 'J2StoreModel')->product_id($this->j2store_product_id)->getList();

			J2Store::plugin()->event('ProductFiles',  array(&$downloadable_files[$this->j2store_product_id], $this));
		}
		return $downloadable_files[$this->j2store_product_id];
	}

}