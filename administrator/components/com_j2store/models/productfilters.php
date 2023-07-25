<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2StoreModelProductFilters extends F0FModel {

    public function buildQuery($overrideLimits = false) {
        $query = parent::buildQuery($overrideLimits);
        $db = JFactory::getDbo();
        $product_id = $this->getState('product_id', '');
        if(isset($product_id) && (is_array($product_id) || is_numeric($product_id))) {
            $search_product_ids ='';
            if(is_array($product_id)){
                $product_id = J2Store::platform()->toInteger($product_id);
                $search_product_ids = implode(',',$product_id);
            } elseif (is_numeric($product_id)) {
                $search_product_ids = ($product_id >0)? $db->q($product_id):'';
            }

            if(!is_null($search_product_ids) && !empty($search_product_ids)) {
                $query->where('#__j2store_product_filters.product_id IN ('. $search_product_ids.')');
            }
        }

        $query->select('#__j2store_filters.filter_name, #__j2store_filters.group_id')
            ->leftJoin('#__j2store_filters ON #__j2store_filters.j2store_filter_id = #__j2store_product_filters.filter_id')
            ->select('#__j2store_filtergroups.group_name')
            ->leftJoin('#__j2store_filtergroups ON #__j2store_filtergroups.j2store_filtergroup_id=#__j2store_filters.group_id');
		$query->group('#__j2store_product_filters.filter_id');
		$query->order('#__j2store_filtergroups.ordering ASC');
		$query->order('#__j2store_filters.ordering ASC');
		$query->where('#__j2store_filtergroups.enabled = 1');
		return $query;
    }
}