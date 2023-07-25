/**
 * Setup (required for Joomla! 3)
 */
if(typeof jQuery === 'undefined' || (parseInt(jQuery.fn.jquery) === 1 && parseFloat(jQuery.fn.jquery.replace(/^1\./,'')) < 10)){
    var head = document.getElementsByTagName('head')[0];
    var script = document.createElement('script');
    script.src = '//ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js';
    script.type = 'text/javascript';
    script.onload = script.onreadystatechange = function() {
        if (script.readyState) {
            if (script.readyState === 'complete' || script.readyState === 'loaded') {
                script.onreadystatechange = null;
                onload(jQuery.noConflict(true));
            }
        }
        else {
            onload(jQuery.noConflict(true));
        }
    };
    head.appendChild(script);
}
if(typeof(j2store) == 'undefined') {
	var j2store = {};
}
if(typeof(j2store.jQuery) == 'undefined') {
	j2store.jQuery = jQuery.noConflict();
}

if(typeof(j2storeURL) == 'undefined') {
	var j2storeURL = '';
}

//make sure the ajax requests are not cached
(function($) {
	$(document).ready(function() {
		$.ajaxSetup({
			cache: false,
			headers: {
				'Cache-Control': 'no-cache, no-store, must-revalidate',
				'Pragma': 'no-cache',
				'Expires': '0'
			}
		});
	});
})(j2store.jQuery);

(function($) {
// Ajax add to cart
$( document ).on( 'click', '.j2store_add_to_cart_button', function(e) {

	// AJAX add to cart request
	var $thisbutton = $( this );

	if ( ! $thisbutton.attr( 'data-product_id' ) )
		return true;

		$thisbutton.removeClass( 'added' );
		$thisbutton.addClass( 'loading' );

		var data = {
			option: 'com_j2store',
			view: 'carts',
			task: 'addItem',
			ajax: '1',
		};

		$.each( $thisbutton.data(), function( key, value ) {
			data[key] = value;
		});

		// Trigger event
		$( 'body' ).trigger( 'adding_to_cart', [ $thisbutton, data ] );
		
		var href = $thisbutton.attr('href');
		if(typeof href === 'undefined' || href === '') {
			href = 'index.php';
		}

		// Ajax action
		$.post( href, data, function( response ) {

			if ( ! response )
				return;

			var this_page = window.location.toString();

			this_page = this_page.replace( 'add-to-cart', 'added-to-cart' );

			if (response['error']) {
				window.location = response.product_url;
				return;
			}
			
			if (response['redirect']) {
				window.location.href = response['redirect'];
				return;
			}				
			if (response['success']) {
			  $thisbutton.removeClass( 'loading' );
			  // Changes button classes
			  $thisbutton.addClass( 'added' );
			  $thisbutton.parent().find('.cart-action-complete').show();			    
			  //if module is present, let us update it.
			  $( 'body' ).trigger( 'after_adding_to_cart', [ $thisbutton, response, 'link'] );
			   //doMiniCart();
			}
		}, 'json');

		return false;
	
});
})(j2store.jQuery);


