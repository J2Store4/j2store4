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
$action = J2Store::platform()->getCartUrl(array('task' => 'addItem','product_id' => (int)$product->j2store_product_id));

if(!empty($product->addtocart_text)) {
	$cart_text = JText::_($product->addtocart_text);
} else {
	$cart_text = JText::_('J2STORE_ADD_TO_CART');
}

if($product->variant->availability || J2Store::product()->backorders_allowed($product->variant)) {
	$show = true;
} else {
	$show = false;
}

?>

<?php echo J2Store::plugin()->eventWithHtml('BeforeAddToCartButton', array($product, J2Store::utilities()->getContext('cart'))); ?>
<?php if($show): ?>
	<div class="cart-action-complete" style="display:none;">
			<p class="text-success">
				<?php echo JText::_('J2STORE_ITEM_ADDED_TO_CART');?>
				<a href="<?php echo $product->checkout_link; ?>" class="j2store-checkout-link">
					<?php echo JText::_('J2STORE_CHECKOUT'); ?>
				</a>
			</p>
	</div>
	
	<?php if(count($product->options) || $product->product_type == 'variable'): ?>
				<a class="<?php echo $params->get('choosebtn_class', 'btn btn-success'); ?>"
			    	href="<?php echo $product->product_view_url; ?>">
							<?php echo JText::_('J2STORE_CART_CHOOSE_OPTIONS'); ?>
				</a>
			<?php else: ?>	
	
	
		<a class="<?php echo $params->get('addtocart_button_class', 'btn btn-primary');?> j2store_add_to_cart_button"
		href="<?php echo $action; ?>" data-quantity="1" data-product_id="<?php echo $product->j2store_product_id;?>"
		rel="nofollow">
		<?php echo $cart_text; ?>
		</a>
	<?php endif; ?>

<?php else: ?>
	<span class="outofstock">
		<?php echo JText::_('J2STORE_OUT_OF_STOCK'); ?>
	</span>
<?php endif; ?>

<?php echo J2Store::plugin()->eventWithHtml('AfterAddToCartButton', array($product, J2Store::utilities()->getContext('cart'))); ?>