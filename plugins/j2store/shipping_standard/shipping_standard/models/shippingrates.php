<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

class J2StoreModelShippingRates extends F0FModel
{
    public $cache_enabled = false;
    public function buildQuery($overrideLimits = false)
    {
    	$db = JFactory::getDbo();
    	$query = $db->getQuery(true);
    	$this->_buildQueryFields($query);
    	$query->from('#__j2store_shippingrates as tbl');
    	$this->_buildQueryWhere($query);
    	$this->_buildQueryJoins($query);
    	return $query;
    }

    protected function _buildQueryWhere($query)
    {
        $filter_id	= $this->getState('filter_id');
        $filter_shippingmethod  = $this->getState('filter_shippingmethod');
        $filter_weight = $this->getState('filter_weight');
       	$filter_user_group	= $this->getState('filter_user_group');
        $filter_geozone = $this->getState('filter_geozone');
        $filter_geozones = $this->getState('filter_geozones');

		if (strlen($filter_id))
        {
            $query->where('tbl.j2store_shipping_rate_id = '.(int) $filter_id);
       	}
        if (strlen($filter_shippingmethod))
        {
           $query->where('tbl.shipping_method_id = '.(int) $filter_shippingmethod);
        }
    	if (strlen($filter_user_group))
        {
            $query->where('tbl.group_id = '.(int) $filter_user_group);
       	}
    	if (strlen($filter_weight))
        {
        	$query->where("(
        		tbl.shipping_rate_weight_start <= '".$filter_weight."'
        		AND (
                    tbl.shipping_rate_weight_end >= '".$filter_weight."'
                    OR
                    tbl.shipping_rate_weight_end = '0.000'
                    )
			)");
       	}
        if (strlen($filter_geozone))
        {
            $query->where('tbl.geozone_id = '.(int) $filter_geozone);
        }

        if (is_array($filter_geozones))
        {
            $query->where("tbl.geozone_id IN ('" . implode("', '", $filter_geozones ) . "')" );
        }
    }

    protected function _buildQueryJoins($query)
    {
        $query->join('LEFT', '#__j2store_geozones AS geozone ON tbl.geozone_id = geozone.j2store_geozone_id');
    }

    protected function _buildQueryFields($query)
    {
        $field = array();
       // $field[] = " geozone.geozone_name ";

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
