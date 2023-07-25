<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class J2StoreViewOrders extends F0FViewHtml
{
	/**
	 * Executes before rendering the page for the Add task.
	 *
	 * @param   string  $tpl  Subtemplate to use
	 *
	 * @return  boolean  Return true to allow rendering of the page
	 */
	/**
	 * Displays the view
	 *
	 * @param   string  $tpl  The template to use
	 *
	 * @return  boolean|null False if we can't render anything
	 */
	protected function onDisplay($tpl = null)
	{

		$view = $this->input->getCmd('view', 'cpanel');

		if (in_array($view, array('cpanel', 'cpanels')))
		{
			return;
		}

		// Load the model
		$model = $this->getModel();

		$app = JFactory::getApplication();
		$state = array();
		$state['search'] = $app->input->getString('search',  $model->getState('search', ''));
		$state['since'] = $app->input->get('since', $model->getState('since', ''));
		$state['until'] = $app->input->get('until', $model->getState('until', ''));
		$state['orderstate'] = $app->input->get('orderstate', $model->getState('orderstate', 0));
		$state['user_id'] = $app->input->getInt('user_id', $model->getState('user_id', 0));
		$state['coupon_code'] = $app->input->getString('coupon_code', $model->getState('coupon_code', ''));
		$state['moneysum']= $app->input->getString('moneysum','');
		$state['frominvoice']= $app->input->getString('frominvoice', $model->getState('frominvoice', ''));
		$state['toinvoice']= $app->input->getString('toinvoice', $model->getState('toinvoice', ''));
		$state['paykey']= $app->input->getString('paykey','');
		$state['filter_order']= $app->input->getString('filter_order','order_id');
		$state['filter_order_Dir']= $app->input->getString('filter_order_Dir','DESC');
		foreach($state as $key => $value){
			$model->setState($key,$value);
		}
		// ...ordering
		/* $this->lists->set('order', $model->getState('filter_order', 'id', 'cmd'));
		$this->lists->set('order_Dir', $model->getState('filter_order_Dir', 'DESC', 'cmd'));
 */
		// Assign data to the view
		$this->items      = $model->getOrderList();
		$this->pagination = $model->getPagination();
		// Pass page params on frontend only
		if (F0FPlatform::getInstance()->isFrontend())
		{
			$params = JFactory::getApplication()->getParams();
			$this->params = $params;
		}


			$state = $model->getState();
			$this->state = $state;
			$this->currency = J2Store::currency();
			return true;
		}

}