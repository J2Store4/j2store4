<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;

$product = $this->singleton_product;
$params = $this->singleton_params;
$action = 'index.php?option=com_j2store&view=carts&task=addItem&product_id='.$product->j2store_product_id;
?>
<?php echo J2Store::plugin()->eventWithHtml('BeforeAddToCartButton', array($product, J2Store::utilities()->getContext('cart'))); ?>
<div class="cart-action-complete" style="display:none;">
		<p class="text-success">
			<?php echo JText::_('J2STORE_ITEM_ADDED_TO_CART');?>
			<a href="<?php echo $product->checkout_link; ?>" class="j2store-checkout-link">
				<?php echo JText::_('J2STORE_CHECKOUT'); ?>
			</a>
		</p>
</div>
<a class="<?php echo $params->get('addtocart_button_class', 'btn btn-primary');?> j2store_add_to_cart_button"
href="<?php echo JRoute::_($action); ?>" data-quantity="1" data-product_id="<?php echo $product->j2store_product_id;?>"
rel="nofollow">
<?php echo $this->singleton_cartext; ?>
</a>
<?php echo J2Store::plugin()->eventWithHtml('AfterAddToCartButton', array($product, J2Store::utilities()->getContext('cart'))); ?>