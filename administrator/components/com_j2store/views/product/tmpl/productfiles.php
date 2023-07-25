<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
$platform = J2Store::platform();
$platform->addScript('j2store-jqueryFileTree-js','/media/j2store/js/jqueryFileTree.js');
$platform->addStyle('j2store-jqueryFileTree-css','/media/j2store/css/jqueryFileTree.css');

//$doc = JFactory::getDocument();
//$doc->addScript(JURI::root(true).'/media/j2store/js/jqueryFileTree.js');
//$doc->addStyleSheet(JURI::root(true).'/media/j2store/css/jqueryFileTree.css');
$row_class = 'row';
$col_class = 'col-md-';
$product_type_class = 'badge bg-success';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<div class="product-downloadable">
		<div class="j2store-modal">
            <div id="myFileModal" style="display: none;">
            <h4 class="message-title"><?php echo JText::_('J2STORE_CHOOSE_FILE'); ?></h4>
            <hr/>
            <div><div id="fileTreeDemo_1" class="demo1"></div></div>
            </div>

				</div>
<div class="j2store">
	<h1><?php echo JText::_('J2STORE_PFILE_CURRENT_FILES');?></h1>
	<form class="form-horizontal form-validate" id="adminForm" 	name="adminForm" method="post" action="index.php">
		<?php echo J2Html::hidden('option','com_j2store');?>
		<?php echo J2Html::hidden('view','products');?>
		<?php echo J2Html::hidden('task','',array('id'=>'task'));?>
		<?php echo J2Html::hidden('product_id', $this->product_id,array('id'=>'product_id'));?>
		<?php echo JHTML::_( 'form.token' ); ?>
	<div class="note <?php echo $row_class;?>">

		<table class="adminlist table table-striped">
			<thead>
				<tr>
					<th><?php echo JText::_('J2STORE_PRODUCT_FILE_DISPLAY_NAME');?></th>
					<th><?php echo JText::_('J2STORE_PRODUCT_FILE_PATH');?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<?php echo J2Html::text('product_file_display_name','',array("id"=>"download-total", 'class' =>'col-md-10')); ?>
					</td>
					<td>
						<?php echo J2Html::text('product_file_save_name', '',array('class'=>'input ' ,'id'=>'savename')); ?>
                        <a data-fancybox data-src="#myFileModal" type="button" class="btn btn-info choose-file" ><?php echo JText::_('J2STORE_CHOOSE_FILE');?></a>
					</td>
					<td>
						<button class="btn btn-primary"
							onclick="document.getElementById('task').value='createproductfile'; document.adminForm.submit();">
							<?php echo JText::_('J2STORE_PRODUCT_CREATE_PRICE'); ?>
						</button>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="row-fluid">
			<div class="pull-right">
   		 		<button class="btn btn-success" 	onclick="document.getElementById('task').value='saveproductfiles'; document.adminForm.submit();">
					<?php echo JText::_('J2STORE_SAVE_ALL_CHANGES'); ?>
				</button>
			</div>
			<table class="table table-striped">
				<thead>
					<tr>
						<th><?php echo JText::_('J2STORE_PRODUCT_FILE_DISPLAY_NAME');?></th>
						<th><?php echo JText::_('J2STORE_PRODUCT_FILE_PATH');?></th>
					<th></th>
					</tr>
				</thead>
	<?php if(isset($this->productfiles) && !empty($this->productfiles)):?>
	<tbody  class="tr_file_attachement">
		<?php 	foreach($this->productfiles as $counter => $singleFile):?>
			<tr id="exist-file-tbody-<?php echo $singleFile->j2store_productfile_id;?>">

				<td>
					<?php echo J2Html::text('product_files['.$counter.'][product_file_display_name]',$singleFile->product_file_display_name); ?>
					<?php echo J2Html::hidden('product_files['.$counter.'][product_file_save_name]',$singleFile->product_file_save_name); ?>
			</td>
				<td><?php echo $singleFile->product_file_save_name;?></td>
				<td>
					<?php echo J2Html::hidden('product_files['.$counter.'][j2store_productfile_id]',$singleFile->j2store_productfile_id); ?>
					<?php echo J2Html::hidden('product_files['.$counter.'][product_id]',$singleFile->product_id); ?>
					<?php // echo J2Html::button('deletebtn',JText::_('J2STORE_DELETE'),array('class'=>'btn btn-danger btn-small tr_delete_add',"id"=>"file-delete-btn-$singleFile->j2store_productfile_id" ,'file_id' =>$singleFile->j2store_productfile_id ,'onclick'=>'deleteProductFiles(this)')); ?>
					<a class="btn btn-danger" href="index.php?option=com_j2store&view=products&task=deleteFiles&product_id=<?php echo $this->product_id;?>&productfile_id=<?php echo $singleFile->j2store_productfile_id; ?>" >
								<?php echo JText::_('J2STORE_REMOVE');?>
							</a>

				</td>
			</tr>
		<?php endforeach;?>
		<?php else:?>
		<tr>
			<td colspan="4">
				<?php echo JText::_('J2STORE_NO_RECORDS');?>
			</td>
		</tr>
		<?php endif;?>
		</tbody>
		</table>
		</div>
	</form>
</div>





<script type="text/javascript">

function handler( event ) {
	(function($) {
		var target = $( event.target );
		if ( target.is( "li" ) ) {
		target.children().toggle();
		}
		$( ".choose-file" ).click( handler ).find( "ul" ).hide();
	})(j2store.jQuery);


}
			(function($) {
			$(document).ready( function() {
				$('#fileTreeDemo_1').fileTree({ script: 'index.php?option=com_j2store&view=products&task=getFiles' }, function(file) {
					$('#savename').val(file);
					$('#myFileModal').modal('hide');
				});
			});
			})(j2store.jQuery);


function deleteProductFiles(element){
	(function($){
		var file_id = $(element).attr('file_id');
		var delete_productfile = {
			option: 'com_j2store',
			view : 'products',
			task : 'deleteFiles',
			file_id : file_id,
			product_id : '<?php echo $this->product_id;?>'
		};
		if(file_id){
			$.ajax({
				url  : '<?php echo JRoute::_('index.php');?>',
			method:'post',
			data: delete_productfile ,
			beforeSend:function(){
				$("#file-delete-btn-"+file_id).attr('value','<?php echo JText::_('J2STORE_DELETING');?>');
			},
			success:function(json){
				if(json['success']){
					$("#exist-file-tbody-"+file_id).remove();
				}
			}
		})
		}

		})(j2store.jQuery);
}
	</script>