(function($) {
	$(document).ready(function(){
	$('.j2store-addtocart-form').each(function(){
		$(this).submit(function(e) {	
		e.preventDefault();
		var form = $(this);
		
		//this will help detect if the form is submitted via ajax or normal submit.
		//sometimes people will submit the form before the DOM loads
		form.find('input[name=\'ajax\']').val(1);
		/* Get input values from form */
		var values = form.find('input[type=\'text\'], input[type=\'number\'], input[type=\'hidden\'], input[type=\'radio\']:checked, input[type=\'checkbox\']:checked, select, textarea');
		form.find('input[type=\'submit\']').val(form.find('input[type=\'submit\']').data('cart-action-always'));
		form.find('input[type=\'submit\']').attr('disabled',true);
		var href = form.attr('action');		
		if(typeof href == 'undefined' || href == '') {
			var href = 'index.php';
		}
			// Trigger event
			$( 'body' ).trigger( 'adding_to_cart', [ form, values ] );
		//var values = form.serializeArray();
	 	var j2Ajax = $.ajax({
				url: href,
				type: 'post',
				data: values,
				dataType: 'json'
					
	 	 });

	 	j2Ajax.done(function(json) {
			    form.find('input[type=\'submit\']').attr('disabled',false);
	 	 		form.find('.j2success, .j2warning, .j2attention, .j2information, .j2error').remove();
				$('.j2store-notification').hide();				
				if (json['error']) {
					
					form.find('input[type=\'submit\']').val(form.find('input[type=\'submit\']').data('cart-action-done'));
					
					if (json['error']['option']) {
						for (i in json['error']['option']) {
							form.find('#option-' + i).after('<span class="j2error">' + json['error']['option'][i] + '</span>');
						}
					}
					if (json['error']['stock']) {
						form.find('.j2store-notifications').html('<span class="j2error">' + json['error']['stock'] + '</span>');
					}
					
					if (json['error']['general']) {
						form.find('.j2store-notifications').html('<span class="j2error">' + json['error']['general'] + '</span>');
					}
					
					if (json['error']['product']) {
						form.find('.j2store-notifications').after('<span class="j2error">' + json['error']['product'] + '</span>');
					}
				}	
				
				if (json['redirect']) {
					window.location.href = json['redirect'];
					return;
				}
				
				if (json['success']) {					
					setTimeout(function() {						
						form.find('input[type=\'submit\']').val(form.find('input[type=\'submit\']').data('cart-action-done'));
						form.find('.cart-action-complete').fadeIn('slow');
					}, form.find('input[type=\'submit\']').data('cart-action-timeout'));
					
					$( 'body' ).trigger( 'after_adding_to_cart', [form, json, 'normal'] );	
					//if module is present, let us update it.
					//	doMiniCart();
				}				
	 	})	 	
	 	.fail(function( jqXHR, textStatus, errorThrown) {
	 		form.find('input[type=\'submit\']').val(form.find('input[type=\'submit\']').data('cart-action-done'));
	 		console.log(textStatus + errorThrown);	 		
	 	})
	 	.always(function(jqXHR, textStatus, errorThrown) {
	 		//form.find('input[type=\'submit\']').val(form.find('input[type=\'submit\']').data('cart-action-always'));	 		
	 	});
		});	
	});		//end of ajax call
  }); //end of document ready
})(j2store.jQuery);


(function($) {
$(document).ready(function(){
	
	if ($('#j2store_shipping_make_same').length > 0) {
		if ($('#j2store_shipping_make_same').is(':checked')) {
			$('#j2store_shipping_section').css({'visible' : 'visible', 'display' : 'none'});
			
			$('#j2store_shipping_section').children(".input-label").removeClass("required");
					
			$('#j2store_shipping_section').children(".input-text").removeClass("required");
		}
	}
	
});
})(j2store.jQuery);

function doMiniCart() {
(function($) {		
		var murl = j2storeURL
			+ 'index.php?option=com_j2store&view=carts&task=ajaxmini';

		$.ajax({
			url : murl,
			type : 'get',
			cache : false,
			contentType : 'application/json; charset=utf-8',
			dataType : 'json',
			success : function(json) {				
				if (json != null && json['response']) {
					$.each(json['response'], function(key, value) {
						if ($('.j2store_cart_module_' + key).length) {
							$('.j2store_cart_module_' + key).each(function() {							
								$(this).html(value);
							});
						}
					});
				}
			}

		});
	
})(j2store.jQuery);
	
}

function j2storeDoTask(url, container, form, msg, formdata) {

	(function($) {		
	//to make div compatible
	container = '#'+container;	

	// if url is present, do validation
	if (url && form) {
		var str = $(form).serialize();
		// execute Ajax request to server
		$.ajax({
			url : url,
			type : 'get',
			 cache: false,
             contentType: 'application/json; charset=utf-8',
             data: formdata,
             dataType: 'json',
             beforeSend: function() {
               	 $(container).before('<span class="wait"><img src="'+j2storeURL+'media/j2store/images/loader.gif" alt="" /></span>');
                   },
             complete: function() {
            	 $('.wait').remove();
             },
			// data:{"elements":Json.toString(str)},
             success: function(json) {
            	if ($(container).length > 0) {            		
            		$(container).html(json.msg);
				}				
				return true;
			}
		});
	} else if (url && !form) {
		// execute Ajax request to server
		$.ajax({
			url : url,
			 type: 'get',
             cache: false,
             contentType: 'application/json; charset=utf-8',
             data: formdata,
             dataType: 'json',
             beforeSend: function() {
               	 $(container).before('<span class="wait"><img src="'+j2storeURL+'media/j2store/images/loader.gif" alt="" /></span>');
                 },
             complete: function() {
            	 $('.wait').remove();
             	},
             success: function(json) {
            	 if ($(container).length > 0) {
            		$(container).html(json.msg);
				}				
			}
		});
	}
	})(j2store.jQuery);
}

