<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
//pricing options
$variant_pricing_calculator = (isset($this->variant->pricing_calculator))?$this->variant->pricing_calculator :'';
$pricing_calculator = J2Html::select()->clearState()
->type('genericlist')
->name($this->form_prefix.'[pricing_calculator]')
->value($variant_pricing_calculator)
->setPlaceHolders(J2Store::product()->getPricingCalculators())
->getHtml();
$base_path = rtrim(JUri::root(),'/').'/administrator';
?>
<?php if (version_compare(JVERSION, '3.99.99', 'lt')): ?>
<style>
    .fancybox-slide--iframe .fancybox-content {
        width: 1000px !important;
        height: 600px !important;
    }
</style>
<?php endif; ?>
<div class="j2store-product-pricing">
	<div class="control-group">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_REGULAR_PRICE'), 'price' ,array('class'=>'control-label')); ?>
		<?php echo J2Html::price($this->form_prefix.'[price]',(isset($this->variant->price))? $this->variant->price:'', array('class'=>'input')); ?>
	</div>
	<div class="control-group">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_SET_ADVANCED_PRICING'), 'sale_price',array('class'=>'control-label')); ?>
		<!-- Link to advanced pricing options. Opens as a popup. -->
        <a data-fancybox class="btn btn-success" data-type="iframe" data-src="<?php echo $base_path."/index.php?option=com_j2store&view=products&task=setproductprice&variant_id=".$this->variant->j2store_variant_id."&layout=productpricing&tmpl=component";?>" href="javascript:;">
            <?php echo JText::_( "J2STORE_PRODUCT_SET_PRICES" );?>
        </a>
		<?php //echo J2StorePopup::popup( $base_path."/index.php?option=com_j2store&view=products&task=setproductprice&variant_id=".$this->variant->j2store_variant_id."&layout=productpricing&tmpl=component", JText::_( "J2STORE_PRODUCT_SET_PRICES" ), array('class'=>'btn btn-success'));?>
	</div>
	<div class="control-group">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_PRICING_CALCULATOR'), 'price_calculator',array('class'=>'control-label')); ?>
		<?php //dropdown list: pre-populate it with Standard (to start with). We will extend this at a later point of time ?>
		<?php echo $pricing_calculator;?>
	</div>
</div>

<div class="alert alert-info">
<h4><?php echo JText::_('J2STORE_QUICK_HELP'); ?></h4>
<?php echo JText::_('J2STORE_PRODUCT_PRICE_HELP_TEXT'); ?>
</div>