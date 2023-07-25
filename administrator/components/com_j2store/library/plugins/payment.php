<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;

require_once(JPATH_ADMINISTRATOR . '/components/com_j2store/library/plugins/_base.php');
if (!defined('F0F_INCLUDED')) {
    require_once JPATH_LIBRARIES . '/f0f/include.php';
}

class J2StorePaymentPlugin extends J2StorePluginBase
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element = '';

    var $_j2version = '';

    function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
    }

    /**
     * Triggered before making the payment
     * You can perform any modification to the order table variables here. Like setting a surcharge
     *
     *
     * @param $order     object order table object
     * @return string   HTML to display. Normally an empty one.
     */
    function _beforePayment($order)
    {
        // Before the payment
        $html = '';
        return $html;
    }

    /**
     * Prepares the payment form
     * and returns HTML Form to be displayed to the user
     * generally will have a message saying, 'confirm entries, then click complete order'
     *
     * Submit button target for onsite payments & return URL for offsite payments should be:
     * index.php?option=com_j2store&view=billing&task=confirmPayment&orderpayment_type=xxxxxx
     * where xxxxxxx = $_element = the plugin's filename
     *
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    function _prePayment($data)
    {
        // Process the payment

        $vars = new \stdClass();
        $vars->message = "Preprocessing successful. Double-check your entries.  Then, to complete your order, click Complete Order!";

        $html = $this->_getLayout('prepayment', $vars);
        return $html;
    }

    /**
     * @param $order
     * @return void
     */
    function onJ2StoreCalculateFees($order) {
        //is customer selected this method for payment ? If yes, apply the fees
        $payment_method = $order->get_payment_method();
        if($payment_method == $this->_element) {
            $total = $order->order_subtotal + $order->order_shipping + $order->order_shipping_tax;
            $surcharge = 0;
            $surcharge_percent = $this->params->get('surcharge_percent', 0);
            $surcharge_fixed = $this->params->get('surcharge_fixed', 0);
            if( $total > 0 && ( (float) $surcharge_percent > 0 || (float) $surcharge_fixed > 0)) {
                //percentage
                if((float) $surcharge_percent > 0) {
                    $surcharge += ($total * (float) $surcharge_percent) / 100;
                }
                if((float) $surcharge_fixed > 0) {
                    $surcharge += (float) $surcharge_fixed;
                }
                $name = $this->params->get('surcharge_name', JText::_('J2STORE_CART_SURCHARGE'));
                $tax_class_id = $this->params->get('surcharge_tax_class_id', '');
                $taxable = false;
                if($tax_class_id && $tax_class_id > 0) $taxable = true;
                if($surcharge > 0) {
                    $order->add_fee($name, round($surcharge, 2), $taxable, $tax_class_id);
                }
            }
        }
    }

    /**
     * Processes the payment form
     * and returns HTML to be displayed to the user
     * generally with a success/failed message
     *
     * @param $data     array       form post data
     * @return string   HTML to display
     * @throws Exception
     */
    function _postPayment($data)
    {
        // Process the payment
        $app = J2Store::platform()->application();
        $paction = $app->input->getString('paction', '');

        $vars = new \stdClass();

        switch ($paction) {
            case "display":
                $vars->message = JText::_($this->params->get('onafterpayment', ''));
                $html = $this->_getLayout('message', $vars);
                $html .= $this->_displayArticle();
                break;
            case "process":
                $vars->message = $this->_process();
                $html = $this->_getLayout('message', $vars);
                echo $html; // TODO Remove this
                $app->close();
                break;
            case "cancel":
                $vars->message = JText::_($this->params->get('oncancelpayment', ''));
                $html = $this->_getLayout('message', $vars);
                break;
            default:
                $vars->message = JText::_($this->params->get('onerrorpayment', ''));
                $html = $this->_getLayout('message', $vars);
                break;
        }

        return $html;
    }

    /**
     * Prepares the 'view' tmpl layout
     * when viewing a payment record
     *
     * @param $orderPayment     object       a valid TableOrderPayment object
     * @return string   HTML to display
     */
    function _renderView($orderPayment)
    {
        // Load the payment from _orderpayments and render its html
        $vars = new \stdClass();
        return $this->_getLayout('view', $vars);
    }

    /**
     * Prepares variables for the payment form
     *
     * @param $data     array       form post data for pre-populating form
     * @return string   HTML to display
     */
    function _renderForm($data)
    {
        $vars = new \stdClass();
        $vars->onselection_text = $this->params->get('onselection', '');
        return $this->_getLayout('form', $vars);
    }

    /**
     * Verifies that all the required form fields are completed
     * if any fail verification, set
     * $object->error = true
     * $object->message .= '<li>x item failed verification</li>'
     *
     * @param $submitted_values     array   post data
     * @return stdClass
     */
    function _verifyForm($submitted_values)
    {
        $vars = new \stdClass();
        $vars->error = false;
        $vars->message = '';
        return $vars;
    }

    /**
     * Tells extension that this is a payment plugin
     *
     * @param $element  string      a valid payment plugin element
     * @return boolean
     */
    function onJ2StoreGetPaymentPlugins($element)
    {
        $success = false;
        if ($this->_isMe($element)) {
            $success = true;
        }
        return $success;
    }

    function onJ2StoreGetPaymentOptions($element, $order)
    {
        // Check if this is the right plugin
        if (!$this->_isMe($element)) {
            return null;
        }

        $found = true;

        // if this payment method should be available for this order, return true
        // if not, return false.
        // by default, all enabled payment methods are valid, so return true here,
        // but plugins may override this

        $order->setAddress();
        $address = $order->getBillingAddress();
        $geozone_id = $this->params->get('geozone_id', '');

        if (isset($geozone_id) && (int)$geozone_id > 0) {
            //get the geozones
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('gz.*,gzr.*')->from('#__j2store_geozones AS gz')
                ->innerJoin('#__j2store_geozonerules AS gzr ON gzr.geozone_id = gz.j2store_geozone_id')
                ->where('gz.j2store_geozone_id=' . $db->q($geozone_id))
                ->where('gzr.country_id=' . $db->q($address['country_id']) . ' AND (gzr.zone_id=0 OR gzr.zone_id=' . $db->q($address['zone_id']) . ')');
            $db->setQuery($query);
            $grows = $db->loadObjectList();

            if (!$geozone_id) {
                $found = true;
            } elseif ($grows) {
                $found = true;
            } else {
                $found = false;
            }
        }

        return $found;
    }


    /**
     * Wrapper for the internal _renderForm method
     *
     * @param $element  string      a valid payment plugin element
     * @param $data     array       form post data
     * @return string
     */
    function onJ2StoreGetPaymentForm($element, $data)
    {
        if (!$this->_isMe($element)) {
            return null;
        }
        return $this->_renderForm($data);
    }

    /**
     * Wrapper for the internal _verifyForm method
     *
     * @param $element  string      a valid payment plugin element
     * @param $data     array       form post data
     * @return stdClass
     */
    function onJ2StoreGetPaymentFormVerify($element, $data)
    {
        if (!$this->_isMe($element)) {
            return null;
        }
        return $this->_verifyForm($data);
    }

    /**
     * Wrapper for the internal _renderView method
     *
     * @param $element  string      a valid payment plugin element
     * @param $orderPayment  object      a valid TableOrderPayment object
     * @return string
     */
    function onJ2StoreGetPaymentView($element, $orderPayment)
    {
        if (!$this->_isMe($element)) {
            return null;
        }

        return $this->_renderView($orderPayment);
    }

    /**
     * Wrapper for the internal _prePayment method
     * which performs any necessary actions before payment
     *
     * @param $element  string      a valid payment plugin element
     * @param $data     array       form post data
     * @return string
     */
    function onJ2StorePrePayment($element, $data)
    {
        if (!$this->_isMe($element)) {
            return null;
        }
        return $this->_prePayment($data);
    }

    /**
     * Wrapper for the internal _postPayment method
     * that processes the payment after user submits
     *
     * @param $element  string      a valid payment plugin element
     * @param $data     array       form post data
     * @return string
     * @throws Exception
     */
    function onJ2StorePostPayment($element, $data)
    {
        if (!$this->_isMe($element)) {
            return null;
        }
        return $this->_postPayment($data);
    }

    /**
     * Wrapper for the internal _beforePayment method
     * which performs any necessary actions before payment
     *
     * @param $element  string      a valid payment plugin element
     * @param $order    object      order object
     * @return string
     */
    function onJ2StoreBeforePayment($element, $order)
    {
        if (!$this->_isMe($element)) {
            return null;
        }
        return $this->_beforePayment($order);
    }


    public function getVersion()
    {

        if (empty($this->_j2version)) {
            $db = JFactory::getDbo();
            // Get installed version
            $query = $db->getQuery(true);
            $query->select($db->quoteName('manifest_cache'))->from($db->quoteName('#__extensions'))->where($db->quoteName('element') . ' = ' . $db->quote('com_j2store'));
            $db->setQuery($query);
            $manifest_cache = $db->loadResult();
            $registry = J2Store::platform()->getRegistry($manifest_cache);
            $this->_j2version = $registry->get('version');
        }

        return $this->_j2version;
    }

    function getCurrency($order, $convert = false)
    {
        $results = array();
        $currency_code = $order->currency_code;
        $currency_value = $order->currency_value;

        $results['currency_code'] = $currency_code;
        $results['currency_value'] = $currency_value;
        $results['convert'] = $convert;

        return $results;
    }

    public function generateHash($order)
    {
        $secrect_key = J2Store::config()->get('queue_key', '');
        $status = $this->params->get('payment_status', 4);
        $session = J2Store::platform()->application()->getSession();
        $session_id = $session->getId();
        $hash_string = $order->order_id . $secrect_key . $order->orderpayment_type . $secrect_key . $status . $secrect_key . $order->user_email . $secrect_key . $session_id . $secrect_key;
        return md5($hash_string);
    }

    public function validateHash($order)
    {
        $app = J2Store::platform()->application();
        $hash = $app->input->getString('hash', '');
        $generator_hash = $this->generateHash($order);
        $status = true;
        if ($hash != $generator_hash) {
            $status = false;
        }
        return $status;
    }

    /**
     * Return url for payment gateway
     */
    public function getReturnUrl()
    {
        $platform = J2Store::platform();
        $url = $platform->getThankyouPageUrl(array('orderpayment_type' => $this->_element, 'paction' => 'display'));
        /*$menus = JMenu::getInstance('site');
        $url = 'index.php?option=com_j2store&view=checkout&task=confirmPayment&layout=postpayment&orderpayment_type='.$this->_element.'&paction=display';
        foreach ($menus->getMenu() as $menu){
            if(isset($menu->type) && isset($menu->component) && isset($menu->query['option']) &&
                isset($menu->query['view']) && isset($menu->query['layout']) && isset($menu->query['task'])
                && $menu->type == 'component' && $menu->component == 'com_j2store'
                && $menu->query['option'] == 'com_j2store' && $menu->query['view'] == 'checkout'
                && $menu->query['layout'] == 'postpayment' && $menu->query['task'] == 'confirmPayment'){
                $url = 'index.php?option=com_j2store&view=checkout&task=confirmPayment&layout=postpayment&orderpayment_type='.$this->_element.'&paction=display&Itemid='.$menu->id;
                break;
            }
        }*/
        return $url;
    }

    function _getFormattedTransactionDetails( $data )
    {
        return json_encode($data);
    }
}
