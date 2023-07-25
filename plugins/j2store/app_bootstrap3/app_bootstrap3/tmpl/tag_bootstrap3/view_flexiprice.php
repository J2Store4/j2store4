<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
$currency = J2Store::currency();
?>
<?php echo J2Store::plugin()->eventWithHtml('BeforeRenderingProductPrice', array($this->product)); ?>
<?php $min_price = (isset($this->product->min_price) && !empty($this->product->min_price)) ? J2Store::product()->displayPrice($this->product->min_price, $this->product, $this->params): $currency->format(0);?>
<?php $max_price = (isset($this->product->max_price) && !empty($this->product->max_price)) ? J2Store::product()->displayPrice($this->product->max_price, $this->product, $this->params): $currency->format(0);?>
    <div class="flexi-product-price-range">
        <strong><?php echo JText::_('J2STORE_PRODUCT_PRICE_RANGE');?></strong> <strong><?php echo JText::sprintf('J2STORE_PRICE_RANGE_FROM_TO',$min_price,$max_price); ?>	</strong>
    </div>
<?php if($this->params->get('item_show_product_base_price', 1) || $this->params->get('item_show_product_special_price', 1)): ?>
    <div class="product-price-container">
        <?php if($this->params->get('item_show_product_base_price', 1) && isset($this->product->pricing->base_price) && isset($this->product->pricing->price) && $this->product->pricing->base_price != $this->product->pricing->price): ?>
            <?php $class='';?>
            <?php if(isset($this->product->pricing->is_discount_pricing_available)) $class='strike'; ?>
            <?php $base_price = J2Store::product()->displayPrice($this->product->pricing->base_price, $this->product, $this->params); ?>
            <div class="base-price <?php echo $class?>">
                <?php echo $base_price;?>
            </div>
        <?php elseif ($this->params->get('item_show_product_base_price', 1)): ?>
            <?php $class='';?>
            <div class="base-price <?php echo $class?>"></div>
        <?php endif; ?>

        <?php if($this->params->get('item_show_product_special_price', 1)): ?>
            <div class="sale-price">
                <?php if( isset($this->product->pricing->price)) echo J2Store::product()->displayPrice($this->product->pricing->price, $this->product, $this->params);?>
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

<?php if($this->params->get('item_show_discount_percentage', 1)):?>
    <div class="discount-percentage">
        <?php if( isset($this->product->pricing->is_discount_pricing_available) && isset($this->product->pricing->base_price) && !empty($this->product->pricing->base_price) && $this->product->pricing->base_price > 0): ?>
            <?php $discount =(1 - ($this->product->pricing->price / $this->product->pricing->base_price) ) * 100; ?>
            <?php if($discount > 0): ?>
                <?php  echo JText::sprintf('J2STORE_PRODUCT_OFFER',round($discount).'%');?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>