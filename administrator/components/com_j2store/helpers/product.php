<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * J2Html class provides Form Inputs
 */

class J2Product extends JObject{

	protected $state;
	protected $item;
	public static $instance;

	protected $options;

	public $_includes_tax = false;
	public $_tax_info = '';

	public function __construct($properties=null) {

		if(!is_object($this->state)) {
			$this->state = new JObject();
		}
		$this->options = array();
		parent::__construct($properties);

	}

	public static function getInstance($properties=null) {

		if (!self::$instance)
		{
			self::$instance = new self($properties);
		}

		return self::$instance;
	}

	/**
	 * Magic getter; allows to use the name of model state keys as properties
	 *
	 * @param   string  $name  The name of the variable to get
	 *
	 * @return  mixed  The value of the variable
	 */
	public function __get($name)
	{
		return $this->getState($name);
	}

	/**
	 * Magic setter; allows to use the name of model state keys as properties
	 *
	 * @param   string  $name   The name of the variable
	 * @param   mixed   $value  The value to set the variable to
	 *
	 * @return  void
	 */
	public function __set($name, $value)
	{
		return $this->setState($name, $value);
	}

	/*
	 * Magic caller; allows to use the name of model state keys as methods to
	* set their values.
	*
	* @param   string  $name       The name of the state variable to set
	* @param   mixed   $arguments  The value to set the state variable to
	*
	* @return  J2Select  Reference to self
	*/
	public function __call($name, $arguments)
	{
		$arg1 = array_shift($arguments);
		$this->setState($name, $arg1);

		return $this;
	}


	/**
	 * Method to set model state variables
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set or null.
	 *
	 * @return  mixed  The previous value of the property or null if not set.
	 */
	public function setState($property, $value = null)
	{
		return $this->state->set($property, $value);
	}

	/**
	 * Method to set model state variables
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set or null.
	 *
	 * @return  mixed  The previous value of the property or null if not set.
	 */
	public function getState($property=null, $default=null)
	{
		return $property === null ? $this->state : $this->state->get($property, $default);
	}

	public function clearState()
	{
		$this->state = new JObject();
		return $this;
	}


	public function setId($product_id){
		$this->setState('product_id', $product_id);
		return $this;
	}

	public function getId(){
		return $this->getState('product_id');
	}

	public function getProduct() {
		return $this->loadProduct();
	}

	private function loadProduct() {
		static $sets;

		if ( !is_array( $sets) )
		{
			$sets = array( );
		}

		$app = JFactory::getApplication();
		$product_id = $this->getState('product_id');

		if(!isset($sets[$product_id])) {

			J2Store::plugin()->importCatalogPlugins();
			$product = F0FTable::getAnInstance('Product', 'J2StoreTable')->getClone();
			if($product->load($product_id) ) {
				$app->triggerEvent('onJ2StoreAfterGetProduct', array(&$product));
			} else {
				$product = F0FTable::getAnInstance('Product', 'J2StoreTable');
			}
			$sets[$product_id] = $product;
		}
		return $sets[$product_id];
	}

	public function exists() {
		$product = $this->getProduct();
		return ($product->enabled) ? true : false;
	}

	public function generateSKU($variant) {

		if(empty($variant->product_id)) return '';

		$product = $this->setId($variant->product_id)->getProduct();
		$sku = '';
		$test=preg_replace('#[^a-z0-9_-]#i','',$product->product_name);
		if(empty($test)){
			static $last_pid = null;
			if($last_pid===null){
				$db = JFactory::getDbo();
				$query = 'SELECT MAX(`j2store_product_id`) FROM #__j2store_products';
				$db->setQuery($query);
				$last_pid = (int)$db->loadResult();
			}
			$last_pid++;
			$sku = 'product_'.$last_pid;
		}else{
			$sku = preg_replace('#[^a-z0-9_-]#i','_',$product->product_name);
		}

		if(in_array($product->product_type,array('variable','advancedvariable'))) {
			if($variant->is_master == 0) {
				//append the variant ID as well. Just to be sure of a unique value
				$sku = $sku.'_'.$variant->j2store_variant_id;
			}else {
				//is a master variant of variable type. This does not need an SKU.
				$sku = '';
			}
		}
        J2Store::plugin()->event('CheckSku', array(&$sku));

        return $sku;
	}

	public function getPriceModifiers() {

		$modifiers = array();
		//default modifiers
		$modifiers = array('+' => '+' , '-' =>'-');
		J2Store::plugin()->event('GetPriceModifiers', array(&$modifiers));
		return $modifiers;
	}

	public function getPriceModifierHtml($name, $value='', $default='+') {
		if(empty($value)) $value = $default;
		$modifiers = $this->getPriceModifiers();
		$html = J2Html::select()->clearState()
		->type('genericlist')
		->name($name)
		->value($value)
		->setPlaceHolders($modifiers)
		->attribs(array('class'=>'input-small'))
		->getHtml();

		return $html;
	}



