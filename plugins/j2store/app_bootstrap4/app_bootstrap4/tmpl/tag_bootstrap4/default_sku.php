<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
?>

<?php if(!empty($this->product->variant->sku)) : ?>
	<div class="product-sku">
		<span class="sku-text"><?php echo JText::_('J2STORE_SKU')?></span>
		<span class="sku"> <?php echo $this->escape($this->product->variant->sku); ?> </span>
	</div>
<?php endif; ?>