function j2storeSetShippingRate(name, price, tax, extra, code, combined, ship_element, css_id )
{
	
(function($) {
	$("input[type='hidden'][name='shipping_name']").val(name);
	$("input[type='hidden'][name='shipping_code']").val(code);
	$("input[type='hidden'][name='shipping_price']").val(price);
	$("input[type='hidden'][name='shipping_tax']").val(tax);
	$("input[type='hidden'][name='shipping_extra']").val(extra);
	var ship_name = name.replace(' ','');
	$('#onCheckoutShipping_wrapper .shipping_element').hide();
	$('#onCheckoutShipping_wrapper .'+css_id+'_select_text').show();
})(j2store.jQuery);

} 
		

function doAjaxFilter(pov_id, product_id, po_id, id) {
	(function($) {

		if (pov_id == '' || $('#ChildOptions' + po_id).length != 0) {
			$('#ChildOptions' + po_id).html('');
		}
		
		var form = $(id).closest('form');
		//sanity check
		if(form.data('product_id') != product_id) return;		
		
		var values = form.serializeArray();
		// pop these params from values-> task : add & view : mycart
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
		}
		arrayClean(values);
		
		//variable check
		if(form.data('product_type') == 'advancedvariable') {
				
				var csv = [];
			form.find('input[type=\'radio\']:checked, select').each( function( index, el ) {	
				if(el.value){					
					if($(el).data('is-variant')){						
						 csv.push(el.value);						 
					}
				}
			});
						
			//need to sort the csv array to make sure correct array orde passing			
			
			var processed_csv =[];
			processed_csv = csv.sort(function(a, b){return a-b});	
			
			var $selected_variant = processed_csv.join();
			
			//get all variants
			//var $variants = form.data('product_variants');		
			
			
			var $variants = form.data('product_variants');
			
			
			var $variant_id = get_matching_variant($variants, $selected_variant);			
			
			form.find('input[name=\'variant_id\']').val($variant_id);		
		
			
				values.push({
					name : "variant_id",
					value :$variant_id
				});		
		}
		values = values.filter(function( element ) {
			return element !== undefined;
		});
		values = jQuery.param(values);
		$( 'body' ).trigger( 'before_doAjaxFilter', [ form, values ] );
		$.ajax({
					url : j2storeURL+'index.php?option=com_j2store&view=products&task=update&po_id='
							+ po_id
							+ '&pov_id='
							+ pov_id
							+ '&product_id='
							+ product_id,
					type : 'get',
					cache : false,
					data : values,
					dataType : 'json',
					beforeSend: function() {
						$('#option-' + po_id).append('<span class="wait">&nbsp;<img src="'+j2storeURL+'/media/j2store/images/loader.gif" alt="" /></span>');
					},
					complete: function() {
						$('.wait').remove();
					},
					success : function(response) {
						
						var $product = $('.product-'+ product_id);
						
						if ($product.length
								&& typeof response.error == 'undefined') {

							//SKU
							if (response.sku) {
								$product.find('.sku').html(response.sku);
							}
							//base price
							if (response.pricing.base_price) {
								$product.find('.base-price').html(response.pricing.base_price);						
							}
							//price
							if (response.pricing.price) {
								$product.find('.sale-price').html(response.pricing.price);
							}
							
							//afterDisplayPrice
							if (response.afterDisplayPrice) {
								$product.find('.afterDisplayPrice').html(response.afterDisplayPrice);
							}
							
							//qty
							if (response.quantity) {
								$product.find('input[name="product_qty"]').val(response.quantity);
								if(form.data('product_type') == 'variable' || form.data('product_type') == 'advancedvariable') {
									$product.find('input[name="product_qty"]').attr({
										value: response.quantity
									});
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
							// main image change
                            if(response.main_image){

                                /*$product.find('.j2store-product-thumb-image-'+product_id).attr("src", response.main_image);
                                j2store.jQuery('.j2store-product-thumb-image-'+product_id).attr("src", response.main_image);*/
                                j2store.jQuery('.j2store-product-main-image-'+product_id).attr("src", response.main_image);
                                $product.find('.j2store-mainimage .j2store-img-responsive').attr("src", response.main_image);
                                $product.find('.j2store-product-additional-images .additional-mainimage').attr("src", response.main_image);
                            }

                            //discount text
                            if(response.pricing.discount_text){

                                $product.find('.discount-percentage').html(response.pricing.discount_text);
                            }else{
                                $product.find('.discount-percentage').addClass('no-discount');
                            }

							//stock status
							
							if (typeof response.stock_status != 'undefined') {
								if (response.availability == 1) {
									$product.find('.product-stock-container').html('<span class="instock">' + response.stock_status + '</span>');
								}else {
									$product.find('.product-stock-container').html('<span class="outofstock">' + response.stock_status + '</span>');
								}	
							}
							
							// option html
							if (response.optionhtml) {
								$product.find(' #ChildOptions' + po_id).html(response.optionhtml);								
							}

						}
						$( 'body' ).trigger( 'after_doAjaxFilter_response', [ $product, response ] );
					},
					error : function(xhr, ajaxOptions, thrownError) {
						console.log(thrownError + "\r\n" + xhr.statusText
								+ "\r\n" + xhr.responseText);
					}
				});
	})(j2store.jQuery);
}

function get_matching_variant(variants, selected) {
	for(var i in variants) {		
		if(variants[i] == selected) return i;
	}
}
			

function doAjaxPrice(product_id, id) {
	(function($) {
		/* Get input values from form */
		var form = $(id).closest('form');		
		//sanity check
		if(form.data('product_id') != product_id) return;
		form.find('input[type=\'submit\']').attr('disabled',true);
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
		}
		arrayClean(values);
		
		//variable check
		if(form.data('product_type') == 'variable' || form.data('product_type') == 'advancedvariable' || form.data('product_type') == 'variablesubscriptionproduct') {
			var csv = [];
			if(form.data('product_type') == 'advancedvariable') {
				form.find('input[type=\'radio\']:checked, select').each( function( index, el ) {	
					if(el.value){					
						if($(el).data('is-variant')){						
							 csv.push(el.value);						 
						}
					}
				});				
			}else {
				form.find('input[type=\'radio\']:checked, select').each( function( index, el ) {
					csv.push(el.value);	
				});
			}
			var processed_csv =[];
			processed_csv = csv.sort(function(a, b){return a-b});
			
			var $selected_variant = processed_csv.join();
			//get all variants
			var $variants = form.data('product_variants');
			 
			var $variant_id = get_matching_variant($variants, $selected_variant);
			form.find('input[name=\'variant_id\']').val($variant_id);
			
			values.push({
				name : "variant_id",
				value :$variant_id
			});	
		}
		values = values.filter(function( element ) {
			return element !== undefined;
		});
		$( 'body' ).trigger( 'before_doAjaxPrice', [ form, values ] );
		$('.j2store-notifications .j2error').html('');
		$.ajax({
			url : j2storeURL+ 'index.php?option=com_j2store&view=product&task=update',
			type : 'get',
			data : values,
			dataType : 'json',
			success : function(response) {
				
				var $product = $('.product-'+ product_id);
				form.find('input[type=\'submit\']').attr('disabled',false);
				if ($product.length
						&& typeof response.error == 'undefined') {
					//SKU
					if (response.sku) {
						$product.find('.sku').html(response.sku);
					}
					//base price
					if (response.pricing.base_price) {
						$product.find('.base-price').html(response.pricing.base_price);
                        if(response.pricing.class){
                            if(response.pricing.class == 'show'){
                                $product.find('.base-price').show()
                            }else{
                                $product.find('.base-price').hide()
                            }
                        }
					}
					//price
					if (response.pricing.price) {
						$product.find('.sale-price').html(response.pricing.price);
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
					if(response.main_image){

						/*$product.find('.j2store-product-thumb-image-'+product_id).attr("src", response.main_image);
						j2store.jQuery('.j2store-product-thumb-image-'+product_id).attr("src", response.main_image);*/
						j2store.jQuery('.j2store-product-main-image-'+product_id).attr("src", response.main_image);
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
					// discount text
                    $product.find('.discount-percentage').html(response.pricing.discount_text);
					// Trigger event
					$( 'body' ).trigger( 'after_doAjaxFilter', [ $product, response ] );
					$( 'body' ).trigger( 'after_doAjaxPrice', [ $product, response ] );
				}
			},
			error : function(xhr, ajaxOptions, thrownError) {
				console.log(thrownError + "\r\n" + xhr.statusText + "\r\n"
						+ xhr.responseText);
			}
		});
	})(j2store.jQuery);
}

function setMainPreview(addimagId, product_id, imageZoom, zoom_type){
	zoom_type = zoom_type || "outer";
	var src ="";
	(function($){
	src = $("#"+addimagId).attr('src');
	//$("#main-image-hidden").show();
	$("#j2store-item-main-image-"+product_id + " img").attr('src','');
	$("#j2store-item-main-image-"+product_id + " img").attr('src',src);
	if(imageZoom){
		if(zoom_type=='outer') {
			$('#j2store-item-main-image-'+product_id).elevateZoom({
			cursor: "crosshair",
			zoomWindowFadeIn: 500,
			zoomWindowFadeOut: 750,
			zoomWindowWidth:450,
			zoomWindowHeight:300
			 });
		}else if(zoom_type=='inner') {
			$("#j2store-item-main-image-"+product_id + " .zoomImg").attr('src',src);
			$("#j2store-item-main-image-"+product_id + " img" ).attr('src',src);
			$('#j2store-item-main-image-'+product_id).elevateZoom({
				cursor: "crosshair",
				zoomWindowFadeIn: 500,
				zoomWindowFadeOut: 750,
				zoomWindowWidth:450,
				zoomWindowHeight:300
			 });
		}	
	}
	})(j2store.jQuery);
}

function removeAdditionalImage(product_id, main_image, imageZoom, zoom_type){
	zoom_type = zoom_type || "outer";
	(function($){
		$("#j2store-item-main-image-"+product_id+ " img").attr('src',main_image);
		setMainPreview('j2store-item-main-image-'+product_id, product_id, imageZoom, zoom_type);
	})(j2store.jQuery);
}

/**
 * Method to Submit the Form
 * used product list view filters
 */
function getJ2storeFiltersSubmit(){
	//show the loading image
	jQuery("#j2store-product-loading").show();
	//submit the form
	jQuery("#productsideFilters").submit();
}


 function resetJ2storeBrandFilter(inputid){
	if(inputid){
		jQuery("#productsideFilters").find("#"+inputid).prop('checked',false);
	}else{
		jQuery(".j2store-brand-checkboxes").each( function(){
			this.checked = false;
		});
	}
	//getJ2storeFiltersSubmit();
}  


/**
 * Method to reset the vendor filter
 */
 function resetJ2storeVendorFilter(inputid){	 
	if(inputid){
		jQuery("#productsideFilters").find("#"+inputid).prop('checked',false);
	}else{
		jQuery(".j2store-vendor-checkboxes").each( function(){
			this.checked = false;
		});
	}
//	getJ2storeFiltersSubmit();
}


/**
 * Method to Reset Product Filter Based on the group
 * @params string productfilter checkboxes class name
 * @return result
 */
 function resetJ2storeProductFilter(productfilter_class,inputid){
	if(productfilter_class){
		//loop the class element
		jQuery("."+productfilter_class).each( function(){
			//set the checked to false
			this.checked = false;
		});
		
	}else if(inputid){		
		jQuery("#productsideFilters").find("#"+inputid).prop('checked',false);
	}
//	getJ2storeFiltersSubmit();	
}

/** Toggle Methods **/
function getPriceFilterToggle(){
	(function($) {
		$('#price-filter-icon-plus').toggle();
		$('#price-filter-icon-minus').toggle();
		$('#j2store-slider-range').toggle();
		$('#j2store-slider-range-box').toggle();
	})(j2store.jQuery);
}

function getCategoryFilterToggle(){
	(function($) {
	$('#cat-filter-icon-plus').toggle();
	$('#cat-filter-icon-minus').toggle();
	$('#j2store_category').toggle();
	})(j2store.jQuery);
}

function getBrandFilterToggle(){
	(function($) {
		$('#brand-filter-icon-plus').toggle();
		$('#brand-filter-icon-minus').toggle();
		$('#j2store-brand-filter-container').toggle();
	})(j2store.jQuery);
}

function getVendorFilterToggle(){
	(function($) {
		$('#vendor-filter-icon-plus').toggle();
		$('#vendor-filter-icon-minus').toggle();
		$('#j2store-vendor-filter-container').toggle();
	})(j2store.jQuery);
}

function getPFFilterToggle(id){
	(function($) {
		$('#pf-filter-icon-plus-'+id).toggle();
		$('#pf-filter-icon-minus-'+id).toggle();
		$('#j2store-pf-filter-'+id).toggle();
	})(j2store.jQuery);
}
