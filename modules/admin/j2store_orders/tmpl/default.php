<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();

$currency =J2Store::currency();
?>
<?php if($orders):?>
<div class="j2store_latest_orders">
	<h3><i class="fa fa-shopping-cart"></i><?php echo JText::_('J2STORE_LATEST_ORDERS'); ?></h3>
	<table class="adminlist table table-striped table-bordered">
		<thead>
			<th><?php echo JText::_('J2STORE_DATE')?></th>
			<th><?php echo JText::_('J2STORE_INVOICE_NO')?></th>
			<th><?php echo JText::_('J2STORE_EMAIL')?></th>
			<th><?php echo JText::_('J2STORE_AMOUNT')?></th>

		</thead>
		<tbody>
			<?php foreach($orders as $order):
			if(isset($order->invoice_number) && $order->invoice_number > 0) {
				$invoice_number = $order->invoice_prefix.$order->invoice_number;
			}else {
				$invoice_number = $order->j2store_order_id;
			}
			$link 	= 'index.php?option=com_j2store&view=order&id='. $order->j2store_order_id;
			?>
			<tr>
				<td><?php echo JHTML::_('date', $order->created_on, $params->get('date_format', JText::_('DATE_FORMAT_LC1'))); ?>
				</td>
				<td><strong><a href="<?php echo $link; ?>"><?php echo $invoice_number; ?></a></strong></td>
				<td><?php echo $order->user_email; ?></td>
				<td><?php echo $currency->format( $order->order_total, $order->currency_code, $order->currency_value ); ?>
				</td>

			</tr>
			<?php endforeach;?>
		</tbody>

	</table>


</div>
<?php endif;?>
