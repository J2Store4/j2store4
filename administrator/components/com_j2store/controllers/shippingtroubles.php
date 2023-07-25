<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2storeControllerShippingtroubles extends F0FController
{
	public function browse()
	{
        $platform = J2Store::platform();
        $app = $platform->application();
		$layout = $app->input->getString('layout','default');
		if($layout == 'default_shipping'){
			//before check shipping enable
			$model = $this->getModel('Shippingtroubles');
			$state = $this->getFilterStates();
			foreach($state as $key => $value){
				$model->setState($key,$value);
			}
			$messages = array();
			$shippings = $model->getShippingMethods();
            $shipping_message = array();
            J2Store::plugin()->event('ShippingParamsValidate',array(&$shipping_message));
            if(!empty($shippings)){
                foreach ($shippings as &$shipping){
                    if(isset($shipping_message[$shipping->element]) && !empty($shipping_message[$shipping->element])){
                        $shipping->messages = $shipping_message[$shipping->element];
                    }
                }
            }
            //echo "<pre>";print_r($shipping_message);exit;
			if($shippings){
				$messages = $model->getShippingValidate();
			}
			$view = $this->getThisView();
			$view->setModel($model);
			$view->assign('shipping_avaliable',$shipping);
			$view->assign('shipping_messages',$messages);
			$view->setLayout($layout);
		}elseif ($layout=='default_shipping_product'){
			//before check shipping enable
			$model = $this->getModel('Shippingtroubles');
			$state = $this->getFilterStates();
			foreach($state as $key => $value){
				$model->setState($key,$value);
			}
			$products = array();
			$shipping = $model->getShippingDetails();
			if($shipping){
				$products = $model->getList();
			}else{
				$app->redirect('index.php?option=com_j2store&view=shippingtroubles&layout=default_shipping');
			}
			$view = $this->getThisView();
			$view->setModel($model);
			$view->assign('shipping_avaliable',$shipping);
			$view->assign('products',$products);
			$view->assign('state', $model->getState());
			$view->setLayout($layout);
		}
		return parent::browse();	
	}
	
	public function getFilterStates() {
		$app = JFactory::getApplication();
		$state = array();
		$state['search'] = $app->input->getString('search','');
		$state['product_type']= $app->input->getString('product_type','');
		$state['filter_order']= $app->input->getString('filter_order','j2store_product_id');
		$state['filter_order_Dir']= $app->input->getString('filter_order_Dir','ASC');
		$state['sku']= $app->input->getString('sku','');
		
		return $state;
	}
}