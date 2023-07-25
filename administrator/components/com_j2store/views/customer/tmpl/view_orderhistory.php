<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();

?>
<h4><?php echo JText::_('J2STORE_ORDER_HISTORY');?></h4>
<?php echo J2Store::plugin()->eventWithHtml('BeforeCustomerOrderList',array($this->orders)); ?>
<table class="table table-striped table-condensed table-bordered">
	<thead>
		<tr>
			<th><?php echo JText::_("J2STORE_ORDER_DATE"); ?></th>
			<th><?php echo JText::_("J2STORE_INVOICE"); ?></th>
			<th><?php echo JText::_("J2STORE_TOTAL"); ?></th>
			<th><?php echo JText::_("J2STORE_ORDER_STATUS"); ?></th>

		</tr>
	</thead>
	<?php 	if($this->orders && !empty($this->orders)):
			foreach($this->orders as $order):?>
	<tr>
		<td>
			<?php echo JHTML::_('date', $order->created_on, JText::_('DATE_FORMAT_LC1')); ?>
		</td>
		<td>
			<a href="<?php echo 'index.php?option=com_j2store&view=order&id='.$order->j2store_order_id;?>">
			<?php echo $order->invoice;?>
			</a>
		</td>
		<td>
			<?php echo $this->currency->format($order->order_total,$order->currency_code,$order->currency_value);?>
		</td>
		<td>
			<?php echo J2Html::getOrderStatusHtml($order->order_state_id);?>
		</td>
	</tr>
	<?php endforeach;?>
	<?php endif;?>

</table>