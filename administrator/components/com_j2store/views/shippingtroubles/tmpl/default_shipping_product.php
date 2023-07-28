<?php
/**
 * -------------------------------------------------------------------------------
 * @package 	J2Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license 	GNU GPL v3 or later
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
defined('_JEXEC') or die;
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$sidebar = JHtmlSidebar::render();
$this->params = J2Store::config();
$row_class = 'row';
$col_class = 'col-md-';
$info_class = 'badge bg-info';
$warning_class = 'badge bg-warning';
$success_class = 'badge bg-success';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
    $info_class = 'label label-info';
    $warning_class = 'label label-warning';
    $success_class = 'label label-success';
}
?>
<div class="<?php echo $row_class; ?>">
<?php if(!empty( $sidebar )): ?>
   <div id="j-sidebar-container" class="<?php echo $col_class; ?>2">
      <?php echo $sidebar ; ?>
   </div>
   <div id="j-main-container" class="<?php echo $col_class; ?>6">
 <?php else : ?> 
	<div class="j2store">
  <?php endif;?>
  <h2> <?php echo JText::_("J2STORE_SHIPPING_PRODUCT_VALIDATE");?></h2>
  <div class="col-sm-10 col-xs-12 col-md-10 <?php echo $col_class; ?>12">
  <form action="index.php" method="post"	name="adminForm" id="adminForm">
  		<div class="pull-right">
  			<?php echo $this->pagination->getLimitBox();?>
  		</div>
  		<?php $search = htmlspecialchars($this->state->search);?>
			<div class="input-prepend">
			<span class="add-on"><?php echo JText::_( 'J2STORE_FILTER_SEARCH' ); ?></span>
			<?php echo  J2Html::text('search',$search,array('id'=>'search' ,'class'=>'input j2store-product-filters'));?>

			<?php  echo  J2Html::button('go',JText::_( 'J2STORE_FILTER_GO' ) ,array('class'=>'btn btn-success','onclick'=>'this.form.submit();'));?>
			<?php  echo  J2Html::button('reset',JText::_( 'J2STORE_FILTER_RESET' ),array('id'=>'reset-filter-search','class'=>'btn btn-inverse',"onclick"=>"jQuery('#search').val('');this.form.submit();"));?>
			</div>
  		
  		<?php echo J2Html::hidden('option','com_j2store');?>
		<?php echo J2Html::hidden('view','shippingtroubles');?>
		<?php echo J2Html::hidden('layout','default_shipping_product');?>
		<?php echo J2Html::hidden('task','browse',array('id'=>'task'));?>
		<?php echo J2Html::hidden('boxchecked','0');?>
		<?php echo J2Html::hidden('filter_order',$this->state->filter_order);?>
		<?php echo J2Html::hidden('filter_order_Dir',$this->state->filter_order_Dir);?>
		<?php echo JHTML::_( 'form.token' ); ?>
		<div class="j2store-product-filters">
			<div class="j2store-alert-box" style="display:none;"></div>
			<!-- general Filters -->
			<?php  //echo $this->loadTemplate('filters');?>
		</div>
		<?php if($this->shipping_available):?>
			<table class="table table-striped table-bordered">
				<thead>
					<tr>
					<th><?php echo "#";?></th>
						<th><?php  echo JHTML::_('grid.sort',  'J2STORE_PRODUCT_ID', 'j2store_product_id',$this->state->filter_order_Dir, $this->state->filter_order ); ?></th>
						<th><?php echo JText::_('J2STORE_PRODUCT_NAME');?></th>
						<th><?php echo JText::_('J2STORE_PRODUCT_SHIPPING_ENABLED');?></th>
						<th><?php echo JText::_('J2STORE_PRODUCT_SHIPPING_DIMENSION');?></th>
						<th><?php echo JText::_('J2STORE_PRODUCT_SHIPPING_WEIGHT');?></th>
						<th><?php echo JText::_('J2STORE_PRODUCT_SHIPPING_CLASS');?></th>
					</tr>
				</thead>
				<tfoot>
		<tr>
			<td colspan="10"><?php  echo $this->pagination->getListFooter(); ?>
					</td>
		</tr>
	</tfoot>
				<tbody>
				
					<?php foreach ($this->products as $i=>$product):?>
					<?php
					$product_helper = J2Store::product();
					$product_helper->setId($product->j2store_product_id);
					$product_data = $product_helper->getProduct();			
					//echo "<pre>";print_r($product_data);echo "</pre>";
					?>
						<tr>
							<td><?php echo $this->pagination->getRowOffset( $i ); ?></td>
							<td><?php echo $product->j2store_product_id;?></td>
							<td><a href="<?php echo $product_data->product_edit_url;?>"><?php echo $product_data->product_name;?></a></td>
							<?php if($product->product_type !='variable'):?>
							<td>
								<?php if($product->shipping):?>
								<label class="<?php echo $success_class ?>"> <?php echo JText::_('J2STORE_ENABLED'); ?> </label>
								<?php else: ?>
								<label class="<?php echo $warning_class ?>"> <?php echo JText::_('J2STORE_DISABLED'); ?> </label>
								<?php endif; ?>
							</td>
							<td>
								<?php echo JText::_('J2STORE_LENGTH').":";
								if($product->length < 0.1):?>
									<label class="<?php echo $warning_class ?>"> <?php echo JText::_('J2STORE_NOT_SET'); ?> </label>
								<?php else:?>
									<label class="<?php echo $success_class ?>"> <?php echo JText::_('J2STORE_OK'); ?> </label>
								<?php endif;?>
								<br/>
							<?php echo JText::_('J2STORE_WIDTH').":";
								if($product->width < 0.1):?>
									<label class="<?php echo $warning_class ?>"> <?php echo JText::_('J2STORE_NOT_SET'); ?> </label>
								<?php else:?>
									<label class="<?php echo $success_class ?>"> <?php echo JText::_('J2STORE_OK'); ?> </label>
								<?php endif;?>
								<br/>							
							<?php echo JText::_('J2STORE_HEIGHT').":";
								if($product->height < 0.1):?>
									<label class="<?php echo $warning_class ?>"> <?php echo JText::_('J2STORE_NOT_SET'); ?> </label>
								<?php else:?>
									<label class="<?php echo $success_class ?>"> <?php echo JText::_('J2STORE_OK'); ?> </label>
								<?php endif;?>
								<br/>
							</td>
							<td><?php echo JText::_('J2STORE_PRODUCT_WEIGHT').":";
								if($product->weight < 0.1):?>
									<label class="<?php echo $warning_class ?>"> <?php echo JText::_('J2STORE_NOT_SET'); ?> </label>
								<?php else:?>
									<label class="<?php echo $success_class ?>"> <?php echo JText::_('J2STORE_OK'); ?> </label>
								<?php endif;?>
								<br/>
							</td>
							<td>
							<?php echo JText::_('J2STORE_PRODUCT_WEIGHT_CLASS').":";
							if($product->weight_class_id == 0):?>
								<label class="<?php echo $warning_class ?>"> <?php echo JText::_('J2STORE_NOT_SET'); ?> </label>
							<?php else:?>
								<label class="<?php echo $success_class ?>"> <?php echo JText::_('J2STORE_OK'); ?> </label>
							<?php endif;?>
							<br/>							
							<?php echo JText::_('J2STORE_PRODUCT_LENGTH_CLASS').":";
							if($product->length_class_id == 0):?>
								<label class="<?php echo $warning_class ?>"> <?php echo JText::_('J2STORE_NOT_SET'); ?> </label>
							<?php else:?>
								<label class="<?php echo $success_class ?>"> <?php echo JText::_('J2STORE_OK'); ?> </label>
							<?php endif;?>
							<br/>
							</td>
							<?php else:?>
							<td colspan="4">
								<?php echo JText::_('J2STORE_HAS_VARIANTS'); ?>
								<button type="button" class="btn btn-small btn-warning"
										id="showvariantbtn-<?php echo $product->j2store_product_id;?>"
										href="javascript:void(0);"
										onclick="jQuery('#hide-icon-<?php echo $product->j2store_product_id;?>').toggle('click');jQuery('#show-icon-<?php echo $product->j2store_product_id;?>').toggle('click');jQuery('#variantListTable-<?php echo $product->j2store_product_id;?>').toggle('click');">
									<?php echo JText::_('J2STORE_OPEN_CLOSE'); ?>
									<i id="show-icon-<?php echo $product->j2store_product_id;?>"
									   class="icon icon-plus"></i> <i
										id="hide-icon-<?php echo $product->j2store_product_id;?>"
										class="icon icon-minus" style="display: none;"></i>
								</button>
								<table id="variantListTable-<?php echo $product->j2store_product_id;?>"
									   class="table table-condensed table-bordered hide">
									<thead>
									<th><?php echo JText::_('J2STORE_VARIANT_NAME'); ?></th>
									<th><?php echo JText::_('J2STORE_PRODUCT_SHIPPING_ENABLED'); ?></th>
									<th><?php echo JText::_('J2STORE_PRODUCT_SHIPPING_DIMENSION'); ?></th>
									<th><?php echo JText::_('J2STORE_PRODUCT_SHIPPING_WEIGHT'); ?></th>
									<th><?php echo JText::_('J2STORE_PRODUCT_SHIPPING_CLASS'); ?></th>
									</thead>
								<tbody>
								<?php
								$variant_model = F0FModel::getTmpInstance('Variants', 'J2StoreModel');
								$variant_model->setState('product_type', $product->product_type);
								$variants = $variant_model->product_id($product->j2store_product_id)
									->is_master(0)
									->getList();
								if(isset($variants) && count($variants)):
									?>

										<?php
									foreach($variants as $variant):
										?>
										<tr>
											<td><?php echo J2Store::product()->getVariantNamesByCSV($variant->variant_name); ?></td>
										<td>
											<?php if($variant->shipping):?>
												<label class="<?php echo $success_class ?>"> <?php echo JText::_('J2STORE_ENABLED'); ?> </label>
											<?php else: ?>
												<label class="<?php echo $warning_class ?>"> <?php echo JText::_('J2STORE_DISABLED'); ?> </label>
											<?php endif; ?>
										</td>
										<td>
											<?php echo JText::_('J2STORE_LENGTH').":";
											if($variant->length < 0.1):?>
												<label class="<?php echo $warning_class ?>"> <?php echo JText::_('J2STORE_NOT_SET'); ?> </label>
											<?php else:?>
												<label class="<?php echo $success_class ?>"> <?php echo JText::_('J2STORE_OK'); ?> </label>
											<?php endif;?>
											<br/>
											<?php echo JText::_('J2STORE_WIDTH').":";
											if($variant->width < 0.1):?>
												<label class="<?php echo $warning_class ?>"> <?php echo JText::_('J2STORE_NOT_SET'); ?> </label>
											<?php else:?>
												<label class="<?php echo $success_class ?>"> <?php echo JText::_('J2STORE_OK'); ?> </label>
											<?php endif;?>
											<br/>
											<?php echo JText::_('J2STORE_HEIGHT').":";
											if($variant->height < 0.1):?>
												<label class="<?php echo $warning_class ?>"> <?php echo JText::_('J2STORE_NOT_SET'); ?> </label>
											<?php else:?>
												<label class="<?php echo $success_class ?>"> <?php echo JText::_('J2STORE_OK'); ?> </label>
											<?php endif;?>
											<br/>
										</td>
										<td><?php echo JText::_('J2STORE_PRODUCT_WEIGHT').":";
											if($variant->weight < 0.1):?>
												<label class="<?php echo $warning_class ?>"> <?php echo JText::_('J2STORE_NOT_SET'); ?> </label>
											<?php else:?>
												<label class="<?php echo $success_class ?>"> <?php echo JText::_('J2STORE_OK'); ?> </label>
											<?php endif;?>
											<br/>
										</td>
										<td>
											<?php echo JText::_('J2STORE_PRODUCT_WEIGHT_CLASS').":";
											if($variant->weight_class_id == 0):?>
												<label class="<?php echo $warning_class ?>"> <?php echo JText::_('J2STORE_NOT_SET'); ?> </label>
											<?php else:?>
												<label class="<?php echo $success_class ?>"> <?php echo JText::_('J2STORE_OK'); ?> </label>
											<?php endif;?>
											<br/>
											<?php echo JText::_('J2STORE_PRODUCT_LENGTH_CLASS').":";
											if($variant->length_class_id == 0):?>
												<label class="<?php echo $warning_class ?>"> <?php echo JText::_('J2STORE_NOT_SET'); ?> </label>
											<?php else:?>
												<label class="<?php echo $success_class ?>"> <?php echo JText::_('J2STORE_OK'); ?> </label>
											<?php endif;?>
											<br/>
										</td>
										</tr>
									<?php endforeach;?>
								<?php endif;?>
								</table>
							</td>
							<?php endif;?>
						</tr>
					<?php endforeach;?>
				</tbody>
			</table>
			
			
		<?php else:?>
		<div class="alert alert-message"><?php echo JText::sprintf('J2STORE_SHIPPING_TROUBLESHOOT_NOTE_MESSAGE','index.php?option=com_j2store&view=shippings',J2Store::buildHelpLink('support/user-guide/standard-shipping.html', 'shipping'));?></div>
		<?php endif;?>
  </form>
  </div>
  <div class="<?php echo $col_class; ?>9 center">
	<a class="fa fa-arrow-left btn btn-large btn-success " href="<?php echo JRoute::_('index.php?option=com_j2store&view=shippingtroubles&layout=default_shipping'); ?>">
		<?php echo 'Back';?>
	</a>
          </div>
            <?php if (!empty($sidebar)): ?>
         </div>
            <?php else: ?>
        </div>
    <?php endif; ?>
</div>
