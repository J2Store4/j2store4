<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();
$order = $this->order;
$platform = J2Store::platform();
$items = $this->order->getItems();
$this->taxes = $order->getOrderTaxrates();
$this->shipping = $order->getOrderShippingRate();
$currency = J2Store::currency();
$colspan = '2';

?>
	<h3><?php echo JText::_('J2STORE_ORDER_SUMMARY')?></h3>
	<table class="j2store-cart-table table table-bordered">
		<thead>
			<tr>
				<th width="70%"><?php echo JText::_('J2STORE_CART_LINE_ITEM'); ?></th>
				<th width="10%"><?php echo JText::_('J2STORE_CART_LINE_ITEM_QUANTITY'); ?></th>
				<?php if(isset($this->taxes) && count($this->taxes) && $this->params->get('show_item_tax', 0)): ?>
					<?php $colspan = '3'; ?>
					<th><?php echo JText::_('J2STORE_CART_LINE_ITEM_TAX'); ?></th>
				<?php endif; ?>
				<th width="20%"><?php echo JText::_('J2STORE_CART_LINE_ITEM_TOTAL'); ?></th>
			</tr>
			</thead>
			<tbody>

				<?php foreach ($items as $item): ?>
				<?php
					$item->params = $platform->getRegistry($item->orderitem_params);
					$thumb_image = $item->params->get('thumb_image', '');
                    $back_order_text = $item->params->get('back_order_item', '');
				?>
				<tr>
					<td>
						<?php if($this->params->get('show_thumb_cart', 1) && !empty($thumb_image) && JFile::exists(JPATH_SITE.JPath::clean('/'.$thumb_image))): ?>
							<span class="cart-thumb-image">
								<img alt="<?php echo $item->orderitem_name; ?>" src="<?php echo JURI::root(true).JPath::clean('/'.$thumb_image); ?>" >
							</span>
						<?php endif; ?>
						<span class="cart-product-name">
							<?php echo $item->orderitem_name; ?> 
						</span>
						<br />
						<?php if(isset($item->orderitemattributes)): ?>
							<span class="cart-item-options">
							<?php foreach ($item->orderitemattributes as $attribute):
								if($attribute->orderitemattribute_type == 'file') {
									unset($table);
									$table = F0FTable::getInstance('Upload', 'J2StoreTable')->getClone();
									if($table->load(array('mangled_name'=>$attribute->orderitemattribute_value))) {
										$attribute_value = $table->original_name;
									}
								}else {
									$attribute_value = JText::_($attribute->orderitemattribute_value);
								}
							?>
								<small>
								- <?php echo JText::_($attribute->orderitemattribute_name); ?> : <?php echo nl2br($attribute_value); ?>
								</small>						
             				   	<br />
							<?php endforeach;?>
							</span>
						<?php endif; ?>

						<?php if($this->params->get('show_price_field', 1)): ?>

							<span class="cart-product-unit-price">
								<span class="cart-item-title"><?php echo JText::_('J2STORE_CART_LINE_ITEM_UNIT_PRICE'); ?></span>								
								<span class="cart-item-value">
								<?php echo $currency->format($this->order->get_formatted_lineitem_price($item, $this->params->get('checkout_price_display_options', 1))); ?>
								</span>
							</span>
						<?php endif; ?>

						<?php if($this->params->get('show_sku', 1)): ?>
						<br />
							<span class="cart-product-sku">
								<span class="cart-item-title"><?php echo JText::_('J2STORE_CART_LINE_ITEM_SKU'); ?></span>
								<span class="cart-item-value"><?php echo $item->orderitem_sku; ?></span>
							</span>

						<?php endif; ?>

                        <?php if($back_order_text):?>
                            <br />
                            <span class="label label-inverse"><?php echo JText::_($back_order_text);?></span>
                        <?php endif;?>
						<?php echo J2Store::plugin()->eventWithHtml('AfterDisplayLineItemTitle', array($item, $this->order, $this->params));?>
					</td>
					<td><?php echo $item->orderitem_quantity; ?></td>

					<?php if(isset($this->taxes) && count($this->taxes) && $this->params->get('show_item_tax', 0)): ?>
						<td><?php 	echo $currency->format($item->orderitem_tax);	?></td>
					<?php endif; ?>

					<td class="cart-line-subtotal">
						<?php echo $currency->format($this->order->get_formatted_lineitem_total($item, $this->params->get('checkout_price_display_options', 1))); ?>
						<?php echo J2Store::plugin()->eventWithHtml('AfterDisplayLineItemTotal', array($item, $this->order, $this->params));?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
			<tfoot class="cart-footer">
				<?php if($totals = $this->order->get_formatted_order_totals()): ?>
					<?php foreach($totals as $total): ?>
						<tr>
							<th scope="row" colspan="<?php echo $colspan; ?>"> <?php echo $total['label']; ?></th>
							<td><?php echo $total['value']; ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tfoot>
			</table>