<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal','a.modal');
$platform->loadExtra('behavior.formvalidator');
//JHTML::_('behavior.modal', 'a.modal');
$this->address_type='shipping';
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<div class="<?php echo $row_class ?>">
	<div class="<?php echo $col_class ?>8" style="<?php echo (isset($this->orderinfo->j2store_orderinfo_id) && !empty($this->orderinfo->j2store_orderinfo_id) && !empty($this->orderinfo->shipping_country_id) /*&& !empty($this->orderinfo->shipping_zone_id)*/) ?'display:none;':'';?>" id="select_shipping_address">
		<input type="hidden" value="<?php echo $this->address_type;?>" name="address_type" />
		<div class="display_message" id="display_message"></div>
		<div class="shipping-infos">
			<?php if (isset($this->addresses) && count($this->addresses) > 0) : ?>
				<input type="radio" name="address" value="existing" id="shipping-address-existing" checked="checked" />
				<label for="shipping-address-existing"><?php echo JText::_('J2STORE_ADDRESS_EXISTING'); ?></label>
				 <select class="input-xxlarge" 	name="address_id" id="address_id" size="5" >
				    <?php foreach ($this->addresses as $address) :  ?>
				    <?php if ($address->j2store_address_id == $this->shipping_address_id) : ?>
				    	<option value="<?php echo $address->j2store_address_id; ?>" selected="selected">
				    		<?php echo $address->first_name; ?> 	<?php echo $address->last_name; ?>, <?php echo $address->address_1; ?>, <?php echo $address->city; ?>, <?php echo $address->zip; ?>, <?php echo JText::_($address->zone_name); ?>, <?php echo JText::_($address->country_name); ?>
				    	</option>
				    <?php else: ?>
				    	<option value="<?php echo $address->j2store_address_id; ?>">
				    		<?php echo $address->first_name; ?> <?php echo $address->last_name; ?>, <?php echo $address->address_1; ?>, <?php echo $address->city; ?>, <?php echo $address->zip; ?>, <?php echo JText::_($address->zone_name); ?>, <?php echo JText::_($address->country_name); ?>
				    	</option>
				    <?php endif; ?>
				    <?php endforeach; ?>
				  </select>
				<?php endif;?>
		</div>

		<div id="new-address">
			<input name="validate_type" type="hidden" value="shipping" id="validate_type">
			<input type="radio" name="address" value="new" id="shipping-address-new"  />
			<label for="shipping-address-existing"><?php echo JText::_('J2STORE_ADDRESS_NEW'); ?></label>
			<div id="orderinfo-shipping-<?php echo $this->order->j2store_order_id;?>" style="display:none;">
			<?php
			$html = $this->storeProfile->get('store_billing_layout');

			if(empty($html) || strlen($html) < 5) {
				//we dont have a profile set in the store profile. So use the default one.
				$html = '<div class="'.$row_class.'">
				<div class="'.$col_class.'6">[first_name] [last_name] [phone_1] [phone_2] [company] [tax_number]</div>
				<div class="'.$col_class.'6">[address_1] [address_2] [city] [zip] [country_id] [zone_id]</div>
				</div>';
			}
			//first find all the checkout fields
			preg_match_all("^\[(.*?)\]^",$html,$checkoutFields, PREG_PATTERN_ORDER);
			$allFields = $this->fields;

			?>
			  	<?php foreach ($this->fields as $fieldName => $oneExtraField):?>
				<?php $onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';?>
					<?php
						if(property_exists($this->address, $fieldName)):
							if(($fieldName !='email')){ ?>
						<?php $oneExtraField->display_label = 'yes';?>
						 <?php $html = str_replace('['.$fieldName.']',$this->fieldClass->getFormatedDisplay($oneExtraField,$this->address->$fieldName,$fieldName,false, $options = '', $test = false, $allFields, $allValues = null).'</br />',$html);
						}
					?>
				<?php endif;?>
			  	<?php endforeach; ?>
			 	<?php
			 	 		$unprocessedFields = array();
						  foreach($this->fields as $fieldName => $oneExtraField):
			  			if(!in_array($fieldName, $checkoutFields[1])):
			  				$unprocessedFields[$fieldName] = $oneExtraField;

			  			endif;
			  		endforeach;

			   //now we have unprocessed fields. remove any other square brackets found.
			  preg_match_all("^\[(.*?)\]^",$html,$removeFields, PREG_PATTERN_ORDER);
			  foreach($removeFields[1] as $fieldName) {
			  	$html = str_replace('['.$fieldName.']', '', $html);
			  }
			  ?>

			  <?php  echo $html; ?>

			  <?php if(count($unprocessedFields)): ?>
				<div class="<?php echo $row_class ?>">
					<div class="<?php echo $col_class ?>12">
				  		<?php $uhtml = '';?>
				 		<?php foreach ($unprocessedFields as $fieldName => $oneExtraField): ?>
							<?php $onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';?>
								<?php if(property_exists($this->address, $fieldName)): ?>
									<?php
									$oneExtraField->display_label = 'yes';
										if(($fieldName !='email')){
											$uhtml .= $this->fieldClass->getFormatedDisplay($oneExtraField,$this->address->$fieldName, $fieldName,false, $options = '', $test = false, $allFields, $allValues = null);
										}
										 ?>
								<?php endif;?>
				  			<?php endforeach; ?>
				  		<?php echo $uhtml; ?>
				  	</div>
				  </div>
				<?php endif; ?>
			</div>

		</div>
	</div>	
		<div class="<?php echo $col_class ?>4">
		<div id="baddress-info">
			<?php

			if(isset($this->orderinfo->j2store_orderinfo_id) && $this->orderinfo->j2store_orderinfo_id > 0 && !empty($this->orderinfo->shipping_country_id) /*&& !empty($this->orderinfo->shipping_zone_id)*/):?>
				<strong><?php echo JText::_('J2STORE_SHIPPING_ADDRESS');?></strong>
			<?php echo J2StorePopup::popupAdvanced("index.php?option=com_j2store&view=orders&task=setOrderinfo&order_id=".$this->order->order_id."&address_type=shipping&layout=address&tmpl=component",'',array('class'=>'fa fa-pencil','refresh'=>true,'id'=>'fancybox','width'=>700,'height'=>600));?>
				<br/>
				<br/>
				<?php echo '<strong>'.$this->orderinfo->shipping_first_name." ".$this->orderinfo->shipping_last_name."</strong><br/>"; ?>
					<?php echo $this->orderinfo->shipping_address_1;?>
					<br/>
					<address>
						<?php echo $this->orderinfo->shipping_address_2 ? $this->orderinfo->shipping_address_2 : "<br/>";?>
							<?php echo $this->orderinfo->shipping_city;?><br />
							<?php echo $this->orderinfo->shipping_zone_name ? $this->orderinfo->shipping_zone_name.'<br />' : "";?>
							<?php echo !empty($this->orderinfo->shipping_zip) ? $this->orderinfo->shipping_zip.'<br />': '';?>
							<?php echo $this->orderinfo->shipping_country_name." <br/> ".JText::_('J2STORE_TELEPHONE').":";?>
							<?php echo $this->orderinfo->shipping_phone_1;
							echo $this->orderinfo->shipping_phone_2 ? '<br/> '.$this->orderinfo->shipping_phone_2 : "<br/> ";
							echo '<br/> ';
							echo '<a href="mailto:'.$this->order->user_email.'">'.$this->order->user_email.'</a>';
							echo '<br/> ';
							echo $this->orderinfo->shipping_company ? JText::_('J2STORE_ADDRESS_COMPANY_NAME').':&nbsp;'.$this->orderinfo->shipping_company."</br>" : "";
							echo $this->orderinfo->shipping_tax_number ? JText::_('J2STORE_ADDRESS_TAX_NUMBER').':&nbsp;'.$this->orderinfo->shipping_tax_number."</br>" : "";
							?>
						</address>
							<?php echo J2Store::getSelectableBase()->getFormatedCustomFields($this->orderinfo, 'customfields', 'shipping'); ?>
					<button id="change_address" class="btn btn-warning"><?php echo JText::_("J2STORE_CHOOSE_ALTERNATE_ADDRESS");?></button>
					<br/>
					<br/>
			<?php endif;?>
		</div>
	</div>
