<?php
/*------------------------------------------------------------------------
# com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/


defined('_JEXEC') or die('Restricted access');
$ajax_url = JRoute::_('index.php');
$ajax_loader = JUri::root (true).'/media/j2store/images/loader.gif';
?>

<form action="<?php echo JRoute::_( "index.php?option=com_j2store&view=checkout" ); ?>" method="post" name="money_form" id="money_form" enctype="multipart/form-data">

    <div class="note note-<?php echo $vars->orderpayment_type; ?>">

         <?php 
         	$image = $this->params->get('display_image', '');         	 
         ?>
         <?php if(!empty($image)): ?>
         	<span class="j2store-payment-image">
				<img class="payment-plugin-image payment_cash" src="<?php echo JUri::root().JPath::clean($image); ?>" />
			</span>
		<?php endif; ?>
        <p class="j2store-payment-display-name">
             <strong><?php echo JText::_($vars->display_name);?></strong>
        </p>
        <p class="j2store-on-before-payment-text"><?php echo JText::_($vars->onbeforepayment_text); ?></p>
        <p class="j2store-money-order-information"><?php echo JText::_($vars->moneyorder_information); ?></p>
    </div>
	<input type="button" onclick="doSendRequest()" id="money-submit-button" class="j2store_cart_button button btn btn-primary" value="<?php echo JText::_($vars->button_text); ?>" />
    <input type='hidden' name='order_id' value='<?php echo $vars->order_id; ?>'>
    <input type='hidden' name='orderpayment_type' value='<?php echo $vars->orderpayment_type; ?>'>

	<input type='hidden' name='option' value='com_j2store' />
	<input type='hidden' name='view' value='checkout' />
	<input type='hidden' name='task' value='confirmPayment'>
	<input type='hidden' name='paction' value='process'>
	<input type='hidden' name='hash' value='<?php echo $vars->hash;?>'>
	<div class="plugin_error_div">
		<span class="plugin_error"></span>
		<span class="plugin_error_instruction"></span>
	</div>
	<?php echo JHtml::_( 'form.token' ); ?>
</form>
<script>
	function doSendRequest() {
		(function($) {
			var button = j2store.jQuery('#money-submit-button');
			//get all form values
			var form = $('#money_form');
			var values = form.serializeArray();
			//submit the form using ajax
			var jqXHR =	$.ajax({
				url: '<?php echo $ajax_url; ?>',
				type: 'post',
				data: values,
				dataType: 'json',
				beforeSend: function() {
					$(button).attr('disabled', 'disabled');
					$(button).val('<?php echo addslashes(JText::_('J2STORE_PAYMENT_PROCESSING_PLEASE_WAIT')); ?>');
					$(button).after('<span class="wait">&nbsp;<img src="<?php echo $ajax_loader;?>" alt="" /></span>');
				}
			});

			jqXHR.done(function(json) {
				form.find('.j2success, .j2warning, .j2attention, .j2information, .j2error').remove();
				//console.log(json);
				if (json['error']) {
					form.find('.plugin_error').after('<span class="j2error">' + json['error']+ '</span>');
					form.find('.plugin_error_instruction').after('<br /><span class="j2error"><?php echo JText::_('J2STORE_STRIPE_ON_ERROR_INSTRUCTIONS'); ?></span>');
					$(button).val('<?php echo addslashes(JText::_('J2STORE_PAYMENT_ERROR_PROCESSING'))?>');
				}

				if (json['redirect']) {
					$(button).val('<?php echo addslashes(JText::_('J2STORE_PAYMENT_COMPLETED_PROCESSING'))?>');
					window.location.href = json['redirect'];
				}

			});

			jqXHR.fail(function() {
				$(button).val('<?php echo addslashes(JText::_('J2STORE_PAYMENT_ERROR_PROCESSING'))?>');
			});

			jqXHR.always(function() {
				$('.wait').remove();
			});

		})(j2store.jQuery);
	}
</script>