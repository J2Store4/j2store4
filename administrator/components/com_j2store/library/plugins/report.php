<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
-------------------------------------------------------------------------*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
require_once(JPATH_ADMINISTRATOR . '/components/com_j2store/library/plugins/_base.php');
if (!class_exists('J2StoreReportPlugin')) {
    class J2StoreReportPlugin extends J2StorePluginBase
    {
        /**
         * @var $_element  string  Should always correspond with the plugin's filename,
         *                         forcing it to be unique
         */
        var $_element = '';
        public static $fof_helper,$platform;

        function __construct(&$subject, $config)
        {
            parent::__construct($subject, $config);
            $this->loadLanguage('', JPATH_ADMINISTRATOR);
            $this->loadLanguage('', JPATH_SITE);
            self::$fof_helper = empty(self::$fof_helper) ? J2Store::fof() : self::$fof_helper;
            self::$platform = empty(self::$platform) ? J2Store::platform() : self::$platform;
            $this->includeCustomModels();
        }
        protected function includeCustomModels()
        {
            self::$fof_helper->loadModelFilePath(JPATH_ADMINISTRATOR . '/components/com_j2store/models');
            self::$fof_helper->loadModelFilePath(JPATH_SITE . '/plugins/j2store/' . $this->_element . '/' . $this->_element . '/models');
        }
        /************************************
         * Note to 3pd:
         *
         * The methods between here
         * and the next comment block are
         * yours to modify by overriding them in your shipping plugin
         *
         ************************************/


        public function onJ2StoreGetReportView($row)
        {
            if (!$this->_isMe($row)) {
                return null;
            }
        }


        /************************************
         * Note to 3pd:
         *
         * DO NOT MODIFY ANYTHING AFTER THIS
         * TEXT BLOCK UNLESS YOU KNOW WHAT YOU
         * ARE DOING!!!!!
         *
         ************************************/

        /**
         * Tells extension that this is a shipping plugin
         *
         * @param $element  string      a valid shipping plugin element
         * @return boolean    true if it is this particular shipping plugin
         */
        public function onJ2StoreGetReportPlugins($element)
        {
            $success = false;
            if ($this->_isMe($element)) {
                $success = true;
            }
            return $success;
        }

        /**
         * Prepares the 'view' tmpl layout
         * when viewing a report
         *
         * @return string
         */
        function _renderView($view = 'view', $vars = null)
        {
            if ($vars == null) $vars = new \stdClass();
            return $this->_getLayout($view, $vars);
        }

        /**
         * Prepares variables for the report form
         *
         * @return string
         */
        function _renderForm($data)
        {
            $vars = new \stdClass();
            return $this->_getLayout('form', $vars);
        }

        /**
         * Gets the appropriate values from the request
         */
        function _getState()
        {
            $state = new JObject();
            $app = J2Store::platform()->application();
            foreach ($state->getProperties() as $key => $value) {
                $new_value = $app->input->get($key);
                $value_exists = array_key_exists($key, $_POST);
                if ($value_exists && !empty($key)) {
                    $state->$key = $new_value;
                }
            }
            return $state;
        }
    }

}
