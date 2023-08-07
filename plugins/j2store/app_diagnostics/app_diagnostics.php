<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR . '/components/com_j2store/library/plugins/app.php');

class plgJ2StoreApp_diagnostics extends J2StoreAppPlugin
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element = 'app_diagnostics';

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
        JToolBarHelper::back('J2STORE_BACK_TO_DASHBOARD', 'index.php?option=com_j2store');
        $vars = new \stdClass();
        $vars->info = $this->getInfo();
        $id = $app->input->getInt('id', '0');
        $vars->id = $id;
        $form = array();
        $form['action'] = "index.php?option=com_j2store&view=app&task=view&id={$id}";
        $vars->form = $form;
        return $this->_getLayout('default', $vars);
    }

    public function getInfo()
    {
        $info = array();
        $version = new JVersion;
        $db = JFactory::getDbo();
        if (isset($_SERVER['SERVER_SOFTWARE'])) {
            $sf = $_SERVER['SERVER_SOFTWARE'];
        } else {
            $sf = getenv('SERVER_SOFTWARE');
        }
        $info['php'] = php_uname();
        $info['dbversion'] = $db->getVersion();
        $info['dbcollation'] = $db->getCollation();
        $info['phpversion'] = phpversion();
        $info['server'] = $sf;
        $info['sapi_name'] = php_sapi_name();
        $info['version'] = $version->getLongVersion();
        $info['useragent'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
        $info['j2store_version'] = $this->getJ2storeVersion();
        $info['is_pro'] = J2Store::isPro();
        $info['curl'] = $this->_isCurl();
        $info['json'] = $this->_isJson();
        $config = JFactory::getConfig();
        $info['error_reporting'] = $config->get('error_reporting');
        $caching = $config->get('caching');
        $info['caching'] = ($caching) ? JText::_('J2STORE_ENABLED') : JText::_('J2STORE_DISABLED');
        $cache_plugin = JPluginHelper::isEnabled('system', 'cache');
        $info['plg_cache_enabled'] = $cache_plugin;
        $info['memory_limit'] = ini_get('memory_limit');
        return $info;
    }

    function _isCurl()
    {
        return (function_exists('curl_version')) ? JText::_('J2STORE_ENABLED') : JText::_('J2STORE_DISABLED');
    }

    function _isJson()
    {
        return (function_exists('json_encode')) ? JText::_('J2STORE_ENABLED') : JText::_('J2STORE_DISABLED');
    }

    public function getJ2storeVersion()
    {
        $version = '';
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName('manifest_cache'))->from($db->quoteName('#__extensions'))->where($db->quoteName('element') . ' = ' . $db->quote('com_j2store'));
        $db->setQuery($query);
        $result = $db->loadResult();
        if ($result) {
            $manifest = json_decode($result);
            $version = $manifest->version;
        }
        return $version;
    }

    public function onJ2StoreProcessCron($command)
    {
        if ($command == 'clear_cart') {
            $this->clear_outdated_cart_data();
        }
    }

    /**
     * Task to clear the old cart data
     * */
    public function clear_outdated_cart_data()
    {
        $app = J2Store::platform()->application();
        $clear_time = $app->input->getInt('clear_time', 0);
        if ($clear_time <= 0) {
            $j2params = J2Store::config();
            $no_of_days_old = $j2params->get('clear_outdated_cart_data_term', 90);
            //convert to seconds
            $clear_time = ($no_of_days_old * 1440);
        }

        $tz = JFactory::getConfig()->get('offset');
        $formattedDate = JFactory::getDate('now -' . $clear_time . ' minutes', $tz)->toSql(true);
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->select('ab.j2store_cart_id')->from('#__j2store_carts as ab');
        $query->where('ab.cart_type = ' . $db->q('cart'));
        $query->where('ab.created_on <= ' . $db->q($formattedDate));
        $db->setQuery($query);
        $old_cart_items_exists = $db->loadObjectList();
        if (count($old_cart_items_exists) > 0) {
            $cart_ids = array();
            foreach ($old_cart_items_exists as $cart_id) {
                $cart_ids[] = $cart_id->j2store_cart_id;
            }
            //clear cart details
            $delete_cartitems_qry = "delete from #__j2store_cartitems where cart_id in (" . implode(',', $cart_ids) . ");";
            $db->setQuery($delete_cartitems_qry);
            try {
                $db->execute();
            } catch (Exception $e) {
            }

            $delete_carts_qry = "delete from #__j2store_carts where #__j2store_carts.cart_type=" . $db->q('cart')
                . " AND created_on <= " . $db->q($formattedDate) . ";";
            $db->setQuery($delete_carts_qry);
            try {
                $db->execute();
            } catch (Exception $e) {
            }
        }
    }
}

