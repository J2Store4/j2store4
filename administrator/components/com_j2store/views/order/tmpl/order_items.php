<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$platform->loadExtra('behavior.formvalidator');
//JHtml::_('bootstrap.tooltip');
$platform->loadExtra('bootstrap.modal');
$platform->loadExtra('formbehavior.chosen', 'select');
//JHtml::_('formbehavior.chosen', 'select');
$key = 0;
$route = JURI::root(true)."/index.php";
$document =JFactory::getDocument();

$document->addScript(JUri::root(true).'/media/j2store/js/jquery-ui-timepicker-addon.js');
//JHTML::_('behavior.modal');
$add_product_link = $route."?option=com_j2store&view=products&task=displayAdminProduct&tmpl=component&user_id=".$this->order->user_id."&oid=".$this->order->j2store_order_id."&product_id=";
$item_url = "index.php?option=com_j2store&view=orders&task=saveAdminOrder&layout=items&next_layout=items&oid=".$this->order->j2store_order_id;
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>

<style>
.cart-thumb-image img {
	width: 100px;
}
</style>
<div class="orderitems">
	<div class="<?php echo $row_class ?>">
		<div class="<?php echo $col_class ?>12">

			<h4>
				<?php echo JText::_('J2STORE_ORDER_SUMMARY');?>
			</h4>
			<table class="j2store-cart-table table table-bordered">
				<thead>
					<tr>
						<th width="20"><input type="checkbox" name="checkall-toggle"
					value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
					onclick="Joomla.checkAll(this)" />
						<th><?php echo JText::_('J2STORE_CART_LINE_ITEM'); ?></th>
						<th><?php echo JText::_('J2STORE_CART_LINE_ITEM_QUANTITY'); ?></th>
						<?php if(isset($this->taxes) && count($this->taxes) && $this->params->get('show_item_tax', 0)): ?>
						<th><?php echo JText::_('J2STORE_CART_LINE_ITEM_TAX'); ?>
						<?php endif;?>
						<th><?php echo JText::_('J2STORE_CART_INVENTORY'); ?></th>
						<th><?php echo JText::_('J2STORE_CART_LINE_ITEM_TOTAL'); ?></th>
					</tr>
				</thead>
				<?php echo J2Html::input('hidden','user_id', $this->order->user_id,array('id'=>'user_id'));?>
				<?php echo J2Html::input('hidden', 'boxchecked',0);?>				
				<tbody id="j2store-oitem-body">
					<?php if(!empty($this->orderitems)):?>
					<?php $i = 0; ?>
					<?php foreach ($this->orderitems as $item): ?>
					<?php
					$item->params = $platform->getRegistry($item->orderitem_params);
					$thumb_image = $item->params->get('thumb_image', '');
					$checked = JHTML::_('grid.id', $i, $item->j2store_orderitem_id );
					?>
					<tr>
						<td><?php echo $checked; ?></td>
						<td>
							<?php if($this->params->get('show_thumb_cart', 1) && !empty($thumb_image)): ?>
								<span class="cart-thumb-image">
									<img alt="<?php echo $item->orderitem_name; ?>" src="<?php echo JUri::root().$thumb_image; ?>" />
								</span>
							<?php endif; ?>
							<span class="cart-product-name">
								<?php echo $item->orderitem_name; ?>
										<?php if(!$this->params->get('show_qty_field', 1)) : ?> <a
										class="j2store-remove remove-icon"
										href="<?php echo J2Store::platform()->getCartUrl(array('task' => 'remove','cartitem_id' => $item->cartitem_id));//JRoute::_('index.php?option=com_j2store&view=carts&task=remove&cartitem_id='.$item->cartitem_id); ?>">X</a>
										<?php endif; ?>
							</span>
							<br />

							<?php if(isset($item->orderitemattributes) && $item->orderitemattributes): ?>
							<span class="cart-item-options"> <?php foreach ($item->orderitemattributes as $attribute): ?>
								<small> - <?php echo JText::_($attribute->orderitemattribute_name); ?>
									: <?php echo $attribute->orderitemattribute_value; ?>
							</small> <br /> <?php endforeach;?>
							</span>
							<?php endif; ?>

							<?php if($this->params->get('show_price_field', 1)): ?>
								<span class="cart-product-unit-price">
									<span class="cart-item-title"><?php echo JText::_('J2STORE_CART_LINE_ITEM_UNIT_PRICE'); ?>
								</span>
							<span class="cart-item-value">
								<?php echo $this->currency->format($this->order->get_formatted_lineitem_price($item, $this->params->get('checkout_price_display_options', 1))); ?>
							</span>
						</span> <?php endif; ?> <?php if($this->params->get('show_sku', 1)): ?>
							<br /> <span class="cart-product-sku"> <span
								class="cart-item-title"><?php echo JText::_('J2STORE_CART_LINE_ITEM_SKU'); ?>
							</span> <span class="cart-item-value"><?php echo $item->orderitem_sku; ?>
							</span>

						</span> <?php endif; ?>
                            <?php echo J2Store::plugin()->eventWithHtml('AfterDisplayLineItemTitle', array($item, $this->order, $this->params));?>
                            <?php if(isset($this->onDisplayCartItem[$i])):?>
							<br /> <?php echo $this->onDisplayCartItem[$i];?> <?php endif;?>
							<?php $i++;?>
						</td>
						<td><?php echo J2Html::hidden($this->form_prefix.'[orderitem]['.$item->j2store_orderitem_id.'][j2store_orderitem_id]', $item->j2store_orderitem_id);?>
							<?php echo J2Html::hidden($this->form_prefix.'[orderitem]['.$item->j2store_orderitem_id.'][cartitem_id]', $item->cartitem_id);?>
							<?php echo J2Html::hidden($this->form_prefix.'[orderitem]['.$item->j2store_orderitem_id.'][cart_id]', $item->cart_id);?>
							<input class="input-mini" min="0"
							name="<?php echo $this->form_prefix.'[orderitem]['.$item->j2store_orderitem_id.'][orderitem_quantity]';?>"
							type="number" value="<?php echo $item->orderitem_quantity; ?>" />
							<!--  
							<a class="btn btn-small btn-danger btn-xs j2store-remove remove-icon"
								href="<?php echo JRoute::_('index.php?option=com_j2store&view=orders&task=removeOrderitem&layout=items&oid='.$this->order->j2store_order_id.'&orderitem_id='.$item->j2store_orderitem_id); ?>">
								<i class="fa fa-trash"></i>
							</a>
							-->
						</td>
						

						<?php if(isset($this->taxes) && count($this->taxes) && $this->params->get('show_item_tax', 0)): ?>
						<td>

							<?php 	echo $this->currency->format($item->orderitem_tax); 	?>
						</td>
						<?php endif; ?>
						<td>
							<a href="#" onclick="addInventry('<?php echo $item->variant_id;?>','<?php echo $item->orderitem_quantity;?>','<?php echo $this->order->order_id;?>')" class="btn btn-primary"><?php echo JText::_('J2STORE_INCREASE_STOCK');?></a>
							<a href="#" onclick="removeInventry('<?php echo $item->variant_id;?>','<?php echo $item->orderitem_quantity;?>','<?php echo $this->order->order_id;?>')" class="btn btn-danger"><?php echo JText::_('J2STORE_DECREASE_STOCK');?></a>
						</td>
						<td class="cart-line-subtotal">
							<?php echo $this->currency->format($this->order->get_formatted_lineitem_total($item, $this->params->get('checkout_price_display_options', 1)), $this->order->currency_code, $this->order->currency_value ); ?>
						</td>
					</tr>
					<?php endforeach;?>
					<?php endif;?>
				</tbody>				
				<tfoot>
					<tr>
					<?php if(!empty($this->orderitems)):?>
						<?php $colspan=5;
						if(isset($this->taxes) && count($this->taxes) && $this->params->get('show_item_tax', 0)){
							$colspan=6;
						}
						?>						
						<td colspan="<?php echo $colspan;?>"><a class="btn btn-large btn-warning" onclick="update()" id="update_quantity"><?php echo JText::_('J2STORE_CART_UPDATE');?></a>
						<a class="btn btn-large btn-danger" onclick="remove_all()" id="remove_quantity"><?php echo JText::_('J2STORE_REMOVE');?></a>
						</td>
					<?php endif;?>
					</tr>
				</tfoot>
			</table>
		</div>
		
		<div class="<?php echo $col_class ?>8">
		
			<div class="j2store-order-items">
				<table class="table table-bordered">
					<tr>
						<td colspan="2">
							<h4>
								<?php echo JText::_('J2STORE_ADD_ITEM');?>
							</h4>
						</td>
					</tr>
					<tr id="selector-row">
						<td><?php echo JText::_('J2STORE_CHOOSE_PRODUCTS');?></td>
						 <td>

						 <?php echo J2Html::text('product_name' ,'',array('id'=>'productselector'));?>
							<?php echo J2Html::hidden('product_id' ,'',array()) ;?>

						</td>
					</tr>
				</table>
				
			</div>
			<div id="j2store-product-display" style="display:none;">
				<span id="j2store-product-name"></span>
				<?php //"window.parent.location='index.php?option=com_j2store&view=orders&task=createOrder&layout=items&oid={$this->order->j2store_order_id}"?>
				<?php echo J2StorePopup::popupAdvanced($add_product_link,JText::_( "J2STORE_ORDER_ADD_ITEM" ), array('width'=>800 ,'height'=>400 ,'class'=>'btn btn-success','refresh'=>true,'id'=>'fancybox'));?>
            </div>
		</div

		<div class="<?php echo $col_class ?>4">
				<?php // echo $this->loadTemplate('totals'); ?>				
			</div>
	</div>
