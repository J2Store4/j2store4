<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
$platform = J2Store::platform();
$platform->loadExtra('jquery.framework');
$platform->loadExtra('bootstrap.framework');
//JHtml::_('jquery.framework');
//JHtml::_('bootstrap.framework');
$document = JFactory::getDocument();
//unset the timepicker script
$ajax_base_url = JRoute::_('index.php');
//now load them in order
$row_class = 'row';
$col_class = 'col-md-';
$active_class = 'class = "nav-link active"' ;
$tab_class ='class = "nav-link"';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
    $list_active_class = 'class = "j2store-tab active"' ;
    $list_tab_class = 'class = "j2store-tab"';
}
    ?>
	<?php if(J2Store::isPro() != 1): ?>
	<?php echo J2Html::pro(); ?>
<?php else: ?>
<div class="alert alert-warning alert-block">
	<strong><?php echo JText::_('J2STORE_ORDER_CREATION_BETA_NOTIFICATION')?></strong>
</div>
<div class="message-div <?php echo $col_class ?>12">
</div>
<div class="<?php echo $row_class ?>">
	<form class="form-horizontal form-validate" id="adminForm" name="adminForm" method="post" action="index.php">
		<?php echo J2Html::input('hidden','option','com_j2store',array('id'=>'option'));?>
		<input type="hidden" value="orders" id="view" name="view" />
		<input type="hidden" value="createOrder" id="task" name="task" />
		<input type="hidden" value="<?php echo $this->layout;?>" id="layout" name="layout" />
		<input type="hidden" value="" id="next_layout" name="next_layout" />
		<?php echo J2Html::input('hidden','oid', $this->order->j2store_order_id);?>
		<?php echo J2Html::input('hidden','id', $this->order->j2store_order_id);?>
		<?php echo J2Html::input('hidden','order_id', $this->order->order_id);?>
		<?php echo JHTML::_( 'form.token' ); ?>

	<div class="<?php echo $col_class ?>12">
	  <!-- required for floating -->
	  <!-- Nav tabs -->

	  <ul class="nav nav-tabs tabs-left" role="tablist">
		  <!-- 'tabs-right' for right tabs -->
                <?php $active = isset($list_active_class) && !empty($list_active_class) ? $list_active_class :''; ?>
		    	<?php if(empty($this->order->j2store_order_id)):?>
				    <li  <?php echo $active  ?> >
				    	<a <?php echo isset($active_class) && !empty($active_class) ? $active_class : '' ; ?>" role="tab"
                           href="javascript:void(0);" onclick="getTabcontent(this);" data-layout="billing">
				    		<?php echo JText::_('J2STORE_STORES_GROUP_BASIC');?>
				    	</a>
				    </li>
				   <?php else:?>
				    <?php foreach($this->fieldsets as $key => $text):?>
				    <?php $class=($this->layout  == $key) ? $active : (isset($list_tab_class) && !empty($list_tab_class) ? $list_tab_class :''); ?>
				    <li <?php echo $class ;?> >
				    	<a <?php echo ($this->layout  == $key) ? $active_class : $tab_class ; ?> href="index.php?option=com_j2store&view=orders&task=createOrder&layout=<?php echo $key?>&oid=<?php echo $this->order->j2store_order_id;?>" data-layout="<?php echo $key;?>" role="tab">
				    		<?php echo $text;?>
				    	</a>
				    </li>
					<?php endforeach;?>
				  <?php endif;?>

	    </ul>
	      <!-- Tab panes -->
	    <div class="tab-content">
				<?php if(empty($this->order->j2store_order_id)):?>
					<div class="active" id="basic">
						<?php  echo $this->loadTemplate('basic');?>
					</div>
				<?php else:?>
					<?php foreach($this->fieldsets as $key => $text):?>
					<?php if($this->layout == $key):?>
						 <?php $class=($this->layout  == $key) ? ' tab-pane active ' : 'tab-pane'; ?>
							<div class="<?php echo $class; ?>" id="<?php echo $key;?>">
								<?php  echo $this->loadTemplate($key);?>
							</div>
					<?php endif;?>
					<?php endforeach;?>
				<?php endif;?>
	    </div>
	    <?php
		    $keys = array_keys($this->fieldsets);
		    $prev_ordinal = (array_search($this->layout,$keys)-1)%count($keys);
	    	$next_ordinal = (array_search($this->layout,$keys)+1)%count($keys);?>