	public function getProductOptions($product) {
		static $osets;

		if ( !is_array( $osets) )
		{
			$osets = array( );
		}
		if ( !isset( $osets[$product->j2store_product_id])) {
			$product_option_data =array();
			//now prepare to get the product option values
			if(isset($product->product_options)) {
			foreach($product->product_options as $product_option) {

				//if multiple choices available, then we got to get them
				if ($product_option->type == 'select' || $product_option->type == 'radio' || $product_option->type == 'checkbox') {

					$product_option_value_data = array();

					$product_option_values = $this->getProductOptionValues($product_option->j2store_productoption_id, $product_option->product_id);

					foreach ($product_option_values as $product_option_value) {

						if(!isset($product_option_value->product_optionvalue_default)) {
							$product_option_value->product_optionvalue_default = '';
						}
						$product_option_value_data[] = array(
								'product_optionvalue_id' 		=> $product_option_value->j2store_product_optionvalue_id,
								'optionvalue_id'         		=> $product_option_value->optionvalue_id,
								'optionvalue_name'       		=> $product_option_value->optionvalue_name,
								'product_optionvalue_price' 	=> $product_option_value->product_optionvalue_price,
								'product_optionvalue_prefix'	=> $product_option_value->product_optionvalue_prefix,
								'product_optionvalue_weight' 	=> $product_option_value->product_optionvalue_weight,
								'product_optionvalue_sku' 	=> $product_option_value->product_optionvalue_sku,
								'product_optionvalue_weight_prefix'	=> $product_option_value->product_optionvalue_weight_prefix,
								'product_optionvalue_default'	=> $product_option_value->product_optionvalue_default,
								'optionvalue_image'	=> $product_option_value->optionvalue_image,
								'product_optionvalue_attribs' =>$product_option_value->product_optionvalue_attribs,
						);
					}

					$product_option_data[] = array(
							'productoption_id' => $product_option->j2store_productoption_id,
							'option_id'         => $product_option->option_id,
							'option_name'		=> $product_option->option_name,
							'type'              => $product_option->type,
							'optionvalue'       => $product_option_value_data,
							'required'          => $product_option->required,
							'option_params'          => $product_option->option_params,
							'is_variant' 					=> $product_option->is_variant,
					);

				} else {

					//if no option values are present, then
					$product_option_data[] = array(
							'productoption_id' => $product_option->j2store_productoption_id,
							'option_id'         => $product_option->option_id,
							'option_name'		=> $product_option->option_name,
							'type'              => $product_option->type,
							'optionvalue'       => '',
							'required'          => $product_option->required,
							'option_params'     => $product_option->option_params,
							'is_variant'         => $product_option->is_variant,
					);
				} //endif
			} //end product option foreach
			}
			$osets[$product->j2store_product_id] = $product_option_data;
		}

		return $osets[$product->j2store_product_id];
	}


	public function getProductOptionValues($productoption_id, $product_id) {

		static $osets;
		if ( !is_array( $osets) )
		{
			$osets = array( );
		}
		if(!isset($osets[$productoption_id][$product_id])) {
			$model = F0FModel::getTmpInstance('ProductOptionValues', 'J2StoreModel');
			$osets[$productoption_id][$product_id] = $model->productoption_id($productoption_id)
					->getList();

		}
		return $osets[$productoption_id][$product_id];

	}


	function getChildProductOptions($product_id,$parent_id=0,$parent_optionvalue_id=0) {

		//check $parent_optionvalue_id is set or not.set default value as 0
		if(isset($parent_optionvalue_id)){$parnt_optionvalue_id = $parent_optionvalue_id;}else{$parnt_optionvalue_id=0;}

		static $osets;
		if ( !is_array( $osets) )
		{
			$osets = array( );
		}
		if ( !isset( $osets[$product_id.''.$parent_id])) {

			//first get the product options
			$db = JFactory::getDbo();
			$product_option_data = array();

			$product_options = F0FModel::getTmpInstance('ProductOptions', 'J2StoreModel')
								->product_id($product_id)
								->parent_id($parent_id)
								->limit(0)
								->limitstart(0)
								->getList();

			//now prepare to get the product option values
			foreach($product_options as $product_option) {

				//if multiple choices available, then we got to get them
				if ($product_option->type == 'select' || $product_option->type == 'radio' || $product_option->type == 'checkbox') {

					$product_option_value_data = array();

					$product_option_values = $this->getChildProductOptionValues($product_option->j2store_productoption_id, $product_option->product_id,$parnt_optionvalue_id);

					foreach ($product_option_values as $product_option_value) {
						//print_r($product_option_value->optionvalue_image);
						if(!isset($product_option_value->product_optionvalue_default)) {
							$product_option_value->product_optionvalue_default = '';
						}
						$product_option_value_data[] = array(
								'product_optionvalue_id' 		=> $product_option_value->j2store_product_optionvalue_id,
								'optionvalue_id'         		=> $product_option_value->optionvalue_id,
								'optionvalue_name'       		=> $product_option_value->optionvalue_name,
								'optionvalue_image'       		=> $product_option_value->optionvalue_image,
								'product_optionvalue_price' 	=> $product_option_value->product_optionvalue_price,
								'product_optionvalue_prefix'	=> $product_option_value->product_optionvalue_prefix,
								'product_optionvalue_weight' 	=> $product_option_value->product_optionvalue_weight,
								'product_optionvalue_sku' 	=> $product_option_value->product_optionvalue_sku,
								'product_optionvalue_weight_prefix'	=> $product_option_value->product_optionvalue_weight_prefix,
								'product_optionvalue_default'	=> $product_option_value->product_optionvalue_default
						);

					}

					$product_option_data[] = array(
							'productoption_id' => $product_option->j2store_productoption_id,
							'option_id'         => $product_option->option_id,
							'option_name'		=> $product_option->option_name,
							'type'              => $product_option->type,
							'optionvalue'       => $product_option_value_data,
							'option_params'     => $product_option->option_params,
							'required'          => $product_option->required
					);

				} else {
					$product_option_values = array();
					$product_option_values = $this->getChildProductOptionValues($product_option->j2store_productoption_id, $product_option->product_id,$parnt_optionvalue_id);


					if(!empty($product_option_values)){
						//if no option values are present, then
						$product_option_data[] = array(
								'productoption_id' => $product_option->j2store_productoption_id,
								'option_id'         => $product_option->option_id,
								'option_name'		=> $product_option->option_name,
								'type'              => $product_option->type,
								'optionvalue'       => '',
								'option_params'     => $product_option->option_params,
								'required'          => $product_option->required
						);
					}


				} //endif
			} //end product option foreach
			$osets[$product_id.''.$parent_id] = $product_option_data;
		}
		return $osets[$product_id.''.$parent_id];
	}

