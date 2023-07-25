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
?>
<table class="adminlist table table-striped table-condensed">
	<tr>
		<td>
			<?php $search = htmlspecialchars($this->state->search);?>
			<div class="input-prepend">
			<span class="add-on"><?php echo JText::_( 'J2STORE_FILTER_SEARCH' ); ?></span>
			<?php echo  J2Html::text('search',$search,array('id'=>'search' ,'class'=>'input j2store-product-filters'));?>

			<?php  echo  J2Html::button('go',JText::_( 'J2STORE_FILTER_GO' ) ,array('class'=>'btn btn-success','onclick'=>'this.form.submit();'));?>
			<?php  echo  J2Html::button('reset',JText::_( 'J2STORE_FILTER_RESET' ),array('id'=>'reset-filter-search','class'=>'btn btn-inverse',"onclick"=>"jQuery('#search').val('');this.form.submit();"));?>
			</div>
		</td>
		<td>
			<div class="input-prepend">
				<span class="add-on">
					<?php echo J2html::label(JText::_('J2STORE_PRODUCT_TYPE') ,'product_type',array('class'=>'control-label'));?>
				</span>
				<?php
							echo J2Html::select()->clearState()
							->type('genericlist')
							->name('product_type')
							->attribs(array('class'=>'input-small j2store-product-filters','onchange'=>'this.form.submit();'))
							->value($this->state->product_type)
							->setPlaceHolders($this->product_types)
							->getHtml();
						?>
				</div>
		</td>
		<td>
			<?php echo J2Html::button('reset',JText::_( 'J2STORE_FILTER_RESET_ALL' ),array('id'=>'reset-all-filter','class'=>'btn btn-danger' ,'onclick'=>'j2storeResetAllFilters();'));?>
		</td>
		<td>
			<?php	if($this->state->since || $this->state->until ||  $this->state->visible || $this->state->taxprofile_id || $this->state->vendor_id || $this->state->manufacturer_id || $this->state->productid_from || $this->state->productid_to || $this->state->pricefrom || $this->state->priceto || $this->state->visible ):?>
						<input id="hideBtnAdvancedControl" class="btn btn-inverse" type="button" onclick="jQuery('#advanced-search-controls').toggle('click');jQuery(this).toggle('click');jQuery('#showBtnAdvancedControl').toggle('click');" value="<?php echo JText::_('J2STORE_HIDE_FILTER_ADVANCED')?>"/>
						<input id="showBtnAdvancedControl" class="btn btn-success" type="button" onclick="jQuery('#advanced-search-controls').toggle('click');jQuery(this).toggle('click');jQuery('#hideBtnAdvancedControl').toggle('click');" value="<?php echo JText::_('J2STORE_SHOW_FILTER_ADVANCED')?>" style="display:none;" />
					<?php else:?>
					<input id="hideBtnAdvancedControl" class="btn btn-inverse" type="button" onclick="jQuery('#advanced-search-controls').toggle('click');jQuery(this).toggle('click');jQuery('#showBtnAdvancedControl').toggle('click');" value="<?php echo JText::_('J2STORE_HIDE_FILTER_ADVANCED')?>"  style="display:none;"/>
					<input id="showBtnAdvancedControl" class="btn btn-success" type="button" onclick="jQuery('#advanced-search-controls').toggle('click');jQuery(this).toggle('click');jQuery('#hideBtnAdvancedControl').toggle('click');" value="<?php echo JText::_('J2STORE_SHOW_FILTER_ADVANCED')?>" />
			<?php endif;?>
		</td>
		<td><?php echo $this->pagination->getLimitBox();?></td>
	</tr>
</table>

<script type="text/javascript">
	function j2storeResetAllFilters(){
		jQuery(".j2store-product-filters").each(function(index){
			jQuery(this).val('');
		});
		jQuery("#search").val('');
		jQuery("#j2store_product_type").val('');
		jQuery("#adminForm").submit();
	}
	function resetAdvancedFilters(){
		jQuery("#advanced-search-controls .j2store-product-filters").each(function(index){
			jQuery(this).val('');
		});
		jQuery("#adminForm").submit();
	}
</script>
