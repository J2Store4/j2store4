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
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/report.php');
class plgJ2StoreReport_Products extends J2StoreReportPlugin
{

    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element   = 'report_products';

    function __construct($subject, $config) {
        parent::__construct($subject, $config);
        $this->loadLanguage('plg_j2store_'.$this->_element, JPATH_ADMINISTRATOR);
    }

    /**
     * Overriding
     *
     * @param $row
     * @return string
     */
    function onJ2StoreGetReportView( $row )
    {
        if (!$this->_isMe($row))
        {
            return null;
        }
        return $this->viewList();
    }
    function onJ2StoreIsJ2Store4($element){
        if (!$this->_isMe($element)) {
            return null;
        }
        return true;
    }
    /**
     * Validates the data submitted based on the suffix provided
     * A controller for this plugin, you could say
     *
     * @return string
     */
    function viewList()
    {
        $platform = J2Store::platform();
        $app = $platform->application();
        $fof_helper = J2Store::fof();
        JToolBarHelper::title(JText::_('J2STORE_REPORT').'-'.JText::_('PLG_J2STORE_'.strtoupper($this->_element)),'j2store-logo');
        JToolbarHelper::back('J2STORE_BACK','index.php?option=com_j2store&view=reports');

        $vars = new \stdClass();
        $model = $fof_helper->getModel('ReportProducts','J2StoreModel');
        $model = $this->getProductList ($model);
        $vars->state = $model->getState();
        $vars->orderStatus = $this->getOrderStatus();
        $vars->orderDateType = $this->getOrderDateType();
        $vars->filtertype = $this->getFilterType();
        $lists = $model->getList();
        $vars->pagination = $model->getPagination();
        $product_amount = array();
        $product_name = array();
        $product_qty =array();
        foreach($lists as $key=>$prod){
            $product_amount[] = round($prod->total_final_price_with_tax);
            $product_name[] = $prod->orderitem_name;
            $product_qty[$key] =  $prod->total_qty;
        }
        $filter_order =  $app->input->getString('filter_order','orderitem.j2store_orderitem_id');
        $filter_order_Dir =  $app->input->getString('filter_order_Dir','ASC');

        if($filter_order == 'orderitem.orderitem_quantity'){
            if(strtoupper ( $filter_order_Dir ) == 'ASC'){
                array_multisort($product_qty, SORT_ASC, $lists);
            }else{
                array_multisort($product_qty, SORT_DESC, $lists);
            }
        }
        $vars->product_amount = $product_amount;
        $vars->product_name = $product_name;
        $vars->products = $lists;
        $vars->params = JComponentHelper::getParams('com_j2store');
        $id = $app->input->getInt('id', '0');

        $vars->id = $id;
        $form = array();
        $form['action'] = "index.php?option=com_j2store&view=report&task=view&id={$id}";
        $vars->form = $form;
        return $this->_getLayout('default', $vars);
    }

    function getProductList($model){
        $app = J2Store::platform()->application();
        $option = 'com_j2store';
        $ns = $option.'.reportproducts';
        $data = $app->input->getArray($_REQUEST);
        $model->setState('filter_search', $app->input->getString('filter_search'));
        if(isset($data['filter_orderstatus'])){
            $model->setState('filter_orderstatus', $data['filter_orderstatus']);
        }
        $model->setState('filter_order', $app->input->getString('filter_order'));
        $model->setState('filter_order_Dir', $app->input->getString('filter_order_Dir'));
        //filer for date
        $model->setState('filter_datetype',$app->input->getString('filter_datetype'));
        $model->setState('filter_order_from_date',$app->input->getString('filter_order_from_date'));
        $model->setState('filter_order_to_date',$app->input->getString('filter_order_to_date'));
        $model->setState('filter_shippingmethod',$app->input->getString('filter_shippingmethod'));
        $model->setState('filter_paymentmethod',$app->input->getString('filter_paymentmethod'));
        $model->setState('filter_display_type',$app->input->getString('filter_display_type'));
        $model->setState('filter_coupon_search',$app->input->getString('filter_coupon_search'));
        $model->setState('filter_manufacture',$app->input->getString('filter_manufacture'));
        $model->setState('filter_vendor',$app->input->getString('filter_vendor'));
        $model->setState('filter_taxsearch',$app->input->getString('filter_taxsearch'));
        $model->setState('filter_postcodesearch',$app->input->getString('filter_postcodesearch'));
        $model->setState('filter_order_from_qty',$app->input->getString('filter_order_from_qty'));
        $model->setState('filter_order_to_qty',$app->input->getString('filter_order_to_qty'));
        $model->setState('filter_vat',$app->input->getString('filter_vat'));
        // Get the pagination request variables
        $limit		= $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->getCfg('list_limit'), 'int' );
        $limitstart	= $app->getUserStateFromRequest( $ns.'.limitstart', 'limitstart', 0, 'int' );