</div>
<div class="<?php echo $col_class ?>12"></div>
<script type="text/javascript">

var key =<?php echo $key;?>;
(function($) {
		$(document).ready(function() {

			$('#productselector').autocomplete({
				source : function(request, response) {
					$.ajax({
						type : 'post',
						url :  'index.php?option=com_j2store&view=orders&task=getproducts',
						data : 'q=' + request.term,
						dataType : 'json',
						success : function(data) {
							$('#productselector').removeClass('optionsLoading');
							response($.map(data, function(item) {
								return {
									label: item.sku,
									value: item.j2store_product_id,
									sku: item.sku,
									option: item.options,
									price: item.price,
									product_name : item.product_name,
								}
							}));
						}
					});
				},
				minLength : 2,
				select : function(event, ui) {
					$('input[name=\'product_name\']').attr('value', ui.item.label);
					$('#j2store-product-name').html(ui.item.label); 
					$('input[name=\'product_id\']').attr('value', ui.item.value);
					$('#j2store-product-display a').attr('href','<?php echo $add_product_link?>'+ui.item.value);
					$('#j2store-product-display').show();
					return false;
				},
				search : function(event, ui) {
					$('#productselector').addClass('optionsLoading');
					key++;
				}
			});

		});
		})(j2store.jQuery);

