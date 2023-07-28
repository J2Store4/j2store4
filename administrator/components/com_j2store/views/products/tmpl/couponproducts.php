<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
$platform = J2Store::platform();
$platform->loadExtra('bootstrap.tooltip');
$platform->loadExtra('behavior.framework');

$app = $platform->application();
$db = JFactory::getDbo();
$function  = $app->input->getString('function', 'jSelectProduct');
$field = $app->input->getString('field');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));
?>


<form action="<?php echo JRoute::_('index.php?option=com_j2store&view=products&task=setCouponProducts');?>" method="post" name="adminForm" id="productadminForm" class="form-inline">
	<h5><?php echo JText::_('COM_J2STORE_PRODUCTS');?></h5>
	<div class="row-fluid">
		<table class="adminlist table table-striped ">
			<tr>
				<td>
					<div class="input-prepend">
						<span class="add-on"><?php echo JText::_( 'J2STORE_PRODUCT_SKU' ); ?></span>
						<?php echo J2Html::text('search',htmlspecialchars($this->state->search),array('id'=>'search') );?>
						<?php echo J2Html::button('go',JText::_( 'J2STORE_FILTER_GO' ) ,array('class'=>'btn btn-success','onclick'=>'this.form.submit();'));?>
						<?php echo J2Html::button('reset',JText::_( 'J2STORE_FILTER_RESET' ),array('id'=>'reset-filter-search','class'=>'btn btn-inverse',"onclick"=>"jQuery('#search').val('');this.form.submit();"));?>
					</div>
				</td>
				<td>
					<?php echo $this->pagination->getLimitBox(); ?>
				</td>
			<tr>
		</table>
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th>
						<input class="btn btn-success" id="setAllProductsBnt" type="button" value="<?php echo JText::_('J2STORE_SET_VALUES');?>"  style="display:none;"/>
						<br/>
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th class="title">
						<?php echo JText::_('J2STORE_PRODUCT_NAME'); ?>
					</th>
					<th class="center nowrap">
						<?php echo JText::_('J2STORE_PRODUCT_SKU'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="3">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php foreach($this->productitems as $key=>$item):?>
				<?php $canChange  = 1;?>
				<tr>
					<td>
						<input data-product-title="<?php echo $item->product_name;?>" id="cb<?php echo $item->j2store_product_id;?>" type="checkbox" onclick="Joomla.isChecked(this.checked);" value="<?php echo $item->j2store_product_id;?>" name="cid[]">
						<?php echo J2html::hidden('tmp_product_title['.$item->j2store_product_id.']', $item->product_name ,array('class'=>'tmp_product_title')); ?>
					</td>
					<td>
						<a href="javascript:if (window.parent) window.parent.<?php echo $db->escape($function);?>('<?php echo $item->j2store_product_id; ?>','<?php echo $item->product_name;?>' ,'<?php echo $field;?>');">
							<?php echo $item->product_name; ?>
						</a>
					</td>
					<td>
						<?php if($item->product_type == 'variable'):?>
						<h5 class="text text-warning"><?php echo JText::_('J2STORE_HAS_VARIANTS'); ?></h5>
								<?php
										$variant_model = F0FModel::getTmpInstance('Variants', 'J2StoreModel');
										$variant_model->setState('product_type', $item->product_type);
										$variants = $variant_model->product_id($item->j2store_product_id)
													->is_master(0)
													->getList();
										if(isset($variants) && count($variants)):?>

										<?php foreach($variants as $variant):?>
											<?php if(!empty($variant->sku)):?>
												<?php echo $variant->sku; ?>
												<br/>
											<?php endif;?>

										<?php endforeach;?>
										<?php endif;?>
						<?php else:?>
							<?php echo $item->sku; ?>
						<?php endif;?>
					</td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>
		<?php echo J2Html::hidden('option','com_j2store');?>
		<?php echo J2Html::hidden('view','products');?>
		<?php echo J2Html::hidden('tmpl','component');?>
		<?php echo J2Html::hidden('task','setCouponProducts',array('id'=>'task'));?>
		<?php echo J2Html::hidden('layout','couponproducts');?>
		<?php echo J2Html::hidden('boxchecked',0);?>
		<?php echo J2Html::hidden('filter_order',$listOrder);?>
		<?php echo J2Html::hidden('filter_order_Dir',$listDirn);?>
		<?php echo J2Html::hidden('field',$field);?>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<script>
var newArray =new Array();
var checkedValues;
var product_titles;

Joomla.submitform =function(){
	var pressbutton =  jQuery("#task").val();
	// deprecated in joomla 3.4.x
	 //submitform(pressbutton);
	 jQuery('#productadminForm').submit();
}
/**
 * Override Joomlaischecked
 */
Joomla.isChecked=function(a,d){
	if(typeof(d)==="undefined"){
		d=document.getElementById("productadminForm")
	}
	if(a==true){
		d.boxchecked.value++

		(function($){
			//now show the
			$("#setAllProductsBnt").show();

		 		checkedValues =  $('input:checkbox:checked').map(function() {
			    	return this.value ;
				}).get();

					product_titles =$('input:checkbox:checked').map(function(){
				return $(this).data('product-title');
			}).get();

		})(j2store.jQuery);
		}else{
			d.boxchecked.value--

			(function($){
				$("#setAllProductsBnt").hide();
				})(j2store.jQuery);
	}

	var g=true,b,f;
	for(b=0,n=d.elements.length;b<n;b++){
		f=d.elements[b];
	if(f.type=="checkbox"){
		if(f.name!="checkall-toggle"&&f.checked==false){
				g=false;
				break
		}
		}
	}if(d.elements["checkall-toggle"]){
		d.elements["checkall-toggle"].checked= g
	}
};




(function($){
	$("input[name=checkall-toggle]").click(function(){
		$("#setAllProductsBnt").toggle(this.checked);
		checkedValues = $('input:checkbox:checked').map(function() {
	    	return this.value ;
		}).get();
		product_titles = $('.tmp_product_title').map(function(){
			return this.value;
		}).get();

	});
	//$("input[name=checkall-toggle]").trigger('change');
})(j2store.jQuery);

(function($){
	$("#setAllProductsBnt").click(function(){
		var form = $("#adminForm");
		var html ='';
		newArray = mergeArray(checkedValues , product_titles)
		$(newArray).each(function(index,value){
			if($('#jform_product_list' ,window.parent.document).find('#product-row-'+value.id ).length == 0){
				html ='<tr id="product-row-'+ value.id +'"><td><input type="hidden" name="products['+value.id +']" value='+value.id+' />'+value.product_title +'</td><td><button class="btn btn-danger" onclick="jQuery(this).closest(\'tr\').remove();"><i class="icon icon-trash"></button></td></tr>';
				$('#jform_product_list', window.parent.document).append(html);
				window.close();
			}
		});
		checkedValues.length='';

	});

 	function mergeArray(checkedValues , product_titles){

 		checkedValues = cleanArray(checkedValues);
	     for(var i = 0; i < checkedValues.length; i++){
		     newArray.push({'id':checkedValues[i],'product_title': product_titles[i]});
	        }
    	 return newArray;
	 };
	function cleanArray(actual){
	  var tmpArray = new Array();
	  for(var i = 0; i<actual.length; i++){
	      if (actual[i]){
	        tmpArray.push(actual[i]);
	    }
	  }
	  return tmpArray;
	}
})(j2store.jQuery);
</script>

