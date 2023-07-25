<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class J2StoreViewApp extends F0FViewHtml
{

	public function display($tpl=null){
		  $app = JFactory::getApplication();
		   $task =$app->input->getString('task');
		  $id = $app->input->getInt('id');
		  $model= $this->getModel('Apps');
		  $this->item=$model->getItem($id);
		  parent::display($tpl);

	}
}