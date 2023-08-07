<?php
defined('_JEXEC') or die( 'Restricted access' );
$address = $this->address;
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<style>
    .form-search input, .form-search textarea, .form-search select, .form-search .help-inline, .form-search .uneditable-input, .form-search .input-prepend, .form-search .input-append, .form-inline input, .form-inline textarea, .form-inline select, .form-inline .help-inline, .form-inline .uneditable-input, .form-inline .input-prepend, .form-inline .input-append, .form-horizontal input, .form-horizontal textarea, .form-horizontal select, .form-horizontal .help-inline, .form-horizontal .uneditable-input, .form-horizontal .input-prepend, .form-horizontal .input-append {
        display: block;
        margin-bottom: 0;
        vertical-align: middle;
    }
</style>
<div class="j2store">
    <form class="form-horizontal" id="j2storeaddressForm" name="addressForm" method="post" action="<?php echo 'index.php'; ?>" >
        <h3><?php echo JText::_('J2STORE_ADDRESS_EDIT');?></h3>
        <div id="address">
            <div class="j2store-address-alert">
            </div>
            <div class="pull-right">
                <input type="button" onclick="saveCustomer();" value="<?php echo JText::_('JAPPLY'); ?>"  class="button btn btn-success" />
            </div>
            <?php
            //$html = $this->storeProfile->store_billing_layout;
            $html ='';
            if(empty($html) || strlen($html) < 5) {
                //we dont have a profile set in the store profile. So use the default one.
                $html = '<div class="'.$row_class.'">
			<div class="'.$col_class.'6">[first_name] [last_name] [phone_1] [phone_2] [company] [tax_number]</div>
			<div class="'.$col_class.'6">[address_1] [address_2] [city] [zip] [country_id] [zone_id]</div>
			</div>';
            }
            //first find all the checkout fields
            preg_match_all("^\[(.*?)\]^",$html,$checkoutFields, PREG_PATTERN_ORDER);

            $this->fields =  $this->fieldClass->getFields($address->type,$address,'address');

            $allFields = $this->fields;
            ?>
            <?php foreach ($this->fields as $fieldName => $oneExtraField):?>
                <?php $onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';?>
                <?php if(property_exists($address, $fieldName)):
                    $fieldName_prefix = $fieldName;
                    if(($fieldName !='email')){
                        $html = str_replace('['.$fieldName.']',$this->fieldClass->getFormattedDisplay($oneExtraField,$address->$fieldName,$fieldName_prefix,false, $options = '', $test = false, $allFields, $allValues = null),$html);
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
                <div class="<?php echo $row_class ?>">
                    <div class="<?php echo $col_class ?>12">
                        <?php $uhtml = '';?>
                        <?php foreach ($unprocessedFields as $fieldName => $oneExtraField): ?>
                            <?php $onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';?>
                            <?php if(property_exists($address, $fieldName)): ?>
                                <?php
                                if(($fieldName !='email')){
                                    $uhtml .= $this->fieldClass->getFormattedDisplay($oneExtraField,$address->$fieldName, $fieldName,false, $options = '', $test = false, $allFields, $allValues = null);
                                }
                                ?>
                            <?php endif;?>
                        <?php endforeach; ?>
                        <?php echo $uhtml; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <input type="hidden" name="option" value="com_j2store" />
        <input type="hidden" name="view" value="customer" />
        <input type="hidden" id="task" name="task" value="saveCustomer" />
        <input type="hidden" name="type" value="<?php echo $this->address_type;?>" />
        <input type="hidden" name="j2store_address_id" value="<?php echo $address->j2store_address_id;?>" />
        <?php echo JHTML::_( 'form.token' ); ?>
    </form>
</div>
<script type="text/javascript"><!--
    function saveCustomer() {
        (function($) {
            $(".j2store-error").remove();
            var form = $("#j2storeaddressForm");
            form.find("#task").attr('value','saveCustomer');
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
                            if(json['success']['msg']){
                                var html ='';
                                html +='<div class="alert alert-success">';
                                html +='<a href="#" class="close" data-dismiss="alert">&times;</a>';
                                html +=json['success']['msg'] ;
                                html +='</div>';
                                jQuery('.j2store-address-alert').html(html);
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
                        let default_zone_id = $('#address #zone_id_default_value').val();
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

                    $("#address #zone_id").html(html);
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
    //--></script>
