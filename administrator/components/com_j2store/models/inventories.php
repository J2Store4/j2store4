<?php
/**
 * @package J2Store
* @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
* @license GNU GPL v3 or later
*/

// No direct access
defined('_JEXEC') or die;
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/models/behavior/autoload.php';
class J2StoreModelInventories extends F0FModel {
	protected $_productlist = array();
	protected $inventory_pagination = null;
	protected $inventory_pagetotal = null;
	public function getStockProductList($overrideLimits = false, $group = '')
	{
		if (empty($this->_productlist))
		{
			$query = $this->getStockProductListQuery($overrideLimits);

			if (!$overrideLimits)
			{
				$limitstart = $this->getState('limitstart');
				$limit = $this->getState('limit');
				$this->_productlist = $this->_getList((string) $query, $limitstart, $limit, $group);
			}
			else
			{
				$this->_productlist = $this->_getList((string) $query, 0, 0, $group);
			}

		}
		return $this->_productlist;
	}

	public function getStockProductListQuery($overrideLimits = false) {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)->select('#__j2store_productquantities.*')->from('#__j2store_productquantities');
		$this->_buildQueryJoins($query);
		$this->_buildWhereQuery($query);
		$this->_buildQueryOrderBy($query);
		$query->group('#__j2store_products.j2store_product_id');
		//$query->group('#__j2store_productquantities.variant_id');
        $model = $this;
		J2Store::plugin()->event('AfterStockProductListQuery', array(&$query, &$model));
		return $query;

	}

	public function _buildQueryJoins($query){
		$query->select('#__j2store_variants.manage_stock');
		$query->select('#__j2store_variants.availability');
		$query->select('#__j2store_products.j2store_product_id');
		$query->select('#__j2store_products.product_type');
		$query->join('LEFT','#__j2store_variants ON #__j2store_variants.j2store_variant_id = #__j2store_productquantities.variant_id');
		$query->join('INNER','#__j2store_products ON #__j2store_products.j2store_product_id = #__j2store_variants.product_id');
	}

	public function _buildWhereQuery(&$query){
        $db = JFactory::getDbo();
		$inventry = $this->getState ('inventry_stock','');
		if($inventry == 'in_stock'){
			$query->where('#__j2store_variants.availability = 1');
		}
		if($inventry == 'out_of_stock'){
			$query->where('#__j2store_variants.availability = 0');
		}

		$search = $this->getState('search','');
		if($search){
            $query->where('#__j2store_variants.sku LIKE '.$db->q('%'.$search.'%'));
        }
	}

	public function _buildQueryOrderBy($query){
		$db =$this->_db;
		$this->_buildSortQuery($query);
		if(!empty($this->state->filter_order) && in_array($this->state->filter_order,array('variant_id','j2store_productquantity_id'))) {
            if(!in_array(strtolower($this->state->filter_order_Dir),array('asc','desc'))){
                $this->state->filter_order_Dir = 'desc';
            }
            $query->order($db->qn('#__j2store_productquantities').'.'.$db->qn($this->state->filter_order).' '.$this->state->filter_order_Dir);
            //$query->order($db->q('#__j2store_productquantities.'.$this->state->filter_order).' '.$db->q($this->state->filter_order_Dir));
		}else{
			$query->order('#__j2store_productquantities.j2store_productquantity_id DESC');
		}

	}

	public function getInventoryPagination()
	{
		if (empty($this->inventory_pagination))
		{
			// Import the pagination library
			JLoader::import('joomla.html.pagination');

			// Prepare pagination values
			$total = $this->getInventoryPageTotal();
			$limitstart = $this->getState('limitstart');
			$limit = $this->getState('limit');
			// Create the pagination object
			$this->inventory_pagination = new JPagination($total, $limitstart, $limit);
		}

		return $this->inventory_pagination;
	}

	/**
	 * Get the number of all items
	 *
	 * @return  integer
	 */
	public function getInventoryPageTotal()
	{
		if (is_null($this->productpagetotal))
		{
			$query = $this->buildCountQuery();

			if ($query === false)
			{
				$subquery = $this->getStockProductListQuery(false);
				$subquery->clear('order');
				$query = $this->_db->getQuery(true)
				->select('COUNT(*)')
				->from("(" . (string) $subquery . ") AS a");
			}

			$this->_db->setQuery((string) $query);

			$this->productpagetotal = $this->_db->loadResult();
		}

		return $this->productpagetotal;
	}
}