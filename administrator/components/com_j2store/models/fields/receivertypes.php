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
class JFormFieldReceiverTypes extends JFormFieldList {

	protected $type = 'ReceiverTypes';

	public function getRepeatable()
	{
		$html ='';

		$list = array(
			'*' => JText::_( 'J2STORE_EMAILTEMPLATE_RECEIVER_OPTION_BOTH' ),
		 'admin'=> JText::_( 'J2STORE_EMAILTEMPLATE_RECEIVER_OPTION_ADMIN' ),
		'customer'=>JText::_( 'J2STORE_EMAILTEMPLATE_RECEIVER_OPTION_CUSTOMER')
		);
		if(empty($this->item->receiver_type)) $this->item->receiver_type = '*';
		$html .= $list[$this->item->receiver_type];
		return $html;
	}

}
