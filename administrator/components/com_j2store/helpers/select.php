<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * J2Store helper.
 */
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
class J2StoreHelperSelect {



	/**
	 * Method to get array
	 * @param unknown_type $view
	 * @param unknown_type $key
	 * @param unknown_type $value
	 * @param unknown_type $value1
	 * @return multitype:string NULL
	 */
	function getSelectArrayOptions($view,$key,$value,$value1=''){
		$items =F0FModel::getTmpInstance(ucfirst($view),'J2storeModel')->enabled(1)->getList();
		$result = array();
		$result[''] = JText::_('J2STORE_SELECT_OPTION');
		foreach($items as $item){
			$result[$item->$key] = $item->$value;
			if(isset($value1) && !empty($value1)){
				$result[$item->$key] = $item->$value . $item->$value1;
			}
		}
		return $result;
	}


	public static function productattributeoptionprefix( $selected, $name = 'filter_prefix', $attribs = array('class' => 'j2storeprefix', 'size' => '1'), $idtag = null, $allowAny = false, $title = 'Select Prefix' )
	{
		$list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_( $title )." -" );
		}

		$list[] = JHTML::_('select.option',  '+', "+" );
		$list[] = JHTML::_('select.option',  '-', "-" );

		return self::genericlist($list, $name, $attribs, 'value', 'text', $selected, $idtag );
	}



	protected static function genericlist($list, $name, $attribs, $selected, $idTag) {
		if (empty ( $attribs )) {
			$attribs = null;
		} else {
			$temp = '';
			foreach ( $attribs as $key => $value ) {
				$temp .= $key . ' = "' . $value . '"';
			}
			$attribs = $temp;
		}
		return JHTML::_ ( 'select.genericlist', $list, $name, $attribs, 'value', 'text', $selected, $idTag );
	}

	// get countries
	public static function getCountries() {
		$options = array ();
		$enabled = 1;
		$countries = F0FModel::getTmpInstance ( 'countries', 'J2StoreModel' )->enabled ( $enabled )->getList ();
		foreach ( $countries as $country ) {
			$options [$country->j2store_country_id] = JText::_($country->country_name);
		}
		return $options;
	}

	// get taxrates
	public static function getTaxRates() {
		$options = array ();
		$enabled = 1;
		$taxrates = F0FModel::getTmpInstance ( 'taxrates', 'J2StoreModel' )->enabled ( $enabled )->getList ();

		foreach ( $taxrates as $taxrate ) {
			$options [$taxrate->j2store_taxrate_id] = $taxrate->taxrate_name;
		}
		return $options;
	}

	// get languages
	public static function languages($selected = null, $id = 'language', $attribs = array()) {
		JLoader::import ( 'joomla.language.helper' );
		$languages = JLanguageHelper::getLanguages ( 'lang_code' );
		$options = array ();

		if (isset ( $attribs ['allow_empty'] )) {
			if ($attribs ['allow_empty']) {
				$options [] = JHTML::_ ( 'select.option', '', '- ' . JText::_ ( 'JALL_LANGUAGE' ) . ' -' );
			}
		}

		$options [] = JHTML::_ ( 'select.option', '*', JText::_ ( 'JALL_LANGUAGE' ) );
		if (! empty ( $languages ))
			foreach ( $languages as $key => $lang ) {
				$options [] = JHTML::_ ( 'select.option', $key, $lang->title );
			}

		return self::genericlist ( $options, $id, $attribs, $selected, $id );
	}

	// get orderstatus
	public static function OrderStatus($selected = null, $id = '', $attribs = array(), $default_option = null) {
		$orderstatus_options [] = JHTML::_ ( 'select.option', '', JText::_ ( 'JALL' ) );

		$orderlist = self::getOrderStatus ( $default_option, true );
		foreach ( $orderlist as $row ) {
			$orderstatus_options [] = JHTML::_ ( 'select.option', $row->j2store_orderstatus_id, $row->order_name );
		}
		return self::genericlist ( $orderstatus_options, $id, $attribs, $selected, $id );
	}

	/**
	 * Static method that return only the orderstatus.
	 */
	public static function getOrderStatus($default_option = null, $asObject = false) {
		$enabled = 1;
		$orderstatus = F0FModel::getTmpInstance ( 'orderstatuses', 'J2StoreModel' )->enabled ( $enabled )->getList ( true );
		return $orderstatus;
	}

	// get grouplist
	public static function GroupList($selected = null, $id = '', $attribs = array(), $default_option = null) {
		$group_options [] = JHTML::_ ( 'select.option', '', JText::_ ( 'JALL' ) );

		if (version_compare ( JVERSION, '3.0', 'lt' )) {
			require_once (JPATH_LIBRARIES . '/joomla/html/html/user.php');
		}
		$groupList = JHtmlUser::groups ();

		foreach ( $groupList as $row ) {
			$group_options [] = JHTML::_ ( 'select.option', $row->value, JText::_ ( $row->text ) );
		}

		return self::genericlist ( $group_options, $id, $attribs, $selected, $id );
	}

	// get paymentlist
	public static function PaymentList($selected = null, $id = '', $attribs = array(), $default_option = null) {
		$paymentmethod_options [] = JHTML::_ ( 'select.option', '', JText::_ ( 'JALL' ) );

		// ~ $paymentList = self::getPaymentList($default_option, true);
		// ~ foreach($paymentList as $row) {
		// ~ $paymentmethod_options[] = JHTML::_('select.option', $row->element, JText::_($row->element));
		// ~ }

		return self::genericlist ( $paymentmethod_options, $id, $attribs, $selected, $id );
	}

	public static function publish($name, $selected = '', $attribs = array())
	{
		$options = array();

		$options[] = JHTML::_('select.option', '1'  ,JText::_('JYES'));
		$options[] = JHTML::_('select.option', '0'  ,JText::_('JNO'));

		return self::genericlist($options, $name, $attribs, $selected, $name);
	}



	// get countries
	public static function getCurrencies() {
		$options = array ();
		$enabled = 1;
		$currencies = F0FModel::getTmpInstance ( 'currencies', 'J2StoreModel' )->enabled ( $enabled )->getList ();
		foreach ( $currencies as $currency ) {
			$options [$currency->j2store_currency_id] = $currency->currency_name;
		}
		return $options;
	}


	public static function ruleFormatType($name, $selected = '', $attribs = array())
	{
		$options = array();
		$options[] = JHTML::_('select.option', ''  ,JText::_('J2STORE_SELECT_OPTION'));
		$options[] = JHTML::_('select.option', 'product'  ,JText::_('J2STORE_RULE_PRODUCT'));
		$options[] = JHTML::_('select.option', 'discount'  ,JText::_('J2STORE_RULE_DISCOUNT'));
		return self::genericlist($options, $name, $attribs, $selected, $name);
	}




	public static function getParentOption($variant_id,$default_par_id_array,$same_option) {
		$model = F0FModel::getTmpInstance('Options','J2StoreModel');
		//get parent
		$pa_options= $model->getList();
		//generate parent filter list
		$parent_options = array();
		$parent_options[]=JText::_('J2STORE_SELECT_PARENT_OPTION');
		if(!empty($pa_options))
		{
			foreach($pa_options as $row) {
				// parent cannot be same option so check if same option and allow
				if($row->j2store_option_id != $same_option)
					$parent_options[$row->j2store_option_id]=$row->option_name;
			}
		}
		return $parent_options;
	}


	/**
	 * Generates shipping method type list
	 *
	 * @param string The value of the HTML name attribute
	 * @param string Additional HTML attributes for the <select> tag
	 * @param mixed The key that is selected
	 * @returns string HTML for the radio list
	 */
	public static function shippingtype( $selected, $name = 'filter_shipping_method_type', $attribs = array('class' => 'inputbox'), $idtag = null, $allowAny = false, $title = 'J2STORE_SELECT_SHIPPING_TYPE')
	{
		$list = array();
		if($allowAny) {
			$list[] =  self::option('', "- ".JText::_( $title )." -" );
		}
		require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/shipping.php');
		$items = J2StoreShipping::getTypes();
		foreach ($items as $item)
		{
			$list[] = JHTML::_('select.option', $item->id, $item->title );
		}
		$html = self::genericlist($list, $name, $attribs, $selected, $idtag );
		return $html;
	}

	/**
	 * Generates a selectlist for shipping methods
	 *
	 * @param unknown_type $selected
	 * @param unknown_type $name
	 * @param unknown_type $attribs
	 * @param unknown_type $idtag
	 * @return unknown_type
	 */
	public static function shippingmethod( $selected, $name = 'filter_shipping_method', $attribs = array('class' => 'inputbox'), $idtag = null )
	{
		$list = array();

		F0FModel::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/models' );
		//JModelLegacy::addIncludePath( JPATH_ADMINISTRATOR.'/components/com_j2store/models' );
		$model = F0FModel::getAnInstance('Shippingmethods', 'J2StoreModel' );

		$model->setState('filter_enabled', true);
		$items = $model->getList();
		foreach (@$items as $item)
		{
			$list[] =  self::option( $item->shipping_method_id, JText::_($item->shipping_method_name));
		}
		return JHTML::_('select.radiolist', $list, $name, $attribs, 'value', 'text', $selected, $idtag);
	}

	public static function taxclass($default, $name) {
		/* $db = JFactory::getDbo();
			$query = $db->getQuery(true);
		$query->select('j2store_taxprofile_id as value, taxprofile_name as text')->from('#__j2store_taxprofiles')
		->where('enabled=1');
		$db->setQuery($query);
		$array = $db->loadObjectList();
		$options[] = JHtml::_( 'select.option', 0, JText::_('J2STORE_SELECT_OPTION'));
		foreach( $array as $data) {
		$options[] = JHtml::_( 'select.option', $data->value, $data->text);
		}
		return	JHtml::_('select.genericlist', $options, $name, 'class="inputbox"', 'value', 'text', $default); */

		return J2Html::select()->clearState()
		->type('genericlist')
		->name($name)
		->value($default)
		->setPlaceHolders(
				array(''=>JText::_('J2STORE_SELECT_OPTION'))
		)
		->hasOne('Taxprofiles')
		->setRelations( array(
				'fields' => array (
						'key' => 'j2store_taxprofile_id',
						'name' => array('taxprofile_name')
				)
		)
		)->getHtml();

	}

	public static function geozones($default, $name) {
		return J2Html::select()->clearState()
		->type('genericlist')
		->name($name)
		->value($default)
		->setPlaceHolders(
				array(''=>JText::_('J2STORE_SELECT_OPTION'))
		)
		->hasOne('Geozones')
		->setRelations( array(
				'fields' => array (
						'key' => 'j2store_geozone_id',
						'name' => array('geozone_name')
				)
		)
		)->getHtml();

	}



	/**
	 * Returns an array of categories for the given extension.
	 *
	 * @param   string  $extension  The extension option.
	 * @param   array   $config     An array of configuration options. By default, only published and unpublished categories are returned.
	 *
	 * @return  array   Categories for the extension
	 *
	 * @since   1.6
	 */
	public static function getContentCategories()
	{		$config = array('filter.published' => array(0, 1));
			$extension ='com_content';
			$config = (array) $config;
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
			->select('a.id, a.title, a.level, a.parent_id')
			->from('#__categories AS a')
			->where('a.parent_id > 0');

			// Filter on extension.
			$query->where('extension = ' . $db->quote($extension));

			// Filter on the published state
			if (isset($config['filter.published']))
			{
				if (is_numeric($config['filter.published']))
				{
					$query->where('a.published = ' . $db->q((int) $config['filter.published']));
				}
				elseif (is_array($config['filter.published']))
				{
                    $config['filter.published'] = J2Store::platform()->toInteger($config['filter.published']);
					$query->where('a.published IN (' . implode(',', $config['filter.published']) . ')');
				}
			}

			$query->order('a.lft');

			$db->setQuery($query);
			$items = $db->loadObjectList();
			/* foreach($items as &$cat){
				$cat->title = str_replace(' ', '_',$cat->title);
			} */
			return $items;
	}


	public static function getManufacturers(){
		$items =  F0FModel::getTmpInstance('Manufacturers','J2StoreModel')->getItemList();
		$new_options  = array();
		$new_options[] = JText::_('J2STORE_ALL');
		foreach($items as $brand){
			$new_options[$brand->j2store_manufacturer_id] = $brand->company;
		}
		return $new_options;
	}
	
	
	public static function getOptionTypesList($name, $id, $item) {
		$groups = array ();
		
		$types = self::getOptionTypes ();
		foreach ( $types as $type_key => $typeitems ) {
			$groups [$type_key] = array ();
			$groups [$type_key] ['text'] = JText::_ ( 'J2STORE_OPTION_OPTGROUP_LABEL_' . strtoupper ( $type_key ) );
			$groups [$type_key] ['items'] = array ();
			foreach ( $typeitems as $type ) {
				$groups [$type_key] ['items'] [] = JHtml::_ ( 'select.option', $type, JText::_ ( 'J2STORE_' . strtoupper ( $type ) ) );
			}
		}
		
		$attr = array (
				'id' => $id,
				'list.select' => $item->type 
		);
		J2Store::plugin ()->event ( 'GetOptionTypesList', array (
				$name,
				$id,
				$item,
				$groups,
				$attr 
		) );
		return JHtml::_ ( 'select.groupedlist', $groups, $name, $attr );
	}
	
	public static function getOptionTypes() {
		$types = array ();
		$choose = array ();
		$choose [] = 'select';
		$choose [] = 'radio';
		if (J2Store::isPro ()) {
			$choose [] = 'checkbox';
		}
		
		$types ['choose'] = $choose;
		
		if (J2Store::isPro ()) {
			$types ['input'] = array (
					'text',
					'textarea',
					'file' 
			);
			$types ['date'] = array (
					'date',
					'time',
					'datetime' 
			);
		}
		J2Store::plugin ()->event ( 'GetOptionTypes', array (
				&$types 
		) );
		return $types;
	}
}

