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
<div class="j2store-product-inventory">
	<div class="control-group form-inline">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_MANAGE_STOCK'), 'manage_stock',array('class'=>'control-label')); ?>
		<?php echo J2Html::radio($this->form_prefix.'[manage_stock]',(isset($this->variant->manage_stock))?$this->variant->manage_stock:'',array('class'=>'controls'));?>
	</div>

	<div class="control-group">
		<?php
			 echo J2Html::label(JText::_('J2STORE_PRODUCT_QUANTITY'), 'quantity',array('class'=>'control-label'));
			//this gets saved in the productquantities table with the variant_id as the FK
		?>
		<?php echo J2Html::hidden($this->form_prefix.'[quantity][j2store_productquantity_id]', (isset($this->variant->j2store_productquantity_id)) ? $this->variant->j2store_productquantity_id:'',array('class'=>'input')); ?>
		<?php echo J2Html::text($this->form_prefix.'[quantity][quantity]', (isset($this->variant->quantity))?$this->variant->quantity:'',array('class'=>'input ','field_type'=>'integer')); ?>
	</div>

	<div class="control-group">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_ALLOW_BACK_ORDERS'), 'allow_backorder',array('class'=>'control-label'));?>
		<?php
		//three select options: Do not allow, allow, but notify customer, allow
		// Radio Btn Displaying
			echo  $this->allow_backorder; ?>
	</div>

	<div class="control-group">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_STOCK_STATUS'), 'availability',array('class'=>'control-label')); ?>
		<?php 	//two select options: In Stock, Out of stock ?>
		<?php echo $this->availability; ?>
	</div>
	<div class="control-group">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_NOTIFY_QUANTITY'), 'notify_qty',array('class'=>'control-label')); ?>
		<?php
			$attribs = (isset($this->variant->use_store_config_notify_qty) && $this->variant->use_store_config_notify_qty) ? array('id'=>'notify_qty' ,'disabled'=>'disabled','field_type'=>'integer') :array('id'=>'notify_qty','field_type'=>'integer');
			echo J2Html::text($this->form_prefix.'[notify_qty]',(isset($this->variant->notify_qty)) ? $this->variant->notify_qty: '' ,$attribs); ?>
		<div class="qty_restriction">
			<label for="use_store_config_notify_qty">
			<input id="config_notify_qty"
				   type="checkbox" value="<?php echo $this->variant->use_store_config_notify_qty;?>"
				   name="<?php echo $this->form_prefix; ?>[use_store_config_notify_qty]"
				   class="storeconfig"
				<?php echo (isset($this->variant->use_store_config_notify_qty) && $this->variant->use_store_config_notify_qty) ? 'checked' : ''; ?>
					/>
					<?php echo JText::_('J2STORE_PRODUCT_USE_STORE_CONFIGURATION'); ?>
			</label>
		</div>
	</div>

	<div class="control-group form-inline">
				<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_QUANTITY_RESTRICTION'), 'quantity_restriction',array('class'=>'control-label')); ?>
				<?php echo J2Html::radio($this->form_prefix.'[quantity_restriction]',(isset($this->variant->quantity_restriction))? $this->variant->quantity_restriction : '' ); ?>
			</div>
			<div class="control-group">
				<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_MAX_SALE_QUANTITY'), 'max_sale_qty',array('class'=>'control-label')); ?>
					<?php
						$attribs = (isset($this->variant->use_store_config_notify_qty) && $this->variant->use_store_config_notify_qty) ? array('id' =>'max_sale_qty','disabled'=>'disabled','field_type'=>'integer') : array('id' =>'max_sale_qty','field_type'=>'integer');
						echo J2Html::text($this->form_prefix.'[max_sale_qty]',(isset($this->variant->max_sale_qty))?$this->variant->max_sale_qty:'' ,$attribs); ?>
					<div class="qty_restriction">
						<label for="use_store_config_max_sale_qty">
						<input id="store_config_max_sale_qty" type="checkbox" value="<?php echo $this->variant->use_store_config_max_sale_qty;?>"
							   name="<?php echo $this->form_prefix; ?>[use_store_config_max_sale_qty]"
							   class="storeconfig"
							<?php echo (isset($this->variant->use_store_config_max_sale_qty) && $this->variant->use_store_config_max_sale_qty) ? 'checked' : ''; ?>
						   />
						 <?php echo JText::_('J2STORE_PRODUCT_USE_STORE_CONFIGURATION'); ?>
						</label>
					</div>
				</div>
				<div class="control-group">
					<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_MIN_SALE_QUANTITY'), 'min_sale_qty',array('class'=>'control-label')); ?>
					<?php
						$attribs = (isset($this->variant->use_store_config_notify_qty) && $this->variant->use_store_config_notify_qty) ? array('id'=>'min_sale_qty' ,'disabled'=>'disabled','field_type'=>'integer') :array('id'=>'min_sale_qty','field_type'=>'integer');
						echo J2Html::text($this->form_prefix.'[min_sale_qty]', (isset($this->variant->min_sale_qty))?$this->variant->min_sale_qty:'',$attribs); ?>
					<div class="qty_restriction">
						<label for="use_store_config_min_sale_qty">
						<input id="store_config_min_sale_qty" type="checkbox" value="<?php echo $this->variant->use_store_config_min_sale_qty;?>"
								name="<?php echo $this->form_prefix; ?>[use_store_config_min_sale_qty]"
								class="storeconfig"
								<?php echo (isset($this->variant->use_store_config_min_sale_qty) && $this->variant->use_store_config_min_sale_qty) ? 'checked' : ''; ?>
								/>
							<?php echo JText::_('J2STORE_PRODUCT_USE_STORE_CONFIGURATION'); ?>
						</label>
					</div>
				</div>


</div>
<script type="text/javascript">
(function($){
	$("#config_notify_qty").click(function(){
		$(this).attr('value',0);
		if(this.checked == true){
			$(this).attr('value',1);
		}
		$('#notify_qty').attr('disabled',this.checked);
	});

	$("#store_config_max_sale_qty").click(function(){
			$(this).attr('value',0);
			if(this.checked == true){
				$(this).attr('value',1);
			}
			$('#max_sale_qty').attr('disabled',this.checked);
		});

		$("#store_config_min_sale_qty").click(function(){
			$(this).attr('value',0);
			if(this.checked == true){
				$(this).attr('value',1);
			}
			$('#min_sale_qty').attr('disabled',this.checked);
		});
	})(j2store.jQuery);

</script>
<?php else:?>
	<?php echo J2Html::pro(); ?>
<?php endif;?>