<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreModelShippingMethods extends F0FModel
{
	public $cache_enabled = false;

	/**
	 * Public class constructor
	 *
	 * @param array $config The configuration array
	 */
	public function __construct($config = array())
	{
		$table_path =  JPATH_SITE. '/plugins/j2store/shipping_standard/shipping_standard/tables/';
		$this->addTablePath($table_path);
	}


	public function buildQuery($overrideLimits = false)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$this->_buildQueryFields($query);
		$query->from('#__j2store_shippingmethods as tbl');
		$this->_buildQueryWhere($query);
		$this->_buildQueryJoins($query);
		return $query;
	}

	protected function _buildQueryWhere($query)
	{
        $filter         = !is_null($this->getState('filter')) ? $this->getState('filter') : '';
        $filter_id_from = !is_null($this->getState('filter_id_from')) ? $this->getState('filter_id_from') : '';
        $filter_id_to   = !is_null($this->getState('filter_id_to')) ? $this->getState('filter_id_to') : '';
        $filter_name    = !is_null($this->getState('filter_name')) ? $this->getState('filter_name') : '';
        $filter_enabled = !is_null($this->getState('filter_enabled')) ? $this->getState('filter_enabled') : '';
        $filter_taxclass = !is_null($this->getState('filter_taxclass')) ? $this->getState('filter_taxclass') : '';
        $filter_shippingtype = !is_null($this->getState('filter_shippingtype')) ? $this->getState('filter_shippingtype') : '';
        $filter_subtotal = !is_null($this->getState('filter_subtotal')) ? $this->getState('filter_subtotal') : '';

		if ($filter)
		{
			$key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter ) ) ).'%');
			$where = array();
			$where[] = 'LOWER(tbl.shippingmethod_id) LIKE '.$key;
			$where[] = 'LOWER(tbl.shipping_method_name) LIKE '.$key;
			$query->where('('.implode(' OR ', $where).')');
		}

		if (strlen($filter_enabled))
		{
			$query->where('tbl.published = '.$filter_enabled);
		}

		if (strlen($filter_id_from))
		{
			if (strlen($filter_id_to))
			{
				$query->where('tbl.shippingmethod_id >= '.(int) $filter_id_from);
			}
			else
			{
				$query->where('tbl.shippingmethod_id = '.(int) $filter_id_from);
			}
		}

		if (strlen($filter_id_to))
		{
			$query->where('tbl.shippingmethod_id <= '.(int) $filter_id_to);
		}

		if (strlen($filter_name))
		{
			$key    = $this->_db->Quote('%'.$this->_db->getEscaped( trim( strtolower( $filter_name ) ) ).'%');
			$query->where('LOWER(tbl.shipping_method_name) LIKE '.$key);
		}

		if (strlen($filter_taxclass))
		{
			$query->where('tbl.tax_class_id = '.(int) $filter_taxclass);
		}

		if (strlen($filter_shippingtype))
		{
			$query->where('tbl.shipping_method_type = '.(int) $filter_shippingtype);
		}

		if ( strlen($filter_subtotal ))
		{
			$query->where('tbl.subtotal_minimum <= '. $filter_subtotal);
			$query->where('( ( tbl.subtotal_maximum = 0.00000 ) OR ( tbl.subtotal_maximum = -1 ) OR ( ( tbl.subtotal_maximum != 0.00000 AND tbl.subtotal_maximum != -1 ) AND ( tbl.subtotal_maximum >= '.$filter_subtotal.' ) ) )');
		}
	}

	protected function _buildQueryJoins($query)
	{
		$query->join('LEFT', '#__j2store_taxprofiles AS taxclass ON tbl.tax_class_id = taxclass.j2store_taxprofile_id');
	}

	protected function _buildQueryFields($query)
	{
		$field = array();
		$field[] = " taxclass.taxprofile_name ";
		$query->select( $this->getState( 'select', 'tbl.*' ) );
		$query->select( $field );
	}


	/**
	 * Returns an object list
	 *
	 * @param   string   $query       The query
	 * @param   integer  $limitstart  Offset from start
	 * @param   integer  $limit       The number of records
	 * @param   string   $group       The group by clause
	 *
	 * @return  array  Array of objects
	 */
	protected function &_getList($query, $limitstart = 0, $limit = 0, $group = '')
	{
		$db = JFactory::getDbo();
		$db->setQuery($query, $limitstart, $limit);
		$result = $db->loadObjectList($group);
		$this->onProcessList($result);
		return $result;
	}


	protected function onProcessList(&$resultArray)
	{
		foreach($resultArray as $item)
		{
			$item->link = 'index.php?option=com_j2store&view=shipping&task=view&id='.JFactory::getApplication()->input->getInt('id').'&shippingTask=view&sid='.$item->j2store_shippingmethod_id;
		}
	}


	/**
	 * This method runs after an item has been gotten from the database in a read
	 * operation. You can modify it before it's returned to the MVC triad for
	 * further processing.
	 *
	 * @param   F0FTable  &$record  The table instance we fetched
	 *
	 * @return  void
	 */
	protected function onAfterGetItem(&$record)
	{

	}

	/**
	 * Get the number of all items
	 *
	 * @return  integer
	 */
	public function getTotal()
	{
		$db = JFactory::getDbo();
		if (is_null($this->total))
		{
			$query = $this->buildCountQuery();

			if ($query === false)
			{
				$subquery = $this->buildQuery(false);
				$subquery->clear('order');
				$query = $db->getQuery(true)
				->select('COUNT(*)')
				->from("(" . (string) $subquery . ") AS a");
			}

			$db->setQuery((string) $query);

			$this->total = $db->loadResult();
		}

		return $this->total;
	}
}