function getProductDetails(){	
	(function($){
		/* $('#task').attr('value','displayAdminProduct');
		$('#view').attr('value','products'); */
		var post_data = $('#adminForm').serializeArray();
		var data1 = {
				option: 'com_j2store',
				view: 'products',
				task: 'displayAdminProduct',				
			};
		$.each( post_data, function( key, value ) {
			
			 if (!(value['name'] in data1) ){
				 data1[value['name']] = value['value'];	
			}
			
		});
		console.log(data1);
		$.ajax({
			type : 'post',
			url :  '<?php echo $route;?>',
			data : data1,					
			success : function(data) {				
				var html ='';
				 html += data;
				$('#j2store-product-display').html(data);
				//$(html).insertAfter('#j2store-cart-table');				
			},
		 error: function(xhr, ajaxOptions, thrownError) {
             //alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
         }
		});
	})(j2store.jQuery);
}

function update(){
	(function($){
		var post_data = $('#adminForm').serializeArray();
		var data1 = {
				option: 'com_j2store',
				view: 'carts',
				task: 'update',				
			};
		$.each( post_data, function( key, value ) {			
			 if (!(value['name'] in data1) ){
				 data1[value['name']] = value['value'];	
			}
		});
		
		$.ajax({
			type : 'post',
			url :  'index.php',
			data : data1,	
			dataType: "json",		
			beforeSend: function() {
				$('#update_quantity').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
				$('#update_quantity').attr('disabled',true);
				
			},					
			success : function(json) {				
				$('#update_quantity').attr('disabled',false);
				$('.wait').remove();
				$('.j2error').remove();
				if(json['success']){									
					
					window.location='<?php echo $item_url;?>';
				}else if(json['error']){				
					$('.message-div').html('<span class="alert alert-error span12">'+json['error']+'</span>');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
		          // alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		    }
		});		
	})(j2store.jQuery);
}

function remove_all(){
	(function($){
		/* we have to do remove order item function  */
		var post_data = $('input[name="cid[]"]:checked').serializeArray();
		var oid = $('input[name="oid"]').val()
		console.log(oid);
		$.ajax({
			type : 'post',
			url :  'index.php?option=com_j2store&view=orders&task=removeOrderitem&oid='+oid,
			data : post_data,	
			dataType: "json",		
			beforeSend: function() {
				$('#remove_quantity').after('<span class="wait">&nbsp;<img src="<?php echo JUri::root(true); ?>/media/j2store/images/loader.gif" alt="" /></span>');
				$('#remove_quantity').attr('disabled',true);
				
			},					
			success : function(json) {				
				$('#remove_quantity').attr('disabled',false);
				$('.wait').remove();
				$('.j2error').remove();
				if(json['success']){									
					
					window.location='<?php echo $item_url;?>';
				}else if(json['error']){				
					$('.message-div').html('<span class="alert alert-error span12">'+json['error']+'</span>');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
		          // alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
		    }
		});		
		console.log(post_data);
	})(j2store.jQuery);
}

function addInventry(variant_id,qty,order_id){
	(function($) {
		$.ajax({
			type : 'post',
			url :  'index.php?option=com_j2store&view=orders&task=addInventry&variant_id='+variant_id+'&qty='+qty+'&order_id='+order_id,
			dataType: "json",
			beforeSend: function() {
				$('.message-div').html('');
			},
			success : function(json) {
				$('.wait').remove();
				$('.j2error').remove();
				if(json['success']){
					$('.message-div').html('<span class="alert alert-note span12">'+json['success']+'</span>');
				}else if(json['error']){
					$('.message-div').html('<span class="alert alert-error span12">'+json['error']+'</span>');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				// alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	})(j2store.jQuery);
}

function removeInventry(variant_id,qty,order_id){
	(function($) {
		$.ajax({
			type : 'post',
			url :  'index.php?option=com_j2store&view=orders&task=removeInventry&variant_id='+variant_id+'&qty='+qty+'&order_id='+order_id,
			dataType: "json",
			beforeSend: function() {
				$('.message-div').html('');
			},
			success : function(json) {
				$('.wait').remove();
				$('.j2error').remove();
				if(json['success']){
					$('.message-div').html('<span class="alert alert-note span12">'+json['success']+'</span>');
				}else if(json['error']){
					$('.message-div').html('<span class="alert alert-error span12">'+json['error']+'</span>');
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				// alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	})(j2store.jQuery);
}

</script>
