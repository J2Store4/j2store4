<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
?>

<?php $images = $this->loadTemplate('images');
J2Store::plugin()->event('BeforeDisplayImages', array(&$images, $this, 'com_j2store.products.list.bootstrap'));
echo $images;
?>
<?php echo $this->loadTemplate('title'); ?>
<?php if(isset($this->product->event->afterDisplayTitle)) : ?>
		<?php echo $this->product->event->afterDisplayTitle; ?>
<?php endif;?>

<?php if(isset($this->product->event->beforeDisplayContent)) : ?>
	<?php echo $this->product->event->beforeDisplayContent; ?>
<?php endif;?>

<?php echo $this->loadTemplate('description'); ?>

<?php if( J2Store::product()->canShowprice($this->params) ): ?>
<?php echo $this->loadTemplate('price'); ?>
<?php endif; ?>

<?php if($this->params->get('list_show_product_sku', 1) && J2Store::product()->canShowSku($this->params)) : ?>
	<?php echo $this->loadTemplate('sku'); ?>
<?php endif; ?>

<?php if($this->params->get('list_show_product_stock', 1) && J2Store::product()->managing_stock($this->product->variant)): ?>
	<?php echo $this->loadTemplate('stock'); ?>
<?php endif; ?>

<?php if( J2Store::product()->canShowCart($this->params) ): ?>

<form action="<?php echo $this->product->cart_form_action; ?>"
		method="post" class="j2store-addtocart-form"
		id="j2store-addtocart-form-<?php echo $this->product->j2store_product_id; ?>"
		name="j2store-addtocart-form-<?php echo $this->product->j2store_product_id; ?>"
		data-product_id="<?php echo $this->product->j2store_product_id; ?>"
		data-product_type="<?php echo $this->product->product_type; ?>"
		enctype="multipart/form-data">

<?php $cart_type = $this->params->get('list_show_cart', 1); ?>
<?php if($cart_type == 1) : ?>
	<?php echo $this->loadTemplate('options'); ?>
	<?php echo $this->loadTemplate('cart'); ?>

<?php elseif( ($cart_type == 2 && count($this->product->options)) || $cart_type == 3 ):?>
<!-- we have options so we just redirect -->
	<a href="<?php echo $this->product->product_link; ?>" class="<?php echo $this->params->get('choosebtn_class', 'btn btn-success'); ?>"><?php echo JText::_('J2STORE_VIEW_PRODUCT_DETAILS'); ?></a>
<?php else: ?>
	<?php echo $this->loadTemplate('cart'); ?>
<?php endif; ?>

</form>

<?php endif; ?>

<?php if(isset($this->product->event->afterDisplayContent)) : ?>
	<?php echo $this->product->event->afterDisplayContent; ?>
<?php endif;?>

