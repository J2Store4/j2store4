<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 *
 * Bootstrap 2 layout of products
 */
// No direct access
defined('_JEXEC') or die;
$session = JFactory::getSession();
$filter_tag = isset($this->filter_tag) ? $this->filter_tag : '';
?>
<?php  $currency = $this->currency->getSymbol();?>
<form class="form-inline" id="productFilters" name="productfilters"  action="<?php echo JRoute::_('index.php');?>" data-link="<?php echo JRoute::_($this->active_menu->link.'&Itemid='.$this->active_menu->id);?>" method="post">
		<input type="hidden" name="filter_tag" id="sort_filter_tag"  value ="<?php echo $filter_tag;?>" />
		<?php if($this->params->get('list_show_filter_search')):?>
		<?php $search = htmlspecialchars($this->state->search);?>
   		<?php echo J2html::text('search',$search,array('class'=>'j2store-product-search-input'));?>
			<input  type="button" value="<?php echo JText::_('J2STORE_FILTER_GO');?>"
									class="btn btn-success"
								    onclick="jQuery(this.form).submit();" />
				<input  type="button" value="<?php echo JText::_('J2STORE_FILTER_RESET');?>"
							class="btn btn-inverse"
						    onclick="resetJ2storeFilter();" />

        <?php endif;?>
		<!-- Sorting -->
   		<?php if($this->params->get('list_show_filter_sorting')):?>
		<?php
		echo J2Html::select()->clearState()
					->type('genericlist')
					->name('sortby')
					->attribs(array('class'=>'input','onchange'=>'jQuery(this.form).submit()'))
					->value($this->state->sortby)
					->setPlaceHolders($this->filters['sorting'])->getHtml();
			?>
		<?php endif;?>

	<?php echo J2Html::hidden('option','com_j2store');?>
	<?php echo J2Html::hidden('view','producttags');?>
	<?php echo J2Html::hidden('task','browse');?>
	<?php echo J2Html::hidden('Itemid', JFactory::getApplication()->input->getUint('Itemid'));?>
	<?php echo JHTML::_( 'form.token' ); ?>
</form>

<script type="text/javascript">
function resetJ2storeFilter(){
	jQuery(".j2store-product-search-input").val("");
	jQuery("#productFilters").submit();
}
</script>