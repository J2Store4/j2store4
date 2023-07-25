<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class JFormFieldCustomLink extends JFormField {
	protected $type = 'customlink';
	
	public function getInput() {
		
		$html = '';
		$html .= '<a class="btn btn-warning" id="'.$this->id.'" href="#">'.JText::_($this->element['text']).'</a>';
		return  $html;
	}
	
	public function getLabel() {
		return '';
	}
}