	function getChildProductOptionValues($product_option_id, $product_id,$parnt_optionvalue_id) {
		//~ echo $product_option_id.'--'. $product_id.'--'.$parnt_optionvalue_id;
		static $ovsets;

		if ( !is_array( $ovsets) )
		{
			$ovsets = array( );
		}
		if ( !isset( $ovsets[$product_option_id][$product_id])) {
			//first get the product options
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('pov.*');
			$query->from('#__j2store_product_optionvalues AS pov');
			$query->where('pov.productoption_id='. $db->q($product_option_id));
			//$query->where('pov.parent_optionvalue='.$parnt_optionvalue_id);
			$query->where('( parent_optionvalue  = '. $db->q($parnt_optionvalue_id)
					.'OR parent_optionvalue LIKE CONCAT('. $db->q($parnt_optionvalue_id.',%') .')'
					.'OR parent_optionvalue LIKE CONCAT('. $db->q('%,'.$parnt_optionvalue_id.',%') .')'
					.'OR parent_optionvalue LIKE CONCAT('. $db->q('%,'.$parnt_optionvalue_id) .') )'
			) ;

			//join the optionvalues table to get the name
			$query->select('ov.j2store_optionvalue_id, ov.optionvalue_name,ov.optionvalue_image');
			$query->join('LEFT', '#__j2store_optionvalues AS ov ON pov.optionvalue_id=ov.j2store_optionvalue_id');
			$query->order('pov.ordering ASC');

			$db->setQuery($query);
			$ovsets[$product_option_id][$product_id] = $db->loadObjectList();
		}

		return $ovsets[$product_option_id][$product_id];
	}

	/**
	 * Method to retrieve default/selected product options
	 * @param array $options processed option data
	 * @return array default options
	 */

	public function getDefaultProductOptions($options) {
		$default = array();
		foreach($options as $option) {
			if($option['type'] == 'select' || $option['type'] == 'radio' || $option['type'] == 'checkbox' ) {
				foreach($option['optionvalue'] as $optionvalue) {
					if(isset($optionvalue['product_optionvalue_default']) && $optionvalue['product_optionvalue_default'] == 1) {
						$default[$option['productoption_id']] = $optionvalue['product_optionvalue_id'];
					}
				}
			}
		}
		J2Store::plugin()->event('DefaultProductOptions', array(&$default));
		return $default;
	}

	public function validateFlexivariants($variants,$options){
        $traits = array();
        foreach ( $options as $option)
        {

            if ( $option['optionvalue'])
            {
                $attributes = array( );
                foreach ( $option['optionvalue'] as $pkey=>$pov )
                {
                    $attributes[] = $pov['product_optionvalue_id'];
                }
                $traits[] = $attributes;
            }

        }

        $csvarray = F0FModel::getTmpInstance('Products', 'J2StoreModel')->getCombinations($traits);

        foreach ($variants as $variant){
            if(!in_array($variant->variant_name,$csvarray)){
                return false;
            }
        }
        return true;
    }

	public function validateVariants($variants, $options) {

		$traits = array();
		foreach ( $options as $option)
		{

			if ( $option['optionvalue'])
			{
				$attributes = array( );
				foreach ( $option['optionvalue'] as $pkey=>$pov )
				{
					$attributes[] = $pov['product_optionvalue_id'];
				}
				$traits[] = $attributes;
			}

		}

		$csvarray = F0FModel::getTmpInstance('Products', 'J2StoreModel')->getCombinations($traits);
		if(count($variants) == count($csvarray)) return true;
		return false;
	}


	/**
	 * Method to get a list of pricing calculators available.
	 * @return array A list of pricing calculators
	 */

	public function getPricingCalculators() {
		$calculators = array('standard'=>JText::_('COM_J2STORE_PRODUCT_PRICING_CALCULATOR_STANDARD'));
		//allow plugins to add more calculators.
		J2Store::plugin()->event('GetPricingCalculators', array(&$calculators));
		return (array) $calculators;
	}

	public function getPrice($variant, $quantity=1, $group_id='', $date='') {

		//let us take the basic price
		if(!$variant) return false;

		$pricing_calculator = isset($variant->pricing_calculator) ? $variant->pricing_calculator : 'standard';

		require_once JPATH_ADMINISTRATOR.'/components/com_j2store/library/calculators/calculator.php';

		$config = array();
		$config['variant'] = $variant;
		$config['quantity'] = $quantity;
		$config['group_id'] = $group_id;
		$config['date'] = $date;

		$calculator = new Calculator($pricing_calculator, $config);
		$pricing = $calculator->calculate();

		J2Store::plugin()->event('AfterGetPrice', array(&$pricing, $variant, $quantity, $group_id, $date));

		return $pricing;
	}



