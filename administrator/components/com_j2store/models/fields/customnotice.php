<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
/* class JFormFieldFieldtypes extends JFormField */
class JFormFieldCustomNotice extends JFormField 
{
	protected $type = 'customnotice';
	
	public function getInput() {
		
		$html = '';
		$html .= '<div class="alert alert-block alert-info"><strong>'.$this->getTitle().'</strong></div>';
		return  $html;
	}
	
	public function getLabel() {
		return '';
	}
	
}