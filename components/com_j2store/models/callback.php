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

defined ( '_JEXEC' ) or die ( 'Restricted access' );

class J2StoreModelCallback extends F0FModel {

	function runCallback($method) {

		$app = JFactory::getApplication ();
		$rawDataPost = $app->input->getArray($_POST);
		$rawDataGet = $app->input->getArray($_GET);
		$data = array_merge ( $rawDataGet, $rawDataPost );

		// Some plugins result in an empty Itemid being added to the request
		// data, screwing up the payment callback validation in some cases (e.g.
		// PayPal).
		if (array_key_exists ( 'Itemid', $data )) {
			if (empty ( $data ['Itemid'] )) {
				unset ( $data ['Itemid'] );
			}
		}
		
		$plugin_helper = J2Store::plugin();
		$row = $plugin_helper->getPlugin($method);
		
		//sanity check
		if($row == false || $row->element != $method) return false; //undefined method. Do not execute  
		
		//trigger a callback event
		J2Store::plugin()->event('Callback', array($row, $data));
		
		//run the post payment trigger. Callback normally used in post payment.
		$jResponse = J2Store::plugin()->event('PostPayment', array (
				$row,
				$data
		) );
		
		if (empty ( $jResponse ))
			return false;

		$status = false;

		foreach ( $jResponse as $response ) {
			$status = $status || $response;
		}

		return $status;
	}
}