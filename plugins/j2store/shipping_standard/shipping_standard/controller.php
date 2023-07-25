<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/shippingcontroller.php');
class J2StoreControllerShippingStandard extends J2StoreControllerShippingPlugin
{
	var $_element   = 'shipping_standard';
	/**
	 * constructor
	 */
	function __construct()
	{
		parent::__construct();
		J2Store::platform()->application()->getLanguage()->load('plg_j2store_'.$this->_element, JPATH_ADMINISTRATOR);
		$this->registerTask( 'newMethod', 'view' );
		$this->registerTask('apply', 'save');
	}

    /**
     * Gets the plugin's namespace for state variables
     * @return string
     * @throws Exception
     */
	function getNamespace()
	{
        return J2Store::platform()->application()->getLanguage()->getName().'::'.'com.j2store.plugin.shipping.standard';
	}

	function publish() {
        $app = J2Store::platform()->application();
        $fof_helper = J2Store::fof();
		$return = 0;
		$post = $app->input->getArray($_POST);
		$table = $fof_helper->loadTable('ShippingMethods', 'J2StoreTable');
		if($table->load($post['smid']) && $table->j2store_shippingmethod_id == $post['smid']) {
			if($table->published == 1) {
				$table->published = 0;
			}elseif($table->published == 0) {
				$table->published = 1;
				$return = 1;
			}
			$table->store();
		}
		echo $return;
        $app->close();
	}

	function save(){
        $platform = J2Store::platform();
        $app = $platform->application();
        $fof_helper = J2Store::fof();
		$sid = $app->input->getInt('j2store_shippingmethod_id');
		$values = $app->input->getArray($_POST);
		$table = $fof_helper->loadTable('ShippingMethod', 'J2StoreTable');
        $ship_method_data = array(
            'shipping_method_name' => isset($values['shipping_method_name']) && !empty($values['shipping_method_name']) ? $values['shipping_method_name']: '',
            'published' => isset($values['published']) ? (int)$values['published']: 0,
            'shipping_method_type' => isset($values['shipping_method_type']) ? (int)$values['shipping_method_type']: 0,
            'address_override' => isset($values['address_override']) && !empty($values['address_override']) ? $values['address_override']: 'no',
            'tax_class_id' => isset($values['tax_class_id']) ? (int)$values['tax_class_id']: 0,
            'subtotal_minimum' => isset($values['subtotal_minimum']) ? (float)$values['subtotal_minimum']: 0.0,
            'subtotal_maximum' => isset($values['subtotal_maximum']) ? (float)$values['subtotal_maximum']: 0.0
        );
		if($table->load ($sid)){
            $ship_method_data['j2store_shippingmethod_id'] = $table->j2store_shippingmethod_id;
        }
        $params = $platform->getRegistry($table->params);
		if(isset( $values['shipping_select_text'] )){
			$params->set('shipping_select_text',$values['shipping_select_text']);
		}
		if(isset( $values['shipping_price_based_on'] )){
			$params->set('shipping_price_based_on',$values['shipping_price_based_on']);
		}
        $ship_method_data['params'] = $params->toString();
		$table->bind($ship_method_data);
		try {
			$table->store ();
			$link = $this->baseLink();
			$this->messagetype 	= 'message';
			$this->message  	= JText::_('J2STORE_ALL_CHANGES_SAVED');
		} catch(\Exception $e) {
			$link = $this->baseLink().'&shippingTask=view&sid='.$sid;
			$this->messagetype 	= 'error';
			$this->message 		= JText::_('J2STORE_SAVE_FAILED').$e->getMessage();
		}
		if($this->getTask() =='apply') $link = $this->baseLink().'&shippingTask=view&sid='.$table->j2store_shippingmethod_id;
		$redirect = JRoute::_( $link, false );
        $platform->redirect( $redirect, $this->message, $this->messagetype );
	}


