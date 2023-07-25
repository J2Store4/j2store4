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
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<?php	if($this->state->since || $this->state->until ||  $this->state->visible || $this->state->taxprofile_id || $this->state->vendor_id || $this->state->manufacturer_id || $this->state->productid_from || $this->state->productid_to || $this->state->pricefrom || $this->state->priceto || $this->state->visible ):?>
<div class="<?php echo $row_class;?>" id="advanced-search-controls">
<?php else:?>
<div class="<?php echo $row_class;?>" style="display: none;" id="advanced-search-controls">
<?php endif;?>

			<div class="<?php echo $col_class;?>6">
				<table class="adminlist table table-striped table-bordered table-condensed">
					<thead>
						<tr>
							<th></th>
							<th><?php echo JText::_('J2STORE_FROM');?></th>
							<th><?php echo JText::_('J2STORE_TO');?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><?php // echo JText::_('J2STORE_PRODUCT_CREATED_FROM');?></td>
							<td>
								<?php echo J2html::calendar('since',$this->state->since,array('class'=>'input-small j2store-product-filters'));?>
							</td>
							<td>
								<?php echo J2html::calendar('until',$this->state->until,array('class'=>'input-small j2store-product-filters'));?>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo JText::_('J2STORE_PRODUCT_ID');?>
							</td>
							<td><?php echo J2html::text('productid_from',$this->state->productid_from,array('class'=>'input-small j2store-product-filters'));?></td>
							<td><?php echo J2html::text('productid_to',$this->state->productid_to,array('class'=>'input-small j2store-product-filters'));?></td>
						</tr>
						<tr>
							<td>
								<?php echo JText::_('J2STORE_PRODUCT_REGULAR_PRICE');?>
							</td>
							<td><?php echo J2html::price('pricefrom',$this->state->pricefrom ,array('class'=>'input-small j2store-product-filters'));?></td>
							<td><?php echo J2html::price('priceto',$this->state->priceto,array('class'=>'input-small j2store-product-filters'));?></td>
						</tr>
					</tbody>
				</table>
		</div>
	<div class="<?php echo $col_class;?>6">
		<table class="adminlist table table-striped table-bordered table-condensed">
			<tr>
				<td colspan="2">
					<?php echo JText::_('J2STORE_PRODUCT_SKU');?>
					<?php echo J2html::text('sku',$this->state->sku,array('class'=>'input j2store-product-filters'));?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('J2STORE_PRODUCT_MANUFACTURER');?>
					<?php
						echo J2Html::select()->clearState()
							->type('genericlist')
							->name('manufacturer_id')
							->value($this->state->manufacturer_id)
							->attribs(array('class'=>'input-small j2store-product-filters','onchange'=>'this.form.submit();'))
							->setPlaceHolders(
									array(''=>JText::_('J2STORE_SELECT_OPTION'))
							)
							->hasOne('Manufacturers')
							->setRelations( array(
									'fields' => array (
											'key' => 'j2store_manufacturer_id',
											'name' => array('company')
									)
							)
							)->getHtml();
					?>
				</td>
				<td>
					<?php echo JText::_('J2STORE_PRODUCT_VENDOR');?>
					<?php
						echo J2Html::select()->clearState()
					->type('genericlist')
					->name('vendor_id')
					->value($this->state->vendor_id)
					->setPlaceHolders(array(''=>JText::_('J2STORE_SELECT_OPTION')))
					->attribs(array('class'=>'input-small j2store-product-filters','onchange'=>'this.form.submit();'))
					->hasOne('Vendors')
					->setRelations(
							array (
									'fields' => array
									(
											'key'=>'j2store_vendor_id',
											'name'=>array('first_name','last_name')
									)
							)
					)->getHtml();

					?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('J2STORE_PRODUCT_TAX_PROFILE');?>
					<?php
						echo J2Html::select()->clearState()
					->type('genericlist')
					->name('taxprofile_id')
					->value($this->state->taxprofile_id)
					->attribs(array('class'=>'input-small j2store-product-filters','onchange'=>'this.form.submit();'))
					->setPlaceHolders(array('' => JText::_('J2STORE_NOT_TAXABLE')))
					->hasOne('Taxprofiles')
					->setRelations(
							array (
									'fields' => array (
											'key'=>'j2store_taxprofile_id',
											'name'=>'taxprofile_name'
									)
							)
					)->getHtml();

					?>
				</td>
				<td>
					<?php echo JText::_('J2STORE_PRODUCT_VISIBILITY');?>
					<?php
						echo J2Html::select()->clearState()
											->type('genericlist')
											->name('visible')
											->value($this->state->visible)
											->attribs(array('class'=>'input-small j2store-product-filters','onchange'=>'this.form.submit();'))
											->setPlaceHolders(array(
																		'' => JText::_('J2STORE_SELECT_OPTION'),
																	  	1 => JText::_('J2STORE_YES'),
																		0 => JText::_('J2STORE_NO')
																	))
											->getHtml();
					?>
					</td>
				</tr>
				<tr>
				<td colspan="2">
					<?php echo J2Html::button('advanced_search',JText::_('J2STORE_APPLY_FILTER'),array('class'=>'btn btn-success' ,'onclick'=>'this.form.submit();'));?>
					<?php echo J2Html::button('reset_advanced_filters',JText::_('J2STORE_FILTER_RESET'),array('class'=>'btn btn-inverse' ,'onclick'=>'resetAdvancedFilters()'));?>
				</td>
			</tr>
		</table>
	</div>
</div>