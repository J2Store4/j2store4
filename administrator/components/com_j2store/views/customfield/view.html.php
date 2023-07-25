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
//require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/selectable/fields.php');
class J2StoreViewCustomfields extends F0FViewHtml
{
	protected function onAdd($tpl = null)
	{
		$app=JFactory::getApplication();
		$doc = JFactory::getDocument();
		$model=$this->getModel('Customfields');
		$this->item=$model->getTable();
		$this->item->field_table = 'address';
		$this->item->field_type = 'text';
		$selectableBase = J2Store::getSelectableBase();

		if(!empty($this->item->j2store_customfield_id)) {
			$field = $selectableBase->getField($this->item->j2store_customfield_id);
			$data = null;
			$allFields = $selectableBase->getFields('', $data, $field->field_table);
		} else {

			$field = $model->getTable();

			$field->field_table = 'address';
			$field->field_published = 1;
			$field->field_type = 'text';
			$field->field_backend = 1;
			$allFields = array();
		}
		$this->allFields = $allFields;
		$this->field = $field;

		
		//get the field type
		$fieldtype =  J2Store::getSelectableFields();
		$this->assignRef('fieldtype', $fieldtype);
		$this->assignRef('fieldClass', $selectableBase);
		
		//country, zone type
		$zoneType = new j2storeZoneType();
		$this->assignRef('zoneType', $zoneType);

		$lists = array();

		$script = 'function addLine(){
		var myTable=window.document.getElementById("tablevalues");
		var newline = document.createElement(\'tr\');
		var column = document.createElement(\'td\');
		var column2 = document.createElement(\'td\');
		var column3 = document.createElement(\'td\');
		var input = document.createElement(\'input\');
		var input2 = document.createElement(\'input\');
		var input3 = document.createElement(\'select\');
		var option1 = document.createElement(\'option\');
		var option2 = document.createElement(\'option\');
		input.type = \'text\';
		input2.type = \'text\';
		option1.value= \'0\';
		option2.value= \'1\';
		input.name = \'field_values[title][]\';
		input2.name = \'field_values[value][]\';
		input3.name = \'field_values[disabled][]\';
		option1.text= \''.JText::_('J2STORE_NO',true).'\';
		option2.text= \''.JText::_('J2STORE_YES',true).'\';
		try { input3.add(option1, null); } catch(ex) { input3.add(option1); }
		try { input3.add(option2, null); } catch(ex) { input3.add(option2); }
		column.appendChild(input);
		column2.appendChild(input2);
		column3.appendChild(input3);
		newline.appendChild(column);
		newline.appendChild(column2);
		newline.appendChild(column3);
		myTable.appendChild(newline);
		}

		function deleteRow(divName,inputName,rowName){
			var d = document.getElementById(divName);
			var olddiv = document.getElementById(inputName);
			if(d && olddiv){
				d.removeChild(olddiv);
				document.getElementById(rowName).style.display="none";
			}
			return false;
		}

		function setVisible(value){
			if(value=="product" || value=="item" || value=="category"){
				document.getElementById(\'category_field\').style.display = "";
			}else{
				document.getElementById(\'category_field\').style.display = \'none\';
			}
		}';

		$doc->addScriptDeclaration($script);
		return true;
	}
}
