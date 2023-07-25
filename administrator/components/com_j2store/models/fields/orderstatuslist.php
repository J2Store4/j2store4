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
class JFormFieldOrderstatusList extends JFormFieldList {

	protected $type = 'OrderstatusList';

	public function getRepeatable()
	{
		$html ='';
		if($this->item->orderstatus_id != '*'){
			$orderstatus = F0FTable::getAnInstance('Orderstatus','J2StoreTable');
			$orderstatus->load($this->item->orderstatus_id);
			$html ='<label class="label">'.JText::_($orderstatus->orderstatus_name);
			if(isset($orderstatus->orderstatus_cssclass) && $orderstatus->orderstatus_cssclass){
				$html ='<label class="label  '.$orderstatus->orderstatus_cssclass.'">'.JText::_($orderstatus->orderstatus_name);
			}

		}else{
			$html ='<label class="label label-success">'.JText::_('J2STORE_ALL');
		}
		$html .='</label>';
		return $html;
	}


	public function getInput() {

		$model = F0FModel::getTmpInstance('Orderstatuses','J2StoreModel');
		$orderlist = $model->getItemList();
		$attr = array();
		// Get the field options.
				// Initialize some field attributes.
        if($this->class){
            $attr['class']= !empty($this->class) ? $this->class: '';
        }

        if($this->size){
            $attr ['size']= !empty($this->size) ?$this->size : '';
        }

		if($this->multiple){
            $attr ['multiple']= $this->multiple ? 'multiple': '';
        }
        if($this->required){
            $attr ['required']= $this->required ? true:false;
        }

        if($this->autofocus){
            $attr ['autofocus']= $this->autofocus ? 'autofocus' : '';
        }

		// Initialize JavaScript field attributes.
        if($this->onchange){
            $attr ['onchange']= $this->onchange ?  $this->onchange : '';
        }

		//generate country filter list
		$orderstatus_options = array();
		$orderstatus_options['*'] =  JText::_('JALL');
		foreach($orderlist as $row) {
			$orderstatus_options[$row->j2store_orderstatus_id] =  JText::_($row->orderstatus_name);
		}

        if(version_compare(JVERSION, '3.99.99', 'lt')){
            return J2Html::select()->clearState()
                ->type('genericlist')
                ->name($this->name)
                ->attribs($attr)
                ->value($this->value)
                ->setPlaceHolders($orderstatus_options)
                ->getHtml();
        }else{
            $displayData = array(
                'class' => 'input-small',
                'name' => $this->name,
                'value' => $this->value  ,
                'options' =>$orderstatus_options ,
                'autofocus' => '',
                'onchange' => '',
                'dataAttribute' => '',
                'readonly' => '',
                'disabled' => false,
                'hint' => '',
                'required' => $this->required,
                'id' => '',
                'multiple'=> $this->multiple
            );
            $path = JPATH_SITE . '/layouts/joomla/form/field/list-fancy-select.php';
            $media_render = self::getRenderer('joomla.form.field.list-fancy-select', $path);
            return $media_render->render($displayData);
        }
	}
}
