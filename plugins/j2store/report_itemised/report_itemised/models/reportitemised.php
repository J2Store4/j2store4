<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

class J2StoreModelReportItemised extends F0FModel
{
    /*
     * @var array
     */
    var $_data = null;

    /**
     *
     * @var integer
     */
    var $_total = null;

    /**
     * Pagination object
     *
     * @var object
     */
    var $_pagination = null;


    /**
     *
     * @access public
     * @return array
     */
    public function getData()
    {
        // Lets load the content if it doesn't already exist
        if (empty($this->_data)) {
            $query = $this->_buildQuery();
            try {
                $list = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
            } catch (Exception $e) {
                $list = array();
            }
            $fof_helper = J2Store::fof();
            foreach ($list as $item) {
                $item->orderitem_attributes = $fof_helper->getModel('OrderitemAttributes', 'J2StoreModel', array('orderitem_id' => $item->j2store_orderitem_id))->getList();
            }
            $this->_data = $list;
        }

        return $this->_data;
    }


    /**
     * Get the number of all items
     *
     * @return  integer
     */
    public function getTotal()
    {
        if (is_null($this->total)) {
            $query = $this->buildCountQuery();

            if ($query === false) {
                $subquery = $this->_buildQuery(false);
                $subquery->clear('order');
                $query = $this->_db->getQuery(true)
                    ->select('COUNT(*)')
                    ->from("(" . (string)$subquery . ") AS a");
            }

            $this->_db->setQuery((string)$query);

            $this->total = $this->_db->loadResult();
        }

        return $this->total;
    }

    public function getPagination()
    {
        if (empty($this->pagination)) {
            // Import the pagination library
            JLoader::import('joomla.html.pagination');
            // Prepare pagination values
            $total = $this->getTotal();
            $limitstart = $this->getState('limitstart');
            $limit = $this->getState('limit');

            // Create the pagination object
            $this->pagination = new JPagination($total, $limitstart, $limit);
        }

        return $this->pagination;
    }


    /**
     * Method to buildQuery
     * @return object
     */
    function _buildQuery()
    {
        // Get the WHERE and ORDER BY clauses for the query

        $query = JFactory::getDbo()->getQuery(true);
        $query->select('oi.j2store_orderitem_id,oi.orderitem_name,oi.product_id,oi.orderitem_quantity');
        $query->select('count(oi.product_id) AS count');
        $query->select('product.product_source_id');
        $query->select('cont.id');
        $query->select('category.title AS category_name');
        $query->select('SUM(oi.orderitem_quantity) AS sum');
        $query->from('#__j2store_orderitems AS oi');
        $query->leftJoin('#__j2store_products AS product ON product.j2store_product_id=oi.product_id');
        $query->leftJoin('#__content AS cont ON cont.id=product.product_source_id');
        $query->leftJoin('#__categories AS category ON category.id=cont.catid');
        $this->_buildContentWhere($query);
        $query->group('oi.product_id,oi.orderitem_attributes');
        return $query;
    }

    public function buildQuery($overrideLimits = false)
    {
        $query = JFactory::getDbo()->getQuery(true);
        $query->select('oi.*');
        $query->select('count(oi.product_id) AS count');
        $query->select('SUM(oi.orderitem_quantity) AS sum');
        $query->from('#__j2store_orderitems AS oi');
        $query->leftJoin('#__content AS product ON product.id=oi.product_id');
        $query->select('category.title AS category_name');
        $query->leftJoin('#__categories AS category ON category.id=product.catid');
        $this->_buildContentWhere($query);
        $query->group('oi.product_id,oi.orderitem_attributes');
    }

