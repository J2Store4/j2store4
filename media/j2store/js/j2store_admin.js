if(typeof(j2store) == 'undefined') {
	var j2store = {};
}
if(typeof(j2store.jQuery) == 'undefined') {
	j2store.jQuery = jQuery.noConflict();
}

if(typeof(J2Store) == 'undefined') {
	J2Store = jQuery.noConflict();
}

if(typeof(j2storeURL) == 'undefined') {
	var j2storeURL = '';
}

function removePAOption(pao_id,product_type) {
	(function($) {
	$.ajax({
			type : 'post',
			url :  j2storeURL+'administrator/index.php?option=com_j2store&view=products&task=removeProductOption',
			data : 'pao_id=' + pao_id+'&product_type='+product_type,
			dataType : 'json',
			success : function(data) {
				if(data.success) {
					$('#pao_current_option_'+pao_id).remove();
				}
			 }
		});
	})(j2store.jQuery);	
}

(function($) {
	// Ajax add to cart
	$( document ).on( 'click', '.j2store-cart-button', function(e) {
		e.preventDefault();		
		
		var $thisbutton = $('.j2store-cart-button');
		var form = $('.j2store-addtocart-form');
		form.find('input[name=\'ajax\']').val(1);
		//var post_data1 = [];
		//j2store-addtocart-form
		//j2store-product-form
		var post_data1 = $('.j2store-addtocart-form').serializeArray();
		//var answers = [];
		
		var $user_id = $('#user_id').val();
		var $oid = $('#oid').val();
		var $product_id = $('#product_id').val();
		form.find('input[type=\'submit\']').val(form.find('input[type=\'submit\']').data('cart-action-always'));
		var data1 = {
				option: 'com_j2store',
				view: 'carts',
				task: 'addOrderitems',
				ajax: '1',
				user_id: $user_id,
				oid: $oid,
				product_id: $product_id
			};		
		
		$.each( post_data1, function( key, value ) {			
			 if (!(value['name'] in data1) ){
				 if(value['value']){
					 data1[value['name']] = value['value'];	 
				 }
				 
			}			
		});

		$.ajax({
			type : 'post',
			url :  j2storeURL+'administrator/index.php',
			data : data1,		
			dataType : 'json',	
			success : function(json) {
				$('.j2success, .j2warning, .j2attention, .j2information, .j2error').remove();
				form.find('input[type=\'submit\']').val(form.find('input[type=\'submit\']').data('cart-action-done'));
				if(json['success']){						
					$('.j2store-product').before('<div class="alert alert-success j2success">'+json["message"]+'</div>')				
				}
				if (json['error']) {
					
					if (json['error']['option']) {
						for (i in json['error']['option']) {
							form.find('#option-' + i).after('<span class="j2error">' + json['error']['option'][i] + '</span>');
						}
					}
				}				
			},
			
		 error: function(xhr, ajaxOptions, thrownError) {
             //alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
         }
		});
	});
	})(j2store.jQuery);

function doAjaxPrice(product_id, id) {
	(function($) {
		/* Get input values from form */
		var form = $(id).closest('form');		
		//sanity check
		if(form.data('product_id') != product_id) return;
		console.log(j2storeURL);
		var values = form.serializeArray();
		//pop these params from values-> task : add & view : mycart 			
		values.pop({
			name : "task",
			value : 'addOrderitems'
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
		if(form.data('product_type') == 'variable' || form.data('product_type') == 'advancedvariable') {
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
		
		$.ajax({
			url : j2storeURL+'administrator/index.php?option=com_j2store&view=products&task=update&product_id='+product_id,
			type : 'post',
			data : values,
			dataType : 'json',
			success : function(response) {
				console.log(response);
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

				}
			},
			error : function(xhr, ajaxOptions, thrownError) {
				console.log(thrownError + "\r\n" + xhr.statusText + "\r\n"
						+ xhr.responseText);
			}
		});
	})(j2store.jQuery);
}

function get_matching_variant(variants, selected) {
	for(var i in variants) {		
		if(variants[i] == selected) return i;
	}
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
			value : 'addOrderitems'
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
		
		values = jQuery.param(values);
		$.ajax({
					url : j2storeURL+'administrator/index.php?option=com_j2store&view=products&task=update&po_id='
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
						console.log(response);
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
							}
							
							//dimensions
							if (response.dimensions) {
								$product.find('.product-dimensions').html(response.dimensions);						
							}
							
							//weight
							if (response.weight) {
								$product.find('.product-weight').html(response.weight);						
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

					},
					error : function(xhr, ajaxOptions, thrownError) {
						console.log(thrownError + "\r\n" + xhr.statusText+ "\r\n" + xhr.responseText);
					}
				});
	})(j2store.jQuery);
}
function changeZone(country_id,country_value,zone_id,zone_value){
	if(country_id && zone_id){
		(function($) {
			$.ajax({
				url: j2storeURL+'index.php?option=com_j2store&view=carts&task=getCountry&country_id=' + country_value,
				dataType: 'json',
				beforeSend: function() {
					$('#'+country_id).after('<span class="wait">&nbsp;<img src="'+j2storeURL+'/media/j2store/images/loader.gif" alt="" /></span>');
				},
				complete: function() {
					$('.wait').remove();
				},
				success: function(json) {
					let html = '<option value="">--select--</option>';

					if (json['zone'] !== '') {
						default_zone_id = zone_value;
						for (i = 0; i < json['zone'].length; i++) {
							html += '<option value="' + json['zone'][i]['j2store_zone_id'] + '"';

							if (json['zone'][i]['j2store_zone_id'] === default_zone_id) {
								html += ' selected="selected"';
							}

							html += '>' + json['zone'][i]['zone_name'] + '</option>';
						}
					} else {
						html += '<option value="0" selected="selected">None</option>';
					}

					$('#'+zone_id).html(html);
				},
				error: function(xhr, ajaxOptions, thrownError) {
				}
			});
		})(j2store.jQuery);
	}
}

