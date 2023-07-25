<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
?>
<?php if($this->params->get('enable_voucher', 0)):?>
<div class="voucher">
	    <!-- <form action="index.php" method="post" enctype="multipart/form-data"> -->
	    <?php
		$voucher = F0FModel::getTmpInstance ( 'Vouchers', 'J2StoreModel' )->get_voucher();
	    ?>
		<!--  <input type="hidden" name="oid" value="<?php echo $this->order->j2store_order_id;?>" />-->
		<input type="text" name="voucher" value="<?php echo $voucher; ?>" />
		<input type="button" onClick="applyVoucher()"value="<?php echo JText::_('J2STORE_APPLY_VOUCHER')?>" class="button btn btn-primary" />
		<!-- <input type="hidden" name="option" value="com_j2store" />
         <input type="hidden" name="view" value="carts" />
         <input type="hidden" name="task" value="applyVoucher" />	    
	     </form> -->
	  </div>   
    <?php endif; ?>
    <script type="text/javascript">
    function applyVoucher(){	
    	(function($){
    		/* $('#task').attr('value','displayAdminProduct');
    		$('#view').attr('value','products'); */
    		var post_data = $('#adminForm').serializeArray();
    		var data1 = {
    				option: 'com_j2store',
    				view: 'carts',
    				task: 'applyVoucher',				
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