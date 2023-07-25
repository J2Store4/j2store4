<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
/* class JFormFieldFieldtypes extends JFormField */
class JFormFieldCustomHeading extends JFormField 
{
	protected $type = 'customheading';
	
	public function getInput() {
		
		$html = '';
		$html .= '<h3>'.$this->getTitle().'</h3>';
		return  $html;
	}
	
	public function getLabel() {
		return '';
	}
	
}