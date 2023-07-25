<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
/* class JFormFieldFieldtypes extends JFormField */
/**
 * Class used for showing label core / not core
 * @author weblogicx
 *
 */
class JFormFieldCoreFieldtypes extends F0FFormFieldText
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'CoreFieldTypes';

	public function getRepeatable()
	{
		$html ='<label class="label label-warning">'.JText::_('J2STORE_CUSTOM_FIELDS_NOT_CORE').'</label>';
		if(isset($this->item->orderstatus_core) && $this->item->orderstatus_core ){
			$html='<label class="label label-success">'.JText::_('J2STORE_CUSTOM_FIELDS_CORE').'</label>';
			}elseif(isset($this->item->field_core) && $this->item->field_core){
			$html='<label class="label label-success">'.JText::_('J2STORE_CUSTOM_FIELDS_CORE').'</label>';
		}
		return $html;
	}
}
