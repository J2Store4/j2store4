<?php
/**
 * --------------------------------------------------------------------------------
 * User plugin - j2store address field
 * --------------------------------------------------------------------------------
 * @package     Joomla  3.x
 * @subpackage  J2 Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2016 J2Store . All rights reserved.
 * @license     GNU/GPL license: http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
// No direct access to this file
defined('_JEXEC') or die;


jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
jimport('joomla.utilities.date');
jimport('joomla.filesystem.file');
JFormHelper::loadFieldClass('list');
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php';
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2html.php';
class  JFormFieldJ2storeFields extends JFormFieldList {

    protected $type = 'j2storefields';

    public function getInput() {
        $rootURL = rtrim(JURI::base(),'/');
        $subpathURL = JURI::base(true);
        if(!empty($subpathURL) && ($subpathURL != '/')) {
            $rootURL = substr($rootURL, 0, -1 * strlen($subpathURL));
        }
        $app = JFactory::getApplication();

        $doc = JFactory::getDocument();
        $doc->addScriptDeclaration('var j2_ajax_url = "'.$rootURL.'" ');
        $url = trim ( JUri::root (true) )."/plugins/user/j2userregister/js/j2userregister.js";
        JFactory::getDocument ()->addScript ( $url );
        $selectableBase = J2Store::getSelectableBase();
        $address = F0FTable::getAnInstance('Address', 'J2StoreTable')->getClone();
        $fields = $selectableBase->getFields('billing',$address,'address');
        $plugin = JPluginHelper::getPlugin('user', 'j2userregister');
        $registry = new Joomla\Registry\Registry();
        $registry->loadString ( $plugin->params );
        $vars = new stdClass();
        $vars->plugin_params = $registry;
        $vars->selectableBase = J2Store::getSelectableBase();
        $vars->address = $address;
        $vars->fields = $fields;
        $vars->field_html = $registry->get ( 'field_html','' );
        $vars->params = J2Store::config ();
        $app = JFactory::getApplication();
        $session = JFactory::getSession ();
        if($session->has('j2userregister')) {
            $vars->address_default = $session->get ( 'j2userregister',array(),'j2store' );
        }elseif(J2Store::platform()->isClient('administrator')) {
            //load the first found address
            $id = $app->input->getInt('id');
            if($id > 0) {
                $address->load(array('user_id'=>$id));
            }

        }

        $html = $this->_getLayout ( 'form_fields',$vars );
        return $html;
    }

    function _getLayout($layout, $vars = false )
    {


        ob_start();
        $layout = $this->_getLayoutPath( $layout );
        include($layout);
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    function _getLayoutPath($layout = 'default')
    {
        $app = JFactory::getApplication();

        // get the template and default paths for the layout
        $templatePath = JPATH_SITE.'/templates/'.$app->getTemplate().'/html/plugins/user/j2userregister/'.$layout.'.php';
        $defaultPath = JPATH_SITE.'/plugins/user/j2userregister/tmpl/'.$layout.'.php';

        // if the site template has a layout override, use it
        jimport('joomla.filesystem.file');
        if (JFile::exists( $templatePath ))
        {
            return $templatePath;
        }
        else
        {
            return $defaultPath;
        }
    }
}
