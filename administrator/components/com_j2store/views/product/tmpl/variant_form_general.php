<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
?>

<div class="j2store-product-general">
	<div class="control-group">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_SKU'), 'sku',array('class'=>'control-label')); ?>
		<?php echo J2Html::text($this->form_prefix.'[sku]', $this->item->sku,array('class'=>'input-small ')); ?>
	</div>

	<div class="control-group">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_UPC'), 'upc',array('class'=>'control-label')); ?>
		<?php echo J2Html::text($this->form_prefix.'[upc]', $this->item->upc,array('class'=>'input-small ')); ?>
	</div>

</div>