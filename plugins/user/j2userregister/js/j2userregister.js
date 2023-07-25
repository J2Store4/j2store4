if(typeof(j2store) == 'undefined') {
    var j2store = {};
}
if(typeof(j2store.jQuery) == 'undefined') {
    j2store.jQuery = jQuery.noConflict();
}

if(typeof(j2storeURL) == 'undefined') {
    var j2storeURL = '';
}
(function($) {
    $(document).on('change','#billing-new #country_id', function() {
        if (this.value == '') return;
        $.ajax({
            url: j2_ajax_url,
            data: 'option=com_j2store&view=carts&task=getCountry&country_id=' + this.value,
            dataType: 'json',
            beforeSend: function() {
                $('#billing-new #country_id').after('<span class=\"wait\">&nbsp;<img src="/media/j2store/images/loader.gif\" alt=\"\" /></span>');
            },
            complete: function() {
                $('.wait').remove();
            },
            success: function(json) {
                if (json['postcode_required'] == '1') {
                    $('#billing-postcode-required').show();
                } else {
                    $('#billing-postcode-required').hide();
                }

                html = '<option value=\"\">--select--</option>';

                if (json['zone'] != '') {
                    default_zone_id = $('#billing-new #zone_id_default_value').val();
                    for (i = 0; i < json['zone'].length; i++) {
                        html += '<option value=\"' + json['zone'][i]['j2store_zone_id'] + '\"';

                        if (json['zone'][i]['j2store_zone_id'] == default_zone_id) {
                            html += ' selected=\"selected\"';
                        }

                        html += '>' + json['zone'][i]['zone_name'] + '</option>';
                    }
                } else {
                    html += '<option value=\"0\" selected=\"selected\">--zone--</option>';
                }

                $('#billing-new select[name=\'j2reg[zone_id]\']').html(html);
                $('#billing-new select[name=\'j2reg[zone_id]\']').trigger("liszt:updated");
            },
            error: function(xhr, ajaxOptions, thrownError) {

            }
        });
    });
})(j2store.jQuery);

(function($) {
    $(document).ready(function () {
        var j2_data =$('#billing-new select, #billing-new input,#billing-new checkbox').serializeArray();
        $.each(j2_data,function(intex,item){
            $('#billing-new #'+item.name).attr('name','j2reg['+item.name+']');
        });

        if($('#billing-new #country_id').length > 0) {
            $('#billing-new #country_id').trigger('change');
        }

        var flack = 0;
        var form = $('#billing-new').closest("form");

        $(form).submit(function(e) {
            if (flack == 0) {
                e.preventDefault();
                var form_data = form.serializeArray();

                var arrayClean = function(thisArray) {
                    "use strict";
                    var cleanedArray = [];
                    $.each(thisArray, function(index, item) {
                        if (item.name != 'task' && item.name != 'view' && item.name != 'option') {
                            cleanedArray.push(item);
                        }
                    });
                    return cleanedArray;
                };
                form_data = arrayClean(form_data);

                form_data.push({
                    name: 'option',
                    value: 'com_ajax'
                });
                form_data.push({
                    name: 'plugin',
                    value: 'j2userregister'
                });
                form_data.push({
                    name: 'format',
                    value: 'json'
                });
                form_data.push({
                    name: 'group',
                    value: 'user'
                });


                $.ajax({
                    url: 'index.php',
                    type: 'post',
                    data: form_data,
                    dataType: 'json',
                    beforeSend: function() {

                    },
                    complete: function() {
                        $('.wait').remove();
                    },
                    success: function(json) {
                        $('.wait, .j2error').remove();
                        if(json['success']){
                            flack = 1;
                            form.submit();
                        }
                        if (json['error']) {
                            if (json['error']['warning']) {
                                form.prepend('<div class=\"warning alert alert-block alert-danger\" style=\"display: none;\">' + json['error']['warning'] + '<button data-dismiss=\"alert\" class=\"close\" type=\"button\">×</button></div>');

                                $('.warning').fadeIn('slow');
                            }

                            $.each( json['error'], function( key, value ) {
                                if (value) {
                                    form.find('#'+key).after('<span class=\"text-danger j2error\">' + value + '</span>');
                                }

                            });

                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {

                    }
                });

            }

        });

    });

})(j2store.jQuery);
