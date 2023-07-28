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
$platform = J2Store::platform();
$J2gridRow = ($this->params->get('bootstrap_version', 2) == 2) ? 'row-fluid' : 'row';
$J2gridCol = ($this->params->get('bootstrap_version', 2) == 2) ? 'span' : 'col-md-';
?>

<div class="j2store">

<?php
$html = $this->storeProfile->get('store_billing_layout');

if(empty($html) || strlen($html) < 7) {
//we dont have a profile set in the store profile. So use the default one.
	$html = '<div class="'.$J2gridRow.'">
	<div class="'.$J2gridCol.'6">[first_name] [last_name] [email] [phone_1] [phone_2] [password] [confirm_password]</div>
	<div class="'.$J2gridCol.'6">[company] [tax_number] [address_1] [address_2] [city] [zip] [country_id] [zone_id]</div>
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
						 	$html = str_replace('['.$fieldName.']',$this->fieldsClass->getFormattedDisplay($oneExtraField,$this->address->$fieldName, $fieldName,false, $field_options, $test = false, $allFields, $allValues = null).'</br>',$html);
						}
						?>
  <?php endforeach; ?>

<?php

if($status == false) {
//email not found. manually add it
$email ='<span class="j2store_field_required">*</span>'.JText::_('J2STORE_EMAIL');
$email .='<br /><input type="text" name="email" id="email" value="" class="large-field" /> <br />';
$html = str_replace('[email]',$email,$html);
}

$password ='<h2>'.JText::_('J2STORE_CHECKOUT_SET_PASSWORD').'</h2>';
$password .='<span class="j2store_field_required">*</span>'.JText::_('J2STORE_CHECKOUT_ENTER_PASSWORD');
$password .='<br /><input type="password" name="password" value="" class="large-field" /> <br /> <br />';
$confirm_password= '<span class="j2store_field_required">*</span>'.JText::_('J2STORE_CHECKOUT_CONFIRM_PASSWORD').'<br />
  <input type="password" name="confirm" value="" class="large-field" />
  <br />';
if($this->privacyconsent_enabled){
    $privacy_plugin = JPluginHelper::getPlugin('system', 'privacyconsent');
    $privacy_params = $platform->getRegistry($privacy_plugin->params);
    $confirm_password .= '<label id="privacyconsent" for="privacyconsent"><input type="checkbox" value="1"  name="privacyconsent" />  '.JText::_($privacy_params->get('privacy_note','')).'</label><br />';
}
//now replace pass fields
$html = str_replace('[password]',$password,$html);
$html = str_replace('[confirm_password]',$confirm_password,$html);


//re-check if email, password or confirm password fields are deleted. May be accidentally
$phtml = '';

if(!in_array('email', $checkoutFields[1]) && $status == false) {
	//it seems deleted. so process them
	$phtml .= $email;
}

if(!in_array('password', $checkoutFields[1])) {
	//first check if confirm password exists. if yes. remove it. Saving a mis-alignment of password fields
	if(in_array('confirm_password', $checkoutFields[1])) {
		//strip it
		$html = str_replace($confirm_password,' ',$html);
		$key = array_search('confirm_password', $checkoutFields[1]);
		unset($checkoutFields[1][$key]);
	}
	//it seems deleted. so process them
	$phtml .= $password;
}

if(!in_array('confirm_password', $checkoutFields[1])) {
	//it seems deleted. so process them
	$phtml .= $confirm_password;
}

$html = $html.$phtml;

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

  <?php if ($this->showShipping) { ?>
  <div class="<?php echo $J2gridRow; ?>">
  <div class="<?php echo $J2gridCol; ?>12 shipping-make-same" style="clear: both; padding-top: 15px;">
	  <input type="checkbox" name="shipping_address" value="1" id="shipping" checked="checked" />
	  <label for="shipping"><?php echo JText::_('J2STORE_MAKE_SHIPPING_SAME'); ?></label>	  
  </div>
  </div> <!-- end of row-fluid -->
  <br />
  <?php } ?>
  
<?php echo J2Store::plugin()->eventWithHtml('CheckoutRegister', array($this)); ?>
<div class="buttons">
  <div class="left">
    <input type="button" value="<?php echo JText::_('J2STORE_CHECKOUT_CONTINUE'); ?>" id="button-register" class="button btn btn-primary" />
  </div>
</div>
<input type="hidden" name="option" value="com_j2store" />
<input type="hidden" name="view" value="checkout" />
<input type="hidden" name="task" value="register_validate" />

</div> <!-- end of j2store -->

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