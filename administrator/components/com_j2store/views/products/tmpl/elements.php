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

<form action="<?php echo JRoute::_('index.php?option=com_j2store&view=products&task=setProducts');?>" method="post" name="adminForm" id="productadminForm" class="form-inline">
	<h5><?php echo JText::_('COM_J2STORE_PRODUCTS');?></h5>
	<div class="row-fluid">
		<table class="adminlist table table-striped">
			<tr>
				<td>
					<?php echo J2Html::label(JText::_('JCATEGORY'));?>
					<?php
						$catlist = array();
						$catlist[''] = JText::_('J2STORE_SELECT_OPTION');
						foreach($this->categories as $key => $value){
							$catlist[$value->value] = $value->text;
						}
						echo J2Html::select()->clearState()->type('genericlist')->idTag('catid')
											 ->attribs(array('onchange'=>'this.form.submit();'))
											 ->setPlaceholders($catlist)
											 ->name('filter_category')
											 ->value($this->state->get('filter_category',''))
											 ->getHtml();
					?>
				</td>
				<td>
					<?php echo $this->pagination->getLimitBox(); ?>
				</td>
			<tr>
		</table>
		<table class="table table-striped">
			<thead>
				<tr>
					<th>
						<input class="btn btn-success" id="setAllProductsBnt" type="button" value="<?php echo JText::_('J2STORE_SET');?>"  style="display:none;"/>
						<br/>
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th class="title">
						<?php echo JHtml::_('grid.sort', 'J2STORE_PRODUCT_NAME', 'a.j2store_product_id', $listDirn, $listOrder); ?>
					</th>
					<th width="1%" class="center nowrap">
						<?php echo JHtml::_('grid.sort', 'J2STORE_ENABLE', 'a.enabled', $listDirn, $listOrder); ?>
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
						<?php echo JHtml::_('grid.id',$item->j2store_product_id,$item->j2store_product_id);?>
						<?php echo J2html::hidden('tmp_product_title['.$item->j2store_product_id.']', $item->product_name ,array('class'=>'tmp_product_title')); ?>
					</td>
					<td>
						<a href="javascript:void(0)"
							onclick="window.parent.jSelectItem('<?php echo $item->j2store_product_id; ?>', '<?php echo str_replace(array("'", "\""), array("\\'", ""),$item->product_name); ?>', '<?php echo $app->input->getCmd('object','id'); ?>');"
							>
							<?php echo $item->product_name; ?>
						</a>
					</td>
					<td>
						<?php echo JHtml::_('jgrid.published', $item->enabled, $key, 'products.', $canChange, 'cb', 1,0); ?>
					</td>
				</tr>
				<?php endforeach;?>
			</tbody>
		</table>

		<input type="hidden" name="option" value="com_j2store" />
		<input type="hidden" name="view" value="products" />
		<input type="hidden" id="task" name="task" value="setProducts" />
		<input type="hidden" name="layout" value="elements" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<script>
var newArray =new Array();
var checkedValues;
var product_titles;
(function($){
	$("input[name=checkall-toggle]").click(function(){
		$("#setAllProductsBnt").toggle(this.checked);
		checkedValues = $('input:checkbox:checked').map(function() {
	    	return this.value ;
		}).get();
	});
	$("input[name=checkall-toggle]").trigger('change');
})(j2store.jQuery);

(function($){
	$("#setAllProductsBnt").click(function(){
		var form = $("#adminForm");
		var html ='';
		product_titles = $('.tmp_product_title').map(function(){
			return this.value;
		}).get();
		newArray = mergeArray(checkedValues , product_titles)

		$(newArray).each(function(index,value){
			if(form.find('#jform_product_list  option[value='+value.id+']').length == 0){
				html +='<option selected="selected" value='+value.id+'>'+value.product_title+'</option>';
				$('#jform_product_list', window.parent.document).html(html);
				window.close();
			}
		});

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

