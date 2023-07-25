<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;

class StandardCalculator extends JObject {
	
	
	public function __construct($config=array()) {
		parent::__construct($config);
	}
	
	public function calculate() {
		
		$variant = $this->get('variant');

		
		$pricing = new JObject();
		
		//set the base price
		$pricing->base_price = $variant->price;
		$pricing->price = $variant->price;
		$pricing->calculator = 'standard';
		
		//see if we have advanced pricing for this product / variant
		
		$model = F0FModel::getTmpInstance('ProductPrices', 'J2StoreModel');
		$standard_calculator = $this;
		J2Store::plugin()->event('BeforeGetPrice', array(&$pricing, &$model,&$standard_calculator));

		$quantity = $this->get('quantity');
		$date = $this->get('date');
		$group_id = $this->get('group_id');

		$model->setState( 'variant_id', $variant->j2store_variant_id );
		
		//where quantity_from < $quantity
		$model->setState( 'filter_quantity', $quantity);
		
		$tz = JFactory::getConfig()->get('offset');
		// does date even matter?
		$nullDate = JFactory::getDBO( )->getNullDate( );
		if ( empty( $date ) || $date == $nullDate )
		{
			$date = JFactory::getDate('now')->toSql(true);//format('Y-m-d');
		}

		//where date_from <= $date
		//where date_to >= $date OR date_to == nullDate
		
		
		$model->setState( 'filter_date', $date );
		
		// does group_id?
		$user = JFactory::getUser();
		if(empty($group_id)) $group_id = implode(',', JAccess::getGroupsByUser($user->id));
		//if(empty($group_id)) $group_id = implode(',', JAccess::getAuthorisedViewLevels($user->id));
		$model->setState( 'group_id', $group_id );
		
		// set the ordering so the most discounted item is at the top of the list
		$model->setState( 'orderby', 'quantity_from' );
		$model->setState( 'direction', 'DESC' );
		
		try {
			$price = $model->getItem( );
			$pricing->data = $price;
		}catch (Exception $e) {
			$price = new stdClass();
		}
		if(isset($price->price)) {
			$pricing->special_price = $price->price;
			//this is going to be the sale price
			$pricing->price = $price->price;
		
			$pricing->is_discount_pricing_available = ($pricing->base_price > $pricing->price) ? true: false;
		
		}
		return $pricing;
	}
	
}