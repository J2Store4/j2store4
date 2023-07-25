<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';
class J2StoreControllerOrderstatuses extends F0FController
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
        $vars->primary_key = 'j2store_orderstatus_id';
        $vars->id = $this->getPageId();
        $orderstatus_table = F0FTable::getInstance('Orderstatus', 'J2StoreTable')->getClone ();
        $orderstatus_table->load($vars->id);
        $vars->item = $orderstatus_table;
        $vars->field_sets = array();
        $col_class = 'col-md-';
        if (version_compare(JVERSION, '3.99.99', 'lt')) {
            $col_class = 'span';
        }
        $vars->field_sets[] = array(
            'id' => 'basic_information',
            'class' => array(
                $col_class.'12'
            ),
            'fields' => array(
                'orderstatus_name' => array(
                    'label' => 'J2STORE_ORDERSTATUS_NAME',
                    'type' => 'text',
                    'name' => 'orderstatus_name',
                    'value' => $orderstatus_table->orderstatus_name,
                    'options' => array('required' => 'true','class' => 'inputbox')
                ),
                'orderstatus_cssclass' => array(
                    'label' => 'J2STORE_ORDERSTATUS_LABEL',
                    'type' => 'text',
                    'name' => 'orderstatus_cssclass',
                    'value' => $orderstatus_table->orderstatus_cssclass,
                    'options' => array('class' => 'inputbox')
                ),
                'enabled' => array(
                    'label' => 'J2STORE_ENABLED',
                    'type' => 'enabled',
                    'name' => 'enabled',
                    'value' => $orderstatus_table->enabled,
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
        $state['orderstatus_name'] = $app->input->getString('orderstatus_name','');
        $state['orderstatus_cssclass'] = $app->input->getString('orderstatus_cssclass','');
        $state['filter_order']= $app->input->getString('filter_order','j2store_orderstatus_id');
        $state['filter_order_Dir']= $app->input->getString('filter_order_Dir','ASC');
        foreach($state as $key => $value){
            $model->setState($key,$value);
        }
        $items = $model->getList();
        $vars = $this->getBaseVars();
        $vars->edit_view = 'orderstatus';
        $vars->model = $model;
        $vars->items = $items;
        $vars->state = $model->getState();
        $option = $app->input->getCmd('option', 'com_foobar');
        $subtitle_key = strtoupper($option . '_TITLE_' . $app->input->getCmd('view', 'cpanel'));
        JToolBarHelper::title(JText::_(strtoupper($option)) . ': ' . JText::_($subtitle_key), str_replace('com_', '', $option));
        JToolBarHelper::addNew();
        JToolBarHelper::editList();
        $msg = JText::_($option . '_CONFIRM_DELETE');
        JToolBarHelper::deleteList(strtoupper($msg));
        $header = array(
            'j2store_orderstatus_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '10',
                'label' => 'J2STORE_ORDERSTATUS_ID'
            ),
            'orderstatus_name' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'show_link' => 'true',
                'url' => "index.php?option=com_j2store&amp;view=orderstatus&amp;task=edit&amp;id=[ITEM:ID]",
                'url_id' => 'j2store_orderstatus_id',
                'label' => 'J2STORE_ORDERSTATUS_NAME',
                'translate' => false
            ),
            'orderstatus_cssclass' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'tdwidth' => '8%',
                'label' => 'J2STORE_ORDERSTATUS_LABEL'
            ),
            'orderstatus_core' => array(
                'type' => 'corefieldtypes',
                'sortable' => 'true',
                'label' => 'J2STORE_ORDERSTATUS_CORE'
            )
        );
        $this->setHeader($header,$vars);
        $vars->pagination = $model->getPagination();
        echo $this->_getLayout('default',$vars);
    }

}
