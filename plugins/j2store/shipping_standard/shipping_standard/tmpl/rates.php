<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
$rates = array();
$currency = J2Store::currency();
foreach($vars->rates as $rate){
	$r = new \stdClass();
	$r->value = $rate->shipping_rate_id;
	$r->text = $currency->format($rate->shipping_rate_price);
	$rates[] = &$r;
}
?>
<div class="shipping_rates">
<?php
echo JHTML::_( 'select.radiolist', $rates, 'shipping_rate', array() );
?>
</div>