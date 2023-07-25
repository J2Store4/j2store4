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
$options =array();
if(isset($this->option_values) && !empty($this->option_values)){
	foreach($this->option_values as $opvalue){
		$options[$opvalue->j2store_optionvalue_id] = JText::_($opvalue->optionvalue_name);
	}
}
$parent_option_array=array();
if(isset($this->parent_optionvalues) && !empty($this->parent_optionvalues)){
	foreach($this->parent_optionvalues as $parentopvalue) {
		$parent_option_array[$parentopvalue->j2store_product_optionvalue_id] = $parentopvalue->optionvalue_name;
	}
}
$con_span = 0;
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<div class="j2store">
	<h1><?php echo JText::_( 'J2STORE_PAO_SET_OPTIONS_FOR' ); ?>: <?php echo $this->product_option->option_name; ?></h1>
	<form class="form-horizontal form-validate" id="adminForm" name="adminForm" method="post" action="index.php">
		<?php echo  J2Html::hidden('option','com_j2store');?>
		<?php echo  J2Html::hidden('view','products');?>
		<?php echo  J2Html::hidden('tmpl','component');?>
		<?php echo  J2Html::hidden('task','setDefault',array('id'=>'task'));?>
		<?php echo  J2Html::hidden('optiontask','',array('id'=>'optiontask'));?>
		<?php echo  J2Html::hidden('product_id', $this->product_id,array('id'=>'product_id'));?>
		<?php echo  J2Html::hidden('productoption_id', $this->productoption_id,array('id'=>'productoption_id'));?>
		<?php echo  J2Html::hidden('boxchecked','');?>
		<?php echo JHTML::_( 'form.token' ); ?>
	<div class="note <?php echo $row_class;?>">
	    <h3><?php echo JText::_('J2STORE_PAO_ADD_NEW_OPTION'); ?></h3>
	    <h6><?php echo JText::_( "J2STORE_PAO_COMPLETE_TO_ADD_NEW" ); ?>:</h6>
		<table class="adminlist table table-striped">
			<thead>
				<tr>
					<th><?php $con_span += 1;?></th>
					<th><?php echo JText::_( "J2STORE_PAO_NAME" ); $con_span += 1;?></th>
					<?php if($this->product->product_type =='variable' || $this->product->product_type =='variablesubscriptionproduct'):?>
					<th><?php echo JText::_( "J2STORE_PAO_FIELDATTRIBS" ); $con_span += 1;?> </th>
					<?php endif;?>
					<?php if($this->product_option->is_variant != 1 ):?>
					<?php if($this->product->product_type !='variable' && $this->product->product_type !='variablesubscriptionproduct'):?>
					<?php if(isset($this->parent_optionvalues) && !empty($this->parent_optionvalues) ):  ?>
                    <th>
                    	<?php echo JText::_( "J2STORE_PAO_PARENT_OPTION_NAME" ); $con_span += 1;?>
                    </th>
                    <?php endif; ?>

					<th style="width: 15px;">
						<?php echo JText::_( "J2STORE_PAO_PREFIX" ); $con_span += 1;?>
					</th>
					<th><?php echo JText::_( "J2STORE_PAO_PRICE" ); $con_span += 1;?></th>
					<th style="width: 15px;"><?php echo JText::_( "J2STORE_PAO_WEIGHT_PREFIX" ); $con_span += 1;?>
					</th>
					<th><?php echo JText::_( "J2STORE_PAO_WEIGHT" ); $con_span += 1;?></th>
					<!-- <th><?php // echo JText::_( "J2STORE_SKU" ); ?></th>-->
					<?php endif;?>

					<?php endif;?>
					<th><?php echo JText::_('J2STORE_OPTION_ORDERING');$con_span += 1;?></th>
					<th><?php $con_span += 1;?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td></td>
					<td>
						<?php

							 echo J2Html::select()->clearState()
										    ->type('genericlist')
											->name('optionvalue_id')
											->setPlaceHolders($options)
											->attribs(array('class'=>'input-small'))
											->getHtml();
						?>
					</td>
					<?php if($this->product->product_type =='variable' || $this->product->product_type =='variablesubscriptionproduct'):?>
					<td>

						<?php echo J2Html::textarea('product_optionvalue_attribs' ,'');?>
						<p><?php echo JText::_('J2STORE_PAO_FIELD_ATTRIBS_STYLE_HELP');?></p>
					</td>

					<?php endif;?>




					<?php if($this->product->product_type !='variable' && $this->product->product_type !='variablesubscriptionproduct'):?>

					<?php if(isset($this->parent_optionvalues) && !empty($this->parent_optionvalues) ):  ?>
					<td>

					 		<?php echo J2Html::select()->clearState()
										    ->type('genericlist')
											->name('parent_optionvalue[]')
											->setPlaceHolders($parent_option_array)
											->attribs(array('class'=>'input-small','multiple'=>true))
											->getHtml();?>

                    </td>
                    <?php endif; ?>

                    <?php if($this->product_option->is_variant != 1 ):?>
					<td>
						<?php echo J2Store::product()->getPriceModifierHtml('product_optionvalue_prefix', '+'); ?>
					</td>
					<td>
						<?php echo J2Html::text('product_optionvalue_price' ,'',array('id'=>'product_optionvalue_price' ,'class'=>'input-small'));?>
					</td>
					<td>
						<?php
							echo J2Html::select()->clearState()
								->type('genericlist')
								->name('product_optionvalue_weight_prefix')
								->value('+')
								->setPlaceHolders(array('+' => '+' , '-' =>'-'))
								->attribs(array('class'=>'input-small'))
								->getHtml();
						?>
					</td>
					<?php endif;?>


					<td>
						<?php echo J2Html::text('product_optionvalue_weight' ,'',array('id'=>'product_optionvalue_weight' ,'class'=>'input-small'));?>
					</td>					
				<?php endif;?>
				
					<td><?php echo J2Html::text('ordering','0',array('id'=>'ordering' ,'class'=>'input-small'));?></td>
					<td>
					</td>					
					<td>
						<button class="btn btn-primary"
							onclick="document.getElementById('task').value='createproductoptionvalue'; document.adminForm.submit();">
							<?php echo JText::_('J2STORE_PAO_CREATE_OPTION'); ?>
						</button>
					</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="<?php echo $con_span+1;?>"><a class="btn btn-primary" id="add_all_option_value" onclick="addAllOptionValue()"><?php echo JText::_ ( 'J2STORE_ADD_ALL_OPTION_VALUE' )?></a></td>
				</tr>
			</tfoot>
		</table>

	</div>

	<div class="note_green <?php echo $row_class;?>">
   		 <h3><?php echo JText::_('J2STORE_PAO_CURRENT_OPTIONS'); ?></h3>
   		 	<div class="pull-right">
   		 		<button class="btn btn-success"
						onclick="document.getElementById('task').value='saveproductoptionvalue'; document.adminForm.submit();">
					<?php echo JText::_('J2STORE_SAVE_CHANGES'); ?>
				</button>
			</div>
			<table class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>
                			<input type="checkbox" id="checkall-toggle" name="checkall-toggle" value="" onclick="Joomla.checkAll(this);" />
                		</th>

						<th><?php echo JText::_( "J2STORE_PAO_NAME" ); ?></th>
						<?php if($this->product->product_type =='variable' || $this->product->product_type =='variablesubscriptionproduct'):?>
						<th><?php echo JText::_( "J2STORE_PAO_FIELDATTRIBS" ); ?></th>
						<?php endif; ?>
						<?php if($this->product->product_type !='variable' && $this->product->product_type !='variablesubscriptionproduct'):?>

						 <?php if(isset($this->parent_optionvalues) && !empty($this->parent_optionvalues) ):  ?>
                    	<th>
                    		<?php echo JText::_( "J2STORE_PAO_PARENT_OPTION_NAME" ); ?>
                    	</th>
                    	<?php endif; ?>

                    	<?php if($this->product_option->is_variant != 1 ):?>
						<th><?php echo JText::_( "J2STORE_PAO_PREFIX" ); ?>
						</th>
						<th><?php echo JText::_( "J2STORE_PAO_PRICE" ); ?></th>
						<th><?php echo JText::_( "J2STORE_PAO_WEIGHT_PREFIX" ); ?>
						</th>
						<th><?php echo JText::_( "J2STORE_PAO_WEIGHT" ); ?></th>
						<?php if( in_array ( $this->product->product_type, array('simple','advancedvariable', 'booking'))): ?>
						<th><?php echo JText::_( "J2STORE_DEFAULT" ); ?></th>
						<?php endif; ?>

						<?php endif;?>
						<?php endif;?>
						<th><?php echo JText::_('J2STORE_OPTION_ORDERING');?></th>
                        <?php echo J2Store::plugin()->eventWithHtml('ProductOptionValueTableHead',array($this->product)); ?>

						<th>
						</th>
					</tr>
				</thead>
					<tbody>
						<?php $i=0; $k=0; ?>
						<?php
						if(  isset($this->product_optionvalues) && !empty($this->product_optionvalues) ):
		                	foreach($this->product_optionvalues as $key => $poptionvalue):
		                	$canChange=1;
						?>
						<tr class='row<?php echo $k; ?>'>
						<td>
							<?php echo JHTML::_('grid.id', $i, $poptionvalue->j2store_product_optionvalue_id);; ?>
							<?php echo J2Html::hidden($this->prefix.'['.$poptionvalue->j2store_product_optionvalue_id.'][productoption_id]', $this->productoption_id,array('id'=>'productoption_id'));?>
							<?php echo J2Html::hidden($this->prefix.'['.$poptionvalue->j2store_product_optionvalue_id.'][j2store_product_optionvalue_id]', $poptionvalue->j2store_product_optionvalue_id);?>
						</td>

						<td>
							<?php
							echo J2Html::select()->clearState()
										    ->type('genericlist')
											->name($this->prefix.'['.$poptionvalue->j2store_product_optionvalue_id.'][optionvalue_id]')
											->value($poptionvalue->optionvalue_id)
											->setPlaceHolders($options)
											->attribs(array('class'=>'input-small'))
											->getHtml();
							?>
						</td>
						<?php if($this->product->product_type =='variable' || $this->product->product_type =='variablesubscriptionproduct'):?>
						<td>
							<?php echo J2Html::textarea($this->prefix.'['.$poptionvalue->j2store_product_optionvalue_id.'][product_optionvalue_attribs]' ,$poptionvalue->product_optionvalue_attribs);?>
							<p><?php echo JText::_('J2STORE_PAO_FIELD_ATTRIBS_STYLE_HELP');?></p>
						</td>
						<?php endif;?>
						<?php if($this->product->product_type !='variable' && $this->product->product_type !='variablesubscriptionproduct'):?>

						<?php if(isset($this->parent_optionvalues) && !empty($this->parent_optionvalues) ):  ?>
						<td>
							<?php $poptionvalue->parent_optionvalue = isset($poptionvalue->parent_optionvalue) && !empty($poptionvalue->parent_optionvalue) ?  explode(',',$poptionvalue->parent_optionvalue) : '';?>
						 	<?php echo J2Html::select()->clearState()
										    ->type('genericlist')
											->name($this->prefix.'['.$poptionvalue->j2store_product_optionvalue_id.'][parent_optionvalue][]')
											->value($poptionvalue->parent_optionvalue)
											->setPlaceHolders($parent_option_array)
											->attribs(array('multiple'=>true))
											->getHtml();
						 	?>
						</td>
						<?php endif;?>

						<?php if($this->product_option->is_variant != 1 ):?>

							<td>
								<?php echo J2Store::product()
												->getPriceModifierHtml($this->prefix.'['.$poptionvalue->j2store_product_optionvalue_id.'][product_optionvalue_prefix]', $poptionvalue->product_optionvalue_prefix);
								?>
							</td>

							<td>
								<?php echo J2Html::text($this->prefix.'['.$poptionvalue->j2store_product_optionvalue_id.'][product_optionvalue_price]' ,$poptionvalue->product_optionvalue_price,array('id'=>'product_optionvalue_price' ,'class'=>'input-small'));?>
							</td>
							<td>
							<?php
								echo J2Html::select()->clearState()
									->type('genericlist')
									->name($this->prefix.'['.$poptionvalue->j2store_product_optionvalue_id.'][product_optionvalue_weight_prefix]')
									->value($poptionvalue->product_optionvalue_weight_prefix)
									->setPlaceHolders(array('+' => '+' , '-' =>'-'))
									->attribs(array('class'=>'input-small'))
									->getHtml();
								?>
							</td>							
							<td>
								<?php echo J2Html::text( $this->prefix.'['.$poptionvalue->j2store_product_optionvalue_id.'][product_optionvalue_weight]' ,$poptionvalue->product_optionvalue_weight,array('id'=>'product_optionvalue_weight' ,'class'=>'input-small'));?>
							</td>
						<?php if( in_array ( $this->product->product_type, array('simple','advancedvariable', 'booking'))): ?>
						<td>
								<?php  echo JHtml::_('jgrid.isdefault',$poptionvalue->product_optionvalue_default,$key,"",$canChange,'cb');?>
							</td>
							<?php endif;?>
							<?php endif;?>
						<?php endif;?>
						<td><?php echo J2Html::text($this->prefix.'['.$poptionvalue->j2store_product_optionvalue_id.'][ordering]',$poptionvalue->ordering,array('id'=>'ordering' ,'class'=>'input-small'));?></td>
                            <?php echo J2Store::plugin()->eventWithHtml('ProductOptionValueTableBody',array($this->product,$poptionvalue)); ?>
							<td>
								<?php $deleteUrl = JRoute::_('index.php?option=com_j2store&view=products&task=deleteProductOptionvalues&product_id='.$this->product_id.'&productoption_id='.$poptionvalue->productoption_id.'&cid[]='.$poptionvalue->j2store_product_optionvalue_id, false); ?>
								 <a class="btn btn-danger" href="<?php echo $deleteUrl; ?>" >
									<i class="icon icon-trash"></i>
								</a>
							</td>

						</tr>
						<?php $i=$i+1; $k = (1 - $k); ?>
						<?php endforeach;?>
						<?php endif;?>
				</tbody>
			</table>
		</div>
	</form>
