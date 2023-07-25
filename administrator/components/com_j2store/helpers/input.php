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
defined('_JEXEC') or die;

/**
 * J2Store helper.
  */
class J2StoreInput{

	public static $input;
	public static $name;
	protected $class;
	protected $element;
	protected $value;
	protected $type;
	protected $validate;

	function __construct() {

	}


	public static function getText($label,$name,$value,$type,$pholder,$options)
	{

		$class=$options['class'];
		$required=$options['required'];
		return "<input type='".$type."'  name='".$name."' placeholder='".$pholder."'  class='".$class."'  value='".htmlspecialchars($value, ENT_COMPAT, 'UTF-8')."'   $required/>";


	}

	public static function getLabel($name, $options)
	{

		return "<label class='control-label' for=".JText::_($name).">".JText::_($name)."</label>";
	}

	public static function getTextarea($label,$name,$value,$type, $options)
	{

		$class=$options['class'] ? $options['class']:'';
		$required=$options['required'] ? $options['required'] :'';

		return "<div class='controls'><textarea type=".$type." name=".$name." class=".$class. " " .$required.">".$value."</textarea></div>";

	}
	public static function getControlGroup($label,$name,$value,$type,$pholder,$options)
	{
		$class=$options['class'] ? $options['class'] :'';
		$required=$options['required'] ? $options['required'] : '';
		return "<div class='control-group'><label class='control-label'>".JText::_($label)."</label><div class='controls'><input  type='".$type."'  name='".$name."' placeholder='".$pholder."'  class='".$class."'  value='".htmlspecialchars($value, ENT_COMPAT, 'UTF-8')."'   $required/></div></div>";

	}

}