	public function getOptionPrice($options, $product_id) {
		$option_price = 0;
		$option_weight = 0;
		$option_data = array();
        $utility = J2Store::utilities();
		foreach ($options as $product_option_id => $option_value) {

			$product_option = $this->getCartProductOptions($product_option_id, $product_id);

			if ($product_option) {
				if ($product_option->type == 'select' || $product_option->type == 'radio') {

					//ok now get product option values
					$product_option_value = $this->getCartProductOptionValues($product_option_id, $option_value);

					if ($product_option_value) {

						//option price
						if (isset($product_option_value->product_optionvalue_prefix) && $product_option_value->product_optionvalue_prefix == '+') {
							$option_price += $product_option_value->product_optionvalue_price;
						} elseif (isset($product_option_value->product_optionvalue_prefix) && $product_option_value->product_optionvalue_prefix == '-') {
							$option_price -= $product_option_value->product_optionvalue_price;
						}

						//allow plugins to modify the option price. This allows usage of different operators
						J2Store::plugin()->event('GetOptionpriceItem', array($product_id, $product_option, $product_option_value, &$option_price));

						//options weight
						if (isset($product_option_value->product_optionvalue_weight_prefix) && $product_option_value->product_optionvalue_weight_prefix == '+') {
							$option_weight += $product_option_value->product_optionvalue_weight;
						} elseif (isset($product_option_value->product_optionvalue_weight_prefix) && $product_option_value->product_optionvalue_weight_prefix == '-') {
							$option_weight -= $product_option_value->product_optionvalue_weight;
						}


						$option_data[] = array(
								'product_option_id'       => $product_option_id,
								'product_optionvalue_id' => $option_value,
								'option_id'               => $product_option->option_id,
								'optionvalue_id'         => isset($product_option_value->optionvalue_id) ? $product_option_value->optionvalue_id: '',
								'name'                    => $product_option->option_name,
								'option_value'            => isset($product_option_value->optionvalue_name) ? $product_option_value->optionvalue_name : '',
								'type'                    => $product_option->type,
								'price'                   => isset($product_option_value->product_optionvalue_price) ? $product_option_value->product_optionvalue_price : '',
								'price_prefix'            => isset($product_option_value->product_optionvalue_prefix) ? $product_option_value->product_optionvalue_prefix :'',
								'weight'                   =>isset($product_option_value->product_optionvalue_weight) ? $product_option_value->product_optionvalue_weight :'',
								'option_sku'               => isset($product_option_value->product_optionvalue_sku) ? $product_option_value->product_optionvalue_sku :'',
								'weight_prefix'            => isset($product_option_value->product_optionvalue_weight_prefix) ? $product_option_value->product_optionvalue_weight_prefix : ''
						);
					}
				} elseif ($product_option->type == 'checkbox' && is_array($option_value)) {
					foreach ($option_value as $product_optionvalue_id) {
						$product_option_value = $this->getCartProductOptionValues($product_option->j2store_productoption_id, $product_optionvalue_id);
						if ($product_option_value) {

							//option price
							if ($product_option_value->product_optionvalue_prefix == '+') {
								$option_price += $product_option_value->product_optionvalue_price;
							} elseif ($product_option_value->product_optionvalue_prefix == '-') {
								$option_price -= $product_option_value->product_optionvalue_price;
							}

							//allow plugins to modify the option price. This allows usage of different operators
							J2Store::plugin()->event('GetOptionpriceItem', array($product_id, $product_option, $product_option_value, &$option_price));


							//option weight

							if ($product_option_value->product_optionvalue_weight_prefix == '+') {
								$option_weight += $product_option_value->product_optionvalue_weight;
							} elseif ($product_option_value->product_optionvalue_weight_prefix == '-') {
								$option_weight -= $product_option_value->product_optionvalue_weight;
							}

							$option_data[] = array(
									'product_option_id'       => $product_option_id,
									'product_optionvalue_id' => $product_optionvalue_id,
									'option_id'               => $product_option->option_id,
									'optionvalue_id'         => $product_option_value->optionvalue_id,
									'name'                    => $product_option->option_name,
									'option_value'            => $product_option_value->optionvalue_name,
									'type'                    => $product_option->type,
									'price'                   => $product_option_value->product_optionvalue_price,
									'price_prefix'            => $product_option_value->product_optionvalue_prefix,
									'weight'                   => $product_option_value->product_optionvalue_weight,
									'option_sku'                => $product_option_value->product_optionvalue_sku,
									'weight_prefix'            => $product_option_value->product_optionvalue_weight_prefix
							);
						}
					}
				} elseif ($product_option->type == 'text' || $product_option->type == 'textarea' || $product_option->type == 'date' || $product_option->type == 'datetime' || $product_option->type == 'time' || $product_option->type == 'file') {
					$option_data[] = array(
							'product_option_id'       => $product_option_id,
							'product_optionvalue_id' => '',
							'option_id'               => $product_option->option_id,
							'optionvalue_id'         => '',
							'name'                    => $product_option->option_name,
							'option_value'            => $utility->text_sanitize($option_value),
							'type'                    => $product_option->type,
							'price'                   => '',
							'price_prefix'            => '',
							'weight'                   => '',
							'weight_prefix'            => ''
					);
				}

				J2Store::plugin()->event('GetOptionPrice', array($product_id, $product_option_id, $option_value, $product_option, &$option_data, &$option_price, &$option_weight));
			}
		} // option loop

		$return = array();
		$return['option_price'] = $option_price;
		$return['option_weight'] = $option_weight;
		$return['option_data'] = $option_data;
	return $return;

	}

	public function displayPrice($price, $product, $params=array(),$context='') {

		$currency = J2Store::currency();
		if(empty($params)) {
			$params = J2Store::config();
		}
		$this->reset_tax_text();
		//if no tax profile id found, just return the price.
		if(!$product->taxprofile_id) {
			$text =  $currency->format($price);
		} else {

			switch($params->get('price_display_options', 1)) {

				case '1':
				default:
					//Price only
					$text = $currency->format($this->get_price_excluding_tax($price, $product->taxprofile_id));
					break;

				case '2':
					//product including tax
					$amount = $this->get_price_including_tax($price, $product->taxprofile_id);
					$text = $currency->format($amount);
					break;

			}
		}

		//allow plugins to modify the price display
		J2Store::plugin()->event('DisplayPrice', array(&$text, $product, $price,$context));
		return $text;
	}

	/**
	 * Method to display the quantity box in product and cart pages
	 * @param 	string 		$context 	context or scope where the function is called
	 * @param 	object 		$product 	product or cartitem object depending on the context
	 * @param 	JRegistry 	$params 	merged params
	 * @param 	array 		$options 	options or extra attribs for the element
	 * @return 	string 					html for the quantity box
	 * */
	public function displayQuantity($context, $product, $params=array(), $options = array() ) {

		if( empty($params) ) {
			$params = J2Store::config();
		}

        $params = J2Store::platform()->getRegistry($params);
		$class = 'input-mini form-control ';
		if ( isset($options['class']) && !empty($options['class']) ) {
			$class = $options['class'];	
		}

		if ($context == 'com_j2store.carts') {
			//this is a cart item. So its name is different.
			$name = 'quantities['.$product->cartitem_id.']' ;
			$value = (int) $product->orderitem_quantity ;
			$min = 0;
		} else {
			$name = 'product_qty' ;
			$value = (int) $product->quantity ;
			$min = $value;
		}

		$text = '';
			switch( $params->get('show_qty_field', 1 ) ) {
				case '0':
					// Hidden field Quantity
					$text = '<input type="hidden" name="'.$name.'" value="'. $value .'" />' ;
					break;

				case '1':
				default:
					// Quantity as Textbox
					$text .= '<div class="product-qty">';
					$text .= '<input type="number" name="'.$name.'" value="'. $value .'" class="'.$class.'" min="0" step="1" />' ;
					$text .= '</div>';
					break;
			}
		
		//Allow plugins to modify the quantity box display
		J2Store::plugin()->event('DisplayQuantity', array($context, &$text, $product, $params, $options ));
		return $text;
	}

