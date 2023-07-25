jQuery(document).ready(function($) {
	 jQuery('.radio.btn-group label').addClass('btn')
			    jQuery(".btn-group label:not(.active)").click(function(){
			        var label = jQuery(this);
			        var input = jQuery('#' + label.attr('for'));

			        if (!input.prop('checked')){
		            	label.closest('.btn-group').find("label").removeClass('active btn-primary');
		            	label.addClass('active btn-primary');
			            input.prop('checked', true);
			        }
			    });
			    jQuery(".btn-group input[checked=checked]").each(function(){
					jQuery("label[for=" + jQuery(this).attr('id') + "]").addClass('active btn-primary');

			    });
	});