    function _buildContentWhere($query)
    {
        // To load only the Normal order items
        $query->where('oi.orderitem_type <> \'subscription\'');

        $mainframe = J2Store::platform()->application();
        $option = 'com_j2store';
        $ns = $option . '.report';
        $db = JFactory::getDBO();
        $filter_order = $this->getState('filter_order');//$mainframe->getUserStateFromRequest( $ns.'filter_order',		'filter_order',		'oi.order_id',	'cmd' );
        $filter_order_Dir = $this->getState('filter_order_Dir', 'ASC');//$mainframe->getUserStateFromRequest( $ns.'filter_order_Dir',	'filter_order_Dir',	'ASC',				'word' );
        $filter_orderstate = $mainframe->getUserStateFromRequest($ns . 'filter_orderstate', 'filter_orderstate', '', 'word');
        $search = $mainframe->getUserStateFromRequest($ns . 'filter_search', 'filter_search', '', 'string');
        $filter_datetype = $this->getState('filter_datetype');
        if ($filter_datetype) {
            if ($filter_datetype == 'today') {
                $query->where('oi.created_on LIKE ' . $this->_db->q(date("Y-m-d") . '%'));
            }
            if ($filter_datetype == 'this_week') {
                $weekdate = $this->getWeekdate();

                $query->where('oi.created_on BETWEEN' . $this->_db->q($weekdate['start'] . '%') . ' AND ' . $this->_db->q($weekdate['end'] . '%'));
            }
            if ($filter_datetype == 'this_month') {
                $start = date('Y-m-01', strtotime('this month'));
                $end = date('Y-m-t', strtotime('this month'));
                $query->where('oi.created_on BETWEEN' . $this->_db->q($start . '%') . ' AND ' . $this->_db->q($end . '%'));
            }
            if ($filter_datetype == 'this_year') {
                $start = date('Y');
                $query->where('oi.created_on LIKE ' . $this->_db->q($start . '%'));
            }
            if ($filter_datetype == 'last_7day') {
                $start = date('Y-m-d', strtotime('-7 days'));
                $end = date("Y-m-d");
                $query->where('oi.created_on BETWEEN' . $this->_db->q($start . '%') . ' AND ' . $this->_db->q($end . '%'));
            }
            if ($filter_datetype == 'last_month') {
                $start = date('Y-m-d', strtotime('first day of last month'));
                $end = date('Y-m-d', strtotime('last day of last month'));
                $query->where('oi.created_on BETWEEN' . $this->_db->q($start . '%') . ' AND ' . $this->_db->q($end . '%'));
            }
            if ($filter_datetype == 'last_year') {
                $start = date('Y') - 1;
                $query->where('oi.created_on LIKE ' . $this->_db->q($start . '%'));
            }

        }

        if (strpos($search, '"') !== false) {
            $search = str_replace(array('=', '<'), '', $search);
        }
        $search = strtolower($search);

        $where = array();

        if ($search) {
            $where[] = 'LOWER(oi.orderitem_name) LIKE ' . $db->Quote('%' . $db->escape($search, true) . '%', false) .
                'OR LOWER(oi.product_id) LIKE ' . $db->Quote('%' . $db->escape($search, true) . '%', false) .
                ' OR LOWER(oi.orderitem_sku) LIKE ' . $db->Quote('%' . $db->escape($search, true) . '%', false);
        }

        if ($filter_orderstate) {
            if ($filter_orderstate == 'Confirmed') {
                $where[] = 'a.order_state = ' . $db->Quote($db->escape($filter_orderstate, true), false);
            } else if ($filter_orderstate == 'Pending') {
                $where[] = 'a.order_state = ' . $db->Quote($db->escape($filter_orderstate, true), false);
            } else if ($filter_orderstate == 'Failed') {
                $where[] = 'a.order_state = ' . $db->Quote($db->escape($filter_orderstate, true), false);
            }
        }
        foreach ($where as $w) {
            $query->where($w);
        }
        if (!in_array(strtolower($filter_order_Dir), array('asc', 'desc'))) {
            $filter_order_Dir = 'desc';
        }
        if (!empty($filter_order) && in_array($filter_order, array('oi.product_id', 'oi.orderitem_name', 'sum', 'count'))) {
            $query->order($db->qn($filter_order) . ' ' . $filter_order_Dir);
            // $query->order($filter_order.'  '.$filter_order_Dir);
        }


        $query->order('oi.order_id');
        return;
    }

    function getWeekdate()
    {
        $ddate = date('Y-m-d'); // Change to whatever date you need
        $year = date('Y');
        $date = new DateTime($ddate);
        $week = $date->format("W");
        $week = $week - 1;
        $time = strtotime("1 January $year", time());
        $day = date('w', $time);
        $time += ((7 * $week) + 1 - $day) * 24 * 3600;
        $ret['start'] = date('Y-n-j', $time);
        $time += 6 * 24 * 3600;
        $ret['end'] = date('Y-n-j', $time);
        return $ret;
    }

    function _getOrderID($id)
    {
        $db = JFactory::getDBO();
        $query = "SELECT order_id FROM #__j2store_orders WHERE id={$id}";
        $db->setQuery($query);
        return $db->loadResult();

    }

    function _getOrderItemIDs($id)
    {

        //first get the order_id
        $order_id = $this->_getOrderID($id);

        //get the order item ids
        $db = JFactory::getDBO();
        $query = "SELECT orderitem_id FROM #__j2store_orderitems WHERE order_id=" . $db->Quote($order_id);
        $db->setQuery($query);
        return $db->loadResultArray();
    }

    /**
     * Method to get Processed array of data for Export
     * @param object array $data
     * return array
     */
    public function export($data)
    {
        $export_data = array();
        foreach ($data as $i => $item) {
            $export_data[$i]['product_id'] = $item->product_id;
            $export_data[$i]['product_name'] = $item->orderitem_name;
            $option = array();
            if (isset($item->orderitem_attributes) && $item->orderitem_attributes) {
                $string = '';
                foreach ($item->orderitem_attributes as $a => $attr) {
                    $string .= $attr->orderitemattribute_name . ' : ' . $attr->orderitemattribute_value;
                }
                $export_data[$i]['item_option'] = $string;
            }
            $export_data[$i]['category_name'] = $item->category_name;
            $export_data[$i]['product_qty'] = $item->sum;
            $export_data[$i]['no_of_orders'] = $item->count;
        }
        return $export_data;
    }

    /**
     * Method to get Header FIELDS for file Export
     * @return array;
     */
    public function getHeaderfields($export_data)
    {
        J2Store::platform()->application()->getLanguage()->load('plg_j2store_report_itemised', JPATH_ADMINISTRATOR);
        $data = array();
        $data[] = JText::_("J2STORE_PRODUCT_ID");
        $data[] = JText::_("J2STORE_PRODUCT_NAME");
        $data[] = JText::_("J2STORE_PRODUCT_OPTIONS");
        $data[] = JText::_("JCATEGORY");
        $data[] = JText::_("J2STORE_QUANTITY");
        $data[] = JText::_("J2STORE_REPORTS_ITEMISED_PURCHASES");
        return $data;
    }

}
