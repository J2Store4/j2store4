<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
$ajax_base_url = JRoute::_('index.php');
?>
<div id="j2store-checkout" class="j2store checkout">
	<div id="j2store-checkout-content">
		<h3>Checkout</h3>
		<div id="shipping-payment-method">
			<div class="checkout-heading">
				<?php if ($this->showShipping) : ?>
					<?php echo JText::_('J2STORE_CHECKOUT_SHIPPING_PAYMENT_METHOD'); ?>
				<?php else: ?>
					<?php echo JText::_('J2STORE_CHECKOUT_PAYMENT_METHOD'); ?>
				<?php endif;?>
			</div>
			<div class="checkout-content"></div>
		</div>
		<div id="confirm">
			<div class="checkout-heading"><?php echo JText::_('J2STORE_CHECKOUT_CONFIRM');; ?></div>
			<div class="checkout-content"></div>
		</div>
	</div>
</div>
<script>
	(function ($) {
		$(document).ready(function () {
			$.ajax({
				url: '<?php echo $ajax_base_url; ?>',
				type: 'post',
				cache: false,
				data: 'option=com_j2store&view=checkouts&task=shipping_payment_method',
				dataType: 'html',
				success: function(html) {
					$('#shipping-payment-method .checkout-content').html(html);

					$('#shipping-address .checkout-content').slideUp('slow');

					$('#shipping-payment-method .checkout-content').slideDown('slow');

					$('#shipping-address .checkout-heading a').remove();
					$('#shipping-payment-method .checkout-heading a').remove();
					//$('#payment-method .checkout-heading a').remove();

					$('#shipping-address .checkout-heading').append('<a><?php echo JText::_('J2STORE_CHECKOUT_MODIFY'); ?></a>');
					$(window).scrollTop(200);
					//$('#shipping-payment-method .checkout-content input[name=view]').val('myprofile');
				},
				error: function(xhr, ajaxOptions, thrownError) {
					//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		});
	})(j2store.jQuery);
	//shipping and payment methods
	(function($) {
		$(document).on('click', '#button-payment-method', function() {
			$.ajax({
				url: '<?php echo $ajax_base_url; ?>',
				type: 'post',
				cache: false,
				data: $('#shipping-payment-method input[type=\'text\'], #shipping-payment-method input[type=\'hidden\'], #shipping-payment-method input[type=\'radio\']:checked, #shipping-payment-method input[type=\'checkbox\']:checked, #shipping-payment-method textarea, #shipping-payment-method select'),
				dataType: 'json',
				beforeSend: function() {
					$('#button-payment-method').attr('disabled', true);
					$('#button-payment-method').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
				},
				complete: function() {

				},
				success: function(json) {
					$('.warning, .j2error').remove();

					if (json['redirect']) {
						location = json['redirect'];
					} else if (json['error']) {
						$('.checkout-content').scrollTop();
						if (json['error']['shipping']) {
							$('#shipping_error_div').html('<span class="j2error">' + json['error']['shipping'] + '</span>');
							$(window).scrollTop($('#shipping-payment-method').offset().top);
						}

						if (json['error']['warning']) {
							$('#shipping-payment-method .checkout-content').prepend('<div class="warning alert alert-danger" style="display: none;">' + json['error']['warning'] + '<button data-dismiss="alert" class="close" type="button">×</button></div>');

							$('.warning').fadeIn('slow');
							$(window).scrollTop($('#shipping-payment-method .checkout-content .warning').offset().top);
						}

						$.each( json['error'], function( key, value ) {
							if (value) {
								$('#shipping-payment-method #'+key).after('<br class="j2error" /><span class="j2error">' + value + '</span>');
							}
						});


					} else {
						$.ajax({
							url: '<?php echo $ajax_base_url; ?>',
							type: 'post',
							cache: false,
							data: 'option=com_j2store&view=checkouts&task=confirm',
							dataType: 'html',
							success: function(html) {
								$('#confirm .checkout-content').html(html);

								$('#shipping-payment-method .checkout-content').slideUp('slow');

								$('#confirm .checkout-content').slideDown('slow');

								$('#shipping-payment-method .checkout-heading a').remove();

								$('#shipping-payment-method .checkout-heading').append('<a><?php echo JText::_('J2STORE_CHECKOUT_MODIFY'); ?></a>');
							},
							error: function(xhr, ajaxOptions, thrownError) {
								//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
							}
						});
					}
					$('#button-payment-method').attr('disabled', false);
					$('.wait').remove();
				},
				error: function(xhr, ajaxOptions, thrownError) {
					//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		});
	})(j2store.jQuery);

	function getFormData(target) {
		var d = document, ret = '';
		if( typeof(target) == 'string' )
			target = d.getElementById(target);
		if( target === undefined )
			target = d;
		var typelist = ['input','select','textarea'];
		for(var t in typelist ) {
			t = typelist[t];
			var inputs = target.getElementsByTagName(t);
			for(var i = inputs.length - 1; i >= 0; i--) {
				if( inputs[i].name && !inputs[i].disabled ) {
					var evalue = inputs[i].value, etype = '';
					if( t == 'input' )
						etype = inputs[i].type.toLowerCase();
					if( (etype == 'radio' || etype == 'checkbox') && !inputs[i].checked )
						evalue = null;
					if( (etype != 'file' && etype != 'submit') && evalue != null ) {
						if( ret != '' ) ret += '&';
						ret += encodeURI(inputs[i].name) + '=' + encodeURIComponent(evalue);
					}
				}
			}
		}
		return ret;
	}
</script>