	function setRates()
	{
        $platform = J2Store::platform();
        $app = $platform->application();
        $fof_helper = J2Store::fof();
		$sid = $app->input->getInt('sid');
		$shipping_method = $fof_helper->loadTable('ShippingMethod', 'J2StoreTable');
        $shipping_method->load($sid);
		$model  = $fof_helper->getModel('ShippingRates', 'J2StoreModel');
		//set the shipping method id
		$model->set('filter_shippingmethod' , $sid);
		$items = $model->getList();
		//form
		$form = array();
		$form['action'] = $this->baseLink();
		JToolBarHelper::title(JText::_('J2STORE_SHIPM_SHIPPING_METHODS'),'j2store-logo');
		$view = $this->getView( 'ShippingMethods', 'html' );
		$view->setModel( $model, true );
		$view->addTemplatePath(JPATH_SITE.'/plugins/j2store/'.$this->_element.'/'.$this->_element.'/tmpl');
	 	$view->set('row', $shipping_method);
		$view->items = $items;
        $view->set( 'total', $model->getTotal() );
		$view->set( 'pagination', $model->getPagination());
		$view->form2 = $form;
		$view->baseLink = $this->baseLink();
		$view->setLayout('setrates');
		$view->display();
	}

	function cancel(){
		$redirect = $this->baseLink();
		$redirect = JRoute::_( $redirect, false );
		$this->setRedirect( $redirect, '', '' );
	}

	function view()
	{
        $platform = J2Store::platform();
        $app = $platform->application();
        $fof_helper = J2Store::fof();
		require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/select.php');
		$id = $app->input->getInt('id', '0');
		$sid = $app->input->getInt('sid', 0);
        $model = $fof_helper->getModel('ShippingMethods', 'J2StoreModel');
		$shippingmethod_table = $fof_helper->loadTable('ShippingMethod' , 'J2StoreTable');
		$shippingmethod_table->load($sid);
		$data = array();
		$data ['published'] = JHTML::_('select.booleanlist',  'published', 'class=""', $shippingmethod_table->published );

		$data ['taxclass'] =  J2StoreHelperSelect::taxclass($shippingmethod_table->tax_class_id, 'tax_class_id');
		$data ['shippingtype'] =  J2StoreHelperSelect::shippingtype($shippingmethod_table->shipping_method_type, 'shipping_method_type', '', 'shipping_method_type', false );
		$params = $platform->getRegistry($shippingmethod_table->params);
		$shipping_select_table = $params->get('shipping_select_text','');
		$shipping_price_based_on = $params->get('shipping_price_based_on',0);
		$shipping_price = array();
		$shipping_price[]= JHtml::_('select.option', '0', JText::_('J2STORE_STANDARD_SHIPPING_BEFORE_DISCOUNT'));
		$shipping_price[]= JHtml::_('select.option', '1', JText::_('J2STORE_STANDARD_SHIPPING_AFTER_DISCOUNT'));
		$data ['shipping_price_based_on'] = JHtmlSelect::genericlist($shipping_price, 'shipping_price_based_on', array(), 'value', 'text', $shipping_price_based_on);
		$data ['shipping_select_text'] = J2Html::text ( 'shipping_select_text', $shipping_select_table );
		$options = array();
		$options[] = JHtml::_('select.option', 'no', JText::_('JNO'));
		$options[] = JHtml::_('select.option', 'store', JText::_('J2STORE_SHIPPING_STORE_ADDRESS'));
		$data ['address_override'] = JHtmlSelect::genericlist($options, 'address_override', array(), 'value', 'text', $shippingmethod_table->address_override);

		// Form
		$form = array();
		$form['action'] = $this->baseLink();
		$form['shippingTask'] = 'save';
		//We are calling a view from the ShippingMethods we isn't actually the same  controller this has, however since all it does is extend the base view it is
		// all good, and we don't need to remake getView()
		$view = $this->getView( 'ShippingMethods','html');
		$view->hidemenu = true;
		$view->hidestats = true;

		$view->setModel( $model, true );
		$view->assign('item', $shippingmethod_table);
		$view->assign('data', $data );
		$view->assign('form2', $form);
		$view->setLayout('view');
		$view->display();
	}

