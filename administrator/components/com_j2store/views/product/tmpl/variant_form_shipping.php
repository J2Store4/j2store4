<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
?>

<div class="j2store-product-shipping">

	<div class="control-group form-inline">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_ENABLE_SHIPPING'), 'shipping',array('class'=>'control-label')); ?>

		<?php echo J2Html::radio($this->form_prefix.'[shipping]', $this->item->shipping,array('class'=>'controls')); ?>
	</div>
		<div class="control-group form-inline">
			<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_DIMENSIONS'), 'dimensions',array('class'=>'control-label')); ?>
			<?php echo J2Html::text($this->form_prefix.'[length]',$this->item->length,array('class'=>'input-mini'));?>
			<?php echo J2Html::text($this->form_prefix.'[width]',$this->item->width,array('class'=>'input-mini'));?>
			<?php echo J2Html::text($this->form_prefix.'[height]',$this->item->height,array('class'=>'input-mini'));?>
		</div>
		<div class="control-group form-inline">
			<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_LENGTH_CLASS'), 'length_class'); ?>
			<?php echo $this->lengths ;?>
		</div>

		<div class="control-group form-inline">
			<?php  echo J2Html::label(JText::_('J2STORE_PRODUCT_WEIGHT'), 'weight'); ?>
			<?php echo J2Html::text($this->form_prefix.'[weight]',$this->item->weight);?>
			<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_WEIGHT_CLASS'), 'weight_class'); ?>
			<?php echo $this->weights; ?>
		</div>
<!-- 
		<div class="control-group">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_SHIPPING'), 'shipping',array('class'=>'control-label')); ?>
		<?php
		/* $attribs = isset($this->item->allshipping ) ? array('multiple'=>true) : array('multiple'=>true);
		$this->item->shippingmethods = isset($this->item->shippingmethods) ? explode(',',$this->item->shippingmethods) : '';
		echo J2Html::select()->clearState()
		->type('genericlist')
		->name($this->form_prefix.'[shippingmethods][]')
		->attribs($attribs)
		->idTag('shippingInput')
		->value($this->item->shippingmethods)
		->setPlaceHolders(array(''=>JText::_('J2STORE_ALL_SHIPPINGS_METHODS')))
		->hasOne('Shippings')
		->setRelations(
				array (
						'fields' => array (
								'key'=>'element',
								'name'=>'element'
						)
				)
						)->getHtml(); */
		?>


	</div>
	 -->
</div>
