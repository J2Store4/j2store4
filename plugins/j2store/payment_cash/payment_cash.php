<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR . '/components/com_j2store/library/plugins/payment.php');

class plgJ2StorePayment_cash extends J2StorePaymentPlugin
{
    /**
     * @var $_element  string  Should always correspond with the plugin's filename,
     *                         forcing it to be unique
     */
    var $_element = 'payment_cash';

    /**
     * Constructor
     *
     * For php4 compatibility we must not use the __constructor as a constructor for plugins
     * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
     * This causes problems with cross-referencing necessary for the observer design pattern.
     *
     * @param object $subject The object to observe
     * @param array $config An array that holds the plugin configuration
     * @since 2.5
     */
    function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        $this->loadLanguage('com_j2store', JPATH_ADMINISTRATOR);
    }
    function onJ2StoreIsJ2Store4($element){
        if (!$this->_isMe($element)) {
            return null;
        }
        return true;
    }
    function onJ2StoreCalculateFees($order)
    {
        //is customer selected this method for payment ? If yes, apply the fees
        $payment_method = $order->get_payment_method();

        if ($payment_method == $this->_element) {
            $total = $order->order_subtotal + $order->order_shipping + $order->order_shipping_tax;
            $surcharge = 0;
            $surcharge_percent = $this->params->get('surcharge_percent', 0);
            $surcharge_fixed = $this->params->get('surcharge_fixed', 0);

            if ((float)$surcharge_percent > 0 || (float)$surcharge_fixed > 0) {
                //percentage
                if ((float)$surcharge_percent > 0) {
                    $surcharge += ($total * (float)$surcharge_percent) / 100;
                }

                if ((float)$surcharge_fixed > 0) {
                    $surcharge += (float)$surcharge_fixed;
                }

                $name = $this->params->get('surcharge_name', JText::_('J2STORE_CART_SURCHARGE'));
                $tax_class_id = $this->params->get('surcharge_tax_class_id', '');
                $taxable = false;
                if ($tax_class_id && $tax_class_id > 0) $taxable = true;
                if ($surcharge > 0) {
                    $order->add_fee($name, round($surcharge, 2), $taxable, $tax_class_id);
                }

            }


        }

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
                ->where('gz.j2store_geozone_id=' . $geozone_id)
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
        if ($found) {
            $min_subtotal = $this->params->get('min_subtotal', 0);
            $max_subtotal = $this->params->get('max_subtotal', -1);
            if (($min_subtotal == 0 || $order->order_subtotal >= $min_subtotal) && ($max_subtotal == -1 || $order->order_subtotal <= $max_subtotal)) {
                $found = true;
            } else {
                $found = false;
            }
        }
        return $found;
    }

    /**
     * Prepares the payment form
     * and returns HTML Form to be displayed to the user
     * generally will have a message saying, 'confirm entries, then click complete order'
     *
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    function _prePayment($data)
    {
        $vars = new \stdClass();
        $vars->order_id = $data['order_id'];
        $vars->orderpayment_amount = $data['orderpayment_amount'];
        $vars->orderpayment_type = $this->_element;
        $vars->display_name = $this->params->get('display_name', JText::_("PLG_J2STORE_PAYMENT_CASH"));
        $vars->onbeforepayment_text = $this->params->get('onbeforepayment', '');
        $vars->button_text = $this->params->get('button_text', 'J2STORE_PLACE_ORDER');
        $fof_helper = J2Store::fof();
        $order = $fof_helper->loadTable('Order', 'J2StoreTable', array('order_id' => $data['order_id']));
        $vars->hash = $this->generateHash($order);
        return $this->_getLayout('prepayment', $vars);
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
        $vars = new \stdClass();
        $app = J2Store::platform()->application();
        $paction = $app->input->getString('paction');
        switch ($paction) {
            case 'display' :
                $vars->onafterpayment_text = JText::_($this->params->get('onafterpayment', ''));
                $html = $this->_getLayout('postpayment', $vars);
                $html .= $this->_displayArticle();
                break;
            case 'process' :
                JSession::checkToken() or die('Invalid Token');
                $result = $this->_process($data);
                $json = json_encode($result);
                echo $json;
                $app->close();
                break;
            default :
                $vars->message = JText::_($this->params->get('onerrorpayment', ''));
                $html = $this->_getLayout('message', $vars);
                break;
        }

        return $html;
    }

    /**
     * Processes the payment form
     * and returns HTML to be displayed to the user
     * generally with a success/failed message
     *
     * @param $data     array       form post data
     * @return array   HTML to display
     * @throws Exception
     */
    function _process($data)
    {
        // Process the payment
        $app = J2Store::platform()->application();
        $json = array();
        $order_id = $app->input->getString('order_id');
        $fof_helper = J2Store::fof();
        $order = $fof_helper->loadTable('Order', 'J2StoreTable');
        if ($order->load(array(
            'order_id' => $order_id
        ))) {

            if (($order->orderpayment_type != $this->_element) || !$this->validateHash($order)) {
                $json ['error'] = $this->params->get('onerrorpayment', '');
                return $json;
            }


            $order_state_id = $this->params->get('payment_status', 4); // DEFAULT: PENDING

            if ($order_state_id == 1) {

                // set order to confirmed and set the payment process complete.
                $order->payment_complete();
            } else {
                // set the chosen order status and force notify customer
                $order->update_status($order_state_id, true);
                // also reduce stock
                $order->reduce_order_stock();
            }

            if ($order->store()) {
                //empty the cart
                $order->empty_cart();
                $json ['success'] = JText::_($this->params->get('onafterpayment', ''));
                $return_url = $this->getReturnUrl();
                $json ['redirect'] = JRoute::_($return_url);//JRoute::_ ( 'index.php?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=' . $this->_element . '&paction=display' );
            } else {
                //$html  = $this->params->get('onerrorpayment', '');
                $json ['error'] = $order->getError();
            }

        } else {
            // order not found
            $json ['error'] = $this->params->get('onerrorpayment', '');
        }

        return $json;
    }

    /**
     * Prepares variables and
     * Renders the form for collecting payment info
     *
     * @return string
     */
    function _renderForm($data)
    {
        $vars = new \stdClass();
        $vars->onselection_text = $this->params->get('onselection', '');
        return $this->_getLayout('form', $vars);
    }

    function getPaymentStatus($payment_status)
    {
        switch ($payment_status) {
            case 1:
                $status = JText::_('J2STORE_CONFIRMED');
                break;

            case 3:
                $status = JText::_('J2STORE_FAILED');
                break;

            default:
            case 4:
                $status = JText::_('J2STORE_PENDING');
                break;
        }
        return $status;
    }
}
