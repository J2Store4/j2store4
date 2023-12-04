<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2023 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
\defined('_JEXEC') or die;

class PlgInstallerJ2Store extends \Joomla\CMS\Plugin\CMSPlugin
{
    function onInstallerBeforePackageDownload(&$url, &$headers)
    {
        $domain = 'j2store.org';
        if (strpos($url, $domain) !== false) {
            $element = substr(substr($url, strrpos($url, "/") + 1), 0, -4);
            $pathSegments = explode('/', parse_url($url, PHP_URL_PATH));
            $position =  array_search($domain, $pathSegments);
            $type = $pathSegments[$position + 1];
            if (!empty($type) && !empty($element)) {
                $plugin = $this->getPlugin($type, $element);
                if (is_object($plugin) && isset($plugin->params)) {
                    $params = new \Joomla\Registry\Registry($plugin->params);
                    $is_free = $params->get('is_free',false);
                    if (empty($is_free) && $plugin->type == 'module') {
                        $moduleParams = $this->getModuleParams($plugin->element);
                        $license_key = '';
                        if (!empty($moduleParams)) {
                            $moduleParamsArray = json_decode($moduleParams, true);

                            if (isset($moduleParamsArray['license_key'])) {
                                $license_key = $moduleParamsArray['license_key'];
                            }
                        }
                    }
                    else{
                        $license_key = (array)$params->get('license_key', '');
                    }
                    if($is_free){
                        $url = "https://github.com/j2store/".$element."/releases/download/stable/".$element.".zip";
                    }else{
                        $baseURL = str_replace('/administrator', '', JURI::base());
                        $api_params = array(
                            'edd_action' => 'get_version',
                            'license' => is_array($license_key) && isset($license_key['license']) && !empty($license_key['license']) ? $license_key['license'] : '',
                            'url' => $baseURL,
                            'element' => $element
                        );
                        require_once(JPATH_ADMINISTRATOR . '/components/com_j2store/helpers/license.php');
                        $license_helper = J2License::getInstance();
                        $license = $license_helper->getVersion($api_params);
                        if (is_array($license) && isset($license['download_link']) && !empty($license['download_link'])) {
                            $url = $license['download_link'];
                        }
                    }
                }
            }
        }
    }

    protected function getPlugin($type, $element){
        if(empty($type) || empty($element)){
            return array();
        }
        $db = \Joomla\CMS\Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select("*")->from('#__extensions')->where('type=' . $db->q($type))
            ->where('element=' . $db->q($element));

        $db->setQuery($query);
        return $db->loadObject();
    }
     protected function getModuleparams($extension_name){
        if (empty($extension_name)) {
            return;
        }
        $db = \Joomla\CMS\Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select("params")->from('#__modules')
            ->where($db->qn('module') . ' = ' . $db->q($extension_name));
        $db->setQuery($query);
        $result = $db->loadObject();
        return $result->params;
    }
}