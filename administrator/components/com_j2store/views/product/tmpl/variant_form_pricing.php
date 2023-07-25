<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access
defined('_JEXEC') or die;
JHtml::_('behavior.modal');
?>
<div class="j2store-product-pricing">
	<div class="control-group">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_REGULAR_PRICE'), 'price',array('class'=>'control-label')); ?>
		<?php  echo J2Html::price($this->form_prefix.'[price]', $this->item->price,array('class'=>'input ')); ?>
	</div>

	<div class="control-group">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_SET_ADVANCED_PRICING'), 'sale_price',array('class'=>'control-label')); ?>
        <?php $base_path = rtrim(JUri::root(),'/').'/administrator';
        $url = $base_path ."/index.php?option=com_j2store&view=products&task=setproductprice&variant_id=".$this->item->j2store_variant_id."&layout=productpricing&tmpl=component";?>
		<?php echo J2StorePopup::popup($url , JText::_( "J2STORE_PRODUCT_SET_PRICES" ), array('class'=>'btn btn-success'));?>
	</div>
	<div class="form-group">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_PRICING_CALCULATOR'), 'pricing_calculator',array('class'=>'control-label')); ?>
		<?php //dropdown list: pre-populate it with Standard (to start with). We will extend this at a later point of time ?>
		<?php // echo $this->pricing_calculator;
        	//pricing options
	    	echo J2Html::select()->clearState()
						->type('genericlist')
						->name($this->form_prefix.'[pricing_calculator]')
						->value($this->item->pricing_calculator)
						->setPlaceHolders(J2Store::product()->getPricingCalculators())
						->getHtml();
		?>
	</div>


</div>