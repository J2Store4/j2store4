<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
?>
<?php if($this->params->get('enable_coupon', 0)):?>
   <div class="coupon">
	  <!--   <form action="index.php" method="post" enctype="multipart/form-data">	  -->
	    <?php
		$coupon = F0FModel::getTmpInstance ( 'Coupons', 'J2StoreModel' )->get_coupon();
	    ?>
		<input type="text" name="coupon" value="<?php echo $coupon; ?>" />
		<input type="button" onClick="applyCoupon()" value="<?php echo JText::_('J2STORE_APPLY_COUPON')?>" class="button btn btn-primary" />
  
	    </div> 
    <?php endif; ?>
     <script type="text/javascript">
    function applyCoupon(){	
    	(function($){
    		/* $('#task').attr('value','displayAdminProduct');
    		$('#view').attr('value','products'); */
    		var post_data = $('#adminForm').serializeArray();
    		var data1 = {
    				option: 'com_j2store',
    				view: 'carts',
    				task: 'applyCoupon',				
    			};
    		$.each( post_data, function( key, value ) {
    			
    			 if (!(value['name'] in data1) ){
    				 data1[value['name']] = value['value'];	
    			}
    			
    		});
    		console.log(data1);
    		$.ajax({
    			type : 'post',
    			url :  'index.php',
    			data : data1,		
    			dataType: 'json',
    			success : function(json) {	
    				
    				if(json['error']){
    					//$('.j2store-remove').after('<span>'+json['error']+'</span>');			
    				}
    				if(json['success']){
    					 window.location = json['redirect']; 
    				}
    						
    			},
    		 error: function(xhr, ajaxOptions, thrownError) {
                 //alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
             }
    		});
    	})(j2store.jQuery);
    }
</script>