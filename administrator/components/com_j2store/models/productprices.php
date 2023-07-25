<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;

class J2StoreModelProductPrices extends F0FModel {

	private function getFilterValues()
	{

		return (object)array(
				'date_from'		=> $this->getState('date_from',null,'string'),
				'date_to'			=> $this->getState('date_to',null,'string'),
				'filter_date'		=> $this->getState('filter_date',null,'string'),
				'quantity_from'		=> $this->getState('quantity_from',null,'int'),
				'quantity_to'		=> $this->getState('quantity_to',null,'int'),
				'filter_quantity'		=> $this->getState('filter_quantity',null,'int'),
				'group_id'		=> $this->getState('group_id',null,'string'),
				'variant_id'		=> $this->getState('variant_id',null,'int'),
				'orderby'		=> $this->getState('orderby',null,'string'),
				'direction'		=> $this->getState('direction',null,'string')
				);
	}

	public function buildQuery($overrideLimits = false) {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)->select('#__j2store_product_prices.*')
		->from('#__j2store_product_prices');

		$this->_buildQueryWhere($query);
		$this->_buildQueryOrder($query);
        $product_price_obj = $this;
		J2Store::plugin()->event('ProductPricesAfterBuildQuery', array(&$query, &$product_price_obj));
		return $query;
	}

    protected function _buildQueryWhere($query)
    {
        $db = $this->getDbo();
        $state = $this->getFilterValues();
        JLoader::import('joomla.utilities.date');

        $from = trim($state->filter_date);
        if (strlen($from))
        {
            $nullDate	= JFactory::getDbo()->getNullDate();
            $query->where("(#__j2store_product_prices.date_from <= ".$db->q($from)." OR #__j2store_product_prices.date_from = ".$db->q($nullDate)." OR #__j2store_product_prices.date_from IS NULL)");
            $query->where("(#__j2store_product_prices.date_to >= ".$db->q($from)." OR #__j2store_product_prices.date_to = ".$db->q($nullDate)." OR #__j2store_product_prices.date_to IS NULL )");
        }


        if ($state->filter_quantity)
        {
            $query->where("(#__j2store_product_prices.quantity_from <= ".$db->q($state->filter_quantity).")");
        }

        if ($state->group_id)
        {
            $query->where('#__j2store_product_prices.customer_group_id IN ('.$state->group_id.')');
        }

        if ($state->variant_id)
        {
            $query->where('#__j2store_product_prices.variant_id = '.$db->q((int)$state->variant_id));
        }


    }

	protected function _buildQueryOrder($query) {
		$state = $this->getFilterValues();
        if(isset($state->orderby) && !empty($state->orderby) && in_array($state->orderby,array('variant_id','price','quantity_from'))) {
            $db = $this->getDbo();
            if(!in_array(strtolower($state->direction),array('asc','desc'))){
                $state->direction = 'desc';
            }
            $query->order($db->qn('#__j2store_product_prices').'.'.$db->qn($state->orderby).' '.$state->direction);
			//$query->order('#__j2store_product_prices.'.$state->orderby.' '.$state->direction);
		}
	}

	public function &getItem($id = null) {

		$query = $this->buildQuery();
		$this->_db->setQuery( (string) $query );
		$item = $this->_db->loadObject();
		return $item;
	}


}