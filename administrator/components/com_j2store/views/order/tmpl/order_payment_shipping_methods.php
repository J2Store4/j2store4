<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
$platform = J2Store::platform();
?>
<div class="j2store-shipping " id="shippingcost-pane">
	<div id="onCheckoutShipping_wrapper">		
		<h3>
			<?php echo JText::_('J2STORE_ENTER_SHIPPING_DETAILS'); ?>
		</h3>
		<table>
			<tbody>
				<tr>
					<td><?php echo JText::_("J2STORE_SHIPPING_NAME");?></td>
					<td><input type="text" name="shipping_name" value="<?php echo $this->shipping_name;?>"></td>
				</tr>
				<tr>
					<td><?php echo JText::_("J2STORE_SHIPPING_PRICE");?></td>
					<td><input name="shipping_price" type="number" value="<?php  echo $this->shipping_price;?>" /></td>
				</tr>
                <tr>
                    <td><?php echo JText::_("J2STORE_SHIPPING_PRICE_TAX");?></td>
                    <td><input name="shipping_tax" type="number" value="<?php  echo $this->shipping_tax;?>" /></td>
                </tr>
				<tr>
					<td><?php echo JText::_("J2STORE_SHIPPING_TRACKING_ID");?></td>
					<td>
						<textarea rows="3" cols="6" name="shipping_tracking_id"><?php echo $this->shipping_tracking_id; ?></textarea>
					</td>
				</tr>
			</tbody>
		</table>				
	</div>
</div>
<div id='onCheckoutPayment_wrapper'>
	<h3>
		<?php echo JText::_('J2STORE_SELECT_A_PAYMENT_METHOD'); ?>
	</h3>
	<?php if (!empty($this->paymentplugins)): ?>
	<?php foreach ($this->paymentplugins as $plugin): ?>
	<?php
	$params= $platform->getRegistry($plugin->params);
	$image = $params->get('display_image', '');
	?>
	<?php echo J2Store::plugin()->eventWithHtml('BeforeDisplayPaymentMethod',array($plugin->element, $this->order)); ?>
	<label class="payment-plugin-image-label <?php echo $plugin->element; ?>">
			<?php if($this->order->orderpayment_type && $this->order->orderpayment_type == $plugin->element):?>
			<input	value="<?php echo $plugin->element; ?>" class="payment_plugin" name="payment_plugin" type="radio"
				onclick="j2storeGetPaymentForm('<?php echo $plugin->element; ?>', 'payment_form_div');"
				checked="checked" />
			<?php else:?>
			<input	value="<?php echo $plugin->element; ?>" class="payment_plugin" name="payment_plugin" type="radio"
					onclick="j2storeGetPaymentForm('<?php echo $plugin->element; ?>', 'payment_form_div');"
				<?php echo (!empty($plugin->checked)) ? "checked" : ""; ?> 	title="<?php echo JText::_('J2STORE_SELECT_A_PAYMENT_METHOD'); ?>" />
		<?php endif;?>
		<?php if(!empty($image)): ?>

		<img class="payment-plugin-image <?php echo $plugin->element; ?>" src="<?php echo JUri::root().JPath::clean($image); ?>" /> <?php endif; ?>
		<?php
			$title = $params->get('display_name', '');
		if(!empty($title)) {
			echo JText::_($title);
		} else {
			echo JText::_($plugin->name );
		}
		?>


	</label>

	<?php echo J2Store::plugin()->eventWithHtml('AfterDisplayPaymentMethod',array($plugin->element, $this->order)); ?>

	<?php endforeach; ?>
	<?php endif; ?>

</div>
<div class="j2error"></div>
<div id='payment_form_div' style="padding-top: 10px;">
	<?php
	if (!empty($this->payment_form_div))
	{
		echo $this->payment_form_div;
	}
	?>

</div>
