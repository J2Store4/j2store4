<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
class J2StoreViewInventories extends F0FViewHtml
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
	
	/**
	 * Executes before rendering a generic page, default to actions necessary
	 * for the Browse task.
	 *
	 * @param   string  $tpl  Subtemplate to use
	 *
	 * @return  boolean  Return true to allow rendering of the page
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
	
		// ...ordering
		//$this->lists->set('order', $model->getState('filter_order', 'id', 'cmd'));
		//$this->lists->set('order_Dir', $model->getState('filter_order_Dir', 'DESC', 'cmd'));
	
		// Assign data to the view
		//$this->items      = $model->getItemList();
		$this->pagination = $model->getInventoryPagination();
	
		// Pass page params on frontend only
		//if (F0FPlatform::getInstance()->isFrontend())
		//{
		//	$params = JFactory::getApplication()->getParams();
		//	$this->params = $params;
		//}
	
		return true;
	}

}