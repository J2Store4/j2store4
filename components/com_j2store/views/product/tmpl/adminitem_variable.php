<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
$this->form_id = 'j2store-addtocart-form-'.$this->product->j2store_product_id;
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
<?php if( J2Store::product()->canShowCart($this->params) ): ?>
	<form action="<?php echo $this->product->cart_form_action; ?>"
		method="post" class="j2store-addtocart-form"
		id="<?php echo $this->form_id; ?>"
		name="j2store-addtocart-form-<?php echo $this->product->j2store_product_id; ?>"
		data-product_id="<?php echo $this->product->j2store_product_id; ?>"
		data-product_type="<?php echo $this->product->product_type; ?>"
		<?php if(isset($this->product->variant_json)): ?>
		data-product_variants="<?php echo $this->escape($this->product->variant_json);?>"
		<?php endif; ?>
		enctype="multipart/form-data">
		<?php if($this->product->has_options): ?>
				<?php echo $this->loadTemplate('variableoptions'); ?>
		<?php endif; ?>

		<?php echo $this->loadTemplate('cart'); ?>
		<div class="j2store-notifications"></div>
		<input type="hidden" name="variant_id" value="<?php echo $this->product->variant->j2store_variant_id; ?>" />
	</form>
<?php endif; ?>