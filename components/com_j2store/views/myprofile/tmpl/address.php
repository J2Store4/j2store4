<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
// get j2Store Params to determine which bootstrap version we're using - Waseem Sadiq (waseem@bulletprooftemplates.com)
$config = J2Store::config();
$J2gridRow = ($config->get('bootstrap_version', 2) == 2) ? 'row-fluid' : 'row';
$J2gridCol = ($config->get('bootstrap_version', 2) == 2) ? 'span' : 'col-md-';
if(!empty($this->address_type)){
    $this->address->type = $this->address_type;
}
if(empty($this->address->type)){
    $this->address->type = 'billing';
}
$app = J2Store::platform();
$back_url = $app->getMyprofileUrl();
$user = JFactory::getUser();
if(empty($user->id)){
	$app->redirect($back_url);
}
?>

<style>
	.form-search input, .form-search textarea, .form-search select, .form-search .help-inline, .form-search .uneditable-input, .form-search .input-prepend, .form-search .input-append, .form-inline input, .form-inline textarea, .form-inline select, .form-inline .help-inline, .form-inline .uneditable-input, .form-inline .input-prepend, .form-inline .input-append, .form-horizontal input, .form-horizontal textarea, .form-horizontal select, .form-horizontal .help-inline, .form-horizontal .uneditable-input, .form-horizontal .input-prepend, .form-horizontal .input-append {
    display: block;
    margin-bottom: 0;
    vertical-align: middle;
}
</style>
<form class="form-horizontal" id="j2storeaddressForm" name="addressForm" method="post" action="<?php echo $back_url; ?>" >
	<h3><?php echo JText::_('J2STORE_ADDRESS_EDIT');?></h3>
	<div id="address">
		<div class="j2store-address-alert">
		</div>
		 <div class="pull-right">
			 <a  onclick="J2storeSubmitForm(this,'apply')" class="button btn btn-success"><span class="icon-apply icon-white"></span><?php echo JText::_('JSAVE'); ?></a>
			 <a  onclick="J2storeSubmitForm(this,'save')" class="button btn "><span class="icon-save"></span><?php echo JText::_('JTOOLBAR_SAVE'); ?></a>
	  	</div>
		<div class="pull-left">
			<a class="btn btn-warning" href="<?php echo $back_url;?>" >
				<span class="fa fa-chevron-left"></span>
				<?php echo JText::_('J2STORE_BACK_TO_PROFILE');?>
			</a>
		</div>
		<br>
		<br>
		<div class="control-group">
		  	<?php echo J2Html::label(JText::_('J2STORE_ADDRESS_TYPE') ,array('class'=>'control-label'));?>
			  <?php echo J2Html::select()->clearState()
			  			->type('genericlist')
			  			->name('type')
			  			->value($this->address->type)
			  			->setPlaceHolders(
			  				array('billing'=>JText::_('J2STORE_BILLING_ADDRESS'), 'shipping'=>JText::_('J2STORE_SHIPPING_ADDRESS'))
			  			)->getHtml();
			  ?>
	  </div>
	<?php
	//$html = $this->storeProfile->store_billing_layout;
	$html ='';
	if(empty($html) || strlen($html) < 5) {
		//we dont have a profile set in the store profile. So use the default one.
		$html = '<div class="'.$J2gridRow.'">
			<div class="'.$J2gridCol.'6">[first_name] [last_name] [phone_1] [phone_2] [company] [tax_number]</div>
			<div class="'.$J2gridCol.'6">[address_1] [address_2] [city] [zip] [country_id] [zone_id]</div>
			</div>';
	}
	//first find all the checkout fields
	preg_match_all("^\[(.*?)\]^",$html,$checkoutFields, PREG_PATTERN_ORDER);
	$this->fields =  $this->fieldClass->getFields($this->address->type,$this->address,'address');
	$allFields = $this->fields;
	?>
	  	<?php foreach ($this->fields as $fieldName => $oneExtraField):?>
		<?php $onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';?>
			<?php if(property_exists($this->address, $fieldName)):
				if(($fieldName !='email')){
                    $placeholder =  (isset($oneExtraField->field_options['placeholder']) ? $oneExtraField->field_options['placeholder'] : "");
                    $field_options = '';
                    if($placeholder){
                        $field_options .= ' placeholder="'.$placeholder.'" ';
                    }
			 		$html = str_replace('['.$fieldName.']',$this->fieldClass->getFormattedDisplay($oneExtraField,$this->address->$fieldName, $fieldName,false, $field_options, $test = false, $allFields, $allValues = null),$html);
				}

			?>
		<?php endif;?>
	  	<?php endforeach; ?>
	 	<?php
	 		 //check for unprocessed fields.
	 		 //If the user forgot to add the
	 		 //fields to the checkout layout in store profile, we probably have some.
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
	  <?php echo $html; ?>

	  <?php if(count($unprocessedFields)): ?>
	 	<div class="<?php echo $J2gridRow; ?>">
	  		<div class="<?php echo $J2gridCol;?>12">
		  		<?php $uhtml = '';?>
		 		<?php foreach ($unprocessedFields as $fieldName => $oneExtraField): ?>
					<?php $onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';?>
						<?php if(property_exists($this->address, $fieldName)): ?>
							<?php
							if(($fieldName !='email')){
                                $placeholder =  (isset($oneExtraField->field_options['placeholder']) ? $oneExtraField->field_options['placeholder'] : "");
                                $field_options = '';
                                if($placeholder){
                                    $field_options .= ' placeholder="'.$placeholder.'" ';
                                }
								$uhtml .= $this->fieldClass->getFormattedDisplay($oneExtraField,$this->address->$fieldName, $fieldName,false, $field_options, $test = false, $allFields, $allValues = null);
							}
								 ?>
						<?php endif;?>
		  			<?php endforeach; ?>
		  		<?php echo $uhtml; ?>
	  		</div>
	  	</div>
		<?php endif; ?>
	</div>

  <input type="hidden" id="task" name="task" value="saveAddress" />
  <input type="hidden" name="option" value="com_j2store" />
  <input type="hidden" name="view" value="myprofile" />
  <input type="hidden" id="address_id" name="address_id" value="<?php echo $this->address->j2store_address_id;?>" />
  <input type="hidden" id="j2store_address_id" name="j2store_address_id" value="<?php echo $this->address->j2store_address_id;?>" />
  <input type="hidden" name="user_id" value="<?php echo $this->address->user_id;?>" />

  </form>

  <script type="text/javascript"><!--
	  if(typeof(j2store) == 'undefined') {
		  var j2store = {};
	  }
	  if(typeof(j2store.jQuery) == 'undefined') {
		  j2store.jQuery = jQuery.noConflict();
	  }

	  (function ($) {
		  $( '#j2store_type' ).change( 'change', function(e) {
			  var address_type = $('#j2store_type').val();
			  window.location = '<?php echo 'index.php?option=com_j2store&view=myprofile&task=editAddress&layout=address&address_id='.$this->address->j2store_address_id.'&address_type=';?>'+address_type;
		  });
	  })(j2store.jQuery);
  function J2storeSubmitForm(element,t_type){
		(function($) {
			$(".j2store-error").remove();
			var form = $("#j2storeaddressForm");
			form.find("#task").attr('value','saveAddress');
			var data = form.serializeArray();
			$.ajax({
				url : 'index.php',
				type: 'post',
				data :data,
				dataType: 'json',
				beforeSend: function() {
					$('.j2error').remove();
				},
				success: function(json){
					console.log(json);
					if(json['success']){
						if(json['success']['url']){
							$(".j2store-error").remove();
							$('#address_id').val(json['success']['address_id']);
							$('#j2store_address_id').val(json['success']['address_id']);
							if(t_type == 'save'){
								window.location =json['success']['url'];
							}
							if(json['success']['msg'] && t_type == 'apply'){
								var html ='';
							    html +='<div class="alert alert-success">';
							    html +='<a href="#" class="close" data-dismiss="alert">&times;</a>';
							    html +=json['success']['msg'] ;
							    html +='</div>';
								jQuery('.j2store-address-alert').html(html);
                                window.location =json['success']['apply_url'];
							}
						}
					}else if(json['error']){
						$.each( json['error'], function( key, value ) {
							if (value) {
								$('#address #'+key).after('<span class="j2error">' + value + '</span><br class="j2error">');
							}
						});
						if(json['error']['message']){
							var html ='';
							html +='<div class="alert alert-danger">';
							html +='<a href="#" class="close" data-dismiss="alert">&times;</a>';
							html +=json['error']['message'];
							html +='</div>';
							jQuery('.j2store-address-alert').html(html);
						}
					}

				}

			});
		})(j2store.jQuery);


	}

(function($) {
$('#address select[name=\'country_id\']').bind('change', function() {
	if (this.value == '') return;
	$.ajax({
		url: 'index.php?option=com_j2store&view=myprofile&task=getCountry&country_id=' + this.value,
		dataType: 'json',
		beforeSend: function() {
			$('#address select[name=\'country_id\']').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
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

			$('#address select[name=\'zone_id\']').html(html);
		},
		error: function(xhr, ajaxOptions, thrownError) {
			//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});
})(j2store.jQuery);

(function($) {
	if($('#address select[name=\'country_id\']').length > 0) {
		$('#address select[name=\'country_id\']').trigger('change');
	}
})(j2store.jQuery);
//--></script>