<div class="pull-right">
	    <?php if(isset($keys[$prev_ordinal])):?>
    		<?php  // echo $prev_layout = $this->fieldsets[$keys[$prev_ordinal]];?>
    		<a class="btn btn-primary" href="index.php?option=com_j2store&view=orders&task=createOrder&layout=<?php echo $keys[$prev_ordinal];?>&oid=<?php echo $this->order->j2store_order_id;?>" data-layout="<?php echo $key;?>">
				<?php echo JText::_('J2STORE_PREV');?>
			</a>
    	<?php endif;?>


	    <?php if(isset($keys[$next_ordinal])):
		     //$next_layout = $this->fieldsets[$keys[$next_ordinal]];
	    //print_r($this->orderinfo->j2store_orderinfo_id);print_r($keys[$next_ordinal]);
	    ?>
	    <?php if($keys[$next_ordinal] =='shipping' || $keys[$next_ordinal] == 'items'){ ?>
			<?php if((!isset($this->orderinfo->j2store_orderinfo_id) || empty($this->orderinfo->j2store_orderinfo_id) || empty($this->orderinfo->shipping_country_id) || empty($this->orderinfo->shipping_zone_id))&& $keys[$next_ordinal] =='items'):?>
			<a class="btn btn-success" style="display:none;" id="nextlayout" href="javascript:void(0);" onClick="nextlayout('<?php echo $keys[$next_ordinal];?>')" data-layout="<?php echo $keys[$next_ordinal];?>">
				<?php echo JText::_('J2STORE_SAVE_AND_NEXT');?>
			</a>
			<button class="btn btn-success " id="saveAndNext" >	<?php echo JText::_('J2STORE_SAVE_AND_NEXT');?>	</button>
			<?php elseif((!isset($this->orderinfo->j2store_orderinfo_id) || empty($this->orderinfo->j2store_orderinfo_id) || empty($this->orderinfo->billing_country_id) /*|| empty($this->orderinfo->billing_zone_id)*/)&& $keys[$next_ordinal] =='shipping'):?>
			<a class="btn btn-success" style="display:none;" id="nextlayout" href="javascript:void(0);" onClick="nextlayout('<?php echo $keys[$next_ordinal];?>')" data-layout="<?php echo $keys[$next_ordinal];?>">
				<?php echo JText::_('J2STORE_SAVE_AND_NEXT');?>
			</a>
			<button class="btn btn-success " id="saveAndNext" >	<?php echo JText::_('J2STORE_SAVE_AND_NEXT');?>	</button>
			<?php else:?>
			<a class="btn btn-success" id="nextlayout" href="javascript:void(0);" onClick="nextlayout('<?php echo $keys[$next_ordinal];?>')" data-layout="<?php echo $keys[$next_ordinal];?>">
				<?php echo JText::_('J2STORE_SAVE_AND_NEXT');?>
			</a>
			<button class="btn btn-success" style="display:none;" id="saveAndNext" >	<?php echo JText::_('J2STORE_SAVE_AND_NEXT');?>	</button>

			<?php endif;?>
			<?php } else if($keys[$next_ordinal] == 'basic'){?>
			<a class="btn btn-success" id="nextlayout" href="javascript:void(0);" onClick="nextlayout('summary')" data-layout="<?php echo 'summary';?>">
				<?php echo JText::_('J2STORE_SAVE_ORDER');?>
			</a>
			<button class="btn btn-success" style="display:none;" id="saveAndNext" >	<?php echo JText::_('J2STORE_SAVE_AND_NEXT');?>	</button>
			<?php }else{?>
			<a class="btn btn-success" id="nextlayout" href="javascript:void(0);" onClick="nextlayout('<?php echo $keys[$next_ordinal];?>')" data-layout="<?php echo $keys[$next_ordinal];?>">
				<?php echo JText::_('J2STORE_SAVE_AND_NEXT');?>
			</a>
			<button class="btn btn-success" style="display:none;" id="saveAndNext" >	<?php echo JText::_('J2STORE_SAVE_AND_NEXT');?>	</button>
			<?php }?>
	    <?php endif ;?>
	    </div>
	</div>


