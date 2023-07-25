<?php
/**
 * --------------------------------------------------------------------------------
 * Report Plugin - Products
 * --------------------------------------------------------------------------------
 * @package     Joomla 3.x
 * @subpackage  J2 Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2015 J2Store . All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
defined('_JEXEC') or die( 'Restricted access' );
class J2storeModelReportProducts extends F0FModel{
    public $cache_enabled = false;

    public $filter_search;
    var $filter_orderstatus;


    function __construct()
    {
        parent::__construct();
        $app = JFactory::getApplication();
        $option = 'com_j2store';
        $ns = $option.'.reportsales';
        $data = $app->input->getArray($_REQUEST);
        // Get the pagination request variables
        $this->filter_search = $app->input->getString('filter_search','');
        if(isset($data['filter_orderstatus'])){
            $this->filter_orderstatus = $data['filter_orderstatus'];
            $this->setState('filter_orderstatus',  $this->filter_orderstatus);
        }
        $filter_order =  $app->input->getString('filter_order','orderitem.j2store_orderitem_id');
        //$app->getUserStateFromRequest($ns.'filter_order','filter_order','tbl.order_id','');
        $filter_order_Dir =  $app->input->getString('filter_order_Dir','ASC');
        $filter_name      =  $app->getUserStateFromRequest($ns.'orderitem_name', 'filter_name', '', '');
        $filter_date      = $app->getUserStateFromRequest($ns.'modified_date', 'filter_date', '', '');
        $filter_order_id  = $app->getUserStateFromRequest($ns.'order_id', 'filter_order_id', '', '');

        $limit		= $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->getCfg('list_limit'), 'int' );
        $limitstart	= $app->getUserStateFromRequest( $ns.'.limitstart', 'limitstart', 0, 'int' );

        // In case limit has been changed, adjust limitstart accordingly
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        //date
        $this->filter_datetype = $app->input->getString('filter_datetype');
        $this->filter_order_from_date =  $app->getUserStateFromRequest($ns.'filter_order_from_date','filter_order_from_date','','');
        $this->filter_order_to_date =  $app->getUserStateFromRequest($ns.'filter_order_to_date','filter_order_to_date','','');
        //shipping
        $this->filter_shippingmethod = $app->input->getString('filter_shippingmethod');
        //payment
        $this->filter_paymentmethod = $app->input->getString('filter_paymentmethod');

        $this->filter_coupon_search = $app->input->getString('filter_coupon_search');
        $this->filter_manufacture = $app->input->getString('filter_manufacture');
        $this->filter_vendor = $app->input->getString('filter_vendor');
        $this->filter_taxsearch = $app->input->getString('filter_taxsearch');
        $this->filter_postcodesearch = $app->input->getString('filter_postcodesearch');
        $this->filter_order_from_qty = $app->input->getString('filter_order_from_qty');
        $this->filter_order_to_qty = $app->input->getString('filter_order_to_qty');
        $this->filter_vat = $app->input->getString('filter_vat');


        $this->setState('limit', $limit);
        $this->setState('limitstart', $limitstart);

        $this->setState('filter_search',  $this->filter_search);

        $this->setState('filter_order_Dir',  $filter_order_Dir);
        $this->setState('filter_order',  $filter_order);
        $this->setState('filter_name',  $filter_name);
        $this->setState('filter_date', $filter_date );
        $this->setState('filter_order_id',$filter_order_id);

        //date
        $this->setState('filter_datetype',$this->filter_datetype);
        $this->setState('filter_order_from_date',$this->filter_order_from_date);
        $this->setState('filter_order_to_date',$this->filter_order_to_date);
        $this->setState('filter_shippingmethod',$this->filter_shippingmethod);
        $this->setState('filter_paymentmethod',$this->filter_paymentmethod);
        $this->setState('filter_coupon_search',$this->filter_coupon_search);
        $this->setState('filter_manufacture',$this->filter_manufacture);
        $this->setState('filter_vendor',$this->filter_vendor);
        $this->setState('filter_taxsearch',$this->filter_taxsearch);
        $this->setState('filter_postcodesearch',$this->filter_postcodesearch);
        $this->setState('filter_order_from_qty',$this->filter_order_from_qty);
        $this->setState('filter_order_to_qty',$this->filter_order_to_qty);
        $this->setState('filter_vat',$this->filter_vat);
    }
    public function buildQuery($overrideLimits = false)
    {
        // Get the WHERE and ORDER BY clauses for the query //order_id,tbl.user_email,tbl.order_total,tbl.order_subtotal,tbl.order_tax
        $query = JFactory::getDbo()->getQuery(true);
        $query->select('orderitem.orderitem_name,orderitem.orderitem_sku,sum(orderitem.orderitem_quantity) as total_qty,sum(orderitem.orderitem_finalprice_without_tax) as total_final_price_without_tax, 
		sum(orderitem.orderitem_tax) as total_item_tax,sum(orderitem.orderitem_discount) as total_item_discount,sum(orderitem.orderitem_discount_tax) as total_item_discount_tax,
		sum(orderitem.orderitem_finalprice_with_tax ) as total_final_price_with_tax');
        $query->from('#__j2store_orderitems as orderitem')->group('orderitem.variant_id');
        $this->_buildQueryJoins($query);
        $this->_buildQueryWhere($query);
        $this->_buildQueryOrder($query);
        return $query;
    }
    protected function _buildQueryWhere($query)
    {
        // To load only the Normal order items
        $query->where('orderitem.orderitem_type <> \'subscription\'');

        $filter_search    = $this->getState('filter_search');
        $filter_orderstatus    = $this->getState('filter_orderstatus');
        $filter_datetype  = $this->getState('filter_datetype');
        $filter_order_from_date = $this->getState('filter_order_from_date');
        $filter_order_to_date = $this->getState('filter_order_to_date');
        $filter_manufacture = $this->getState('filter_manufacture');
        $filter_vendor = $this->getState('filter_vendor');
        if ($filter_search)
        {
            $key	= $this->_db->Quote('%'.$this->_db->escape( trim( strtolower( $filter_search ) ) ).'%');
            $where = array();
            $where[] = 'LOWER(orderitem.orderitem_sku) LIKE '.$key;
            $query->where('('.implode(' OR ', $where).')');
        }
        if ($filter_orderstatus)
        {
            $status=implode(',', $filter_orderstatus);
            $query->where('tbl.order_state_id IN ('.$status.')');
        }
        if($filter_datetype == 'today'){
            $query->where('tbl.created_on LIKE '.$this->_db->q(date("Y-m-d").'%'));
        }
        if($filter_datetype == 'this_week' ){
            $weekdate=$this->getWeekdate();
            $query->where('tbl.created_on BETWEEN'.$this->_db->q($weekdate['start'].'%').' AND '.$this->_db->q($weekdate['end'].'%'));
        }
        if($filter_datetype == 'this_month'){
            $start = date('Y-m-01',strtotime('this month'));
            $end = date('Y-m-t',strtotime('this month'));
            $query->where('tbl.created_on BETWEEN'.$this->_db->q($start.'%').' AND '.$this->_db->q($end.'%'));
        }
        if($filter_datetype == 'this_year'){
            $start = date('Y');
            $query->where('tbl.created_on LIKE '.$this->_db->q($start.'%'));
        }
        if($filter_datetype == 'last_7day'){
            $start = date('Y-m-d', strtotime('-7 days'));
            $end = date("Y-m-d");
            $query->where('tbl.created_on BETWEEN'.$this->_db->q($start.'%').' AND '.$this->_db->q($end.'%'));
        }
        if($filter_datetype == 'last_month'){
            $start = date('Y-m-d', strtotime('first day of last month'));
            $end = date('Y-m-d', strtotime('last day of last month'));
            $query->where('tbl.created_on BETWEEN'.$this->_db->q($start.'%').' AND '.$this->_db->q($end.'%'));
        }
        if($filter_datetype == 'last_year'){
            $start = date('Y')-1;
            $query->where('tbl.created_on LIKE '.$this->_db->q($start.'%'));
        }
        if(!empty($filter_order_from_date) && !empty($filter_order_to_date)){

            $query->where('tbl.created_on BETWEEN'.$this->_db->q($filter_order_from_date.'%').' AND '.$this->_db->q($filter_order_to_date.'%'));
        }

        /*if($filter_manufacture){
            $query->where('product.manufacturer_id ='.$filter_manufacture);
        }
        if($filter_vendor){
            $query->where('orderitem.vendor_id ='.$filter_vendor);
        }*/
    }
    function getWeekdate(){
        $ddate = date('Y-m-d'); // Change to whatever date you need
        $year=date('Y');
        $date = new DateTime($ddate);
        $week = $date->format("W");

        $week=$week -1;
        $time = strtotime("1 January $year", time());
        $day = date('w', $time);
        $time += ((7*$week)+1-$day)*24*3600;
        $ret['start'] = date('Y-n-j', $time);
        $time += 6*24*3600;
        $ret['end'] = date('Y-n-j', $time);
        return $ret;
    }
    protected function _buildQueryJoins($query)
    {
        $query->join('INNER', '#__j2store_orders AS tbl ON tbl.order_id = orderitem.order_id');
        $query->join('LEFT', '#__j2store_orderstatuses AS orderstatus ON tbl.order_state_id = orderstatus.j2store_orderstatus_id');
    }

    function _buildQueryOrder($query)
    {
        $filter_order		= $this->getState ('filter_order','orderitem.j2store_orderitem_id');
        //$mainframe->getUserStateFromRequest( $ns.'filter_order',		'filter_order',		'orderitem.j2store_orderitem_id',	'cmd' );
        $filter_order_Dir	= $this->getState ('filter_order_Dir','ASC');
        //$mainframe->getUserStateFromRequest( $ns.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );

        if(empty( $filter_order ) && empty( $filter_order_Dir )){
            $query->order('orderitem.j2store_orderitem_id');

        }else{
            $query->order($filter_order.' '.$filter_order_Dir);
        }
    }
}