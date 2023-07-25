<?php
/*
 * --------------------------------------------------------------------------------
   Weblogicx India  - J2Store
 * --------------------------------------------------------------------------------
 * @package		Joomla! 2.5x
 * @subpackage	J2Store
 * @author    	Weblogicx India http://www.weblogicxindia.com
 * @copyright	Copyright (c) 2010 - 2015 Weblogicx India Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link		http://weblogicxindia.com
 * --------------------------------------------------------------------------------
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
$platform = J2Store::platform();
$action = $platform->getCheckoutUrl();
$ajax_base_url = JRoute::_('index.php');
$app = JFactory::getApplication();
$active_menu = $app->getMenu()->getActive();

$page_heading = (isset($active_menu->params) && is_object($active_menu->params)) ? $active_menu->getParams(): $platform->getRegistry('{}');
$page_heading_enabled = $page_heading->get('show_page_heading',0);
$page_heading_text = $page_heading->get('page_heading','');
?>
<?php if($page_heading_enabled):?>
    <div class="page-header">
        <h1> <?php echo $this->escape($page_heading_text); ?> </h1>
    </div>
<?php endif; ?>
<?php echo J2Store::modules()->loadposition('j2store-checkout-top'); ?>
<div id="j2store-checkout" class="j2store checkout">
<div id="j2store-checkout-content">
  <h1><?php echo JText::_('J2STORE_CHECKOUT'); ?></h1>

    <div id="checkout">
      <div class="checkout-heading"><?php echo JText::_('J2STORE_CHECKOUT_OPTIONS'); ?></div>
      <div class="checkout-content"></div>
    </div>
    <?php if (!$this->logged) { ?>
    <div id="billing-address">
      <div class="checkout-heading"><span><?php echo JText::_('J2STORE_CHECKOUT_ACCOUNT'); ?></span></div>
      <div class="checkout-content"></div>
    </div>
    <?php } else { ?>
    <div id="billing-address">
      <div class="checkout-heading"><span><?php echo JText::_('J2STORE_CHECKOUT_BILLING_ADDRESS'); ?></span></div>
      <div class="checkout-content"></div>
    </div>
    <?php } ?>
    <?php if ($this->showShipping) { ?>
    <div id="shipping-address">
      <div class="checkout-heading"><?php echo JText::_('J2STORE_CHECKOUT_SHIPPING_ADDRESS'); ?></div>
      <div class="checkout-content"></div>
    </div>
    <?php } ?>
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
  <?php echo J2Store::modules()->loadposition('j2store-checkout-bottom'); ?>
<script type="text/javascript"><!--

var query = {};
query['option']='com_j2store';
query['view']='checkout';

//force utf
(function($) {
	$(document).ready(function() {
		$.ajaxSetup({
			cache: false,
			headers: {
				'Cache-Control': 'no-cache, no-store, must-revalidate',
				'Pragma': 'no-cache',
				'Expires': '0'
			},
			'beforeSend' : function(xhr) { xhr.overrideMimeType('text/html; charset=UTF-8'); }
		});
	});
})(j2store.jQuery);

(function($) {
$(document).on('change', '#checkout .checkout-content input[name=\'account\']', function() {
	if ($(this).attr('value') == 'register') {
		$('#billing-address .checkout-heading span').html('<?php echo JText::_('J2STORE_CHECKOUT_ACCOUNT'); ?>');
	} else {
		$('#billing-address .checkout-heading span').html('<?php echo JText::_('J2STORE_CHECKOUT_BILLING_ADDRESS'); ?>');
	}
});
})(j2store.jQuery);

(function($) {
$(document).on('click', '.checkout-heading a', function() {
	$('.checkout-content').slideUp('slow');

	$(this).parent().parent().find('.checkout-content').slideDown('slow');
});
})(j2store.jQuery);

//incase only guest checkout is allowed we got to process that first
<?php if((!$this->logged && $this->params->get('allow_guest_checkout')) && (!$this->params->get('show_login_form', 1) && !$this->params->get('allow_registration', 1))){ ?>
(function($) {

$(document).ready(function() {
	$('#billing-address .checkout-heading span').html('<?php echo JText::_('J2STORE_CHECKOUT_BILLING_ADDRESS'); ?>');
	$('#checkout').hide();
	$.ajax({
	url: '<?php echo $ajax_base_url; ?>',
	type: 'post',
	cache: false,
	data: 'option=com_j2store&view=checkout&task=guest',
	dataType: 'html',
	success: function(html) {
		$('.warning, .j2error').remove();

		$('#billing-address .checkout-content').html(html);

		$('#checkout .checkout-content').slideUp('slow');

		$('#billing-address .checkout-content').slideDown('slow');

	},
	error: function(xhr, ajaxOptions, thrownError) {
		//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
	}
});
});
})(j2store.jQuery);

<?php }elseif(!$this->logged) { ?>
(function($) {
$(document).ready(function() {
	$.ajax({
		url: '<?php echo $ajax_base_url; ?>',
		type: 'post',
		cache: false,
		data: 'option=com_j2store&view=checkout&task=login',
		success: function(html) {
			$('#checkout .checkout-content').html(html);

			$('#checkout .checkout-content').slideDown('slow');
			$( 'body' ).trigger( 'after_login_response' );
		},
		error: function(xhr, ajaxOptions, thrownError) {
			//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});
})(j2store.jQuery);
<?php } else { ?>
(function($) {
$(document).ready(function() {
	$.ajax({
		url: '<?php echo $ajax_base_url; ?>',
		type: 'post',
		cache: false,
		data: 'option=com_j2store&view=checkout&task=billing_address',
		dataType: 'html',
		success: function(html) {
			$('#billing-address .checkout-content').html(html);

			$('#billing-address .checkout-content').slideDown('slow');
		},
		error: function(xhr, ajaxOptions, thrownError) {
			//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});
})(j2store.jQuery);
<?php } ?>


//new account
(function($) {
$(document).on('click', '#button-account', function() {
		var task = $('input[name=\'account\']:checked').attr('value');
	$.ajax({
		url: '<?php echo $ajax_base_url; ?>',
		type: 'post',
		cache: false,
		data: 'option=com_j2store&view=checkout&task='+task,
		dataType: 'html',
		beforeSend: function() {
			$('#button-account').attr('disabled', true);
			$('#button-account').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
		},
		complete: function() {
			$('#button-account').attr('disabled', false);
			$('.wait').remove();
		},
		success: function(html) {
			$('.warning, .j2error').remove();

			$('#billing-address .checkout-content').html(html);

			$('#checkout .checkout-content').slideUp('slow');

			$('#billing-address .checkout-content').slideDown('slow');

			$('.checkout-heading a').remove();

			$('#checkout .checkout-heading').append('<a><?php echo JText::_('J2STORE_CHECKOUT_MODIFY'); ?></a>');
		},
		error: function(xhr, ajaxOptions, thrownError) {
			//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});
})(j2store.jQuery);

function loginKeyPress(e)
{
	(function($) {
		if(e.keyCode == 13){
			$("#button-login").click();
		}
	})(j2store.jQuery);
}

//Login
(function($) {
$(document).on('click', '#button-login', function() {
	$.ajax({
		url: '<?php echo $ajax_base_url; ?>',
		type: 'post',
		cache: false,
		data: $('#checkout #login :input'),
		dataType: 'json',
		beforeSend: function() {
			$('#button-login').attr('disabled', true);
			$('#button-login').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
		},
		complete: function() {
			$('#button-login').attr('disabled', false);
			$('.wait').remove();
		},
		success: function(json) {
			$('.warning, .j2error').remove();

			if (json['redirect']) {
				//it is sufficient to just reload the page
				window.location.href  = json['redirect'];
			} else if (json['error']) {
				$('#checkout .checkout-content').prepend('<div class="warning alert alert-danger" style="display: none;">' + json['error']['warning'] + '<button data-dismiss="alert" class="close" type="button">×</button></div>');

				$('.warning').fadeIn('slow');
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});
})(j2store.jQuery);

// Register
(function($) {
$(document).on('click', '#button-register', function() {
	$.ajax({
		url: '<?php echo $ajax_base_url; ?>',
		type: 'post',
		cache: false,
		data: $('#billing-address input[type=\'text\'], #billing-address input[type=\'password\'], #billing-address input[type=\'checkbox\']:checked, #billing-address input[type=\'radio\']:checked, #billing-address input[type=\'hidden\'], #billing-address select, #billing-address textarea'),
		dataType: 'json',
		beforeSend: function() {
			$('#button-register').attr('disabled', true);
			$('#button-register').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
		},
		complete: function() {
			$('#button-register').attr('disabled', false);
			$('.wait').remove();
		},
		success: function(json) {
			$('.warning, .j2error').remove();

			if (json['redirect']) {
				location = json['redirect'];
			} else if (json['error']) {
				if (json['error']['warning']) {
					$('#billing-address .checkout-content').prepend('<div class="warning alert alert-block alert-danger" style="display: none;">' + json['error']['warning'] + '<button data-dismiss="alert" class="close" type="button">×</button></div>');

					$('.warning').fadeIn('slow');
				}

				$.each( json['error'], function( key, value ) {
					if (value) {
						$('#billing-address #'+key).after('<br class="j2error" /><span class="j2error">' + value + '</span>');
					}
					//alert( key + ": " + value );
					});

				if (json['error']['password']) {
					$('#billing-address input[name=\'password\'] + br').after('<span class="j2error">' + json['error']['password'] + '</span>');
				}

				if (json['error']['confirm']) {
					$('#billing-address input[name=\'confirm\'] + br').after('<span class="j2error">' + json['error']['confirm'] + '</span>');
				}				

			} else {
				<?php if ($this->showShipping) { ?>
				var shipping_address = $('#billing-address input[name=\'shipping_address\']:checked').attr('value');

				if (shipping_address) {
					$.ajax({
						url: '<?php echo $ajax_base_url; ?>',
						type: 'post',
						cache: false,
						data: 'option=com_j2store&view=checkout&task=shipping_payment_method',
						dataType: 'html',
						success: function(html) {
							$('#shipping-payment-method .checkout-content').html(html);

							$('#billing-address .checkout-content').slideUp('slow');

							$('#shipping-payment-method .checkout-content').slideDown('slow');

							$('#checkout .checkout-heading a').remove();
							$('#billing-address .checkout-heading a').remove();
							$('#shipping-address .checkout-heading a').remove();
							$('#shipping-payment-method .checkout-heading a').remove();
							//$('#payment-method .checkout-heading a').remove();

							$('#shipping-address .checkout-heading').append('<a><?php echo JText::_('J2STORE_CHECKOUT_MODIFY'); ?></a>');
							$('#billing-address .checkout-heading').append('<a><?php echo JText::_('J2STORE_CHECKOUT_MODIFY'); ?></a>');
							$(window).scrollTop(200);
							$.ajax({
								url: '<?php echo $ajax_base_url; ?>',
								type: 'post',
								data: 'option=com_j2store&view=checkout&task=shipping_address',
								dataType: 'html',
								cache: false,
								success: function(html) {
									$('#shipping-address .checkout-content').html(html);
								},
								error: function(xhr, ajaxOptions, thrownError) {
									//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
								}
							});
						},
						error: function(xhr, ajaxOptions, thrownError) {
							//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
						}
					});
				} else {
					$.ajax({
						url: '<?php echo $ajax_base_url; ?>',
						type: 'post',
						cache: false,
						data: 'option=com_j2store&view=checkout&task=shipping_address',
						dataType: 'html',
						success: function(html) {
							$('#shipping-address .checkout-content').html(html);

							$('#billing-address .checkout-content').slideUp('slow');

							$('#shipping-address .checkout-content').slideDown('slow');

							$('#checkout .checkout-heading a').remove();
							$('#billing-address .checkout-heading a').remove();
							$('#shipping-address .checkout-heading a').remove();
							$('#shipping-payment-method .checkout-heading a').remove();
							//$('#payment-method .checkout-heading a').remove();

							$('#billing-address .checkout-heading').append('<a><?php echo JText::_('J2STORE_CHECKOUT_MODIFY'); ?></a>');
						},
						error: function(xhr, ajaxOptions, thrownError) {
							//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
						}
					});
				}
				<?php } else { ?>
				$.ajax({
					url: '<?php echo $ajax_base_url; ?>',
					type: 'post',
					cache: false,
					data: 'option=com_j2store&view=checkout&task=shipping_payment_method',
					dataType: 'html',
					success: function(html) {
						$('#shipping-payment-method .checkout-content').html(html);

						$('#billing-address .checkout-content').slideUp('slow');

						$('#shipping-payment-method .checkout-content').slideDown('slow');

						$('#checkout .checkout-heading a').remove();
						$('#billing-address .checkout-heading a').remove();
						//$('#payment-method .checkout-heading a').remove();
						$('#shipping-payment-method .checkout-heading a').remove();

						$('#billing-address .checkout-heading').append('<a><?php echo JText::_('J2STORE_CHECKOUT_MODIFY'); ?></a>');
						$(window).scrollTop(200);
					},
					error: function(xhr, ajaxOptions, thrownError) {
						//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
				<?php } ?>

				$.ajax({
					url: '<?php echo $ajax_base_url; ?>',
					type: 'post',
					cache: false,
					data: 'option=com_j2store&view=checkout&task=billing_address',
					dataType: 'html',
					success: function(html) {
						$('#billing-address .checkout-content').html(html);

						$('#billing-address .checkout-heading span').html('<?php echo JText::_('J2STORE_BILLING_ADDRESS'); ?>');
					},
					error: function(xhr, ajaxOptions, thrownError) {
						//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});
})(j2store.jQuery);

//billing address
(function($) {
$(document).on('click', '#button-billing-address', function() {
	$.ajax({
		url: '<?php echo $ajax_base_url; ?>',
		type: 'post',
		cache: false,
		data: $('#billing-address input[type=\'text\'], #billing-address input[type=\'password\'], #billing-address input[type=\'checkbox\']:checked, #billing-address input[type=\'radio\']:checked, #billing-address input[type=\'hidden\'], #billing-address select, #billing-address textarea'),
		dataType: 'json',
		beforeSend: function() {
			$('#button-billing-address').attr('disabled', true);
			$('#button-billing-address').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
		},
		complete: function() {
			$('#button-billing-address').attr('disabled', false);
			$('.wait').remove();
		},
		success: function(json) {
			$('.warning, .j2error').remove();

			if (json['redirect']) {
				location = json['redirect'];
			} else if (json['error']) {
				if (json['error']['warning']) {
					$('#billing-address .checkout-content').prepend('<div class="warning" style="display: none;">' + json['error']['warning'] + '<button data-dismiss="alert" class="close" type="button">×</button></div>');

					$('.warning').fadeIn('slow');
				}

				$.each( json['error'], function( key, value ) {
					if (value) {
						$('#billing-address #'+key).after('<br class="j2error" /><span class="j2error">' + value + '</span>');
					}
				});

			} else {
				<?php if ($this->showShipping) { ?>
				$.ajax({
					url: '<?php echo $ajax_base_url; ?>',
					type: 'post',
					cache: false,
					data: 'option=com_j2store&view=checkout&task=shipping_address',
					dataType: 'html',
					success: function(html) {
						$('#shipping-address .checkout-content').html(html);

						$('#billing-address .checkout-content').slideUp('slow');

						$('#shipping-address .checkout-content').slideDown('slow');

						$('#billing-address .checkout-heading a').remove();
						$('#shipping-address .checkout-heading a').remove();
						$('#shipping-payment-method .checkout-heading a').remove();
						//$('#payment-method .checkout-heading a').remove();

						$('#billing-address .checkout-heading').append('<a><?php echo JText::_('J2STORE_CHECKOUT_MODIFY'); ?></a>');
					},
					error: function(xhr, ajaxOptions, thrownError) {
						//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
				<?php } else { ?>
				$.ajax({
					url: '<?php echo $ajax_base_url; ?>',
					type: 'post',
					cache: false,
					data: 'option=com_j2store&view=checkout&task=shipping_payment_method',
					dataType: 'html',
					success: function(html) {
						$('#shipping-payment-method .checkout-content').html(html);

						$('#billing-address .checkout-content').slideUp('slow');

						$('#shipping-payment-method .checkout-content').slideDown('slow');

						$('#billing-address .checkout-heading a').remove();
						$('#shipping-payment-method .checkout-heading a').remove();

						$('#billing-address .checkout-heading').append('<a><?php echo JText::_('J2STORE_CHECKOUT_MODIFY'); ?></a>');
						$(window).scrollTop(200);
					},
					error: function(xhr, ajaxOptions, thrownError) {
						//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
				<?php } ?>

				$.ajax({
					url: '<?php echo $ajax_base_url; ?>',
					type: 'post',
					cache: false,
					data: 'option=com_j2store&view=checkout&task=billing_address',
					dataType: 'html',
					success: function(html) {
						$('#billing-address .checkout-content').html(html);
					},
					error: function(xhr, ajaxOptions, thrownError) {
						//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});
})(j2store.jQuery);

//Shipping Address
(function($) {
$(document).on('click', '#button-shipping-address', function() {
	$.ajax({
		url: '<?php echo $ajax_base_url; ?>',
		type: 'post',
		cache: false,
		data: $('#shipping-address input[type=\'text\'], #shipping-address input[type=\'hidden\'], #shipping-address input[type=\'password\'], #shipping-address input[type=\'checkbox\']:checked, #shipping-address input[type=\'radio\']:checked, #shipping-address select, #shipping-address textarea'),
		dataType: 'json',
		beforeSend: function() {
			$('#button-shipping-address').attr('disabled', true);
			$('#button-shipping-address').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
		},
		complete: function() {
			$('#button-shipping-address').attr('disabled', false);
			$('.wait').remove();
		},
		success: function(json) {
			$('.warning, .j2error').remove();
			if (json['redirect']) {
				location = json['redirect'];
			} else if (json['error']) {
				if (json['error']['warning']) {
					$('#shipping-address .checkout-content').prepend('<div class="warning alert alert-danger" style="display: none;">' + json['error']['warning'] + '<button data-dismiss="alert" class="close" type="button">×</button></div>');

					$('.warning').fadeIn('slow');
				}

				$.each( json['error'], function( key, value ) {
					if (value) {
						$('#shipping-address #'+key).after('<br class="j2error" /><span class="j2error">' + value + '</span>');
					}
				});

			} else {
				$.ajax({
					url: '<?php echo $ajax_base_url; ?>',
					type: 'post',
					cache: false,
					data: 'option=com_j2store&view=checkout&task=shipping_payment_method',
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
						$.ajax({
							url: '<?php echo $ajax_base_url; ?>',
							type: 'post',
							cache: false,
							data: 'option=com_j2store&view=checkout&task=shipping_address',
							dataType: 'html',
							success: function(html) {
								$('#shipping-address .checkout-content').html(html);
							},
							error: function(xhr, ajaxOptions, thrownError) {
								//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
							}
						});
					},
					error: function(xhr, ajaxOptions, thrownError) {
						//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});

				$.ajax({
					url: '<?php echo $ajax_base_url; ?>',
					type: 'post',
					cache: false,
					data: 'option=com_j2store&view=checkout&task=billing_address',
					dataType: 'html',
					success: function(html) {
						$('#billing-address .checkout-content').html(html);
					},
					error: function(xhr, ajaxOptions, thrownError) {
						//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});
})(j2store.jQuery);

//Guest
(function($) {
$(document).on('click', '#button-guest', function() {
	$.ajax({
		url: '<?php echo $ajax_base_url; ?>',
		type: 'post',
		cache: false,
		data: $('#billing-address input[type=\'text\'], #billing-address input[type=\'checkbox\']:checked, #billing-address input[type=\'radio\']:checked, #billing-address input[type=\'hidden\'], #billing-address select, #billing-address textarea'),
		dataType: 'json',
		beforeSend: function() {
			$('#button-guest').attr('disabled', true);
			$('#button-guest').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
		},
		complete: function() {
			$('#button-guest').attr('disabled', false);
			$('.wait').remove();
		},
		success: function(json) {
			$('.warning, .j2error').remove();

			if (json['redirect']) {
				location = json['redirect'];
			} else if (json['error']) {
				if (json['error']['warning']) {
					$('#billing-address .checkout-content').prepend('<div class="warning alert alert-danger" style="display: none;">' + json['error']['warning'] + '<button data-dismiss="alert" class="close" type="button">×</button></div>');

					$('.warning').fadeIn('slow');
				}

				$.each( json['error'], function( key, value ) {
					if (value) {
						$('#billing-address #'+key).after('<br class="j2error" /><span class="j2error">' + value + '</span>');
					}
				});				
				
			} else {
				<?php if ($this->showShipping) { ?>
				var shipping_address = $('#billing-address input[name=\'shipping_address\']:checked').attr('value');

				if (shipping_address) {
					$.ajax({
						url: '<?php echo $ajax_base_url; ?>',
						type: 'post',
						cache: false,
						data: 'option=com_j2store&view=checkout&task=shipping_payment_method',
						dataType: 'html',
						success: function(html) {
							$('#shipping-payment-method .checkout-content').html(html);

							$('#billing-address .checkout-content').slideUp('slow');

							$('#shipping-payment-method .checkout-content').slideDown('slow');

							$('#billing-address .checkout-heading a').remove();
							$('#shipping-address .checkout-heading a').remove();
							$('#shipping-payment-method .checkout-heading a').remove();

							$('#billing-address .checkout-heading').append('<a><?php echo JText::_('J2STORE_CHECKOUT_MODIFY'); ?></a>');
							$('#shipping-address .checkout-heading').append('<a><?php echo JText::_('J2STORE_CHECKOUT_MODIFY'); ?></a>');
							$(window).scrollTop(200);
							$.ajax({
								url: '<?php echo $ajax_base_url; ?>',
								type: 'post',
								cache: false,
								data: 'option=com_j2store&view=checkout&task=guest_shipping',
								dataType: 'html',
								success: function(html) {
									$('#shipping-address .checkout-content').html(html);
								},
								error: function(xhr, ajaxOptions, thrownError) {
									//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
								}
							});
						},
						error: function(xhr, ajaxOptions, thrownError) {
							//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
						}
					});
				} else {
					$.ajax({
						url: '<?php echo $ajax_base_url; ?>',
						type: 'post',
						cache: false,
						data: 'option=com_j2store&view=checkout&task=guest_shipping',
						dataType: 'html',
						success: function(html) {

							$('#shipping-address .checkout-content').html(html);

							$('#billing-address .checkout-content').slideUp('slow');

							$('#shipping-address .checkout-content').slideDown('slow');

							$('#billing-address .checkout-heading a').remove();
							$('#shipping-address .checkout-heading a').remove();
							$('#shipping-payment-method .checkout-heading a').remove();
							//$('#payment-method .checkout-heading a').remove();

							$('#billing-address .checkout-heading').append('<a><?php echo JText::_('J2STORE_CHECKOUT_MODIFY'); ?></a>');
						},
						error: function(xhr, ajaxOptions, thrownError) {
							//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
						}
					});
				}
				<?php } else { ?>
				$.ajax({
					url: '<?php echo $ajax_base_url; ?>',
					type: 'post',
					cache: false,
					data: 'option=com_j2store&view=checkout&task=shipping_payment_method',
					dataType: 'html',
					success: function(html) {
						$('#shipping-payment-method .checkout-content').html(html);

						$('#billing-address .checkout-content').slideUp('slow');

						$('#shipping-payment-method .checkout-content').slideDown('slow');

						$('#billing-address .checkout-heading a').remove();
						$('#shipping-payment-method .checkout-heading a').remove();

						$('#billing-address .checkout-heading').append('<a><?php echo JText::_('J2STORE_CHECKOUT_MODIFY'); ?></a>');
						$(window).scrollTop(200);
					},
					error: function(xhr, ajaxOptions, thrownError) {
						//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
				<?php } ?>
			}
		},
		error: function(xhr, ajaxOptions, thrownError) {
			//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		}
	});
});
})(j2store.jQuery);

// Guest Shipping
(function($) {
$(document).on('click', '#button-guest-shipping', function() {
	$.ajax({
		url: '<?php echo $ajax_base_url; ?>',
		type: 'post',
		cache: false,
		data: $('#shipping-address input[type=\'text\'], #shipping-address input[type=\'checkbox\']:checked, #shipping-address input[type=\'radio\']:checked, #shipping-address input[type=\'hidden\'], #shipping-address select, #shipping-address textarea'),
		dataType: 'json',
		beforeSend: function() {
			$('#button-guest-shipping').attr('disabled', true);
			$('#button-guest-shipping').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
		},
		complete: function() {
			$('#button-guest-shipping').attr('disabled', false);
			$('.wait').remove();
		},
		success: function(json) {
			$('.warning, .j2error').remove();

			if (json['redirect']) {
				location = json['redirect'];
			} else if (json['error']) {
				if (json['error']['warning']) {
					$('#shipping-address .checkout-content').prepend('<div class="warning alert alert-danger" style="display: none;">' + json['error']['warning'] + '<button data-dismiss="alert" class="close" type="button">×</button></div>');

					$('.warning').fadeIn('slow');
				}

				$.each( json['error'], function( key, value ) {
					if (value) {
						$('#shipping-address #'+key).after('<br class="j2error" /><span class="j2error">' + value + '</span>');
					}
				});
			} else {
				$.ajax({
					url: '<?php echo $ajax_base_url; ?>',
					type: 'post',
					cache: false,
					data: 'option=com_j2store&view=checkout&task=shipping_payment_method',
					dataType: 'html',
					success: function(html) {
						$('#shipping-payment-method .checkout-content').html(html);

						$('#shipping-address .checkout-content').slideUp('slow');

						$('#shipping-payment-method .checkout-content').slideDown('slow');

						$('#shipping-address .checkout-heading a').remove();
						$('#shipping-payment-method .checkout-heading a').remove();

						$('#shipping-address .checkout-heading').append('<a><?php echo JText::_('J2STORE_CHECKOUT_MODIFY'); ?></a>');
						$(window).scrollTop(200);
					},
					error: function(xhr, ajaxOptions, thrownError) {
						//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}
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
					data: 'option=com_j2store&view=checkout&task=confirm',
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


//--></script>
