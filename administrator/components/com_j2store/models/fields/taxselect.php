<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
/* class JFormFieldFieldtypes extends JFormField */

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2html.php';
class JFormFieldTaxSelect extends JFormFieldList {

	protected $type = 'Taxselect';

	public function getInput() {

		$model = F0FModel::getTmpInstance('Taxprofiles','J2StoreModel');
		$list = $model->enabled(1)->getItemList();
		$attr = array();
		// Get the field options.
		// Initialize some field attributes.
		$attr['class']= !empty($this->class) ? $this->class: '';
		// Initialize JavaScript field attributes.
		$attr ['onchange']= $this->onchange ?  $this->onchange : '';
		//generate country filter list
		$options = array();
		$options[''] =  JText::_('J2STORE_NOT_TAXABLE');
		foreach($list as $row) {
			$options[$row->j2store_taxprofile_id] =  JText::_($row->taxprofile_name);
		}
		return J2Html::select()->clearState()
						->type('genericlist')
						->name($this->name)
						->attribs($attr)
						->value($this->value)
						->setPlaceHolders($options)
						->getHtml();
	}
}
