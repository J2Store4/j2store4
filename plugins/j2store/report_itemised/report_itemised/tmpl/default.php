<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Restricted access');
$db = JFactory::getDbo();
?>
<?php $state = $vars->state;

$listOrder = $state->get('filter_order');
$listDirn = $state->get('filter_order_Dir');
?>
<?php $form = $vars->form;
?>
<?php $items = $vars->list;?>
<div class="j2store">
<form action="<?php echo $form['action'];?>" name="adminForm" class="adminForm" id="adminForm" method="post">
		<?php echo J2Html::hidden('option','com_j2store');?>
		<?php echo J2Html::hidden('view','report');?>
		<?php echo J2Html::hidden('task','view',array('id'=>'task'));?>
		<?php echo J2Html::hidden('reportTask','',array('id'=>'reportTask'));?>
		<?php echo J2Html::hidden('format','html',array('id'=>'format'));?>
		<?php echo J2Html::hidden('id',$vars->id);?>
		<?php echo J2Html::hidden('boxchecked',0);?>
		<?php echo J2Html::hidden('filter_order',$listOrder);?>
		<?php echo J2Html::hidden('filter_order_Dir',$listDirn);?>
		<?php echo JHtml::_('form.token'); ?>
	<table class="adminlist table table-striped ">
		<tr>
			<td>
			<div class="alert alert-block alert-info">
				<?php echo JText::_('PLG_J2STORE_REPORT_ITEMISED_EXPORT_HELP');?>
			</div>
			</td>
			<td>
				<div class="controls">
					<div id="toolbar-icon icon-download" class="btn-wrapper">
							<a class="btn btn-small" href="<?php echo 'index.php?option=com_j2store&view=reports&format=csv&task=browse&reportTask=export&report_id='.$vars->id;?>">
							<span class="icon-icon icon-download"></span><?php echo JText::_('JTOOLBAR_EXPORT');?>
							</a>
					</div>
				</div>
			</td>
		</tr>
			<tr>
				<td>
					<div>
						<span class="span6">
							<?php echo JText::_( 'J2STORE_FILTER_SEARCH' ); ?>:
							<input type="text" name="filter_search" id="search" value="<?php echo htmlspecialchars($state->get('filter_search'));?>" class="text_area" onchange="document.adminForm.submit();" />
							<button class="btn btn-success" onclick="this.form.submit();"><?php echo JText::_( 'J2STORE_FILTER_GO' ); ?></button>
							<button class="btn btn-inverse" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'J2STORE_FILTER_RESET' ); ?></button>
						</span>
						<span>
							<label> <strong><?php echo JText::_('J2STORE_FILTER_DURATION');?></strong></label>
							<?php
								$attribs = array (
									'class' => 'input',
									'onchange' => 'this.form.submit();'
								);
								echo JHtml::_ ( 'select.genericlist', $vars->orderDateType, 'filter_datetype', $attribs, 'value', 'text', $state->get ( 'filter_datetype' ) );
							?>
						</span>
					</div>
				</td>
				<td>
					<?php  echo $vars->pagination->getLimitBox();?>
				</td>

			</tr>
		  </table>
		  <table id="optionsList" class="adminlist table table-bordered table-striped " >
			<thead>
				<tr>
				<th>#</th>
				<th class="name">
					<?php
						echo JHtml::_('grid.sort',  'J2STORE_PRODUCT_ID', 'oi.product_id', $state->get('filter_order_Dir'), $state->get('filter_order')); ?>
				</th>

				<th class="name">
					<?php echo JHtml::_('grid.sort',  'J2STORE_PRODUCT_NAME', 'oi.orderitem_name', $state->get('filter_order_Dir'), $state->get('filter_order')); ?>
				</th>
				<th class="name">
					<?php echo JText::_('J2STORE_PRODUCT_OPTIONS');?>
				</th>
				<th class="name">
					<?php echo JText::_('JCATEGORY');?>
				</th>
				<th class="name">
					<?php echo JHtml::_('grid.sort',  'J2STORE_QUANTITY', 'sum', $state->get('filter_order_Dir'), $state->get('filter_order')); ?>
				</th>
				<th class="id">
					<?php echo JHtml::_('grid.sort',  'J2STORE_REPORTS_ITEMISED_PURCHASES', 'count', $state->get('filter_order_Dir'), $state->get('filter_order')); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="9">
						<?php  echo $vars->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php if($items) : ?>
					<?php foreach ($items as $i => $item):?>
				<tr class="row<?php echo $i%2; ?>">
			   	<td><?php echo $i+1; ?></td>
				<td><?php echo $item->product_id;?></td>
				<td>
					<?php echo $item->orderitem_name;?>
				</td>
				<td>
					<?php

						if(isset($item->orderitem_attributes) && $item->orderitem_attributes):

						foreach($item->orderitem_attributes as $attr):?>
							<small><strong><?php echo $attr->orderitemattribute_name;?> :</strong> <?php echo $attr->orderitemattribute_value;?></small><br/>
						<?php endforeach;?>
					<?php endif;?>
				</td>
				<td><?php echo $item->category_name;?></td>
				<td> <?php echo $db->escape($item->sum);?> </td>
				<td> <?php echo $db->escape($item->count);?> </td>
				<?php endforeach; ?>
			<?php else: ?>
				 <td colspan="9"><?php echo JText::_('J2STORE_NO_ITEMS_FOUND'); ?></td>
			<?php endif; ?>
			</tr>
			</tbody>
		  </table>
		</form>
</div>
<script type="text/javascript">
	function getExportedItems(){
		jQuery('#reportTask').attr('value','exportItems');
		jQuery('#format').attr('value','csv');
		var form =jQuery("#adminForm");
		var data = form.serializeArray();
		jQuery.ajax({
				url:'index.php',
				method:'post',
				data:data,
				success:function(json){
				},
				complete:function(){
					setInterval(function () {
							location.reload();
						}, 3000);
					}
			});
	}
</script>