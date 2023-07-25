<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access
defined('_JEXEC') or die;
?>
<?php if(J2Store::isPro() == 1) : ?>
<div class="j2store-product-general">
	<div class="control-group form-inline">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_MANAGE_STOCK'), 'manage_stock',array('class'=>'control-label')); ?>
		<?php echo J2Html::radioBooleanList($this->form_prefix.'[manage_stock]',$this->item->manage_stock,array('hide_label'=>true) );?>
	</div>
	<div class="control-group">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_QUANTITY'), 'quantity',array('class'=>'control-label'));
			//this gets saved in the productquantities table with the variant_id as the FK
		?>
		<?php echo J2Html::hidden($this->form_prefix.'[quantity][j2store_productquantity_id]', $this->item->j2store_productquantity_id,array('class'=>'input ')); ?>
		<?php echo J2Html::text($this->form_prefix.'[quantity][quantity]', $this->item->quantity,array('class'=>'input ')); ?>
	</div>

	<div class="control-group">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_ALLOW_BACK_ORDERS'), 'allow_backorder',array('class'=>'control-label'));?>
		<?php
		//three select options: Do not allow, allow, but notify customer, allow
		// Radio Btn Displaying
			echo  J2Html::select()->clearState()
				->type('genericlist')
				->name($this->form_prefix.'[allow_backorder]')
				->value($this->item->allow_backorder)
				->setPlaceHolders(
						array('0' => JText::_('COM_J2STORE_DO_NOT_ALLOW_BACKORDER'),
								'1' => JText::_('COM_J2STORE_DO_ALLOW_BACKORDER'),
								'2' => JText::_('COM_J2STORE_ALLOW_BUT_NOTIFY_CUSTOMER')
						))
						->getHtml(); ?>
	</div>

	<div class="control-group">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_STOCK_STATUS'), 'availability',array('class'=>'control-label')); ?>
		<?php 	//two select options: In Stock, Out of stock ?>
		<?php echo $this->availability; ?>
		</div>
		<div class="control-group">
			<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_NOTIFY_QUANTITY'), 'notify_qty',array('class'=>'control-label')); ?>

			<?php
				$attribs = (isset($this->item->use_store_config_notify_qty)) ? array('id' =>'notify_qty','disabled'=>'disabled') : array('id' =>'notify_qty');
				echo J2Html::text($this->form_prefix.'[notify_qty]', $this->item->notify_qty ,$attribs); ?>
			<div class="qty_restriction">
				<label>
				<input id="variant_config_notify_qty" type="checkbox" value="<?php echo $this->item->use_store_config_notify_qty;?>"
					   name="<?php echo $this->form_prefix; ?>[use_store_config_notify_qty]"
				       class="storeconfig"
				       <?php echo (isset($this->item->use_store_config_notify_qty) && $this->item->use_store_config_notify_qty) ? 'checked' : ''; ?>
				        />
				   <?php echo JText::_('J2STORE_PRODUCT_USE_STORE_CONFIGURATION'); ?>
				 </label>
			</div>
		</div>

	<div class="control-group form-inline">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_QUANTITY_RESTRICTION'), 'quantity_restriction',array('class'=>'control-label')); ?>
		<?php echo J2Html::radio($this->form_prefix.'[quantity_restriction]', $this->item->quantity_restriction, array('class'=>'controls')); ?>
	</div>

	<div class="control-group form-inline">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_MAX_SALE_QUANTITY'), 'max_sale_qty',array('class'=>'control-label')); ?>
		<?php
			$attribs = (isset($this->item->use_store_config_max_sale_qty) && $this->item->use_store_config_max_sale_qty ) ? array('id'=>'max_sale_qty','disabled'=>'disabled'): array('id'=>'max_sale_qty');
			echo J2Html::text($this->form_prefix.'[max_sale_qty]', $this->item->max_sale_qty,$attribs); ?>
		<div class="qty_restriction">
			<label>
			<input id="store_config_max_sale_qty" type="checkbox" value="<?php echo $this->item->use_store_config_max_sale_qty;?>"
				   name="<?php echo $this->form_prefix; ?>[use_store_config_max_sale_qty]"
				   class="storeconfig"
				<?php echo isset($this->item->use_store_config_max_sale_qty)  ? 'checked' : '';?>
			/>
			<?php echo JText::_('J2STORE_PRODUCT_USE_STORE_CONFIGURATION'); ?>
			</label>
		</div>
	</div>

		<div class="control-group form-inline">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_MIN_SALE_QUANTITY'), 'min_sale_qty',array('class'=>'control-label')); ?>
		<?php
			$attribs = (isset($this->item->use_store_config_min_sale_qty)) ? array('id' =>'min_sale_qty','disabled'=>'disabled'): array('id'=>'min_sale_qty');
			echo J2Html::text($this->form_prefix.'[min_sale_qty]', $this->item->min_sale_qty,$attribs); ?>
		<div class="qty_restriction">
			<label>
			<input id="store_config_min_sale_qty"
				   type="checkbox" value="<?php echo $this->item->use_store_config_min_sale_qty ;?>"
				   name="<?php echo $this->form_prefix; ?>[use_store_config_min_sale_qty]"
				   class="storeconfig"
				   <?php echo isset($this->item->use_store_config_min_sale_qty) ? 'checked' : ''; ?>
			    />
			  <?php echo JText::_('J2STORE_PRODUCT_USE_STORE_CONFIGURATION'); ?>
			  </label>
		</div>
	</div>

</div>
<script type="text/javascript">
(function($){
	$("#variant_config_notify_qty").click(function(){
		$(this).attr('value',0);
		if(this.checked == true){
			$(this).attr('value',1);
		}
		$('#notify_qty').attr('disabled',this.checked);

		$("#store_config_max_sale_qty").click(function(){
			$(this).attr('value',0);
			if(this.checked == true){
				$(this).attr('value',1);
			}

			$('#max_sale_qty').attr('disabled',this.checked);
		});

		$("#store_config_min_sale_qty").click(function(){
			$('#min_sale_qty').attr('disabled',this.checked);
		});

	});
})(j2store.jQuery);
</script>
<?php else:?>
	<?php echo J2Html::pro(); ?>
<?php endif;?>