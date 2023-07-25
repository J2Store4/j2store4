<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
class J2StoreControllerShippingPlugin extends F0FController {

	// the same as the plugin's one!
	var $_element = '';
	/**
	 * Overrides the getView method, adding the plugin's layout path
	 */
 	public function getView( $name = '', $type = '', $prefix = '', $config = array() ){
    	$view = parent::getView( $name, $type, $prefix, $config );
    	$view->addTemplatePath(JPATH_SITE.'/plugins/j2store/'.$this->_element.'/'.$this->_element.'/tmpl/');
    	return $view;
    }

    /**
     * Overrides the delete method, to include the custom models and tables.
     */
    public function delete()
    {
    	$this->includeCustomModel('ShippingRates');
    	parent::delete();
    }

    protected function includeCustomModel( $name ){
    	$dispatcher = J2Store::platform()->application();
		$dispatcher->triggerEvent('includeCustomModel', array($name, $this->_element) );
    }

    protected function baseLink(){
    	$id = JFactory::getApplication()->input->getInt('id', '');
    	return "index.php?option=com_j2store&view=shippings&task=view&id={$id}";
    }
}
