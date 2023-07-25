<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR . '/components/com_j2store/library/plugins/report.php');

class plgJ2StoreReport_itemised extends J2StoreReportPlugin
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element = 'report_itemised';

    /**
     * Overriding
     *
     * @param $row
     * @return string
     * @throws Exception
     */
    function onJ2StoreGetReportView($row)
    {
        if (!$this->_isMe($row)) {
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
     * @throws Exception
     */
    function viewList()
    {
        $app = self::$platform->application();
        JToolBarHelper::title(JText::_('J2STORE_REPORT') . '-' . JText::_('PLG_J2STORE_' . strtoupper($this->_element)), 'j2store-logo');
        $vars = new \stdClass();
        $model = self::$fof_helper->getModel('ReportItemised', 'J2StoreModel');
        $model->setState('limit', $app->input->getInt('limit', 0));
        $model->setState('limitstart', $app->input->getInt('limitstart', 0));
        $model->setState('filter_search', $app->input->getString('filter_search'));
        $model->setState('filter_orderstatus', $app->input->getString('filter_orderstatus'));
        $model->setState('filter_order', $app->input->getString('filter_order', 'oi.j2store_orderitem_id'));
        $model->setState('filter_order_Dir', $app->input->getString('filter_order_Dir'));
        $model->setState('filter_datetype', $app->input->getString('filter_datetype'));
        try {
            $list = $model->getData();
            $vars->list = $list;
            $vars->total = $model->getTotal();
            $vars->total = 0;
        } catch (Exception $e) {
            $vars->list = array();
        }
        $vars->state = $model->getState();
        $vars->pagination = $model->getPagination();
        $order_status_model = self::$fof_helper->getModel('OrderStatuses', 'J2StoreModel',array('enabled' => 1));
        $vars->orderStatus = $order_status_model->getList();
        $vars->orderDateType = $this->getOrderDateType();
        $vars->id = $app->input->getInt('id', 0);
        $form = array();
        $form['action'] = "index.php?option=com_j2store&view=report&task=view&id={$vars->id}";
        $vars->form = $form;
        return $this->_getLayout('default', $vars);
    }

    //search order by days type
    public function getOrderDateType()
    {
        return array(
            'select' => JText::_('J2STORE_DAY_TYPES'),
            'today' => JText::_('J2STORE_TODAY'),
            'this_week' => JText::_('J2STORE_THIS_WEEK'),
            'this_month' => JText::_('J2STORE_THIS_MONTH'),
            'this_year' => JText::_('J2STORE_THIS_YEAR'),
            'last_7day' => JText::_('J2STORE_LAST_7_DAYS'),
            'last_month' => JText::_('J2STORE_LAST_MONTH'),
            'last_year' => JText::_('J2STORE_LAST_YEAR')
        );
    }

    function onJ2StoreGetReportExported($row)
    {
        $ignore_column = array('sum', 'count', 'orderitem_quantity', 'product_source_id', 'id');
        if (!$this->_isMe($row)) {
            return null;
        }
        $model = self::$fof_helper->getModel('ReportItemised', 'J2StoreModel');
        $items = $model->getData();
        foreach ($items as &$item) {
            $item->orderitem_options = '';
            if (isset($item->orderitem_attributes) && $item->orderitem_attributes) {
                foreach ($item->orderitem_attributes as $attr) {
                    unset($item->orderitem_attributes);
                    $item->orderitem_options .= $attr->orderitemattribute_name . ' : ' . $attr->orderitemattribute_value;
                }
            }
            $item->qty = $item->sum;
            $item->total_purchase = $item->count;

            foreach ($ignore_column as $key => $value) {
                unset($item->$value);
            }
        }
        return $items;
    }


}

