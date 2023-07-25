<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
?>
<h3><?php echo JText::_('J2STORE_CART_TOTALS'); ?></h3>
<table class="cart-footer table table-bordered">
				<?php if($totals = $this->order->get_formatted_order_totals()): ?>
					<?php foreach($totals as $total): ?>
						<tr valign="top">
							<th scope="row" colspan="2"> 
							<?php echo $total['label']; ?>
							<?php if(isset($total['link'])):?>
								<?php echo $total['link']; ?>
							<?php endif;?>
							</th>
							<td><?php echo $total['value']; ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</table>
			
			<div class="buttons-right">
				<span class="cart-checkout-button">
					<a class="btn btn-large btn-success" href="<?php echo $this->checkout_url; ?>" ><?php echo JText::_('J2STORE_PROCEED_TO_CHECKOUT'); ?> </a>
				</span>
				<?php echo J2Store::plugin()->eventWithHtml('AfterDisplayCheckoutButton', array($this->order)); ?>	
			</div>