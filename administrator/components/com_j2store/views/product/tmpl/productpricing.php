<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/select.php';
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2html.php';
$this->prefix = 'jform[prices]';
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<div class="j2store">
	<?php if(isset($this->variant_id) && $this->variant_id > 0): ?>
	<h1><?php echo JText::_( 'J2STORE_PRODUCT_ADD_PRICING' ); ?></h1>
	<form class="form-horizontal form-validate" id="adminForm" 	name="adminForm" method="post" action="index.php">
		<?php echo  J2Html::hidden('option','com_j2store');?>
		<?php echo  J2Html::hidden('view','products');?>
		<?php echo  J2Html::hidden('task','',array('id'=>'task'));?>
		<?php echo  J2Html::hidden('variant_id', $this->variant_id, array('id'=>'variant_id'));?>
		<?php echo JHTML::_( 'form.token' ); ?>
	<div class="note <?php echo $row_class;?>">
		<table class="adminlist table table-bordered table-striped">
			<thead>
				<tr>
					<th><?php echo JText::_('J2STORE_PRODUCT_PRICE_DATE_RANGE');?></th>
					<th><?php echo JText::_('J2STORE_PRODUCT_PRICE_QUANTITY_RANGE');?></th>
					<th><?php echo JText::_('J2STORE_PRODUCT_PRICE_GROUP_RANGE');?></th>
					<th><?php echo JText::_('J2STORE_PRODUCT_PRICE_VALUE');?></th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>
						<?php echo J2Html::calendar('date_from','',array('class'=>'col-sm-2 form-control input-small','id'=>'price_date_from','format' => '%d-%m-%Y %H:%M:%S','showTime' => true ));?>
						<?php echo JText::_('J2STORE_TO');?>
						<?php echo J2Html::calendar('date_to','',array('class'=>'col-sm-2 form-control input-small','id'=>'price_date_to','format' => '%d-%m-%Y %H:%M:%S','showTime' => true ));?>
					</td>
					<td>
						<?php echo J2Html::text('quantity_from', '',array('class'=>'input-small ')); ?>
						<?php echo JText::_('J2STORE_QUANTITY_AND_ABOVE');?>
					</td>
					<td>
						<?php echo JHtml::_('select.genericlist', $this->groups, 'customer_group_id', array(), 'value', 'text',''); ?>
						<?php //echo JHtmlAccess::level('customer_group_id', '', '', false); ?>
					</td>
					<td>
						<?php echo J2Html::price('price','',array('class'=>'input-small ')); ?>

					</td>
					<td>
						<button class="btn btn-primary"
							onclick="document.getElementById('task').value='createproductprice'; document.adminForm.submit();">
							<?php echo JText::_('J2STORE_PRODUCT_CREATE_PRICE'); ?>
						</button>
					</td>
				</tr>
			</tbody>
		</table>

	</div>

	<div class="note_green <?php echo $row_class;?>">
   		 <h3><?php echo JText::_('J2STORE_PRODUCT_CURRENT_PRICES'); ?></h3>
   		 	<div class="pull-right">
   		 		<button class="btn btn-success"
								onclick="document.getElementById('task').value='saveproductprices'; document.adminForm.submit();">
								<?php echo JText::_('J2STORE_PRODUCT_SAVE_ALL_PRICES'); ?>
							</button>
			</div>
			<table class="table table-striped">
				<thead>
					<tr>

						<th><?php echo JText::_('J2STORE_PRODUCT_PRICE_DATE_RANGE');?></th>
						<th><?php echo JText::_('J2STORE_PRODUCT_PRICE_QUANTITY_RANGE');?></th>
						<th><?php echo JText::_('J2STORE_PRODUCT_PRICE_GROUP_RANGE');?></th>
						<th><?php echo JText::_('J2STORE_PRODUCT_PRICE_VALUE');?></th>
					</tr>
				</thead>
				<tbody>
					<?php
						if(isset($this->prices) && !empty($this->prices)):
                            $utility = J2Store::utilities();
					foreach($this->prices as $key => $pricing):?>
					<tr class="row<?php echo $key%2;?>" id="productprice-row-<?php echo $pricing->j2store_productprice_id;?>">
						<td>

							<?php echo J2Html::calendar($this->prefix."[$pricing->j2store_productprice_id][date_from]",$utility->convert_utc_current($pricing->date_from),array('class'=>'col-sm-2 form-control input-small','id'=>"price_date_from_$key",'format' => '%d-%m-%Y %H:%M:%S','showTime' => true ));?>
							<?php echo JText::_('J2STORE_TO');?>
							<?php echo J2Html::calendar($this->prefix."[$pricing->j2store_productprice_id][date_to]",$utility->convert_utc_current($pricing->date_to),array('class'=>'col-sm-2 form-control input-small','id'=>"price_date_to_$key",'format' => '%d-%m-%Y %H:%M:%S','showTime' => true ));?>
						</td>
						<td>
							<?php echo J2Html::text($this->prefix."[$pricing->j2store_productprice_id][quantity_from]",$pricing->quantity_from,array('class'=>'input-small ')); ?>
							<?php echo JText::_('J2STORE_QUANTITY_AND_ABOVE');?>
						</td>
						<td>
							<?php echo JHtml::_('select.genericlist', $this->groups, $this->prefix."[$pricing->j2store_productprice_id][customer_group_id]", array(), 'value', 'text',$pricing->customer_group_id);?>
							<?php // echo JHtmlAccess::level($this->prefix."[$pricing->j2store_productprice_id][customer_group_id]", $pricing->customer_group_id, '', false); ?>
						</td>
						<td>
							<?php echo J2Html::price_with_data($this->prefix, $pricing->j2store_productprice_id, "[$pricing->j2store_productprice_id][price]",$pricing->price,array('class'=>'input-small '), $pricing); ?>
							<?php echo J2Html::hidden($this->prefix."[$pricing->j2store_productprice_id][j2store_productprice_id]",$pricing->j2store_productprice_id,array('id'=>"product_price_id_$pricing->j2store_productprice_id"));?>
							<?php echo J2Html::hidden($this->prefix."[$pricing->j2store_productprice_id][variant_id]",$pricing->variant_id,array('id'=>"variant_id_$pricing->j2store_productprice_id"));?>
						</td>
						<td>
							<a class="btn btn-danger"
									href="index.php?option=com_j2store&view=products&task=removeproductprice&variant_id=<?php echo $pricing->variant_id;?>&productprice_id=<?php echo $pricing->j2store_productprice_id; ?>&cid[]=<?php echo $pricing->j2store_productprice_id;?>" >

								<?php echo JText::_('J2STORE_REMOVE');?>
							</a>
						</td>
					</tr>
					<?php endforeach;?>
					<?php endif;?>
				</tbody>

			</table>
		</div>
	</form>
	<?php else: ?>	
	<?php echo JText::_('J2STORE_NO_VARIANT_FOUND'); ?>	
	<?php endif;?>
</div>