	/**
	 * Returns price including tax. This can re-calculate the prices based on the customer's address
	 * @param float $price
	 * @param int $taxprofile_id
	 * @return float
	 */

	public function get_price_including_tax($price, $taxprofile_id) {
		$params = J2Store::config ();
		// check if it is taxable
		if ($taxprofile_id) {

			if ($params->get ( 'config_including_tax', 0 ) != 1) {
				// price does not include tax. This is kinda sanity check
				$result = F0FModel::getTmpInstance ( 'Taxprofiles', 'J2StoreModel' )->getTaxwithRates( $price, $taxprofile_id, $params->get ( 'config_including_tax', 0 ) );
				$price = $price + $result->taxtotal;
				$this->set_tax_text($price, $taxprofile_id, $result->taxes, 1);
			} else {
				// price includes tax. So first get the base price
				$item_taxrates = F0FModel::getTmpInstance ( 'Taxprofiles', 'J2StoreModel' )->getTaxwithRates( $price, $taxprofile_id, 1 );
				$shop_taxrates = F0FModel::getTmpInstance ( 'Taxprofiles', 'J2StoreModel' )->getBaseTaxRates ( $price, $taxprofile_id, 1 );

				//price includes tax
				$this->set_tax_text($price, $taxprofile_id, $item_taxrates->taxes, 1);

				$customer = F0FTable::getAnInstance ( 'Customer', 'J2StoreTable' );
				if ($customer->is_vat_exempt ()) {
					$base_tax_amount = $shop_taxrates->taxtotal;
					$price = ($price - $base_tax_amount);

					//vat exempt. so price excludes tax
					$this->set_tax_text($price, $taxprofile_id, array(), 0);

				} elseif ($item_taxrates->taxtotal !== $shop_taxrates->taxtotal) {

					$base_tax_amount = $shop_taxrates->taxtotal;
					$modded_tax = F0FModel::getTmpInstance ( 'Taxprofiles', 'J2StoreModel' )->getTaxwithRates( ($price - $shop_taxrates->taxtotal), $taxprofile_id, 0 );
					$price = ($price - $base_tax_amount) + $modded_tax->taxtotal;

					//price includes tax. but different. May or may not have tax
					$this->set_tax_text($price, $taxprofile_id, $modded_tax->taxes, 1);
				}
			}
		}
		return $price;
	}

	public function get_price_excluding_tax($price, $taxprofile_id) {
		$params = J2Store::config ();
		$rates = array();
		if ($taxprofile_id && $params->get ( 'config_including_tax', 0 ) == 1) {
			//if product is taxable and only when prices include tax, we need to do this calculation
			//get the shops base tax
			$shop_taxrates = F0FModel::getTmpInstance ( 'Taxprofiles', 'J2StoreModel' )->getBaseTaxRates ( $price, $taxprofile_id, 1 );
			$rates = $shop_taxrates->taxes;
			$price      = ($price - $shop_taxrates->taxtotal);
		}elseif($taxprofile_id && $params->get ( 'config_including_tax', 0 ) != 1) {
			//price does not include tax. Attempt to determine the tax rates
			$taxrates = F0FModel::getTmpInstance ( 'Taxprofiles', 'J2StoreModel' )->getTaxwithRates( $price, $taxprofile_id, 0 );
			$rates = $taxrates->taxes;
		}
		//price always excludes tax
		$this->set_tax_text($price, $taxprofile_id, $rates, 0);
		return $price;
	}

	public function set_tax_text($price, $taxprofile_id, $rates = array(), $includes_tax = 0) {
		$text = '';
		if (! $includes_tax) {
			// does not include tax. But check the rates. If they are present, we can display excl. tax with percentage.
			$total = 0;
			if(count($rates)) {
				foreach ( $rates as $rate ) {
					$total += floatval ( $rate ['rate'] );
				}
			}
			if ($total > 0) {
				$text = JText::sprintf( 'J2STORE_PRICE_EXCLUDING_TAX_WITH_PERCENTAGE', round ( $total, 2 ) . '%' );
			}else {
				$text = JText::_ ( 'J2STORE_PRICE_EXCLUDING_TAX' );
			}

		} else {

			// includes tax. Get the total tax percentage
			$total = 0;
			foreach ( $rates as $rate ) {
				$total += floatval ( $rate ['rate'] );
			}
			if ($total > 0) {
				$text = JText::sprintf ( 'J2STORE_PRICE_INCLUDING_TAX', round ( $total, 2 ) . '%' );
			} else {
				$text = JText::_ ( 'J2STORE_PRICE_EXCLUDING_TAX' );
			}
		}
		J2Store::plugin()->event('ProductTaxText', array($price, $taxprofile_id, $rates, $includes_tax, &$text));
		$this->_tax_info = $text;
	}

	public function get_tax_text() {
		return $this->_tax_info;
	}

	public function reset_tax_text() {
		$this->_tax_info = '';
	}

