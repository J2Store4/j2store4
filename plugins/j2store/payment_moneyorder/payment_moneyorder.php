<?php
/*------------------------------------------------------------------------
# com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/payment.php');

class plgJ2StorePayment_moneyorder extends J2StorePaymentPlugin
{
	/**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
    var $_element    = 'payment_moneyorder';

	/**
	 * Constructor
	 *
	 * For php4 compatibility we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 2.5
	 */
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( 'com_j2store', JPATH_ADMINISTRATOR );
	}

    function onJ2StoreIsJ2Store4($element){
        if (!$this->_isMe($element)) {
            return null;
        }
        return true;
    }

	function onJ2StoreCalculateFees($order) {
		// is customer selected this method for payment ? If yes, apply the fees
		$payment_method = $order->get_payment_method ();
		
		if ($payment_method == $this->_element) {
			$total = $order->order_subtotal + $order->order_shipping + $order->order_shipping_tax;
			$surcharge = 0;
			$surcharge_percent = $this->params->get ( 'surcharge_percent', 0 );
			$surcharge_fixed = $this->params->get ( 'surcharge_fixed', 0 );
			if (( float ) $surcharge_percent > 0 || ( float ) $surcharge_fixed > 0) {
				// percentage
				if (( float ) $surcharge_percent > 0) {
					$surcharge += ($total * ( float ) $surcharge_percent) / 100;
				}
				
				if (( float ) $surcharge_fixed > 0) {
					$surcharge += ( float ) $surcharge_fixed;
				}
				
				$name = $this->params->get ( 'surcharge_name', JText::_ ( 'J2STORE_CART_SURCHARGE' ) );
				$tax_class_id = $this->params->get ( 'surcharge_tax_class_id', '' );
				$taxable = false;
				if ($tax_class_id && $tax_class_id > 0)
					$taxable = true;
				if ($surcharge > 0) {
					$order->add_fee ( $name, round ( $surcharge, 2 ), $taxable, $tax_class_id );
				}
			}
		}
	}

    /**
     * Prepares the payment form
     * and returns HTML Form to be displayed to the user
     * generally will have a message saying, 'confirm entries, then click complete order'
     *
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    function _prePayment( $data )
    {
        // prepare the payment form

        $vars = new JObject();
        $vars->order_id = $data['order_id'];
        $vars->orderpayment_id = $data['orderpayment_id'];
        $vars->orderpayment_amount = $data['orderpayment_amount'];
        $vars->orderpayment_type = $this->_element;
        $vars->moneyorder_information = $this->params->get('moneyorder_information', '');
        $vars->display_name = $this->params->get('display_name', JText::_( "PLG_J2STORE_PAYMENT_MONEYORDER"));
        $vars->onbeforepayment_text = $this->params->get('onbeforepayment', '');
        $vars->button_text = $this->params->get('button_text', 'J2STORE_PLACE_ORDER');
		$order = J2Store::fof()->loadTable( 'Order', 'J2StoreTable' );
		$order->load ( array (
			'order_id' => $vars->order_id
		) );
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
	 */
	function _postPayment($data) {
		// Process the payment
		$vars = new \stdClass();
		$app = JFactory::getApplication ();
		$paction = $app->input->getString ( 'paction' );

		switch ($paction) {
			case 'display' :
				$vars->onafterpayment_text = JText::_ ( $this->params->get ( 'onafterpayment', '' ) );
				$html = $this->_getLayout ( 'postpayment', $vars );
				$html .= $this->_displayArticle ();
				break;
			case 'process' :
				JSession::checkToken() or die( 'Invalid Token' );
				$result = $this->_process ($data);
				$json = json_encode ( $result );
				echo $json;
				$app->close ();
				break;
			default :
				$vars->message = JText::_ ( $this->params->get ( 'onerrorpayment', '' ) );
				$html = $this->_getLayout ( 'message', $vars );
				break;
		}

		return $html;
	}

	/**
	 * Processes the payment form
	 * and returns HTML to be displayed to the user
	 * generally with a success/failed message
	 *
	 * @param $data array
	 *        	form post data
	 * @return string HTML to display
	 */
	function _process($data) {
		// Process the payment
		$app = JFactory::getApplication ();
		$json = array();
		$order_id = $app->input->getString( 'order_id' );
		$order = J2Store::fof()->loadTable( 'Order', 'J2StoreTable' );
		if ($order->load ( array (
				'order_id' => $order_id
		) )) {

			if(($order->orderpayment_type != $this->_element) || !$this->validateHash($order)) {
				$json ['error'] = $this->params->get ( 'onerrorpayment', '' );
				return $json;
			}

			$moneyorder_information = $this->params->get ( 'moneyorder_information', '' );

			if (strlen ( $moneyorder_information ) > 5) {

				$html = '<br />';
				$html .= '<strong>' . JText::_ ( 'J2STORE_MONEYORDER_INSTRUCTIONS' ) . '</strong>';
				$html .= '<br />';
				$html .= $moneyorder_information;
                if($this->params->get('enable_strip_tags',0)){
                    $html = strip_tags(preg_replace('#<br\s*/?>#i', "\n",$html));
                }
				$order->customer_note = $order->customer_note . $html;
			}

			$order_state_id = $this->params->get ( 'payment_status', 4 ); // DEFAULT: PENDING
			if ($order_state_id == 1) {

				// set order to confirmed and set the payment process complete.
				$order->payment_complete ();
			} else {
				// set the chosen order status and force notify customer
				$order->update_status ( $order_state_id, true );
				// also reduce stock
				$order->reduce_order_stock ();
			}

			if ($order->store ()) {
				$order->empty_cart();
				$json ['success'] = JText::_ ( $this->params->get ( 'onafterpayment', '' ) );
				$json ['redirect'] = $this->getReturnUrl();
                //JRoute::_ ( 'index.php?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=' . $this->_element . '&paction=display' );
			} else {
				$json ['error'] = $order->getError ();
			}
		} else {
			// order not found
			$json ['error'] = $this->params->get ( 'onerrorpayment', '' );
		}
		return $json;
	}

    /**
     * Prepares variables and
     * Renders the form for collecting payment info
     *
     * @return unknown_type
     */
    function _renderForm( $data )
    {
    	$user = JFactory::getUser();
        $vars = new JObject();
        $vars->onselection_text = $this->params->get('onselection', '');
        $html = $this->_getLayout('form', $vars);
        return $html;
    }
}