        // In case limit has been changed, adjust limitstart accordingly
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
        $model->setState('list.limit', $limit);
        $model->setState('list.start', $limitstart);
        $model->setState('limit', $limit);
        $model->setState('limitstart', $limitstart);
        return $model;
    }


    //export csv
    function onJ2StoreGetReportExported($row){
        if (!($this->_isMe($row)))
        {
            return null;
        }
        $fof_helper = J2Store::fof();
        $model = $fof_helper->getModel('ReportProducts','J2StoreModel');
        $model = $this->getProductList ( $model );
        $items = $model->getList();
        $name = JText::_('PLG_J2STORE_PRODUCT_NAME');
        $quantity = JText::_('J2STORE_REPORT_TOTAL_QUANTITY');
        $discount_text = JText::_('J2STORE_REPORT_PRODUCT_DISCOUNT');
        $total_tax_text = JText::_('J2STORE_REPORT_PRODUCT_TAX');
        $without_tax = JText::_('J2STORE_REPORT_PRODUCT_WITHOUT_TAX');
        $with_tax =JText::_('J2STORE_REPORT_PRODUCT_WITH_TAX');
        $total_text = JText::_('J2STORE_TOTAL');
        $currency = J2Store::currency ();
        $export = array();
        $qty_total = 0;
        $discount_total = 0;
        $total_without_tax = 0;
        $total_with_tax = 0;
        $total_tax = 0;
            foreach ($items as $item) {
                $qty_total += $item->total_qty;
                $discount_total += $item->total_item_discount + $item->total_item_discount_tax;
                $total_without_tax += $item->total_final_price_without_tax;
                $total_with_tax += $item->total_final_price_with_tax;
                $total_tax += $item->total_item_tax;
                $sample = new \stdClass();
                $sample->$name = $item->orderitem_name . ', ' . JText::_('J2STORE_SKU') . ': ' . $item->orderitem_sku;
                $sample->$quantity = $item->total_qty;
                $sample->$discount_text = $currency->format($item->total_item_discount + $item->total_item_discount_tax);
                $sample->$total_tax_text = $currency->format($item->total_item_tax);
                $sample->$without_tax = $currency->format($item->total_final_price_without_tax);
                $sample->$with_tax = $currency->format($item->total_final_price_with_tax);
                $export[] = $sample;
            }
            $final_data = new \stdClass();
            $final_data->$name = $total_text;
            $final_data->$quantity = $qty_total;
            $final_data->$discount_text = $currency->format($discount_text);
            $final_data->$total_tax_text = $currency->format($total_tax_text);
            $final_data->$without_tax = $currency->format($total_without_tax);
            $final_data->$with_tax = $currency->format($total_with_tax);
            $export[] = $final_data;
        return $export;
    }

    /**
     * Method to get order status
     */
    public function getOrderStatus(){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select("*")->from("#__j2store_orderstatuses");
        $db->setQuery($query);
        $row = $db->loadObjectList();
        $data =array();
        foreach($row as $item){

            $data[$item->j2store_orderstatus_id] = JText::_($item->orderstatus_name);
        }
        return $data;
    }
    //search order by days type
    public function getOrderDateType(){
        return array(
            'select' =>JText::_('J2STORE_DAY_TYPES'),
            'today' => JText::_('J2STORE_TODAY'),
            'this_week' => JText::_('J2STORE_THIS_WEEK'),
            'this_month' => JText::_('J2STORE_THIS_MONTH'),
            'this_year' => JText::_('J2STORE_THIS_YEAR'),
            'last_7day' => JText::_('J2STORE_LAST_7_DAYS'),
            'last_month' => JText::_('J2STORE_LAST_MONTH'),
            'last_year' => JText::_('J2STORE_LAST_YEAR'),
            'custom' => JText::_('J2STORE_CUSTOM')
        );
    }
    //tax type
    public function getVat(){
        return array(
            'select' =>JText::_('J2STORE_TAX_TYPE'),
            'with_tax' => JText::_('J2STORE_WITH_TAX'),
            'without_tax' => JText::_('J2STORE_WITHOUT_TAX')
        );
    }
    //search filter type
    public function getFilterType(){
        return array(
            'order' => 'By Order',
            'category' => 'By Category',
            'product' => 'By Product'
        );
    }
}