	public function displayStock($variant, $params) {

		$text = '';
		switch($params->get('stock_display_format', 'always_show')) {

			case 'always_show':
			default:
				//Price only
				if($variant->quantity > 0) {
					$text = JText::sprintf('J2STORE_IN_STOCK_WITH_QUANTITY', $variant->quantity);
				}else {
					$text = JText::_('COM_J2STORE_PRODUCT_IN_STOCK');
				}

				if ( $this->backorders_allowed($variant) && $this->backorders_require_notification($variant) && $variant->quantity < 1) {
					$text = JText::_('J2STORE_BACKORDER_NOTIFICATION');
				}

				break;

			case 'low_stock':
				//using the notify quantity for this
				if($variant->quantity > 0 && $variant->quantity <= $variant->notify_qty) {
					$text = JText::sprintf('J2STORE_LOW_STOCK_WITH_QUANTITY', $variant->quantity);
				} else {
					$text = '';
				}

				if ( $this->backorders_allowed($variant) && $this->backorders_require_notification($variant) && $variant->quantity < 1 ) {
					$text = JText::_('J2STORE_BACKORDER_NOTIFICATION');
				}

			break;

			case 'no_display':
					$text = '';
				break;
		}

		return $text;
	}

	public function managing_stock($variant) {
		$config = J2Store::config();
		return (!$config->get('enable_inventory', 0) || $variant->manage_stock != 1 || J2Store::isPro() == 0) ? false : true;
	}

	public function backorders_require_notification($variant) {
		return $this->managing_stock($variant) && $variant->allow_backorder == 2 ? true : false;
	}

	public function backorders_allowed($variant) {
		return (isset($variant->allow_backorder) && $variant->allow_backorder >= 1) ? true : false;
	}

	public function check_stock_status($variant, $quantity) {
		$stock_status = true;
		if($this->managing_stock($variant) && $this->backorders_allowed($variant) === false) {
			//inventory is enabled for this product. So validate stock
			$stock_status = $this->validateStock($variant, $quantity);

		}

		return $stock_status;
	}

	public function validateStock($variant, $qty=1) {

		$status = true;

		//if stock is less that or equval to 0

		if($variant->quantity <= 0) {
			$status = false;
		}

		//if purchase qty is greater than available stock;
		if($qty > $variant->quantity) {
			$status = false;
		}

		// if availability is out of stock
		if (! $variant->availability) {
			$status = false;
		}
		J2Store::plugin ()->event ( 'GetValidateStockQuantity',array(&$status,$variant,$qty) );

		return $status;
	}

	public function get_stock_quantity($product_quantity_table){
		$qty = $product_quantity_table->quantity;
		J2Store::plugin ()->event ( 'GetStockQuantity',array(&$qty,$product_quantity_table) );
		return $qty;
	}

	public function getQuantityRestriction(&$variant) {
		$store = J2Store::storeProfile ();

		if(isset($variant->use_store_config_min_sale_qty) && $variant->use_store_config_min_sale_qty > 0) {
			$variant->min_sale_qty = (float) $store->get('store_min_sale_qty');
		}

		if(isset($variant->use_store_config_max_sale_qty) && $variant->use_store_config_max_sale_qty > 0) {
			$variant->max_sale_qty = (float) $store->get('store_max_sale_qty');
		}

		if(isset($variant->use_store_config_notify_qty) && $variant->use_store_config_notify_qty > 0) {
			$variant->notify_qty = (float) $store->get('store_notify_qty');
		}

	}

	public function validateQuantityRestriction($variant, $cart_total_qty, $addto_qty=0) {

		$error = '';
		if ($variant->quantity_restriction && J2Store::isPro())
		{
			$quantity_total = $cart_total_qty+$addto_qty;
			$min = $variant->min_sale_qty;
			$max = $variant->max_sale_qty;

			if( $max && $max > 0 )
				{
					if ($quantity_total > $max )
					{
						$error = JText::sprintf('J2STORE_MAX_QUANTITY_FOR_PRODUCT', floatval($max), J2Store::utilities()->stock_qty($cart_total_qty));
					}
			}

			if( $min && $min > 0 )
			{
				if ($quantity_total < $min )
				{
					$error = JText::sprintf('J2STORE_MIN_QUANTITY_FOR_PRODUCT', floatval($min));
				}
			}

		}

		return $error;
	}

	public function getTotalCartQuantity($variant_id) {

		if(!isset($variant_id) || empty($variant_id) || $variant_id < 1) return 0;

		$cart_model = F0FModel::getTmpInstance('Carts', 'J2StoreModel');
		$cart_model->getCart();
		$cart_id = $cart_model->getCartId();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)->select('SUM(product_qty) as total_cart_qty')
		->from('#__j2store_cartitems')
		->where('variant_id='.$db->q($variant_id));

		if(!empty($cart_id)) {
			$query->where('cart_id ='.$db->q($cart_id));
		}

