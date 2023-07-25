<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
?>
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
	<?php if( J2Store::product()->canShowCart($this->params) ): ?>

	<form action="<?php echo $this->product->cart_form_action; ?>"
		method="post" class="j2store-addtocart-form"
		id="j2store-addtocart-form-<?php echo $this->product->j2store_product_id; ?>"
		name="j2store-addtocart-form-<?php echo $this->product->j2store_product_id; ?>"
		data-product_id="<?php echo $this->product->j2store_product_id; ?>"
		data-product_type="<?php echo $this->product->product_type; ?>"				
		enctype="multipart/form-data">
		<?php if($this->product->has_options): ?>
				<?php echo $this->loadTemplate('options'); ?>
		<?php endif; ?>

		<?php echo $this->loadTemplate('cart'); ?>
		<div class="j2store-notifications"></div>
	</form>
	<?php endif; ?>