	/**
	 * Deletes a shipping method
	 */
	function delete()
	{
        $platform = J2Store::platform();
        $app = $platform->application();
        $fof_helper = J2Store::fof();
		$error = false;
		$message_type	= '';
		$message 		= '';
		$shipping_method = $fof_helper->loadTable('ShippingMethod', 'J2StoreTable');
		$ids = $app->input->get('cid', array (), 'array');
		if(count($ids) ) {
			foreach ($ids as $cid)
			{
				if (!$shipping_method->delete($cid))
				{
					$message = $shipping_method->getError();
                    $message_type = 'notice';
					$error = true;
				}
			}
			if ($error)
			{
                $message = JText::_('J2STORE_ERROR') . " - " . $message;
			}
			else
			{
                $message = JText::_('J2STORE_ITEMS_DELETED');
			}
		} else {
            $message_type = 'warning';
            $message = JText::_('J2STORE_SELECT_ITEM_TO_DELETE');
		}

		$redirect = $this->baseLink();
		$platform->redirect( $redirect, $message, $message_type );
	}

    /**
     * Creates a shipping rate and redirects
     * @throws Exception
     */
	function createrate()
	{
        $platform = J2Store::platform();
        $app = $platform->application();
        $fof_helper = J2Store::fof();
		$data = $app->input->getArray($_POST);
		$shipping_rate = $fof_helper->loadTable('ShippingRate','J2StoreTable');
		$message = JText::_('J2STORE_SHIPPING_METHOD_RATE_SAVE_SUCCESS');
		$message_type = 'info';
		if (!$shipping_rate->save($data['jform']) )	{
            $message_type  = 'error';
            $message      = JText::_('J2STORE_SAVE_FAILED')." - ".$shipping_rate->getError();
		}
		$redirect = $this->baseLink()."&shippingTask=setrates&sid={$shipping_rate->shipping_method_id}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );
        $platform->redirect( $redirect, $message, $message_type );
	}

    /**
     * Saves the properties for all prices in list
     *
     * @throws Exception
     */
	function saverates()
	{
        $platform = J2Store::platform();
        $app = $platform->application();
        $fof_helper = J2Store::fof();
		$data = $app->input->getArray($_POST);
		$sid = $app->input->getInt('sid');
		$error = false;
        $message_type = 'info';
        $message = JText::_('J2STORE_SHIPPING_METHOD_RATE_SAVE_SUCCESS');
        $shipping_rate = $fof_helper->loadTable('ShippingRate','J2StoreTable');
		foreach ($data['standardrates'] as $item)
		{
            $shipping_rate->load($item['j2store_shippingrate_id']);
			if (!$shipping_rate->save($item)) {
                $message = $shipping_rate->getError();
                $message_type = 'error';
				$error = true;
			}
		}
		if($error)	$message = JText::_('J2STORE_ERROR') . " - " . $message;
		$redirect = $this->baseLink()."&shippingTask=setrates&sid={$sid}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );
        $platform->redirect( $redirect, $message, $message_type );
	}

	/**
	 * Deletes a shipping rate and redirects
	 */
	function deleterate()
	{
        $platform = J2Store::platform();
        $app = $platform->application();
        $fof_helper = J2Store::fof();
        $shipping_rate = $fof_helper->loadTable('ShippingRate','J2StoreTable');
		$sid = $app->input->getInt('sid');
		$cids = $app->input->get('cid', array(), 'array');
		$message = '';
        $error = false;
		foreach ($cids as $cid)
		{
            $shipping_rate->load( $cid );
			try {
                $shipping_rate->delete();
				$message = JText::_('J2STORE_SHIPPING_METHOD_RATE_DELETE_SUCCESS');
				$message_type = 'info';
			} catch (\Exception $e)
			{
                $message .= $e->getMessage();
                $message_type = 'error';
				$error = true;
			}
		}
		if($error)	$message = JText::_('J2STORE_ERROR') . " - " . $message;
		$redirect = $this->baseLink()."&shippingTask=setrates&sid={$sid}&tmpl=component";
		$redirect = JRoute::_( $redirect, false );
        $platform->redirect( $redirect, $message, $message_type );
	}
}
