<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

$parentOpvalues  = array();
if(isset($this->parent_optionvalues) && !empty($this->parent_optionvalues)){
	foreach($this->parent_optionvalues as $parentopvalue) {
		$parentOpvalues[$parentopvalue->j2store_product_optionvalue_id]=$parentopvalue->optionvalue_name;
	}
}
?>
<div class="j2store">
	<h1><?php echo JText::_( 'J2STORE_PAO_SET_OPTIONS_FOR' ); ?>: <?php echo $this->product_option->option_name; ?></h1>
	<form class="form-horizontal form-validate" id="adminForm" 	name="adminForm" method="post" action="index.php">
		<?php echo  J2Html::hidden('option','com_j2store');?>
		<?php echo  J2Html::hidden('view','products');?>
		<?php echo  J2Html::hidden('task','',array('id'=>'task'));?>
		<?php echo  J2Html::hidden('productoption_id', $this->productoption_id,array('id'=>'productoption_id'));?>
		<?php echo JHTML::_( 'form.token' ); ?>
	<div class="parent-option-value">
		<table class="table table-striped table-condensed">
			<thead>
				<tr>
					<th><?php echo JText::_('J2STORE_OPTION_PARENT_OPTION_VALUES');?></th>
					<th>
						<button class="btn btn-success"
								onclick="document.getElementById('task').value='saveparentproductoptionvalue'; document.adminForm.submit();">
							<?php echo JText::_('J2STORE_SAVE_CHANGES'); ?>
						</button>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php if(isset($this->parent_optionvalues) && !empty($this->parent_optionvalues) ): ?>
				<?php if(isset($this->product_optionvalues) && !empty($this->product_optionvalues)):?>
				<?php foreach($this->product_optionvalues as $singleitem):?>
				<tr>
					<td colspan="2">
					<?php echo J2Html::hidden('j2store_product_optionvalue_id',  $singleitem->j2store_product_optionvalue_id, array('id'=>'j2store_product_optionvalue_id'));?>
					<?php $singleitem->parent_optionvalue =  explode(',',$singleitem->parent_optionvalue);?>
					<?php


					echo J2Html::select()->clearState()
					->type('genericlist')
					->name('parent_optionvalue[]')
					->value($singleitem->parent_optionvalue)
					->setPlaceHolders($parentOpvalues)
					->attribs(array('class'=>'input-small','multiple'=>true))
					->getHtml();
					//echo JHtml::_('select.genericlist', $this->parentopvalue_array, 'parent_optionvalue[]', array('class'=>'input-small' ,'multiple'=>true ,'id'=> 'parent_optionvalue'), 'value', 'text',$singleitem->parent_optionvalue);

					?>
					</td>
				</tr>
				<?php endforeach;?>

			<?php else:?>
			<tr>
				<td colspan="2">
					<?php
						echo J2Html::select()->clearState()
								->type('genericlist')
								->name('parent_optionvalue[]')
								->value()
								->setPlaceHolders($parentOpvalues)
								->attribs(array('class'=>'input-small','multiple'=>true))
								->getHtml();
					?>
				</td>
			</tr>
			<?php endif;?>
			</tbody>
			<?php endif;?>
		</table>
</div>