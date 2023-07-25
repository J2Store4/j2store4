<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';
class J2StoreControllerTaxrates extends F0FController
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
        $vars->primary_key = 'j2store_taxrate_id';
        $vars->id = $this->getPageId();
        $taxrate_table = F0FTable::getInstance('Taxrate', 'J2StoreTable')->getClone ();
        $taxrate_table->load($vars->id);
        $vars->item = $taxrate_table;
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
            'label' => 'J2STORE_TAXRATE',
            'fields' => array(
                'taxrate_name' => array(
                    'label' => 'J2STORE_TAXRATE_NAME',
                    'type' => 'text',
                    'name' => 'taxrate_name',
                    'value' => $taxrate_table->taxrate_name,
                    'options' => array('required' => 'true','class' => 'input-xlarge')
                ),
                'tax_percent' => array(
                    'label' => 'J2STORE_TAXRATE_PERCENT',
                    'type' => 'text',
                    'name' => 'tax_percent',
                    'value' => $taxrate_table->tax_percent,
                    'options' => array('required' => 'true','class' => 'input-xlarge')
                ),
                'geozone_id' => array(
                    'label' => 'J2STORE_TAXRATE_GEOZONE_NAME_LABEL',
                    'type' => 'fieldsql',
                    'name' => 'geozone_id',
                    'value' => $taxrate_table->geozone_id,
                    'options' => array('required' => 'true', 'id' => 'j2store_geozone_id', 'key_field' => 'j2store_geozone_id', 'value_field' => 'geozone_name', 'has_one' => 'geozone')
                ),
                'enabled' => array(
                    'label' => 'J2STORE_ENABLED',
                    'type' => 'enabled',
                    'name' => 'enabled',
                    'value' => $taxrate_table->enabled,
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
        $state['taxrate_name'] = $app->input->getString('taxrate_name','');
        $state['tax_percent'] = $app->input->getString('tax_percent','');
        $state['filter_order']= $app->input->getString('filter_order','j2store_taxrate_id');
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
            'j2store_taxrate_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '20',
                'label' => 'J2STORE_TAXRATE_ID'
            ),
            'taxrate_name' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'show_link' => 'true',
                'url' => "index.php?option=com_j2store&amp;view=taxrate&amp;id=[ITEM:ID]",
                'url_id' => 'j2store_taxrate_id',
                'label' => 'J2STORE_TAXRATE_NAME'
            ),
            'tax_percent' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'label' => 'J2STORE_TAXRATE_PERCENT'
            ),
            'geozone_id' => array(
                'type' => 'fieldsql',
                'sortable' => 'true',
                'key_field' => 'j2store_geozone_id',
                'value_field' => 'geozone_name',
                'query'  => 'SELECT * FROM #__j2store_geozones',
                'label' => 'J2STORE_TAXRATE_GEOZONE_NAME_LABEL'
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