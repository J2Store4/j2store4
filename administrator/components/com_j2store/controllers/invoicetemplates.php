<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';


class J2StoreControllerInvoicetemplates extends F0FController {

    use list_view;
    public function execute($task) {
        if (in_array($task, array('edit', 'add'))) {
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
        $vars->primary_key = ' j2store_invoicetemplate_id';
        $vars->id = $this->getPageId();
        $Invoicetemplate_table = F0FTable::getInstance('Invoicetemplate', 'J2StoreTable')->getClone ();
        $Invoicetemplate_table->load($vars->id);
        $vars->item = $Invoicetemplate_table;
        $vars->field_sets = array();

        $order_status_model = F0FModel::getTmpInstance('Orderstatuses', 'J2StoreModel');
        $default_order_status_list = $order_status_model->enabled(1)->getList();
        $order_status = array();
        $order_status['*'] = JText::_('JALL');
        foreach ($default_order_status_list as $status) {
            $order_status[$status->j2store_orderstatus_id] = JText::_(strtoupper($status->orderstatus_name));
        }

        $payment_model = F0FModel::getTmpInstance('Payments', 'J2StoreModel');
        $default_payment_list = $payment_model->enabled(1)->getList();
        $payment_list = array();
        $payment_list['*'] = JText::_('JALL');
        $payment_list['free'] = JText::_('J2STORE_FREE_PAYMENT');
        foreach ($default_payment_list as $payment) {
            $payment_list[$payment->element] = JText::_(strtoupper($payment->element));
        }


        $groupList = JHtmlUser::groups ();
        $group_options = array();
        $group_options [''] =  JText::_ ( 'JALL' ) ;
        foreach ( $groupList as $row ) {
            $group_options [  $row->value ] = JText::_ ( $row->text ) ;
        }


        $languages = JLanguageHelper::getLanguages ( );
        $language_list = array ();
        $language_list ['*'] = JText::_ ( 'JALL_LANGUAGE' ) ;
        foreach ( $languages as  $lang ) {
            $language_list [$lang->lang_code] =JText ::_ ( strtoupper( $lang->title_native));
        }


        $vars->field_sets[] = array(
            'id' => 'basic_options',
            'label'  => 'J2STORE_BASIC_OPTIONS',
            'fields' => array(
                'title' => array(
                    'label' => 'J2STORE_INVOICETEMPLATE_TITLE_LABEL',
                    'type' => 'text',
                    'name' => 'title',
                    'value' => $Invoicetemplate_table->title,
                    'options' => array('class' => 'input-xlarge','required'=> true)
                ),
                'orderstatus_id' => array(
                    'label' => 'J2STORE_EMAILTEMPLATE_ORDERSTATUS',
                    'type' => 'list',
                    'name' => 'orderstatus_id',
                    'value' => isset($vars->item->orderstatus_id) && !is_null($vars->item->orderstatus_id) ? $vars->item->orderstatus_id : '*',
                    'options' => array( 'translate' => false,'options' => $order_status  ),
                    'desc' => 'J2STORE_EMAILTEMPLATE_ORDERSTATUS_DESC'
                ),
                'language' => array(
                    'label' => 'JFIELD_LANGUAGE_LABEL',
                    'type' => 'list',
                    'default' => 'en-GB',
                    'name' => 'language',
                    'desc' => 'J2STORE_INVOICETEMPLATE_LANGUAGE_DESC',
                    'value' => isset($vars->item->language) && !is_null($vars->item->language) ? $vars->item->language : '*',
                    'options' => array( 'options' => $language_list )
                ),
                'group_id' => array(
                    'label' => 'J2STORE_INVOICETEMPLATE_GROUPS',
                    'type' => 'list',
                    'name' => 'group_id',
                    'default' => '',
                    'value' => isset($vars->item->group_id) && !is_null($vars->item->group_id) ? $vars->item->group_id : '',
                    'options' => array('options' => $group_options),
                    'desc' => 'J2STORE_INVOICETEMPLATE_GROUPS_DESC'
                ),
                'paymentmethod' => array(
                    'label' => 'J2STORE_INVOICETEMPLATE_PAYMENTMETHODS',
                    'type' => 'list',
                    'name' => 'paymentmethod',
                    'value' => isset($vars->item->paymentmethod) && !is_null($vars->item->paymentmethod) ? $vars->item->paymentmethod : '*',
                    'options' => array('options' => $payment_list),
                    'desc' => 'J2STORE_INVOICETEMPLATE_PAYMENTMETHODS_DESC'
                ),
                'enabled' => array(
                    'label' => 'J2STORE_ENABLED',
                    'type' => 'enabled',
                    'name' => 'enabled',
                    'value' => $Invoicetemplate_table->enabled,
                    'options' => array('class' => 'input-xlarge')
                ),
            )
        );
        $vars->field_sets[] = array(
            'id' => 'advanced_information',
            'label' => 'J2STORE_ADVANCED_SETTINGS',
            'fields' => array(
                'body' => array(
                    'label' => 'J2STORE_EMAILTEMPLATE_BODY_LABEL',
                    'type' => 'editor',
                    'name' => 'body',
                    'value' => $Invoicetemplate_table->body,
                    'options' =>array('class' => 'input-xlarge','buttons' => true,'required'=> true)
                ),
            )
        );
        echo $this->_getLayout('email_tab', $vars , 'edit');
    }
    public function browse()
    {
        $app = J2Store::platform()->application();
        $model = F0FModel::getTmpInstance('Invoicetemplate', 'J2StoreModel');
        //Toolbar
        if(J2Store::isPro()) {
            $this->addBrowseToolBar();
        }else {
            $this->noToolbar();
        }
        $state = array();
        $state['option_name'] = $app->input->getString('option_name','');
        $state['filter_order']= $app->input->getString('filter_order','j2store_invoicetemplate_id');
        $state['filter_order_Dir']= $app->input->getString('filter_order_Dir','ASC');
        foreach($state as $key => $value){
         ( $model->setState($key,$value)) ;
        }
        $items = $model->getList();
        $vars = $this->getBaseVars();
        $vars->edit_view = 'invoicetemplates';
        $vars->model = $model;
        $vars->items = $items;
        $vars->state = $model->getState();
        $header = array(
            'j2store_invoicetemplate_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '20',
                'label' => 'J2STORE_INVOICETEMPLATE_ID'
            ),
            'title' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'show_link' => 'true',
                'url' => "index.php?option=com_j2store&amp;view=invoicetemplates&amp;task=edit&amp;id=[ITEM:ID]",
                'url_id' => 'j2store_invoicetemplate_id',
                'label' => 'J2STORE_INVOICETEMPLATE_TITLE_LABEL'
            ),
            'language' => array(
                'type' => 'text',
                'sortable' => 'true',
                'label' => 'JFIELD_LANGUAGE_LABEL'
            ),
            'orderstatus_id' => array(
                'type' => 'orderstatuslist',
                'sortable' => 'true',
                'label' => 'J2STORE_INVOICETEMPLATE_ORDERSTATUS'
            ),

            'group_id' => array(
                'type' => 'fieldsql',
                'query' => 'SELECT * FROM #__usergroups',
                'key_field' => 'id',
                'value_field' => 'title',
                'sortable' => 'true',
                'translate' => 'false',
                'label' => 'J2STORE_INVOICETEMPLATE_GROUPS'
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
