<?php
/*------------------------------------------------------------------------
# com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
$J2gridRow = ($this->params->get('bootstrap_version', 2) == 2) ? 'row-fluid' : 'row';
$J2gridCol = ($this->params->get('bootstrap_version', 2) == 2) ? 'span' : 'col-md-';
if (isset($this->addresses) && count($this->addresses) > 0) : ?>

<input type="radio" name="shipping_address" value="existing" id="shipping-address-existing" checked="checked" />
<label for="shipping-address-existing"><?php echo JText::_('J2STORE_ADDRESS_EXISTING'); ?></label>
<div id="shipping-existing">
  <select name="address_id" style="width: 100%; margin-bottom: 15px;" size="5">
    <?php foreach ($this->addresses as $address) : ?>
		<?php if(empty($this->address_id)){
			$this->address_id = $address->j2store_address_id;
		} ?>
     <?php if ($address->j2store_address_id == $this->address_id) : ?>
    	<option value="<?php echo $address->j2store_address_id; ?>" selected="selected">    	
    		<?php echo $address->first_name; ?> <?php echo $address->last_name; ?>, <?php echo $address->address_1; ?>, <?php echo $address->city; ?>, <?php echo $address->zip; ?>, <?php echo JText::_($address->zone_name); ?>, <?php echo JText::_($address->country_name); ?>
    	</option>
  
    <?php else: ?>
    	<option value="<?php echo $address->j2store_address_id; ?>">
    		<?php echo $address->first_name; ?> <?php echo $address->last_name; ?>, <?php echo $address->address_1; ?>, <?php echo $address->city; ?>, <?php echo $address->zip; ?>, <?php echo JText::_($address->zone_name); ?>, <?php echo JText::_($address->country_name); ?>
    	</option>
    <?php endif; ?>
    
    <?php endforeach; ?>
  </select>
</div>
<p>
  <input type="radio" name="shipping_address" value="new" id="shipping-address-new" />
  <label for="shipping-address-new"><?php echo JText::_('J2STORE_ADDRESS_NEW'); ?></label>
</p>
<?php endif; ?>
<div id="shipping-new" style="display: <?php echo ($this->addresses ? 'none' : 'block'); ?>;">

<?php
$html = $this->storeProfile->get('store_shipping_layout');

if(empty($html) || strlen($html) < 5) {
	//we dont have a profile set in the store profile. So use the default one.
	$html = '<div class="'. $J2gridRow .'">
		<div class="'.$J2gridCol.'6">[first_name] [last_name] [phone_1] [phone_2] [company]</div>
		<div class="'.$J2gridCol.'6">[address_1] [address_2] [city] [zip] [country_id] [zone_id]</div>
		</div>';
}
//first find all the checkout fields
preg_match_all("^\[(.*?)\]^",$html,$checkoutFields, PREG_PATTERN_ORDER);
//print_r($this->address);
$allFields = $this->fields;
?>
  <?php foreach ($this->fields as $fieldName => $oneExtraField): ?>
						<?php
						$onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';
						//echo $this->fieldsClass->display($oneExtraField,@$this->address->$fieldName,$fieldName,false);
						if(property_exists($this->address, $fieldName)) {
                            $placeholder =  (isset($oneExtraField->field_options['placeholder']) ? $oneExtraField->field_options['placeholder'] : "");
                            $field_options = '';
                            if($placeholder){
                                $field_options .= ' placeholder="'.$placeholder.'" ';
                            }
						 	$html = str_replace('['.$fieldName.']',$this->fieldsClass->getFormattedDisplay($oneExtraField,$this->address->$fieldName, $fieldName,false, $field_options, $test = false, $allFields, $allValues = null).'</br>',$html);
						}
						?>
  <?php endforeach; ?>

   <?php
  //check for unprocessed fields. If the user forgot to add the fields to the checkout layout in store profile, we probably have some.
  $unprocessedFields = array();
  foreach($this->fields as $fieldName => $oneExtraField) {
  	if(!in_array($fieldName, $checkoutFields[1])) {
  		$unprocessedFields[$fieldName] = $oneExtraField;
  	}
  }
  //now we have unprocessed fields. remove any other square brackets found.
  preg_match_all("^\[(.*?)\]^",$html,$removeFields, PREG_PATTERN_ORDER);
  foreach($removeFields[1] as $fieldName) {
      if(!empty($fieldName)){
          $html = str_replace('['.$fieldName.']', '', $html);
      }
  }

  ?>

<?php echo $html; ?>

 <?php if(count($unprocessedFields)): ?>
 <div class="<?php echo $J2gridRow; ?>">
  <div class="<?php echo $J2gridCol; ?>12">
  <?php $uhtml = '';?>
 <?php foreach ($unprocessedFields as $fieldName => $oneExtraField): ?>
						<?php
						$onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';
						//echo $this->fieldsClass->display($oneExtraField,@$this->address->$fieldName,$fieldName,false);
						if(property_exists($this->address, $fieldName)) {
                            $placeholder =  (isset($oneExtraField->field_options['placeholder']) ? $oneExtraField->field_options['placeholder'] : "");
                            $field_options = '';
                            if($placeholder){
                                $field_options .= ' placeholder="'.$placeholder.'" ';
                            }
						 	$uhtml .= $this->fieldsClass->getFormattedDisplay($oneExtraField,$this->address->$fieldName, $fieldName,false, $field_options, $test = false, $allFields, $allValues = null);
						 	$uhtml .='<br />';
						}
						?>
  <?php endforeach; ?>
  <?php echo $uhtml; ?>
  </div>
</div>
<?php endif; ?>

</div>
<?php echo J2Store::plugin()->eventWithHtml('CheckoutShipping', array($this)); ?>
<br />
<div class="buttons">
  <div class="left">
    <input type="button" value="<?php echo JText::_('J2STORE_CHECKOUT_CONTINUE'); ?>" id="button-shipping-address" class="button btn btn-primary" />
  </div>
</div>
 <input type="hidden" name="task" value="shipping_address_validate" />
  <input type="hidden" name="option" value="com_j2store" />
  <input type="hidden" name="view" value="checkout" />
<script type="text/javascript"><!--
(function($) {
$(document).on('change', '#shipping-address input[name=\'shipping_address\']', function() {
	if (this.value == 'new') {
		$('#shipping-existing').hide();
		$('#shipping-new').show();
	} else {
		$('#shipping-existing').show();
		$('#shipping-new').hide();
	}
});
})(j2store.jQuery);

//--></script>
<script type="text/javascript"><!--
(function($) {
$('#shipping-address select[name=\'country_id\']').bind('change', function() {
	if (this.value == '') return;
	$.ajax({
		url: 'index.php?option=com_j2store&view=carts&task=getCountry&country_id=' + this.value,
		dataType: 'json',
		beforeSend: function() {
			$('#shipping-address select[name=\'country_id\']').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
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
				default_zone_id = $('#shipping-address #zone_id_default_value').val();
				for (i = 0; i < json['zone'].length; i++) {
        			html += '<option value="' + json['zone'][i]['j2store_zone_id'] + '"';

					if (json['zone'][i]['j2store_zone_id'] == default_zone_id) {
	      				html += ' selected="selected"';
	    			}

	    			html += '>' + json['zone'][i]['zone_name'] + '</option>';
				}
			} else {
				html += '<option value="0" selected="selected"><?php echo JText::_('J2STORE_CHECKOUT_NONE'); ?></option>';
			}

			$('#shipping-address select[name=\'zone_id\']').html(html);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});
})(j2store.jQuery);

(function($) {
	if($('#shipping-address select[name=\'country_id\']').length > 0) {
		$('#shipping-address select[name=\'country_id\']').trigger('change');
	}
})(j2store.jQuery);
//--></script>
