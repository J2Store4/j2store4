<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/popup.php');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/select.php');
$items = $this->items;
$row = $this->row;
$sid = JFactory::getApplication()->input->getInt('sid');
$form = $this->form2;
$baseLink = $this->baseLink;
?>
<div class="j2store">
<h3>
	<?php echo JText::_( "J2STORE_SRATE_SET_RATE_FOR" ); ?>:<?php  echo $row->shipping_method_name; ?>
</h3>
<form action="<?php echo JRoute::_( $form['action'] )?>" method="post" id="adminForm" name="adminForm" enctype="multipart/form-data">
	<table class="table table-striped table-bordered">
		<thead>
			<th><?php echo JText::_( "J2STORE_SRATE_GEOZONES" ); ?></th>
				<?php if($row->shipping_method_type == 1
            				|| $row->shipping_method_type == 2
            				|| $row->shipping_method_type == 4 ||
            				$row->shipping_method_type == 5 ):?>
			<th><?php echo JText::_( "J2STORE_SRATE_RANGE" ); ?></th>

			<?php endif; ?>
			<th><?php echo JText::_( "J2STORE_SFR_SHIPPING_RATE_PRICE" ); ?></th>
			<th><?php echo JText::_( "J2STORE_SRATE_HANDLING_FEE" ); ?></th>
		</thead>
		<tbody>
			<tr>
	            <td>
	            	<?php echo J2Html::select()->clearState()
							  				   ->type('genericlist')
											   ->name('jform[geozone_id]')
											   ->value()
											   ->hasOne('Geozones')
											   ->setRelations(array('fields' => array ('key' => 'j2store_geozone_id','name' => array('geozone_name'))))
	            							   ->getHtml();?>
	              <?php echo J2Html::hidden('jform[shipping_method_id]',$sid);?>
	            </td>

	              		<?php if($row->shipping_method_type == 1
            				|| $row->shipping_method_type == 2
            				|| $row->shipping_method_type == 4 ||
            				$row->shipping_method_type == 5 ):?>

            				<td>
            					<?php echo J2html::input('number','jform[shipping_rate_weight_start]' ,0,array('id'=>'shipping_rate_weight_start'));?>
            					 <?php echo JText::_("J2STORE_TO"); ?>
            					<?php echo J2html::input('number','jform[shipping_rate_weight_end]' ,0,array('id'=>'shipping_rate_weight_start'));?>
							</td>
						<?php endif; ?>
				<td><?php echo J2Html::input('number','jform[shipping_rate_price]' ,0);?></td>
				<td><?php echo J2Html::input('number','jform[shipping_rate_handling]' ,0);?>			</td>
				</tr>
				<tr>
					<td colspan="4">
						<div class="pull-right">
							<button class="btn btn-success" onclick="document.getElementById('shippingTask').value='createrate'; document.adminForm.submit();">
								<i class="icon-new"></i>
								<?php echo JText::_('J2STORE_CREATE'); ?>
							</button>
						</div>
					</td>
	        </tr>
		</tbody>
	</table>
	<table class="table table-striped table-bordered">
		<tbody>
			<tr>
				<td colspan="5">
						<div class="pull-right">
							<button class="btn btn-secondary" onclick="document.getElementById('shippingTask').value='saverates'; document.adminForm.submit();">
								<i class="icon-save"></i>
								<?php echo JText::_('J2STORE_SAVE_CHANGES'); ?>
							</button>
							<button	class="btn btn-danger"
									onclick="document.getElementById('shippingTask').value='deleterate'; document.adminForm.submit();" >
									<i class="icon-remove"></i>
									<?php echo JText::_('J2STORE_DELETE'); ?>
							</button>
						</div>
					</td>
			</tr>
			<?php $i=0; $k=0; ?>
			<?php foreach($this->items as $item):?>
			<?php $checked = JHTML::_('grid.id', $i, $item->j2store_shippingrate_id); ?>
			 <tr class='row<?php echo $k; ?>'>
				<td style="text-align: center;">
					<?php echo $checked; ?>
				</td>
	           	<td>
	           		<?php echo J2html::hidden('standardrates['.$item->j2store_shippingrate_id.'][j2store_shippingrate_id]',$item->j2store_shippingrate_id); ?>
           			<?php echo J2Html::select()->clearState()->type('genericlist')->name('standardrates['.$item->j2store_shippingrate_id.'][geozone_id]')
												->value($item->geozone_id)
												->hasOne('Geozones')
												->setRelations(array('fields' => array ('key' => 'j2store_geozone_id','name' => array('geozone_name'))))
           										->getHtml();?>
              		<?php echo J2Html::hidden('standardrates['.$item->j2store_shippingrate_id.'][shippingmethod_id]',$sid)?>
            	</td>
            	 	<?php if($row->shipping_method_type == 1|| $row->shipping_method_type == 2 || $row->shipping_method_type == 4 || $row->shipping_method_type == 5 ):?>
            	<td>
            		<?php echo J2Html::input('number','standardrates['.$item->j2store_shippingrate_id.'][shipping_rate_weight_start]' , (float)$item->shipping_rate_weight_start);?>
            		<?php echo JText::_("J2STORE_TO"); ?>
            		<?php echo J2Html::input('number','standardrates['.$item->j2store_shippingrate_id.'][shipping_rate_weight_end]' , (float)$item->shipping_rate_weight_end);?>
            	</td>
				<?php endif; ?>
				<td><?php echo J2Html::input('number','standardrates['.$item->j2store_shippingrate_id.'][shipping_rate_price]',(float)$item->shipping_rate_price);?></td>
				<td><?php echo J2Html::input('number','standardrates['.$item->j2store_shippingrate_id.'][shipping_rate_handling]',(float)$item->shipping_rate_handling);?></td>
        	</tr>
		<?php $i=$i+1; $k = (1 - $k); ?>
			<?php endforeach; ?>

			<?php if (!count(@$items)) : ?>
			<tr>
				<td colspan="10" align="center">
					<?php echo JText::_('J2STORE_NO_ITEMS_FOUND'); ?>
				</td>
			</tr>
		<?php endif; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="20">
					<?php  echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
	</table>
	<?php echo J2Html::hidden('order_change', '0');?>
	<?php echo J2Html::hidden('sid', $sid);?>
	<?php echo J2Html::hidden('view','shippings' ,array('id'=>'view'));?>
	<?php echo J2Html::hidden('task','view' ,array('id'=>'task'));?>
	<?php echo J2Html::hidden('shippingTask','setrates' ,array('id'=>'shippingTask'));?>
	<?php echo J2Html::hidden('boxchecked','');?>
	</div>
</form>