		$db->setQuery($query);
		return $db->loadResult();

	}

	public function getAddtocartAction(&$product) {
		$product->cart_form_action = J2Store::platform()->getCartUrl(array('task' => 'addItem'));//JRoute::_('index.php?option=com_j2store&view=carts&task=addItem');
		J2Store::plugin()->event('ProductCartAction', array($product));
	}

	public function getCheckoutLink(&$product) {
		$product->checkout_link = J2Store::platform()->getCartUrl();//JRoute::_('index.php?option=com_j2store&view=carts');
		J2Store::plugin()->event('ProductCheckoutLink', array($product));
	}

	public function getProductLink(&$product) {
		$product->product_link = J2Store::platform()->getProductUrl(array('task' => 'view','id' => $product->j2store_product_id),false);
		J2Store::plugin()->event('ProductLink', array($product));
	}


	public function getDefaultVariant($variants) {
		$default_variant = array();
		foreach ($variants as $variant) {
			if(empty($default_variant)){
				$default_variant = $variant;
			}
			if(isset($variant->isdefault_variant) && $variant->isdefault_variant == 1) {
				$default_variant = $variant;
				break;
			}
		}
		return $default_variant;
	}

	function getVariantByOptions($product_options, $product_id) {

		$db = JFactory::getDbo();
		$optionvalues = array();
		foreach($product_options as $productoption_id => $optionvalue) {
			$optionvalues[] = intval($optionvalue);
		}
		sort($optionvalues);
		$values = implode(',', $optionvalues);

		$query = $db->getQuery(true)->select('#__j2store_product_variant_optionvalues.variant_id')->from('#__j2store_product_variant_optionvalues')
		->where('product_optionvalue_ids='.$db->q($values));
		$db->setQuery($query);
		/*$row = $db->loadObject();
		//load the variant
		if(isset($row->variant_id) && $row->variant_id) {
			$variant = F0FModel::getTmpInstance('Variants', 'J2StoreModel')->getItem($row->variant_id);
		} else {
			$variant = false;
		}*/
        $rows = $db->loadObjectList();
        if(!empty($rows)){
            foreach ($rows as $row){
                $variant = F0FModel::getTmpInstance('Variants', 'J2StoreModel')->getItem($row->variant_id);
                if($row->variant_id > 0 && ($row->variant_id == $variant->j2store_variant_id)){
                    break;
                }
            }
        }else{
            $variant = false;
        }
		return $variant;
	}

	public function getVariantNamesByCSV($csv) {
		$productoptionvalues = explode(',' ,$csv);
		$names = array();
		foreach($productoptionvalues as $product_optionvalue_id) {
            $optionvalue_name = $this->getOptionvalueName($product_optionvalue_id);
            if(empty($optionvalue_name)){
                $optionvalue_name = JText::_('J2STORE_ALL_OPTIONVALUE');
            }
			$names[] = $optionvalue_name;
		}
		return implode(',', $names);
	}

	public function getOptionvalueName($product_optionvalue_id) {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)->select('#__j2store_optionvalues.optionvalue_name')
									->from('#__j2store_product_optionvalues')
									->join('INNER', '#__j2store_optionvalues ON #__j2store_product_optionvalues.optionvalue_id = #__j2store_optionvalues.j2store_optionvalue_id')
									->where('#__j2store_product_optionvalues.j2store_product_optionvalue_id='.$db->q($product_optionvalue_id));
		$db->setQuery($query);
		return $db->loadResult();
	}

	public function getCartProductOptions($product_option_id, $product_id) {
		static $posets;

		if ( !is_array( $posets) )
		{
			$posets = array( );
		}
		if ( !isset( $posets[$product_option_id][$product_id])) {
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('po.*');
			$query->from('#__j2store_product_options AS po');
			$query->where('po.j2store_productoption_id='.$db->q($product_option_id));
			$query->where('po.product_id='.$db->q($product_id));

			//join the options table to get the name
			$query->select('o.option_name, o.type');
			$query->join('LEFT', '#__j2store_options AS o ON po.option_id=o.j2store_option_id');
			$query->order('o.ordering ASC');
			$db->setQuery($query);

			$product_option = $db->loadObject();
			$posets[$product_option_id][$product_id] = $product_option;
		}
		return $posets[$product_option_id][$product_id];
	}

	public function getCartProductOptionValues($product_option_id, $option_value ) {

		static $ovsets;

		if ( !is_array( $ovsets) )
		{
			$ovsets = array( );
		}
		if(empty($option_value)) return $ovsets;
        //sanity check
        if(is_array($option_value)){
            $option_value = J2Store::platform()->toInteger($option_value);
            $option_value = implode(',',$option_value);
        }else {
            $option_value = intval($option_value);
        }

        if ( !isset( $ovsets[$product_option_id][$option_value])) {

			//first get the product options
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('pov.*');
			$query->from('#__j2store_product_optionvalues AS pov');
			$query->where('pov.j2store_product_optionvalue_id='.$db->q($option_value));
			$query->where('pov.productoption_id='.$db->q($product_option_id));

			//join the optionvalues table to get the name
			$query->select('ov.j2store_optionvalue_id, ov.optionvalue_name');
			$query->join('LEFT', '#__j2store_optionvalues AS ov ON pov.optionvalue_id=ov.j2store_optionvalue_id');

			$db->setQuery($query);
			$ovsets[$product_option_id] [$option_value] = $db->loadObject ();
		}
		return $ovsets [$product_option_id] [$option_value];
	}

	/**
	 *
	 * @param object $source_product Product table Object
	 * @return array An array of products or an empty array if no results found
	 */

	public function getUpsells($source_product) {
		$products = array ();
		$up_sells = array ();

		$upsell_csv = $source_product->up_sells;

		if (! empty ( $upsell_csv )) {

			$item_up_sells = explode ( ',', $upsell_csv );
			if (count ( $item_up_sells )) {
				$up_sells = array_merge ( $item_up_sells, $up_sells );
			}

			if (count ( $up_sells )) {

				foreach ( $up_sells as $upsell ) {

					$upsell_product = $this->setId ( $upsell )->getProduct ();
					if(empty($upsell_product->product_name) || ($upsell_product->visibility == 0) || $upsell_product->enabled != 1) continue;
					F0FModel::getTmpInstance ( 'Products', 'J2StoreModel' )->runMyBehaviorFlag ( true )->getProduct ( $upsell_product );

					if ($upsell_product->variant->availability || J2Store::product ()->backorders_allowed ( $upsell_product->variant )) {
						$show = true;
					} else {
						$show = false;
					}

					if ($upsell_product->product_type == 'variable') {
						$show = true;
					}

                    $user = JFactory::getUser();
                    //access
                    $access_groups = $user->getAuthorisedViewLevels();
                    if(($show &&  isset($upsell_product->source->access) && !empty($upsell_product->source->access) && in_array($upsell_product->source->access,$access_groups)) || !isset($upsell_product->source->access)) {
                        $show = true;
                    }else{
                        $show = false;
                    }
                    J2Store::plugin()->event('AfterProcessUpSellItem',array($upsell_product,&$show));
					// Dont show if product not available. No use in showing a related product that is not available!
					if ($show == false)
						continue;

					$products [] = $upsell_product;
				}
			}
		}
		// allow plugins to modify the results
		J2Store::plugin ()->event ( 'AfterGetUpsells', array (
				&$products,
				$source_product
		) );

		return $products;
	}

	/**
	 * Method to get Cross sell products
	 * @param object $source_product Source Product Table Object
	 * @return array An array of products or an empty array if no results found.
	 */

	public function getCrossSells($source_product) {
		$cross_sell_csv = $source_product->cross_sells;
		$products = array ();
		$cross_sells = array ();

		if (! empty ( $cross_sell_csv )) {

			$item_cross_sells = explode ( ',', $cross_sell_csv );
			if (count ( $item_cross_sells )) {
				$cross_sells = array_merge ( $item_cross_sells, $cross_sells );
			}

			if (count ( $cross_sells )) {

				foreach ( $cross_sells as $cross_sell ) {

					$cross_sell_product = $this->setId ( $cross_sell )->getProduct ();					
					if(empty($cross_sell_product->product_name) || $cross_sell_product->visibility == 0 || $cross_sell_product->enabled != 1) continue;
					F0FModel::getTmpInstance ( 'Products', 'J2StoreModel' )->runMyBehaviorFlag ( true )->getProduct ( $cross_sell_product );

					if ($cross_sell_product->variant->availability || J2Store::product ()->backorders_allowed ( $cross_sell_product->variant )) {
						$show = true;
					} else {
						$show = false;
					}

					if ($cross_sell_product->product_type == 'variable') {
						$show = true;
					}
                    $user = JFactory::getUser();
                    //access
                    $access_groups = $user->getAuthorisedViewLevels();
                    if(($show &&  isset($cross_sell_product->source->access) && !empty($cross_sell_product->source->access) && in_array($cross_sell_product->source->access,$access_groups)) || !isset($cross_sell_product->source->access)) {
                        $show = true;
                    }else{
                        $show = false;
                    }
                    J2Store::plugin()->event('AfterProcessCrossSellItem',array($cross_sell_product,&$show));
					// Dont show if product not available. No use in showing a related product that is not available!
					if ($show == false)
						continue;

					$products [] = $cross_sell_product;
				}
			}
		}
		// allow plugins to modify the results
		J2Store::plugin ()->event ( 'AfterGetCrossSells', array (
				&$products,
				$source_product
		) );
		return $products;
	}

	function getRelatedProducts($items){

		if(is_string($items) && !empty($items)){
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('v.sku,v.upc,#__j2store_products.*')->from('#__j2store_products');
			$query->join ( 'LEFT', '#__j2store_variants AS v ON v.product_id=#__j2store_products.j2store_product_id' );
			$query->where('v.is_master=1 AND #__j2store_products.j2store_product_id IN('.$items.')');
            $query->group('#__j2store_products.j2store_product_id');
			$model = F0FModel::getTmpInstance('Products','J2StoreModel');
			J2Store::plugin()->importCatalogPlugins();
			JFactory::getApplication()->triggerEvent('onJ2StoreAfterProductListQuery',array(&$query ,&$model));
			$db->setQuery($query);
			return $db->loadObjectList();
		}
		return new stdClass();
	}

	public function displayImage($product,$product_data){
		$html = "";
		if(!isset($product_data['type']) || !isset($product_data['params'])){
			return $html;
		}
		//$event = 'Display'.$product_data['type'].'Image';
		return J2Store::plugin()->eventWithHtml('DisplayProductImage',array($product,$product_data['params'],$product_data));
	}

	public function validateVariableProduct($product){
		if(!isset( $product->variant )  || !isset( $product->product_type )){
		    if($product->product_type == 'flexivariable'){
                return true;
            }
			return false;
		}
		$show = false;
		if($product->variant->availability || $this->backorders_allowed($product->variant)) {
			$show = true;
		}

		if(in_array ( $product->product_type, array('variable','advancedvariable','flexivariable', 'variablesubscriptionproduct') )) {
			if(isset( $product->all_sold_out ) && $product->all_sold_out){
				$show = false;
			}else{
				$show = true;
			}
		}
		return $show;
	}

	public function is_product_type_allowed($product_type,$allowed_product_types,$context){
		if(empty( $allowed_product_types ) || empty( $product_type ) ){
			return false;
		}
		if(!is_array ( $allowed_product_types )){
			$allowed_product_types = (array)$allowed_product_types;
		}
		$status = false;
		if(in_array ( $product_type, $allowed_product_types )){
			$status = true;
		}

		J2Store::plugin ()->event ( 'IsProductTypeAllowed',array($product_type,$allowed_product_types,$context,&$status) );
		return $status;

	}

	public function getJ2StoreBaseUrl() {

		return 'index.php?option=com_j2store';
	}

	public function canShowCart($params){
        $isregister = $params->get('isregister', 0);

        $allow_display = true;
        if($isregister && !JFactory::getUser()->id) {
            $allow_display = false;
        }
        $catelog = J2Store::config ()->get('catalog_mode',0);
        $status = false;
        if( $catelog == 0 && $allow_display ){
            $status = true;
        }
        return $status;
    }
    public function canShowprice($params){
        $show_product_price = $params->get('show_product_price_for_register_user', 0);
        $user_id = JFactory::getUser()->id;
        $status = true;
        if( $show_product_price && empty($user_id)){
            $status = false;
        }
        return $status;
    }
    public function canShowSku($params){
        $show_product_sku = $params->get('show_product_sku_for_register_user', 0);
        $user_id = JFactory::getUser()->id;
        $status = true;
        if( $show_product_sku && empty($user_id)){
            $status = false;
        }
        return $status;
    }

    function getVariableProductTypes(){
        $default = array('variable','advancedvariable','flexivariable', 'variablesubscriptionproduct');
        J2Store::plugin()->event('VariableProductTypes',array(&$default));
        return $default;
    }

}