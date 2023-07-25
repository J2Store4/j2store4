<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
?>
<?php echo J2Store::plugin()->eventWithHtml('BeforeRenderingProductPrice', array($this->product)); ?>

<?php if($this->params->get('show_base_price', 1) || $this->params->get('show_price_field', 1)): ?>
<div class="product-price-container">
		<?php if($this->params->get('show_base_price', 1) && $this->product->pricing->base_price != $this->product->pricing->price): ?>
			<?php $class='';?>
			<?php if(isset($this->product->pricing->is_discount_pricing_available)) $class='strike'; ?>
			<div class="base-price <?php echo $class?>">					
					<?php echo J2Store::product()->displayPrice($this->product->pricing->base_price, $this->product, $this->params);?>					
			</div>
		<?php endif; ?>

		<?php if($this->params->get('show_price_field', 1)): ?>
			<div class="sale-price">			
				<?php echo J2Store::product()->displayPrice($this->product->pricing->price, $this->product, $this->params);?>				
			</div>
		<?php endif; ?>		
		<?php if($this->params->get('display_price_with_tax_info', 0) ): ?>
			<div class="tax-text">
				<?php echo J2Store::product()->get_tax_text(); ?>				
			</div>
		<?php endif; ?>
	
</div>
<?php endif; ?>

<?php echo J2Store::plugin()->eventWithHtml('AfterRenderingProductPrice', array($this->product)); ?>