</div>
<script type="text/javascript">
(function($) {
	$('#change_address').on('click',function(e){
		e.preventDefault();
		$('#select_shipping_address').show();
		$('#nextlayout').hide();
		$('#saveAndNext').show();
		$('#baddress-info').hide();
		$('#display_message').after('<button id="close_address" class="btn btn-warning pull-right"><?php echo JText::_('J2STORE_CLOSE');?></button>');
	});

})(j2store.jQuery);
(function($) {
	$('#shipping-address-existing').on('click' ,function(){
		$('#orderinfo-shipping-<?php echo $this->order->j2store_order_id;?>').slideUp(200);
		$('#nextlayout').hide();
		$('#saveAndNext').show();
		$('.j2error').remove();
	});
	$('#shipping-address-new').on('click',function(){
		$('#orderinfo-shipping-<?php echo $this->order->j2store_order_id;?>').slideDown(200);
		$('#nextlayout').show();
		$('#saveAndNext').hide();
		$('.j2error').remove();
	});

$('#address #country_id').bind('change', function() {
	if (this.value == '') return;
	$.ajax({
		url: 'index.php?option=com_j2store&view=orders&task=getCountry&country_id=' + this.value,
		dataType: 'json',
		beforeSend: function() {
			$('#address #country_id').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
		},
		complete: function() {
			$('.wait').remove();
		},
		success: function(json) {
			if (json['postcode_required'] == '1') {
				$('#shipping-postcode-required').show();
			} else {
				$('#shipping-postcode-required').hide();
			}

			html = '<option value=""><?php echo JText::_('J2STORE_SELECT_OPTION'); ?></option>';

			if (json['zone'] != '') {

				for (i = 0; i < json['zone'].length; i++) {
        			html += '<option value="' + json['zone'][i]['j2store_zone_id'] + '"';

					if (json['zone'][i]['j2store_zone_id'] == '<?php echo $this->address->zone_id; ?>') {
	      				html += ' selected="selected"';
	    			}

	    			html += '>' + json['zone'][i]['zone_name'] + '</option>';
				}
			} else {
				html += '<option value="0" selected="selected"><?php echo JText::_('J2STORE_CHECKOUT_NONE'); ?></option>';
			}

            /*$("#<?php echo $this->address_type;?>_zone_id").html(html);*/
            $("#zone_id").html(html);
		},
		error: function(xhr, ajaxOptions, thrownError) {
            /*alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);*/
        }
	});
});
})(j2store.jQuery);

(function($) {
	if($('#address #country_id').length > 0) {
		$('#address #country_id').trigger('change');
	}
})(j2store.jQuery);
</script>