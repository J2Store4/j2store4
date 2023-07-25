<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';
class J2StoreControllerWeights extends F0FController
{
    use list_view;
    public function execute($task) {
        if(in_array($task, array('edit', 'add'))) {
            $task = 'add';
        }
        return parent::execute($task);
    }
    function add()
    {
        $platform = J2Store::platform();
        $app = $platform->application();
        $vars = $this->getBaseVars();
        $this->editToolBar();
        $vars->primary_key = 'j2store_weight_id';
        $vars->id = $this->getPageId();
        $weight_table = F0FTable::getInstance('Weight', 'J2StoreTable')->getClone ();
        $weight_table->load($vars->id);
        $vars->item = $weight_table;
        $vars->field_sets = array();
        $col_class = 'col-md-';
        if (version_compare(JVERSION, '3.99.99', 'lt')) {
            $col_class = 'span';
        }
        $vars->field_sets[] = array(
            'id' => 'basic_information',
            'class' => array(
                $col_class.'6'
            ),
            'fields' => array(
                'weight_title' => array(
                    'label' => 'J2STORE_WEIGHT_TITLE_LABEL',
                    'type' => 'text',
                    'name' => 'weight_title',
                    'value' => $weight_table->weight_title,
                    'options' => array('required' => 'true','class' => 'inputbox')
                ),
                'weight_unit' => array(
                    'label' => 'J2STORE_WEIGHT_UNIT_LABEL',
                    'type' => 'text',
                    'name' => 'weight_unit',
                    'value' => $weight_table->weight_unit,
                    'options' => array('required' => 'true','class' => 'inputbox')
                ),
                'weight_value' => array(
                    'label' => 'J2STORE_WEIGHT_VALUE_LABEL',
                    'type' => 'number',
                    'name' => 'weight_value',
                    'value' => (float)$weight_table->weight_value,
                    'options' => array('required' => 'true','class' => 'inputbox')
                ),
                'enabled' => array(
                    'label' => 'J2STORE_ENABLED',
                    'type' => 'enabled',
                    'name' => 'enabled',
                    'value' => $weight_table->enabled,
                    'options' => array('class' => 'input-xlarge')
                ),
            )
        );
        echo $this->_getLayout('form', $vars,'edit');
    }
    public function browse()
    {
        $app = JFactory::getApplication();
        $model = $this->getThisModel();
        $state = array();
        $state['weight_title'] = $app->input->getString('weight_title','');
        $state['filter_order']= $app->input->getString('filter_order','j2store_weight_id');
        $state['filter_order_Dir']= $app->input->getString('filter_order_Dir','ASC');
        foreach($state as $key => $value){
            $model->setState($key,$value);
        }
        $items = $model->getList();
        $vars = $this->getBaseVars();
        $vars->model = $model;
        $vars->items = $items;
        $vars->state = $model->getState();
        $this->addBrowseToolBar();
        $header = array(
            'j2store_weight_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '20',
                'label' => 'J2STORE_WEIGHT_ID'
            ),
            'weight_title' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'show_link' => 'true',
                'url' => "index.php?option=com_j2store&amp;view=weight&amp;id=[ITEM:ID]",
                'url_id' => 'j2store_weight_id',
                'label' => 'J2STORE_WEIGHT_TITLE_LABEL'
            ),
            'weight_unit' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_WEIGHT_UNIT_LABEL'
            ),
            'weight_value' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_WEIGHT_VALUE_LABEL'
            ),
            'enabled' => array(
                'type' => 'published',
                'sortable' => 'true',
                'label' => 'J2STORE_ENABLED'
            )
        );
        $this->setHeader($header,$vars);
        $vars->pagination = $model->getPagination();
        echo $this->_getLayout('default',$vars);
    }

}