<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';
class J2StoreControllerVendors extends F0FController
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
        $vars->primary_key = 'j2store_vendor_id';
        $vars->id = $this->getPageId();
        $vendor_table = F0FTable::getInstance('Vendor', 'J2StoreTable')->getClone ();
        $vendor_table->load($vars->id);
        $vars->item = $vendor_table;
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
            'label' => 'J2STORE_VENDOR_GENERAL_INFORMATION',
            'fields' => array(
                'first_name' => array(
                    'label' => 'J2STORE_ADDRESS_FIRSTNAME',
                    'type' => 'text',
                    'name' => 'first_name',
                    'value' => $vendor_table->first_name,
                    'options' => array('required' => 'true','class' => 'input-xlarge')
                ),
                'last_name' => array(
                    'label' => 'J2STORE_ADDRESS_LASTNAME',
                    'type' => 'text',
                    'name' => 'last_name',
                    'value' => $vendor_table->last_name,
                    'options' => array('required' => 'true','class' => 'input-xlarge')
                ),
                'user_id' => array(
                    'label' => 'J2STORE_LINKED_USER',
                    'type' => 'user',
                    'name' => 'user_id',
                    'value' => $vendor_table->user_id,
                    'options' => array('required' => 'true','class' => 'input-xlarge')
                ),
                'address_1' => array(
                    'label' => 'J2STORE_ADDRESS_LINE1',
                    'type' => 'text',
                    'name' => 'address_1',
                    'value' => $vendor_table->address_1,
                    'options' => array('required' => 'true','class' => 'input-xlarge')
                ),
                'address_2' => array(
                    'label' => 'J2STORE_ADDRESS_LINE2',
                    'type' => 'text',
                    'name' => 'address_2',
                    'value' => $vendor_table->address_2,
                    'options' => array('class' => 'input-xlarge')
                ),
                'city' => array(
                    'label' => 'J2STORE_ADDRESS_CITY',
                    'type' => 'text',
                    'name' => 'city',
                    'value' => $vendor_table->city,
                    'options' => array('required' => 'true','class' => 'input-xlarge')
                ),
                'zip' => array(
                    'label' => 'J2STORE_ADDRESS_ZIP',
                    'type' => 'text',
                    'name' => 'zip',
                    'value' => $vendor_table->zip,
                    'options' => array('required' => 'true','class' => 'input-xlarge')
                ),
                'phone_1' => array(
                    'label' => 'J2STORE_ADDRESS_PHONE',
                    'type' => 'text',
                    'name' => 'phone_1',
                    'value' => $vendor_table->phone_1,
                    'options' => array('class' => 'input-xlarge')
                ),
                'phone_2' => array(
                    'label' => 'J2STORE_ADDRESS_MOBILE',
                    'type' => 'text',
                    'name' => 'phone_2',
                    'value' => $vendor_table->phone_2,
                    'options' => array('class' => 'input-xlarge')
                ),
                'email' => array(
                    'label' => 'J2STORE_EMAIL',
                    'type' => 'email',
                    'name' => 'email',
                    'value' => $vendor_table->email,
                    'options' => array('class' => 'input-xlarge')
                ),
            ),
        );
        $vars->field_sets[] = array(
            'id' => 'advanced_information',
            'class' => array(
                $col_class.'6'
            ),
            'label' => 'J2STORE_VENDOR_ADVANCED_INFORMATION',
            'fields' => array(
                'company' => array(
                    'label' => 'J2STORE_ADDRESS_COMPANY_NAME',
                    'type' => 'text',
                    'name' => 'company',
                    'value' => $vendor_table->company,
                    'options' => array('class' => 'input-xlarge')
                ),
                'tax_number' => array(
                    'label' => 'J2STORE_ADDRESS_TAX_NUMBER',
                    'type' => 'text',
                    'name' => 'tax_number',
                    'value' => $vendor_table->tax_number,
                    'options' => array('class' => 'input-xlarge')
                ),
                'country_id' => array(
                    'label' => 'J2STORE_ADDRESS_COUNTRY',
                    'type' => 'country',
                    'name' => 'country_id',
                    'value' => $vendor_table->country_id,
                    'options' => array('class' => 'input-xlarge','id' => 'country_id','zone_id' => 'zone_id','zone_value' => empty($vendor_table->zone_id) ? 1:$vendor_table->zone_id)
                ),
                'zone_id' => array(
                    'label' => 'J2STORE_ADDRESS_ZONE',
                    'type' => 'zone',
                    'name' => 'zone_id',
                    'value' => empty($vendor_table->zone_id) ? 1:$vendor_table->zone_id,
                    'options' => array('class' => 'input-xlarge','id' => 'zone_id')
                ),
                'enabled' => array(
                    'label' => 'J2STORE_ENABLED',
                    'type' => 'enabled',
                    'name' => 'enabled',
                    'value' => $vendor_table->enabled,
                    'options' => array('class' => 'input-xlarge')
                ),
            )
        );
        echo $this->_getLayout('form', $vars,'edit');
        echo '<script>
            jQuery("#country_id").trigger("change");
			jQuery("#zone_id").trigger("liszt:updated");
             </script>';
    }

    public function browse()
    {
        $app = JFactory::getApplication();
        $model = $this->getThisModel();
        $state = array();
        $state['first_name'] = $app->input->getString('first_name', '');
        $state['filter_order'] = $app->input->getString('filter_order', 'j2store_vendor_id');
        $state['filter_order_Dir'] = $app->input->getString('filter_order_Dir', 'ASC');
        foreach ($state as $key => $value) {
            $model->setState($key, $value);
        }
        $items = $model->getList();
        $vars = $this->getBaseVars();
        $vars->model = $model;
        $vars->items = $items;
        $vars->state = $model->getState();
        $this->addBrowseToolBar();
        $header = array(
            'j2store_vendor_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '20',
                'label' => 'J2STORE_VENDOR_ID'
            ),
            'first_name' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'show_link' => 'true',
                'url' => "index.php?option=com_j2store&amp;view=vendor&amp;id=[ITEM:ID]",
                'url_id' => 'j2store_vendor_id',
                'label' => 'J2STORE_VENDOR_NAME_LABEL'
            ),
            'city' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_ADDRESS_CITY'
            ),
            'country_name' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_ADDRESS_COUNTRY'
            ),
            'zone_name' => array(
                'sortable' => 'true',
                'label' => 'J2STORE_ADDRESS_ZONE'
            ),
            'enabled' => array(
                'type' => 'published',
                'sortable' => 'true',
                'label' => 'J2STORE_ENABLED'
            )
        );
        $this->setHeader($header,$vars);
        $vars->pagination = $model->getPagination();
        echo $this->_getLayout('default', $vars);
    }
}
