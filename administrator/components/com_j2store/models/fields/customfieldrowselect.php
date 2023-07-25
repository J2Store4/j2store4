<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

/**
 * Class used for showing rowselect only if item is not core
 * @author weblogicx
 *
 */
class JFormFieldCustomFieldRowSelect extends F0FFormFieldSelectrow
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Customfieldrowselect';
	public function getRepeatable()
	{
		$html ='';
		if(isset($this->item->field_core)  && $this->item->field_core){
			$html ='<div style="display:none;">';
		}elseif(isset($this->item->orderstatus_core) && $this->item->orderstatus_core){
			$html ='<div style="display:none;">';
		}
		$html .=parent::getRepeatable();
		if(isset($this->item->field_core)  && $this->item->field_core){
			$html .='</div>';
		}elseif(isset($this->item->orderstatus_core) && $this->item->orderstatus_core){
			$html .='</div>';
		}
		return $html;
	}

}
