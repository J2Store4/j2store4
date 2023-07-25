<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access
defined('_JEXEC') or die;
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$this->params = J2Store::config();
$label_class = $platform->getLabel();
$info_class = $platform->getLabel('info');
$warning_class = $platform->getLabel('warning');
$success_class = $platform->getLabel('success');
    ?>
<table class="table table-striped table-bordered">
	<thead>
		<tr>
			<th><?php echo JText::_('J2STORE_NUM');?></th>
			<th><input type="checkbox" name="checkall-toggle" value=""
				title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
				onclick="Joomla.checkAll(this)" /></th>
			<th>
						<?php  echo JHTML::_('grid.sort',  'J2STORE_PRODUCT_ID', 'j2store_product_id',$this->state->filter_order_Dir, $this->state->filter_order ); ?>
					</th>
			<th width="30%" class="title">
						<?php  echo JText::_('J2STORE_PRODUCT_NAME'); ?>
					</th>

			<th><?php  echo JText::_('J2STORE_PRODUCT_SKU'); ?></th>
			<th><?php  echo JText::_('J2STORE_PRODUCT_PRICE'); ?></th>			
			<th><?php  echo JText::_('J2STORE_SHIPPING'); ?></th>
            <?php if($this->params->get('enable_inventory', 0)):?>
                <th><?php  echo JText::_('J2STORE_CURRENT_STOCK'); ?></th>
            <?php endif;?>
            <th><?php  echo JHTML::_('grid.sort',  'J2STORE_PRODUCT_SOURCE', 'product_source', $this->state->filter_order_Dir, $this->state->filter_order ); ?></th>
			<th><?php  echo JHTML::_('grid.sort',  'J2STORE_PRODUCT_SOURCE_ID', 'product_source_id', $this->state->filter_order_Dir, $this->state->filter_order ); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="10"><?php  echo $this->pagination->getListFooter(); ?>
					</td>
		</tr>
	</tfoot>
	<tbody>
				<?php
					if($this->products && !empty($this->products)):
					foreach($this->products as $i => $item):					
					$checked = JHTML::_('grid.id', $i, $item->j2store_product_id );

					?>
					<tr>
			<td><?php echo $this->pagination->getRowOffset( $i ); ?>
						</td>
			<td><?php echo $checked; ?>
						</td>
			<td>
							<?php echo $item->j2store_product_id;?>

						</td>
			<td>

							<?php
                            $thumbimage='';
                            $platform = J2Store::platform();
                            $thumbimage = $platform->getImagePath($item->thumb_image);
                           ?>
							 	<?php if(!empty($thumbimage )): ?>
							 	<div class="pull-left">
					<a onclick="return ! window.open(this.href);" href="<?php echo $item->product_edit_url;?>"> <img
						class="j2store-product-thumb-image"
						src="<?php echo $thumbimage;?>"
						title="<?php echo $this->escape($item->product_name);?>"
						alt="<?php echo $this->escape($item->product_name);?>" />
					</a>
				</div>
								<?php endif; ?>

									<a onclick="return ! window.open(this.href);" href="<?php echo $item->product_edit_url;?>">
					<strong>
										<?php echo $item->product_name;?>
									</strong>
			</a> <br /> <span>
									<?php echo JText::_('J2STORE_PRODUCT_TYPE')?> : <label
					class="<?php echo $info_class ?>"><?php echo $item->product_type; ?></label>
			</span> <br /> <span>
									<?php echo JText::_('J2STORE_PRODUCT_VISIBILITY')?> : <label
					class="<?php echo $label_class ?><?php echo $item->visibility ? 'success':'important'; ?>"><?php echo $item->visibility ? JText::_('JYES'):JText::_('JNO'); ?></label>
			</span>
						<?php echo JText::_('J2STORE_TAXPROFILE'); ?>: 
						
						<?php if($item->taxprofile_id):?>
							<label class="label label-inverted"><?php echo $item->taxprofile_name; ?></label>
						<?php else: ?>
							<label class="<?php echo $warning_class ?>"><?php echo JText::_('J2STORE_NOT_TAXABLE'); ?> </label>
						<?php endif; ?>
			

			</td>

                        <?php if(!in_array($item->product_type,J2Store::product()->getVariableProductTypes())):?>
						<td><?php echo $this->escape($item->sku); ?></td>
					<td> <?php echo J2store::currency()->format($item->price); ?></td>
					
			
						<td>
							<?php if($item->shipping):?>
							<label class="<?php echo $success_class ?>"> <?php echo JText::_('J2STORE_ENABLED'); ?> </label>
							<?php else: ?>
							<label class="<?php echo $warning_class ?>"> <?php echo JText::_('J2STORE_DISABLED'); ?> </label>
							<?php endif; ?>
						</td>
                        <?php if($this->params->get('enable_inventory')):?>
                            <td>
								<?php if($item->manage_stock == 1): ?>
									<?php echo $item->quantity; ?>
								<?php else : ?>
									<?php echo JText::_('J2STORE_NO_STOCK_MANAGEMENT'); ?>
								<?php endif; ?>
							</td>
                            <?php endif;?>
						<?php else:?>
                        <?php $enable_inventory = $this->params->get('enable_inventory'); ?>
                        <?php $colspan = (isset($enable_inventory)) && !empty($enable_inventory) ? 4 : 3 ; ?>
						<td colspan="<?php echo $colspan; ?>">
                            <?php if(in_array($item->product_type,J2Store::product()->getVariableProductTypes())):?>
							<?php echo JText::_('J2STORE_HAS_VARIANTS'); ?>
							<button type="button" class="btn btn-small btn-warning"
					id="showvariantbtn-<?php echo $item->j2store_product_id;?>"
					href="javascript:void(0);"
					onclick="jQuery('#hide-icon-<?php echo $item->j2store_product_id;?>').toggle('click');jQuery('#show-icon-<?php echo $item->j2store_product_id;?>').toggle('click');jQuery('#variantListTable-<?php echo $item->j2store_product_id;?>').toggle('click');">
								<?php echo JText::_('J2STORE_OPEN_CLOSE'); ?>
								<i id="show-icon-<?php echo $item->j2store_product_id;?>"
						class="icon icon-plus"></i> <i
						id="hide-icon-<?php echo $item->j2store_product_id;?>"
						class="icon icon-minus" style="display: none;"></i>
				</button>
				<table id="variantListTable-<?php echo $item->j2store_product_id;?>"
					class="table table-condensed table-bordered" style="display: none;">

					<thead>
						<th><?php echo JText::_('J2STORE_VARIANT_NAME'); ?></th>
						<th><?php echo JText::_('J2STORE_VARIANT_SKU'); ?></th>
						<th><?php echo JText::_('J2STORE_VARIANT_PRICE'); ?></th>
						<th><?php echo JText::_('J2STORE_PRODUCT_ENABLE_SHIPPING'); ?></th>
						<th><?php echo JText::_('J2STORE_CURRENT_STOCK'); ?></th>
					</thead>
					<tbody>
										<?php
										$variant_model = F0FModel::getTmpInstance('Variants', 'J2StoreModel');
										$variant_model->setState('product_type', $item->product_type);
										$variants = $variant_model->product_id($item->j2store_product_id)
													->is_master(0)
													->getList();
										if(isset($variants) && count($variants)):
										foreach($variants as $variant):
										?>
										<tr class="variants-list">
							<td><?php echo J2Store::product()->getVariantNamesByCSV($variant->variant_name); ?></td>
							<td><?php echo $variant->sku; ?></td>
							<td><?php echo J2store::currency()->format($variant->price); ?></td>
							<td><?php echo (isset($variant->shipping) && ($variant->shipping)) ? JText::_('J2STORE_YES') : JText::_('J2STORE_NO'); ?></td>
							<td><?php echo $variant->quantity;?></td>
						</tr>
										<?php endforeach;?>
										<?php else:?>
										<tr>
							<td colspan="5"><?php echo JText::_('J2STORE_NO_ITEMS_FOUND')?></td>
						</tr>
										<?php endif;?>
									</tbody>
				</table>
							<?php endif;?>
						</td>
						<?php endif;?>

						<td>
						<?php echo  $item->product_source;?>
						<br />						
						<?php if($item->product_source == 'com_content' && ($item->source->state == 0 || $item->source->state == -2)): ?>
							<?php
							$state_array = array (
									'-2' => array('important', 'JTRASHED'),
									'0' => array('warning', 'JUNPUBLISHED'),
									'1' => array('success', 'JPUBLISHED') 
							);
							?>							
							<label class="<?php echo $label_class ?><?php echo $state_array[$item->source->state][0]; ?>">
								<?php echo JText::_($state_array[$item->source->state][1]);?>
							</label>							
						<?php endif;?>
						
						</td>
			<td><?php echo $item->product_source_id;?></td>
		</tr>
				<?php endforeach;?>
				<?php else:?>
				<tr>
			<td colspan="10"><?php  echo JText::_('J2STORE_NO_ITEMS_FOUND');?></td>
		</tr>
				<?php endif;?>
			</tbody>
</table>