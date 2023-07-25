<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreTableProductFilter extends F0FTable
{
	public function __construct($table, $key, &$db)
	{
		$table = "#__j2store_product_filters";
		$key = "filter_id";
		parent::__construct($table, $key, $db);
	}

	public function deleteProductFilterList($product_id){
		if(empty($product_id)){
			return new stdClass();
		}
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->delete('#__j2store_product_filters');
		$query->where('product_id ='.$db->q($product_id));
		$db->setQuery($query);
		return $db->execute();
	}

	public function searchFilters($q){
		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		$query = $db->getQuery(true);
		$query->select('pfv.*');
		$query->select('pf.*');
		$query->from('#__j2store_filters as pfv');
		$query->leftJoin('#__j2store_filtergroups as pf ON  pfv.group_id = pf.j2store_filtergroup_id');
		$query->where('LOWER(filter_name) LIKE '.$db->Quote( '%'.$db->escape( $q, true ).'%', false )
						.' OR '
						.'LOWER(group_name) LIKE '.$db->Quote( '%'.$db->escape( $q, true ).'%', false )
						);
		$db->setQuery($query);
		$result = $db->loadObjectList();
		return $result;
	}


	/**
	 * Method to add filters to a product
	 * @param array $filters
	 * @param int $product_id
	 * @return boolean True|False
	 */

	public function addFilterToProduct($filters, $product_id) {
	    if(is_array($filters)){
            $filters = J2Store::platform()->fromObject($filters);
        }
		if(!is_array($filters) || empty($product_id)) return false;

		$db = $this->getDbo();
		foreach($filters as $filter_id) {

			$query = $db->getQuery(true)->select('*')->from('#__j2store_product_filters')
							->where('filter_id='.$db->q($filter_id))
							->where('product_id='.$db->q($product_id));
			$row = $db->setQuery($query)->loadObject();
			if(!$row) {
				$object = new JObject();
				$object->product_id = $product_id;
				$object->filter_id = $filter_id;
				try {
					$db->insertObject('#__j2store_product_filters', $object);
				} catch (Exception $e) {
					//echo $e->getMessage();
				}
			}
		}
		return true;
	}


	public function deleteFilter($filter_id, $product_id) {
		$db = $this->getDbo();
		$query = $db->getQuery(true)->delete('#__j2store_product_filters')
		->where('filter_id='.$db->q($filter_id))
		->where('product_id='.$db->q($product_id));
		$db->setQuery($query);
		try {
			$db->execute();
		}catch(Exception $e) {
			return false;
		}
		return true;
	}

	/**
	 * Method to get filters by product
	 * @param mixed int|array $product_id
	 * @return array Rows of filters
	 */

	public function getFiltersByProduct($product_id=null) {
		$db = $this->getDbo();
		$query = $db->getQuery(true)->select('pf.*')->from('#__j2store_product_filters AS pf');

		if(isset($product_id)) {
			$search_product_ids ='';
			if(is_array($product_id)){
				$search_product_ids = implode(',',$product_id);
			} elseif (is_numeric($product_id)) {
				$search_product_ids = ($product_id >0)?$product_id:'';
			}

			if(!is_null($search_product_ids) && !empty($search_product_ids)) {
				$query->where('product_id IN ('. $search_product_ids.')');
			}
		}

		$query->select('f.filter_name, f.group_id')
		->leftJoin('#__j2store_filters as f ON f.j2store_filter_id = pf.filter_id')
		->select('fg.group_name')
		->leftJoin('#__j2store_filtergroups as fg ON fg.j2store_filtergroup_id=f.group_id');
		$query->group('pf.filter_id');
		$query->order('fg.ordering ASC');
		$query->order('f.ordering ASC');
		$query->where('fg.enabled = 1');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$filters = array();
		foreach($rows as $row) {
			$filters[$row->group_id]['group_name'] = $row->group_name;
			$filters[$row->group_id]['filters'][] = $row;
   		}
		return $filters;
	}


	public function getFiltersByFilterIds($filter_ids){

		$db = $this->getDbo();
		$query = $db->getQuery(true)->select('pf.*')->from('#__j2store_product_filters AS pf');
		if($filter_ids ){
			$query->where('filter_id IN('. $filter_ids.')');
		}
		$query->select('f.filter_name, f.group_id')
		->leftJoin('#__j2store_filters as f ON f.j2store_filter_id = pf.filter_id')
		->select('fg.group_name')
		->leftJoin('#__j2store_filtergroups as fg ON fg.j2store_filtergroup_id=f.group_id');
		$query->group('pf.filter_id');
		$query->order('pf.ordering ASC');
		$query->order('f.ordering ASC');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$filters = array();
		foreach($rows as $row) {
			$filters[$row->group_id]['group_name'] = $row->group_name;
			$filters[$row->group_id]['filters'][] = $row;
		}

		return $filters;

	}


	/**
	 * Method to get all the filters
	 */
	public function getFilters(){
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('f.filter_name, f.group_id ,f.j2store_filter_id as filter_id')
		->from('#__j2store_filters as f')
		->select('fg.group_name')
		->leftJoin('#__j2store_filtergroups as fg ON fg.j2store_filtergroup_id=f.group_id');
		$query->group('f.j2store_filter_id');
		$query->order('fg.ordering ASC');
		$query->order('f.ordering ASC');
		$query->where('fg.enabled = 1');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$filters = array();
		foreach($rows as $row) {
			$filters[$row->group_id]['group_name'] = $row->group_name;
			$filters[$row->group_id]['filters'][] = $row;
		}
		return $filters;
	}

}