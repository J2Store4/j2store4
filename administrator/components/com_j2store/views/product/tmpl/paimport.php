<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access
defined('_JEXEC') or die;
 $app = JFactory::getApplication();
JHtml::_('script', 'system/core.js', false, true);
//print_r($this->row);
?>
<style>
nav, header, div.subhead {
	display: none!important;
}
</style>
<div class="j2store">
	<h3><?php echo JText::_( "J2STORE_PAI_IMPORT_PRODUCT_OPTIONS_FOR" ); ?>:<?php echo $this->row->product_name; ?></h3>
	<p class="alert alert-info"><?php echo JText::_('J2STORE_PAIMPORT_SEARCH_HELP_TEXT'); ?></p>
	<div class="row-fluid">
		<form action="index.php" method="get" name="searchForm" id="searchForm" enctype="multipart/form-data">
			<div class="">
				<div class="span2">
					<?php echo JText::_('J2STORE_SKU'); ?> <br>
					<input type="text" name="filter_sku" value="<?php  echo $app->input->getString ( 'filter_sku', '' ); ?>" class="span12">
					
				</div>
				<div class="span1">
				<br />
				<?php echo JText::_('J2STORE_OR'); ?></div>
				<div class="span2">
					
					<?php echo JText::_('J2STORE_PRODUCT_ID'); ?> <br>
					<input type="text" name="filter_pid" value="<?php  echo $app->input->getString ( 'filter_pid', '' ); ?>" class="span8">
				</div>	
				<!--<input type="text" name="filter_search" value="<?php  echo $app->input->getString ( 'filter_search', '' ); ?>" > -->
				
				<div class="span3">
					<br>
					<input type="submit" name="search_button" class="btn btn-success" value="<?php echo JText::_('J2STORE_PAIMPORT_FIND_PRODUCTS_TO_IMPORT')?>">
				</div>
			</div>
			<input type="hidden" name="option" value="com_j2store" />
			<input type="hidden" name="view" value="products" />
			<input type="hidden" name="task" value="setpaimport" />
			<input type="hidden" name="layout" value="paimport" />
			<input type="hidden" name="product_id" value="<?php echo $this->row->j2store_product_id; ?>" />
			<input type="hidden" name="product_type" value="<?php  echo $app->input->getString ( 'product_type', 'simple' ); ?>" />
			<input type="hidden" name="tmpl" value="component" />
		</form>	
	</div>
<?php if(isset($this->products) && count($this->products)):?>
	<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
		
		<div class="row-fluid">
			<div>
				<button class="btn btn-info btn-large pull-right"
					onclick="document.getElementById('task').value='importattributes'; document.adminForm.submit(); ">
					<?php echo JText::_('J2STORE_PAI_IMPORT_PRODUCT_OPTIONS'); ?>
				</button> 
				<br><br>
			</div>
			<br />
			<div class="alert alert-block alert-info"><?php echo JText::_('J2STORE_PAI_IMPORT_PRODUCT_OPTIONS_HELP_TEXT');?></div>
			<table class="adminlist table table-striped">
				<thead>
					<tr>
						<th style="width: 20px;">
							<input type="checkbox"	name="checkall-toggle" value=""  />
						</th>
						<th style="text-align: left;"><?php echo JText::_('J2STORE_PRODUCT_ID'); ?>
						</th>
						<th style="text-align: left;"><?php echo JText::_('J2STORE_PRODUCT_NAME'); ?>
						</th>
						<th style="text-align: left;"><?php echo JText::_( "J2STORE_PRODUCT_OPTIONS" ); ?>
						</th>

					</tr>
				</thead>
				<tbody>

					<?php $i=0; $k=0; ?>
					<?php foreach ($this->products as $item) :
					$checked = JHTML::_('grid.id', $i, $item->j2store_product_id);
					$attributes = $this->productHelper->getProductOptions($item);
					?>
					<tr class='row<?php echo $k; ?>'>
						<td style="text-align: center;"><?php
							echo $checked;
						?>
						</td>
						<td style="text-align: left;"><?php echo $item->j2store_product_id?>
						</td>

						<td style="text-align: left;"><?php echo $item->product_name; ?>
						</td>
						<td style="text-align: left;">
					 <?php if(count($attributes)) : ?>
				 		<ol>
					 	<?php foreach($attributes as $attribute) : ?>
					 		<li><?php echo $this->escape($attribute['option_name']); ?></li>
					 		<?php if(isset($attribute['optionvalue']) && !empty($attribute['optionvalue']) && count($attribute['optionvalue'])) : ?>
					 				<strong> <?php echo JText::_('J2STORE_PAI_IMPORT_VALUES_FOR_THIS_OPTION'); ?></strong>
					 				<ol>
					 				<?php foreach ($attribute['optionvalue'] as $a_option) :
					 				?>
									<li>
										<span><?php echo $this->escape($a_option['optionvalue_name']); ?> </span>
										<span>
										<?php echo $a_option['product_optionvalue_prefix']; ?>&nbsp;<?php echo $this->currency->format($a_option['product_optionvalue_price']); ?>
										</span>
									</li>
									<?php endforeach; ?>
									</ol>
					 		<?php endif; ?>
					 	<?php endforeach; ?>
					 	</ol>
					 <?php endif; ?>
						</td>
					</tr>
					<?php $i=$i+1; $k = (1 - $k); ?>
					<?php endforeach; ?>

				</tbody>
				<tfoot>
					<tr>
						<td colspan="4"><?php //echo @$this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
			</table>

			<input type="hidden" name="order_change" value="0" /> <input
				type="hidden" name="product_id" value="<?php echo $this->row->j2store_product_id; ?>" /> <input
				type="hidden" name="task" id="task" value="importattributes" /> <input
				type="hidden" name="option" value="com_j2store" /> <input
				type="hidden" name="view" value="products" /> <input type="hidden"
				name="boxchecked" value="" />
				<input type="hidden"		name="filter_order" value="<?php // echo $this->lists['order']; ?>" />
			<input type="hidden" name="filter_order_Dir"
				value="<?php // echo $this->lists['order_Dir']; ?>" />
		</div>
	</form>
	<?php else: ?>
	<div class="alert alert-info">
		<?php echo JText::_('J2STORE_PAIMPORT_NO_ITEMS_FOUND'); ?>
	</div>
	<?php endif; ?>
</div>
<script>

jQuery(document).ready(function() {
	jQuery('input[name=checkall-toggle]').click(function(event) {  //on click
        if(this.checked) {
            // check select status
        	jQuery('input[type=checkbox]').each(function() { //loop through each checkbox
                this.checked = true;  //select all checkboxes with class "checkbox1"
            });
        }else{
        	jQuery('input[type=checkbox]').each(function() { //loop through each checkbox
                this.checked = false; //deselect all checkboxes with class "checkbox1"
            });
        }
    });

});
</script>
