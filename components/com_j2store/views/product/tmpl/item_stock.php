<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;

?>
	<div class="product-stock-container">
		<?php if($this->product->variant->availability): ?>
			<span class="<?php echo $this->product->variant->availability ? 'instock':'outofstock'; ?>">
				<?php echo J2Store::product()->displayStock($this->product->variant, $this->params); ?>
			</span>	
		<?php else: ?>
			<span class="outofstock">
				<?php echo JText::_('J2STORE_OUT_OF_STOCK'); ?>
			</span>
		<?php endif; ?>
	</div>

	<?php if($this->product->variant->allow_backorder == 2 && !$this->product->variant->availability): ?>
		<span class="backorder-notification">
			<?php echo JText::_('J2STORE_BACKORDER_NOTIFICATION'); ?>
		</span>	
	<?php endif; ?>
