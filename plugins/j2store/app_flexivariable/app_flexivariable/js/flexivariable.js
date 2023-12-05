/**
 * Setup (required for Joomla! 3)
 */
if(typeof(j2store) == 'undefined') {
    var j2store = {};
}
if(typeof(j2store.jQuery) == 'undefined') {
    j2store.jQuery = jQuery.noConflict();
}

function doFlexiAjaxPrice(product_id, id) {
    (function($) {
        /* Get input values from form */
        var form = $(id).closest('form');
        //sanity check
        if(form.data('product_id') != product_id) return;

        var values = form.serializeArray();
        //pop these params from values-> task : add & view : mycart
        values.pop({
            name : "task",
            value : 'addItem'
        });

        values.pop({
            name : "view",
            value : 'carts'
        });

        values.push({
            name : "product_id",
            value :product_id
        });

        var arrayClean = function(thisArray) {
            "use strict";
            $.each(thisArray, function(index, item) {
                if (item.name == 'task' || item.name == 'view') {
                    delete values[index];
                }
            });
        };
        arrayClean(values);

        values = values.filter(function( element ) {
            return element !== undefined;
        });
        $( 'body' ).trigger( 'before_doAjaxPrice', [ form, values ] );
        $('.j2error').remove();
        $.ajax({
            url : j2storeURL+ 'index.php?option=com_j2store&view=product&task=update',
            type : 'get',
            data : values,
            dataType : 'json',
            success : function(response) {

                var $product = $('.product-'+ product_id);

                if ($product.length
                    && typeof response.error == 'undefined' ) {
                    //SKU
                    if (response.sku) {
                        $product.find('.sku').html(response.sku);
                    }

                    if(response.pricing){
                        //base price
                        if (response.pricing.base_price) {
                            $product.find('.base-price').html(response.pricing.base_price);
                            if(response.pricing.class){
                                if(response.pricing.class == 'show'){
                                    $product.find('.base-price').show();
                                    $product.find('.base-price').addClass('strike')
                                }else{
                                    $product.find('.base-price').hide()
                                }
                            }
                        }
                        //price
                        if (response.pricing.price) {
                            $product.find('.sale-price').html(response.pricing.price);
                        }

                        // discount text
                        $product.find('.discount-percentage').html(response.pricing.discount_text);
                    }

                    //afterDisplayPrice
                    if (response.afterDisplayPrice) {
                        $product.find('.afterDisplayPrice').html(response.afterDisplayPrice);
                    }
                    //qty
                    if (response.quantity) {
                        $product.find('input[name="product_qty"]').val(response.quantity);
                        if(form.data('product_type') == 'variable' || form.data('product_type') == 'advancedvariable' || form.data('product_type') == 'variablesubscriptionproduct') {
                            $product.find('input[name="product_qty"]').attr({
                                value: response.quantity
                            });
                        }
                    }
                    if (response.main_image) {
                        // Check if thumb_image exists before updating attributes
                        if (response.thumb_image) {
                            $product.find('.j2store-product-thumb-image-' + product_id).attr("src", response.thumb_image);
                            j2store.jQuery('.j2store-product-thumb-image-' + product_id).attr("src", response.thumb_image);
                        }

                        j2store.jQuery('.j2store-product-main-image-' + product_id).attr("src", response.main_image);
                        $product.find('.j2store-mainimage .j2store-img-responsive').attr("src", response.main_image);
                        $product.find('.j2store-product-additional-images .additional-mainimage').attr("src", response.main_image);
                    }
                    //stock status

                    if (typeof response.stock_status != 'undefined') {
                        if (response.availability == 1) {
                            $product.find('.product-stock-container').html('<span class="instock">' + response.stock_status + '</span>');
                        }else {
                            $product.find('.product-stock-container').html('<span class="outofstock">' + response.stock_status + '</span>');
                        }
                    }

                    //dimensions
                    if (response.dimensions) {
                        $product.find('.product-dimensions').html(response.dimensions);
                    }

                    //weight
                    if (response.weight) {
                        $product.find('.product-weight').html(response.weight);
                    }


                    // Trigger event
                    $( 'body' ).trigger( 'after_doAjaxFilter', [ $product, response ] );
                    $( 'body' ).trigger( 'after_doAjaxPrice', [ $product, response ] );
                }else {
                    $product.find('#variable-options-'+product_id).after('<div class="j2error">'+response.error+'</div>');
                }
            },
            error : function(xhr, ajaxOptions, thrownError) {
                console.log(thrownError + "\r\n" + xhr.statusText + "\r\n"
                    + xhr.responseText);
            }
        });
    })(j2store.jQuery);
}