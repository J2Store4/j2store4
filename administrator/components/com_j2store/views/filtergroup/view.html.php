<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class J2StoreViewFilterGroup extends F0FViewHtml
{
    protected function onAdd($tpl = null)
    {
        $app = JFactory::getApplication();
        $group_id = $app->input->get('id',0);
        $task = $app->input->get('task','');
        if ($task == 'edit') {
            if (empty($group_id)) {
                $cid = $app->input->get('cid', array());
                $group_id = isset($cid[0]) && !empty($cid[0]) ? $cid[0] : 0;
            }
        }
        $productfilter = F0FTable::getAnInstance('Filtergroup','J2StoreTable')->getClone();
        $fitervalue_model = F0FModel::getTmpInstance('Filtergroups','J2StoreModel');
        $fitervalues = array();
        
        if($productfilter->load($group_id)){
            $fitervalue_model->setState('filter_group_id',$group_id);
            $limit = $app->input->get('filterlimit',20);
            $fitervalue_model->setState('filters.list.limit',$limit);
            
            $limit_start = $app->input->get('filterlimitstart',0);
            $fitervalue_model->setState('filters.list.start',$limit_start);
            $fitervalues = $fitervalue_model->getSFProducts();
        }

        $this->item = $productfilter;
        $this->filtervalues = $fitervalues;
        $this->filter_pagination = $fitervalue_model->getSFPagination();
        return true;
    }
}