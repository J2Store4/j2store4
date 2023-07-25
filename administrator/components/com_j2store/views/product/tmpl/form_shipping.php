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
		<?php echo J2Html::radio($this->form_prefix.'[shipping]',(isset($this->variant->shipping)) ? $this->variant->shipping:'' , array('class'=>'controls')); ?>
	</div>
	<div class="control-group form-inline">
			<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_DIMENSIONS'), 'dimensions' ,array('class'=>'control-label')); ?>
			<?php echo J2Html::text($this->form_prefix.'[length]',(isset($this->variant->length))?$this->variant->length:'',array('class'=>'input-mini', 'placeholder'=>JText::_('J2STORE_LENGTH'),'field_type'=>'integer'));?>
			<?php echo J2Html::text($this->form_prefix.'[width]',(isset($this->variant->width)) ? $this->variant->width:'',array('class'=>'input-mini', 'placeholder'=>JText::_('J2STORE_WIDTH'),'field_type'=>'integer'));?>
			<?php echo J2Html::text($this->form_prefix.'[height]',(isset($this->variant->height)) ? $this->variant->height : '',array('class'=>'input-mini', 'placeholder'=>JText::_('J2STORE_HEIGHT'),'field_type'=>'integer'));?>
		</div>
		<div class="control-group form-inline">
			<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_LENGTH_CLASS'), 'length_class',array('class'=>'control-label')); ?>
			<?php echo $this->lengths ;?>
		</div>

		<div class="control-group form-inline">
			<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_WEIGHT'), 'weight',array('class'=>'control-label')); ?>
			<?php echo J2Html::text($this->form_prefix.'[weight]',(isset($this->variant->weight))?$this->variant->weight:'',array('class'=>'input','field_type'=>'integer'));?>
			<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_WEIGHT_CLASS'), 'weight_class'); ?>
			<?php echo $this->weights; ?>
		</div>
<!-- 
	<div class="control-group">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_SHIPPING'), 'shipping' ,array('class'=>'control-label')); ?>
		<?php
/* 			$this->variant->shippingmethods = isset($this->variant->shippingmethods) ? explode(',',$this->variant->shippingmethods) : '';
		echo J2Html::select()->clearState()
		->type('genericlist')
		->name($this->form_prefix.'[shippingmethods][]')
		->attribs(array('multiple'=>true ,'id'=>'shippingInput'))
		->idTag('shippingInput')
		->value($this->variant->shippingmethods)
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
<script type="text/javascript">
(function($){
	$("#checkAllShipping").click(function(){
		$(this).attr('value',0);
		if(this.checked == true){
			$(this).attr('value',1);
		}
		$("#shippingInput").attr('disabled',this.checked);
	});
})(j2store.jQuery);
</script>