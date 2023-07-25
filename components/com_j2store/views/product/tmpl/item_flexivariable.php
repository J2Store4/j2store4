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
<?php if($this->params->get('show_sku', 1) && J2Store::product()->canShowSku($this->params) && isset($this->product->variant->sku) && !empty($this->product->variant->sku)) : ?>
    <div class="product-sku">
        <span class="sku-text"><?php echo JText::_('J2STORE_SKU')?> :</span>
        <span class="sku"> <?php echo $this->escape($this->product->variant->sku); ?> </span>
    </div>
<?php elseif ($this->params->get('show_sku', 1) && J2Store::product()->canShowSku($this->params)) : ?>
    <div class="product-sku">
        <span class="sku-text"><?php echo JText::_('J2STORE_SKU')?></span>
        <span class="sku"></span>
    </div>
<?php endif; ?>
<?php if( J2Store::product()->canShowprice($this->params) ): ?>
<?php echo $this->loadTemplate('flexiprice'); ?>
<?php endif; ?>

<?php if($this->params->get('list_show_product_stock', 1) ) : ?>
    <div class="product-stock-container">
        <?php if(isset($this->product->variant) && J2Store::product()->managing_stock($this->product->variant)):?>
            <?php if($this->product->variant->availability): ?>
                <span class="<?php echo $this->product->variant->availability ? 'instock':'outofstock'; ?>">
				                    <?php echo J2Store::product()->displayStock($this->product->variant, $this->params); ?>
			                    </span>
            <?php else: ?>
                <span class="outofstock">
				                    <?php echo JText::_('J2STORE_OUT_OF_STOCK'); ?>
			                    </span>
            <?php endif; ?>
        <?php else:?>
            <span class="instock"></span>
            <span class="outofstock"></span>
        <?php endif; ?>
    </div>

    <?php if(isset($this->product->variant->allow_backorder) && $this->product->variant->allow_backorder == 2 && !$this->product->variant->availability): ?>
        <span class="backorder-notification">
			                <?php echo JText::_('J2STORE_BACKORDER_NOTIFICATION'); ?>
		                </span>
    <?php else: ?>
        <span class="backorder-notification"></span>
    <?php endif; ?>
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
            <?php echo $this->loadTemplate('flexivariableoptions'); ?>
        <?php endif; ?>

        <?php echo $this->loadTemplate('cart'); ?>
        <div class="j2store-notifications"></div>
        <input type="hidden" name="variant_id" value="<?php echo isset($this->product->variant->j2store_variant_id) ? $this->product->variant->j2store_variant_id: ''; ?>" />
    </form>
<?php endif; ?>