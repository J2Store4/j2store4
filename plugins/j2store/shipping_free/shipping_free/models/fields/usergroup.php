<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

require_once(JPATH_ADMINISTRATOR .'/components/com_j2store/helpers/j2html.php');

class JFormFieldUserGroup extends JFormFieldList
{
    protected $type = 'usergroup';
    function getInput(){
        return   J2Html::userGroup($this->name,$this->value,array('multiple' => true));
    }
}