</div>
<script type="text/javascript">
	if(typeof(j2store) == 'undefined') {
		var j2store = {};
	}
	if(typeof(j2store.jQuery) == 'undefined') {
		j2store.jQuery = jQuery.noConflict();
	}

	if(typeof(j2storeURL) == 'undefined') {
		var j2storeURL = '';
	}
	function addAllOptionValue() {
		(function ($) {
			var data={
				option: 'com_j2store',
				view: 'products',
				task: 'addAllOptionValue',
				product_id: '<?php echo $this->product_id;?>',
				productoption_id: '<?php echo $this->productoption_id;?>'
			};
			$.ajax({
				url:'index.php',
				method:'post',
				dataType:'json',
				data: data,
				beforeSend: function() {
					$('.j2error').remove();
					$('#add_all_option_value').after('<span class="wait"><img src="'+j2storeURL+'media/j2store/images/loader.gif" alt="" /></span>');
					$('#add_all_option_value').attr('disabled',true);
				},
				success:function(json){
					$('.wait,.j2error').remove();
					$('#add_all_option_value').attr('disabled',false);
					if(json['success']){
						location.reload();
					}
				}
			});
		})(jQuery);

	}
    Joomla.listItemTask = function (id, task) {
        var f = document.adminForm;
        jQuery("#optiontask").attr('value',task);

        cb = eval( 'f.' + id );
        if (cb) {
            for (i = 0; true; i++) {
                cbx = eval('f.cb'+i);
                if (!cbx) break;
                cbx.checked = false;
            } // for
            cb.checked = true;
            f.boxchecked.value = 1;
            var data =jQuery(f).serializeArray();
            jQuery.ajax({
                url:'index.php',
                method:'post',
                dataType:'json',
                data: data,
                success:function(json){
                    if(json['success']){
                        location.reload();
                    }
                }
            });
        }
        return false;
    };

</script>