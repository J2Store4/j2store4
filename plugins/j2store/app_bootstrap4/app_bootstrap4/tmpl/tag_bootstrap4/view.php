<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 *
 * Bootstrap 2 layout of product detail
 */
// No direct access
defined('_JEXEC') or die;
?>
<div class="j2store-single-product <?php echo $this->product->product_type; ?> detail bs3 <?php echo $this->product->params->get('product_css_class','');?>">
	<?php if ($this->params->get('item_show_page_heading')) : ?>
		<div class="page-header">
			<h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
		</div>
	<?php endif; ?>
<?php echo J2Store::modules()->loadposition('j2store-single-product-top'); ?>
	<?php if($this->params->get('item_show_back_to',0) && isset($this->back_link) && !empty($this->back_link)):?>
		<div class="j2store-view-back-button">
			<a href="<?php echo $this->back_link; ?>" class="j2store-product-back-btn btn btn-small btn-info">
				<i class="fa fa-chevron-left"> </i> <?php echo JText::_('J2STORE_PRODUCT_BACK_TO').' '.$this->back_link_title; ?>
			</a>
		</div>
	<?php endif;?>
	<?php echo $this->loadTemplate($this->product->product_type); ?>
	<?php echo J2Store::plugin ()->eventWithHtml ( 'AfterProductDisplay', array($this->product,$this) )?>
<?php echo J2Store::modules()->loadposition('j2store-single-product-bottom'); ?>
</div>

