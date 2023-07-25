<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/app.php');
class plgJ2StoreApp_bootstrap5 extends J2StoreAppPlugin
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element   = 'app_bootstrap5';

    /**
     * Overriding
     *
     * @param $row
     * @return string
     */
    function onJ2StoreGetAppView( $row )
    {
        if (!$this->_isMe($row))
        {
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
     * @return string
     */
    function viewList()
    {
        $app = J2Store::platform()->application();
        JToolBarHelper::title(JText::_('J2STORE_APP').'-'.JText::_('PLG_J2STORE_'.strtoupper($this->_element)),'j2store-logo');
        JToolBarHelper::back('J2STORE_BACK_TO_DASHBOARD', 'index.php?option=com_j2store');
        $vars = new \stdClass();
        $id = $app->input->getInt('id', '0');
        $vars->id = $id;
        return $this->_getLayout('backend', $vars);
    }

    public function escape($var)
    {
        return htmlspecialchars_decode($var,ENT_COMPAT);
    }

    function onJ2StoreTemplateFolderList(&$folder){
        if(!in_array('bootstrap5',$folder)){
            $folder[] = 'bootstrap5';
        }
        if(!in_array('tag_bootstrap5',$folder)){
            $folder[] = 'tag_bootstrap5';
        }
    }

    function onJ2StoreViewProductListHtml(&$view_html, &$view, $model){
        F0FPlatform::getInstance()->setErrorHandling(E_ALL, 'ignore');
        $view = $this->setTemplatePath($view);
        $result = $view->loadTemplate();

        if ($result instanceof Exception)
        {
            F0FPlatform::getInstance()->raiseError($result->getCode(), $result->getMessage());

            return $result;
        }
        $view_html = $result;
    }

    function setTemplatePath($view,$default = 'bootstrap5'){
        $app = J2Store::platform()->application();
        if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

        // Look for template files in component folders
        $view->addTemplatePath(JPATH_SITE.DS.'plugins'.DS.'j2store'.DS.$this->_element.DS.$this->_element.DS.'tmpl'.DS.$default);

        // Look for overrides in template folder (J2 template structure)
        $view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.'templates');
        $view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.'templates'.DS.$default);

        // Look for overrides in template folder (Joomla! template structure)
        $view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.$default);
        $view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store');

        // Look for specific J2 theme files
        if ($view->params->get('subtemplate'))
        {
            $view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.'templates'.DS.$view->params->get('subtemplate'));
            $view->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_j2store'.DS.$view->params->get('subtemplate'));
        }
        return $view;
    }

    function onJ2StoreViewProductListTagHtml(&$view_html, &$view, $model){
        F0FPlatform::getInstance()->setErrorHandling(E_ALL, 'ignore');
        $view = $this->setTemplatePath($view,'tag_bootstrap5');
        $result = $view->loadTemplate();

        if ($result instanceof Exception)
        {
            F0FPlatform::getInstance()->raiseError($result->getCode(), $result->getMessage());

            return $result;
        }
        $view_html = $result;
    }

    function onJ2StoreViewProductHtml(&$view_html, &$view, $model){
        $view->setLayout('view');
        F0FPlatform::getInstance()->setErrorHandling(E_ALL, 'ignore');
        $view = $this->setTemplatePath($view);
        $result = $view->loadTemplate();

        if ($result instanceof Exception)
        {
            F0FPlatform::getInstance()->raiseError($result->getCode(), $result->getMessage());

            return $result;
        }
        $view_html = $result;
    }

    function onJ2StoreViewProductTagHtml(&$view_html, &$view, $model){
        $view->setLayout('view');
        F0FPlatform::getInstance()->setErrorHandling(E_ALL, 'ignore');
        $view = $this->setTemplatePath($view,'tag_bootstrap5');
        $result = $view->loadTemplate();

        if ($result instanceof Exception)
        {
            F0FPlatform::getInstance()->raiseError($result->getCode(), $result->getMessage());

            return $result;
        }
        $view_html = $result;
    }
}

