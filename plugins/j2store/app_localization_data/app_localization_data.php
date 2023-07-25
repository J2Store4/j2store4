<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR . '/components/com_j2store/library/plugins/app.php');

class plgJ2StoreApp_localization_data extends J2StoreAppPlugin
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element = 'app_localization_data';

    /**
     * Overriding
     *
     * @param $row
     * @return string
     */
    function onJ2StoreGetAppView($row)
    {
        if (!$this->_isMe($row)) {
            return null;
        }
        return $this->viewList();
    }
    function onJ2StoreIsJ2Store4($element){
        if (!$this->_isMe($element)) {
            return null;
        }
        return true;
    }
    /**
     * Validates the data submitted based on the suffix provided
     * A controller for this plugin, you could say
     *
     * @return string
     * @throws Exception
     */
    function viewList()
    {
        $app = J2Store::platform()->application();
        JToolBarHelper::title(JText::_('J2STORE_APP') . '-' . JText::_('PLG_J2STORE_' . strtoupper($this->_element)), 'j2store-logo');
        $vars = new \stdClass();
        $id = $app->input->getInt('id', 0);
        $vars->id = $id;
        $form = array();
        $form['action'] = "index.php?option=com_j2store&view=app&task=view&id={$id}";
        $vars->form = $form;
        return $this->_getLayout('default', $vars);
    }

}