</form>
</div>

<script type="text/javascript">
(function($){
	$('#saveAndNext').on('click', function(e){
		e.preventDefault();
		var address_id = $('#address_id').val();
		if(address_id){
			nextlayout('<?php echo $keys[$next_ordinal];?>');
		}else{
			$('.j2error').remove();
			$('#display_message').html('<span class="j2error"><?php echo JText::_('J2STORE_ADDRESS_SELECTION_ERROR');?></span>');
		}
	});

})(j2store.jQuery);

function nextlayout(layout){
	(function($){

			var new_data = $('#new-address :input').serializeArray();
			var data1 = {
					option: 'com_j2store',
					view: 'orders',
					task: 'validate_address',
					order_id: '<?php echo $this->order->order_id;?>'
			};

			var chk_new = 0;
			$.each( new_data, function( key, value ) {
				if(value.name=="address" && value.value == 'new'){
					chk_new = 1;
				}
				data1[value.name] = value.value;
			});

			if(chk_new ){
				$.ajax({
					url: '<?php echo $ajax_base_url; ?>',
					type: 'post',
					cache: false,
					data:data1,
					dataType: 'json',
					success: function(json) {
						if(json['success']){
							$('#task').attr('value','saveAdminOrder');
							$('#next_layout').attr('value',layout );
							$('#adminForm').submit();
						}else if (json['error']) {
							$('.warning, .j2error').remove();
							$.each( json['error'], function( key, value ) {
								if (value) {
									$('#'+key).after('<br class="j2error" /><span class="j2error">' + value + '</span>');
								}
							});
						}

					},
					error: function(xhr, ajaxOptions, thrownError) {
						//alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
					}
				});
			}else{
				var current_layout = $('#layout').val();
				if(current_layout=='summary'){
					var c=confirm("<?php echo JText::_('J2STORE_ORDER_EDIT_SUMMARY_SAVE_CONFIRM');?>");
					if (c){
						$('#task').attr('value','saveAdminOrder');
						$('#next_layout').attr('value',layout );
						$('#adminForm').submit();
					}
				}else{
					$('#task').attr('value','saveAdminOrder');
					$('#next_layout').attr('value',layout );
					$('#adminForm').submit();
				}
				//
			}
	})(j2store.jQuery);

}

 function getTabcontent(element){
	(function($){
			var layout  = $(element).data('layout');
			$('#layout').attr('value',layout );
			$('#adminForm').submit();
	})(j2store.jQuery);
}
var orderinfo = jQuery('#orderinfo_id').attr('value');
 function setOrderinfo1(address_type,address_id){
		(function($){
			var oid = <?php echo !empty($this->order->j2store_order_id) ? $this->order->j2store_order_id : 0;?>;
			var j2Ajax = $.ajax({
				url:'index.php',
				type: 'post',
				data: {'option':'com_j2store',
						'view':'orders',
						'task':'orderSetAddress',
						'oid':oid ,
						'address_type': address_type,
						'address_id':address_id,
						'address_type':address_type,
						'j2store_orderinfo_id' : orderinfo
					},
				dataType: 'json'
	 	 });
		 j2Ajax.done(function(json) {
			 if(json!='' ){
				 if(json['html'] !='' ){
					if(address_type == 'billing'){
				 		$('#baddress-info').html(json['html']);
						$('#orderinfo_id').attr('value',json['orderinfo_id']);
					}else{
				 		$('#saddress-info').html(json['html']);
				 		$('#orderinfo_id').attr('value',json['orderinfo_id']);
					}
				 }
			 }
		 });
		})(j2store.jQuery);
	}

</script>
<?php endif;?>