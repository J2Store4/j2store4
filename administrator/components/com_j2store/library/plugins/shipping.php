<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once(JPATH_ADMINISTRATOR . '/components/com_j2store/library/plugins/_base.php');

if (!class_exists('J2StoreShippingPlugin')) {

    class J2StoreShippingPlugin extends J2StorePluginBase
    {
        /**
         * @var $_element  string  Should always correspond with the plugin's filename,
         *                         forcing it to be unique
         */
        var $_element = '';

        function __construct(&$subject, $config)
        {
            parent::__construct($subject, $config);
            $this->loadLanguage('', JPATH_ADMINISTRATOR);
            $this->loadLanguage('', JPATH_SITE);
        }

        /************************************
         * Note to 3pd:
         *
         * The methods between here
         * and the next comment block are
         * yours to modify by overriding them in your shipping plugin
         *
         ************************************/

        /**
         * Returns the Shipping Rates.
         * @param $element - shipping element name
         * @param $order - Order
         * @return array
         */
        public function onJ2StoreGetShippingRates($element, $order)
        {
            if (!$this->_isMe($element)) {
                return null;
            }

            $rate = array();
            $rate['name'] = "";
            $rate['code'] = "";
            $rate['price'] = "";
            $rate['extra'] = "";
            $rate['total'] = "";
            $rate['tax'] = "";
            $rate['element'] = $this->_element;
            $rate['error'] = false;
            $rate['errorMsg'] = "";
            $rate['debug'] = "";

            $rates[] = $rate;

            return $rates;
        }

        /**
         * Here you will have to save the shipping rate information
         *
         * @param $element - the shipping element name
         * @param $order - the order object
         * @return void|null
         */
        public function onJ2StorePostSaveShipping($element, $order)
        {
            if (!$this->_isMe($element)) {
                return null;
            }
        }

        /**
         * Shows the shipping view
         *
         * @param $row - the shipping data
         * @return string
         */
        public function onJ2StoreGetShippingView($row)
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
        public function onJ2StoreGetShippingPlugins($element)
        {
            $success = false;
            if ($this->_isMe($element)) {
                $success = true;
            }
            return $success;
        }

        /**
         * Determines if this shipping option is valid for this order
         *
         * @param $element
         * @param $order
         * @return bool|null
         */
        function onJ2StoreGetShippingOptions($element, $order)
        {
            // Check if this is the right plugin
            if (!$this->_isMe($element)) {
                return null;
            }

            $found = true;
            $geozones = $this->params->get('geozones');

            //return true if we have empty geozones
            if (!empty($geozones)) {
                $found = false;

                $geozones = explode(',', $geozones);
                $orderGeoZones = $order->getShippingGeoZones();

                //loop to see if we have at least one geozone assigned
                foreach ($orderGeoZones as $orderGeoZone) {
                    if (in_array($orderGeoZone->geozone_id, $geozones)) {
                        $found = true;
                        break;
                    }
                }
            }
            // if this shipping methods should be available for this order, return true
            // if not, return false.
            // by default, all enabled shipping methods are valid, so return true here,
            // but plugins may override this
            return $found;
        }

        function checkAddress($address)
        {
            if (is_array($address)) {
                //cast this as an object
                $address = (object)$address;
            }
            if (empty($address->zone_code)) {
                if (!empty($address->zone_id)) {
                    $table = $this->getZoneById($address->zone_id);
                    $address->zone_code = $table->zone_code;
                }
            }

            if (empty($address->country_code) || empty($address->country_name) || empty($address->country_isocode_2)
                || empty($address->country_isocode_3)) {
                if (!empty($address->country_id)) {
                    $table = $this->getCountryById($address->country_id);
                    $address->country_name = $table->country_name;
                    $address->country_isocode_3 = $table->country_isocode_3;
                    $address->country_isocode_2 = $table->country_isocode_2;
                    $address->country_code = $table->country_isocode_2;
                }
            }

            return $address;
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
            $state = new \stdClass();
            $app = J2Store::platform()->application();
            foreach ($this->getProp($state) as $key => $value) {
                //$new_value = JRequest::getVar( $key );
                $new_value = $app->input->getString($key);
                $value_exists = array_key_exists($key, $_POST);
                if ($value_exists && !empty($key)) {
                    $state->$key = $new_value;
                }
            }
            return $state;
        }

        public function getProp($obj,$public = true)
        {
            $vars = get_object_vars($obj);

            if ($public)
            {
                foreach ($vars as $key => $value)
                {
                    if ('_' == substr($key, 0, 1))
                    {
                        unset($vars[$key]);
                    }
                }
            }

            return $vars;
        }

        function onJ2StoreCustomModelPath(&$paths){
            $paths[] = JPATH_SITE.'/plugins/j2store/'.$this->_element.'/'.$this->_element.'/models';
        }

        function onJ2StoreCustomTablePath(&$paths){
            $paths[] = JPATH_SITE.'/plugins/j2store/'.$this->_element.'/'.$this->_element.'/tables';
        }
    }

}
