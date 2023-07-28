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
// get j2Store Params to determine which bootstrap version we're using - Waseem Sadiq (waseem@bulletprooftemplates.com)
$J2gridRow = ($this->params->get('bootstrap_version', 2) == 2) ? 'row-fluid' : 'row';
$J2gridCol = ($this->params->get('bootstrap_version', 2) == 2) ? 'span' : 'col-md-';

$html = $this->storeProfile->get('store_billing_layout');

if(empty($html) || strlen($html) < 5) {
	//we dont have a profile set in the store profile. So use the default one.
	$html = '<div class="'. $J2gridRow .'">
		<div class="'. $J2gridCol .'6">[first_name] [last_name] [email] [phone_1] [phone_2] [country_id] [zone_id] </div>
		<div class="'. $J2gridCol .'6">[company] [tax_number] [address_1] [address_2] [city] [zip] </div>
		</div>';
}
//first find all the checkout fields
preg_match_all("^\[(.*?)\]^",$html,$checkoutFields, PREG_PATTERN_ORDER);

//print_r($this->address);
$allFields = $this->fields;
$status = false;
?>
<?php foreach ($this->fields as $fieldName => $oneExtraField): ?>
	<?php
	if($fieldName == 'email') {
		$status = true;
	}

	$onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';
	//echo $this->fieldsClass->display($oneExtraField,@$this->address->$fieldName,$fieldName,false);
	if(property_exists($this->address, $fieldName)) {
        $placeholder =  (isset($oneExtraField->field_options['placeholder']) ? $oneExtraField->field_options['placeholder'] : "");
        $field_options = '';
        if($placeholder){
            $field_options .= ' placeholder="'.$placeholder.'" ';
        }
		$html = str_replace('['.$fieldName.']',$this->fieldsClass->getFormattedDisplay($oneExtraField,$this->address->$fieldName, $fieldName,false, $field_options, $test = false, $allFields, $allValues = null).'<br />',$html);
	}
	?>
<?php endforeach; ?>

<?php
$email ='<span class="j2store_field_required">*</span>'.JText::_('J2STORE_EMAIL');
$email .='<br /><input type="text" name="email" value="" class="large-field" /> <br />';
if($status == false) {
	//email not found. manually add it
	$html = str_replace('[email]',$email,$html);
}

//re-check if email field is deleted. May be accidentally
$phtml = '';
if(!in_array('email', $checkoutFields[1]) && $status == false) {
	//it seems deleted. so process them
	$phtml .= $email;
}
?>
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
$html = $html.$phtml;
?>

<?php echo $html; ?>
<?php if(count($unprocessedFields)): ?>
	<div class="<?php echo $J2gridRow;?>">
		<div class="<?php echo $J2gridCol;?>12">
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

<?php if ($this->showShipping) { ?>
	<div class="<?php echo $J2gridCol;?>11 shipping-make-same" style="clear: both; padding-top: 15px;">
		<?php if ($this->data['shipping_address']) { ?>
			<input type="checkbox" name="shipping_address" value="1" id="shipping" checked="checked" />
		<?php } else { ?>
			<input type="checkbox" name="shipping_address" value="1" id="shipping" />
		<?php } ?>
		<label for="shipping"><?php echo JText::_('J2STORE_MAKE_SHIPPING_SAME'); ?></label>
		<br />
		<br />
	</div>
<?php } ?>
<?php echo J2Store::plugin()->eventWithHtml('CheckoutGuest', array($this)); ?>
<div class="buttons">
	<div class="left">
		<input type="button" value="<?php echo JText::_('J2STORE_CHECKOUT_CONTINUE'); ?>" id="button-guest" class="button btn btn-primary" />
	</div>
</div>
<input type="hidden" name="option" value="com_j2store" />
<input type="hidden" name="view" value="checkout" />
<input type="hidden" name="task" value="guest_validate" />

<script type="text/javascript"><!--
	(function($) {
		$('#billing-address select[name=\'country_id\']').bind('change', function() {
			if (this.value == '') return;
			$.ajax({
				url: 'index.php?option=com_j2store&view=carts&task=getCountry&country_id=' + this.value,
				dataType: 'json',
				beforeSend: function() {
					$('#billing-address select[name=\'country_id\']').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
				},
				complete: function() {
					$('.wait').remove();
				},
				success: function(json) {
					if (json['postcode_required'] == '1') {
						$('#payment-postcode-required').show();
					} else {
						$('#payment-postcode-required').hide();
					}

					html = '<option value=""><?php echo JText::_('J2STORE_SELECT_OPTION'); ?></option>';

					if (json['zone'] != '') {
						default_zone_id = $('#billing-address #zone_id_default_value').val();
						for (i = 0; i < json['zone'].length; i++) {
							html += '<option value="' + json['zone'][i]['j2store_zone_id'] + '"';

							if (json['zone'][i]['j2store_zone_id'] == default_zone_id) {
								html += ' selected="selected"';
							}

							html += '>' + json['zone'][i]['zone_name'] + '</option>';
						}
					} else {
						html += '<option value="0" selected="selected"><?php echo JText::_('J2STORE_CHECKOUT_ZONE_NONE'); ?></option>';
					}

					$('#billing-address select[name=\'zone_id\']').html(html);
				},
				error: function(xhr, ajaxOptions, thrownError) {
					//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		});
	})(j2store.jQuery);

	(function($) {
		if($('#billing-address select[name=\'country_id\']').length > 0) {
			$('#billing-address select[name=\'country_id\']').trigger('change');
		}
	})(j2store.jQuery);
	//--></script>
