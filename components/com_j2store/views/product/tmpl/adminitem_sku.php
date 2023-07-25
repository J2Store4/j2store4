<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
?>

<?php if(!empty($this->product->variant->sku) && $this->params->get('show_sku', 1)) : ?>
	<div class="product-sku">
		<span class="sku-text"><?php echo JText::_('J2STORE_SKU')?></span>
		<span class="sku"> <?php echo $this->escape($this->product->variant->sku); ?> </span>
	</div>
<?php endif; ?>

<?php if($this->params->get('show_manufacturer', 0) && !empty($this->product->manufacturer)): ?>
	<span class="manufacturer-brand-text"> <?php echo JText::_('J2STORE_PRODUCT_MANUFACTURER_NAME'); ?> </span>
	<span class="manufacturer-brand">
		<?php if(isset($this->product->brand_desc_id) && !empty($this->product->brand_desc_id)):?>
            <?php $url = J2Store::article()->getArticleLink($this->product->brand_desc_id);?>
			<a href="<?php echo $url;?>" target="_blank"><?php echo $this->escape($this->product->manufacturer);?></a>
		<?php else:?>
			<?php echo $this->escape($this->product->manufacturer); ?>
		<?php endif;?>
	</span>
<?php endif; ?>