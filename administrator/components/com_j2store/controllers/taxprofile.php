<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/controllers/traits/list_view.php';
class J2StoreControllerTaxprofile extends F0FController
{
	use list_view;
	public function browse()
	{
		$app = JFactory::getApplication();
		$model = $this->getThisModel();
		$state = array();
		$state['taxprofile_name'] = $app->input->getString('taxprofile_name','');
		$state['filter_order']= $app->input->getString('filter_order','j2store_taxprofile_id');
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
			'j2store_taxprofile_id' => array(
				'type' => 'rowselect',
				'tdwidth' => '20',
				'label' => 'J2STORE_TAXPROFILE_ID'
			),
			'taxprofile_name' => array(
				'type' => 'fieldsearchable',
				'sortable' => 'true',
				'show_link' => 'true',
				'url' => "index.php?option=com_j2store&amp;view=taxprofile&amp;id=[ITEM:ID]",
				'url_id' => 'j2store_taxprofile_id',
				'label' => 'J2STORE_TAXPROFILE_NAME'
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
	function deleteTaxRule() {
		$app = JFactory::getApplication();
		$taxrule_id = $app->input->getInt('taxrule_id');
		$taxrule =F0FTable::getInstance('taxrules','Table');
		$response = array();
		try {
			$taxrule->delete($taxrule_id);
			$response['success'] =JText::_('J2STORE_TAXRULE_DELETED_SUCCESSFULLY');
		}catch (Exception $e) {
			$response['error'] =JText::_('J2STORE_TAXRULE_DELETE_FAILED');
		}
		echo json_encode($response );
		$app->close();

	}
}
?>
