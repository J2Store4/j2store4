<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';
class J2storeControllerPayments extends F0FController
{
    use list_view;
	public function __construct($config) {
		parent::__construct($config);
		$this->registerTask('apply', 'save');
		$this->registerTask('saveNew', 'save');
	}

    public function browse()
    {
        $platform = J2Store::platform();
        $app = $platform->application();
        $model = $this->getThisModel();
        $state = array();
        $state['name'] = $app->input->getString('name','');
        $state['filter_order']= $app->input->getString('filter_order','extension_id');
        $state['filter_order_Dir']= $app->input->getString('filter_order_Dir','ASC');
        foreach($state as $key => $value){
            $model->setState($key,$value);
        }
        $items = $model->getList();
        $vars = $this->getBaseVars();
        $vars->model = $model;
        $vars->items = $items;
        $this->toolbarBacktodashboard();
        $vars->state = $model->getState();


        $header = array(
            'extension_id' => array(
                'type' => 'rowselect',
                'tdwidth' => '20',
                'label' => 'J2STORE_PAYMENT_ID'
            ),
            'name' => array(
                'type' => 'fieldsearchable',
                'sortable' => 'true',
                'show_link' =>'true',
                'url' => "index.php?option=com_plugins&amp;view=plugins&amp;task=plugin.edit&amp;extension_id=[ITEM:ID]",
                'url_id' => 'extension_id',
                'label' => 'J2STORE_PAYMENT_PLUGIN_NAME'
            ),
            'version' => array(
                'type' => 'field',
                'label' => 'J2STORE_PLUGIN_VERSION'
            ),
            'enabled' => array(
                'type' => 'published',
                'sortable' => 'true',
                'label' => 'J2STORE_ENABLED'
            )

        );

        $this->setHeader($header,$vars);
        $vars->pagination = $model->getPagination();
        $vars->extra_content = $this->_getLayout('payment_info',$vars);
        echo $this->_getLayout('default',$vars);
    }

    public function save()
    {
        $platform = J2Store::platform();
        $fof_helper = J2Store::fof();
        $task = $this->getTask();
        // get the Application Object
        $app = $platform->application();
        // get the payment id
        $payment_id = $app->input->getInt('extension_id');

        // if payment id exists
        if($payment_id)
        {
            $data = $app->input->getArray($_POST);

            $paymentdata = array();

            $paymentdata['extension_id']=$payment_id;
            $registry = $platform->getRegistry($data,true);
            $paymentdata['params'] = $registry->toString('JSON');
            try {
                $fof_helper->loadTable('Payment' ,'J2StoreTable')->save($paymentdata);
            }catch (Exception $e) {
                $msg = $e->getMessage();
            }
            switch($task)
            {
                case 'apply':
                    parent::apply();
                    break;
                case 'save':
                    parent::save();
                    break;
                case  'savenew':
                    parent::savenew();
                    break;
            }
        }
    }
}