function listVariableItemTask(id,isDefault,product_id){
	(function($) {
		let item_data = {
			option: 'com_j2store',
			view: 'products',
			task: 'setDefaultVariant',
			v_id: id,
			status: isDefault,
			product_id: product_id
		};
		$.ajax({
			url  : j2storeURL+'administrator/index.php',
			dataType:'json',
			data : item_data,
			success:function(json){
				if(json['success']){
					location.reload();
				}
			}
		});
	})(j2store.jQuery);
}

/**
 * Method to Expand All Accordian Panel
 */
function setExpandAll(){
	(function($) {
		$('.j2store-product-variants .panel-collapse:not(".show")').collapse('show');
	})(j2store.jQuery);
}

/**
 * Method to Close All Accordian Panel
 */
function setCloseAll(){
	(function($) {
		$('.j2store-product-variants .panel-collapse.in').collapse('hide');
		$('.j2store-product-variants .panel-collapse.show').collapse('hide');
	})(j2store.jQuery);
}

function fancyConfirm( opts ) {
	(function ($){
		opts  = $.extend( true, {
			title     : 'Are you sure?',
			message   : '',
			okButton  : 'OK',
			noButton  : 'Cancel',
			callback  : $.noop
		}, opts || {} );

		$.fancybox.open({
			type : 'html',
			src  :
				'<div class="j2-content">' +
				'<h3>' + opts.title   + '</h3>' +
				'<p>'  + opts.message + '</p>' +
				'<p class="tright">' +
				'<a data-value="0" data-fancybox-close class="btn btn-danger">' + opts.noButton + '</a>' +
				'<button data-value="1" data-fancybox-close class="btn btn-primary">' + opts.okButton + '</button>' +
				'</p>' +
				'</div>',
			opts : {
				animationDuration : 350,
				animationEffect   : 'material',
				modal : true,
				baseTpl :
					'<div class="fancybox-container fc-container" role="dialog" tabindex="-1">' +
					'<div class="fancybox-bg"></div>' +
					'<div class="fancybox-inner">' +
					'<div class="fancybox-stage"></div>' +
					'</div>' +
					'</div>',
				afterClose : function( instance, current, e ) {
					var button = e ? e.target || e.currentTarget : null;
					var value  = button ? $(button).data('value') : 0;

					opts.callback( value );
				}
			}
		});
	})(j2store.jQuery);
}


(function($) {

	/* to open confirm pop  used for both regenerate variants and deletevariants
     * based on the confirm_type will call the regenerate , deletevariants respectively
     */
	$(document).on('click','.j2store-product-variants .launchConfirm', function (e) {
		let  confirm_type = $(this).data('confirm_type');
		let  product_id = $(this).data('product_id');
		let  disable_msg = $(this).data('disable_msg');
		let  title = $(this).data('title');
		let  message = $(this).data('message');
		let  ok_button = $(this).data('yes_text');
		let  no_button = $(this).data('no_text');
		if(confirm_type === 'generateVariants'){
			$(this).attr('disabled', true);
			$(this).attr('value', disable_msg);
			let params = {
				option: 'com_j2store',
				view: 'products',
				task: confirm_type,
				product_id: product_id
			};
			let options = {
				type : 'post',
				url  : j2storeURL+'administrator/index.php',
				cache: false,
				dataType : 'json',
				data: params
			}
			$.ajax(options)
				.done( function(response) {
					location.reload(true);
				});
		}else {
			fancyConfirm({
				title     : title,
				message   : message,
				okButton  : ok_button,
				noButton  : no_button,
				callback  : function (value) {
					if (value) {
						$(this).attr('disabled', true);
						$(this).attr('value', disable_msg);
						let params = {
							option: 'com_j2store',
							view: 'products',
							task: confirm_type,
							product_id: product_id
						};
						let options = {
							type : 'post',
							url  : j2storeURL+'administrator/index.php',
							cache: false,
							dataType : 'json',
							data: params
						}
						$.ajax(options)
							.done( function(response) {
								location.reload(true);
							});
					}
				}
			});
		}
	});
})(j2store.jQuery);