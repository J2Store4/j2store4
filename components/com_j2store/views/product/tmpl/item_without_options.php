<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
$platform = J2Store::platform();
?>

<div class="j2store-product_without_options j2store-product-<?php echo $this->product->j2store_product_id; ?>">

    <?php if( J2Store::product()->canShowSku($this->params) ): ?>
	<?php echo $this->loadTemplate('sku'); ?>
    <?php endif; ?>

    <?php if( J2Store::product()->canShowprice($this->params) ): ?>
	<?php echo $this->loadTemplate('price'); ?>
    <?php endif; ?>

	<?php if(J2Store::product()->managing_stock($this->product->variant)): ?>
		<?php echo $this->loadTemplate('stock'); ?>
	<?php endif; ?>

	<!-- check for catalog mode -->
	<?php if(J2Store::product()->canShowCart($this->params)): ?>

	<form action="<?php echo $this->product->cart_form_action; ?>"
		method="post" class="j2store-addtocart-form"
		id="j2store-addtocart-form-<?php echo $this->product->j2store_product_id; ?>"
		name="j2store-addtocart-form-<?php echo $this->product->j2store_product_id; ?>"
		data-product_id="<?php echo $this->product->j2store_product_id; ?>"
		data-product_type="<?php echo $this->product->product_type; ?>"
		enctype="multipart/form-data"
		>
		<?php 
		$plugin = JPluginHelper::getPlugin('content', 'j2store');
		$pluginParams = $platform->getRegistry($plugin->params);
		?>
		<?php if((count($this->product->options) && $pluginParams->get('category_product_options', 1) == 2 ) || $pluginParams->get('category_product_options', 1) == 3 ): ?>
			<!-- Product has options. Redirect -->
			<a class="cartbutton btn btn-primary" href="<?php echo $this->product->product_view_url; ?>" ><?php echo JText::_('J2STORE_CART_CHOOSE_OPTIONS'); ?></a>
		<?php else: ?>
			<?php echo $this->loadTemplate('cart'); ?>
		<?php endif; ?>
		</form>
	<?php endif; ?>
</div>