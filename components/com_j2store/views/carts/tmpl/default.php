<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
$platform = J2Store::platform();
// get j2Store Params to determine which bootstrap version we're using - Waseem Sadiq (waseem@bulletprooftemplates.com)
$J2gridRow = ($this->params->get('bootstrap_version', 2) == 2) ? 'row-fluid' : 'row';
$J2gridCol = ($this->params->get('bootstrap_version', 2) == 2) ? 'span' : 'col-md-';
$app = JFactory::getApplication();
$active_menu = $app->getMenu()->getActive();
$page_heading = is_object($active_menu) ? $active_menu->getParams(): $platform->getRegistry('{}');
$page_heading_enabled = $page_heading->get('show_page_heading',0);
$page_heading_text = $page_heading->get('page_heading','');
?>
<?php if($page_heading_enabled):?>
    <div class="page-header">
        <h1> <?php echo $this->escape($page_heading_text); ?> </h1>
    </div>
<?php endif; ?>
<?php echo J2Store::modules()->loadposition('j2store-cart-top'); ?>
<div class="j2store">
	<div class="j2store-cart">
		<?php if(count($this->items)): ?>
        <div class="<?php echo $J2gridRow;?>">
			<div class="<?php echo $J2gridCol;?>12"><?php echo $this->before_display_cart;?></div>
        </div>
			<div class="<?php echo $J2gridRow;?>">
				<div class="<?php echo $J2gridCol;?>12">
					<form action="<?php echo $platform->getCartUrl(); ?>"
					      method="post"
					      name="j2store-cart-form"
					      id="j2store-cart-form"
					      enctype="multipart/form-data"
					>

						<input type="hidden" name="option" value="com_j2store" />
						<input type="hidden" name="view" value="carts" />
						<input type="hidden" id="j2store-cart-task" name="task" value="update" />

						<?php  echo $this->loadTemplate('items'); ?>

						<div class="j2store-cart-buttons">
							<div class="buttons-left">
				<span class="cart-continue-shopping-button">
					<?php if($this->continue_shopping_url->type != 'previous'): ?>
						<input class="btn btn-primary" type="button" onclick="window.location='<?php echo $this->continue_shopping_url->url; ?>';" value="<?php echo JText::_('J2STORE_CART_CONTINUE_SHOPPING'); ?>" />
					<?php else: ?>
						<input class="btn btn-primary" type="button" onclick="window.history.back();" value="<?php echo JText::_('J2STORE_CART_CONTINUE_SHOPPING'); ?>" />
					<?php endif;?>

				</span>
				<span class="cart-update-button">
					<input class="btn btn-warning" type="submit" value="<?php echo JText::_('J2STORE_CART_UPDATE'); ?>" />
				</span>
							</div>
							<div class="buttons-right">
								<?php if($this->params->get('show_clear_cart_button', 0)):?>
								<span class="cart-clear-button">
									<a class="btn btn-danger" href="<?php echo $platform->getCartUrl(array('task' => 'clearCart'));?>"><?php echo JText::_('J2STORE_EMPTY_CART')?></a>
								</span>
								<?php endif;?>
							</div>
						</div>
					</form>
					<!-- Display plugin results -->
					<?php  echo $this->after_display_cart; ?>
				</div>
			</div>

			<div class="<?php echo $J2gridRow;?>">
				<div class="<?php echo $J2gridCol;?>6">

					<div class="cart-estimator-discount-block">
						<?php if(J2Store::isPro()): ?>
							<?php echo $this->loadTemplate('coupon'); ?>
						<?php endif;?>
						<?php if(J2Store::isPro()): ?>
							<?php echo $this->loadTemplate('voucher'); ?>
						<?php endif;?>
						<?php echo $this->loadTemplate('calculator'); ?>
						<?php echo $this->loadTemplate('shipping'); ?>
					</div>

				</div>
				<div class="<?php echo $J2gridCol;?>6">
					<?php  echo $this->loadTemplate('totals'); ?>
				</div>
			</div>


		<?php else:  ?>
			<span class="cart-no-items">
				<?php echo JText::_('J2STORE_CART_NO_ITEMS'); ?>
			</span>
		<?php endif; ?>
	</div>
</div>
<?php echo J2Store::modules()->loadposition('j2store-cart-bottom'); ?>