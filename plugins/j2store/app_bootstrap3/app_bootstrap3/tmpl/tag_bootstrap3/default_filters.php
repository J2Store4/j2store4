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

$currency_object = J2Store::currency();
$currency = $currency_object->getSymbol();
$currency_position = $currency_object->getSymbolPosition();
$catid = JFactory::getApplication()->input->get('catid',array(),'Array');

$session = JFactory::getSession();
$session_manufacturer_ids = $session->get('manufacturer_ids',array(), 'j2store');
$session_vendor_ids = $session->get('vendor_ids',array(), 'j2store');
$session_productfilter_ids = $session->get('productfilter_ids',array(), 'j2store');

$currency = $this->currency->getSymbol();
$currency_value = $this->currency->getValue();
$thousand = $this->currency->getThousandSysmbol();
$decimal_place = $this->currency->getDecimalPlace();
$tagid = JFactory::getApplication()->input->getInt('tagid',0);?>
<?php
		$default_tagid ='';
		if(!empty($this->filters['filter_tag']) && count($this->filters['filter_tag'])):?>
		<?php $default_tagid = $this->filters['filter_tag'][0]->id;?>
	<?php endif;?>
<?php
	$filter_tag = isset($this->filter_tag) ? $this->filter_tag : '';
?>

<div id="j2store-product-loading" style="display:none;"></div>

<form
	action="<?php echo JRoute::_('index.php');?>"
	method="post"
	class="form-horizontal"
	id="productsideFilters"
	name="productsideFilters"
	data-link="<?php echo $this->active_menu->link.'&Itemid='.$this->active_menu->id;?>"
	enctype="multipart/form-data">
	<input type="hidden" name="filter_tag" id="filter_tag"  value ="<?php echo $filter_tag;?>" />
	<!-- Price Filters Starts Here -->
	<?php if($this->params->get('list_show_filter_price', 0) && isset($this->filters['pricefilters']) && count($this->filters['pricefilters'])): ?>
		<?php
		$min_price = $this->filters['pricefilters']['min_price'];
		$max_price = $this->filters['pricefilters']['max_price'];
		$range = $this->filters['pricefilters']['range'];
		$pricefrom = isset($this->state->pricefrom) && $this->state->pricefrom ? $this->state->pricefrom : $min_price;
		$priceto = isset($this->state->priceto) && $this->state->priceto ? $this->state->priceto : $max_price;
		$d_pricefrom = $this->currency->format($pricefrom,$this->currency->getCode(),$this->currency->getValue(),false);
		$d_priceto = $this->currency->format($priceto,$this->currency->getCode(),$this->currency->getValue(),false);
		?>
		<div id="j2store-price-filter-container" class="j2store-product-filters price-filters"  >
			<h4 class="product-filter-heading"><?php echo JText::_('J2STORE_PRODUCT_FILTER_PRICE_TITTLE'); ?></h4>
				<div  id="j2store-slider-range" style="width:100%;" ></div>
			<br/>
			<!-- Price Filters Ends Here -->
			<div id="j2store-slider-range-box" class="price-input-box" >
				<input class="btn btn-success" type="submit"   id="filterProductsBtn"  value="<?php echo JText::_('J2STORE_FILTER_GO');?>" />
					<div class="pull-right">
                        <span id="min_price" class="hide"><?php echo $pricefrom;?></span>
                        <span id="max_price" class="hide"><?php echo $priceto;?></span>
						<?php if($currency_position == 'pre') echo $currency;?><span id="min_price_display"><?php echo $d_pricefrom;?></span> <?php if($currency_position == 'post') echo $currency; ?>
						<?php echo JText::_('J2STORE_TO_PRICE');?>
						<?php if($currency_position == 'pre') echo $currency;?><span id="max_price_display"><?php echo $d_priceto;?></span><?php if($currency_position == 'post') echo $currency; ?>
						<?php echo J2Html::hidden('pricefrom',$pricefrom ,array('id'=>'min_price_input'));?>
						<?php echo J2Html::hidden('priceto',$priceto,array('id'=>'max_price_input'));?>
					</div>
			</div>
		</div>
	<?php endif;?>
	<?php echo J2Store::modules()->loadposition('j2store-tag-filter'); ?>
	<!-- Manufacturer -->
	<?php if($this->params->get('list_show_manfacturer_filter', 0)):?>
	<?php if(count($this->filters['manufacturers'])): ?>
		<!-- Brand / Manufacturer Filters -->
		<div class="j2store-product-filters manufacturer-filters">

		<div class="j2store-product-filter-title j2store-product-brand-title">
			<h4 class="product-filter-heading"><?php echo JText::_('J2STORE_PRODUCT_FILTER_BY_BRAND');?></h4>
			<span>
				<?php if(!empty($session_manufacturer_ids)):?>
					<a href="javascript:void(0);"  onclick="resetJ2storeBrandFilter();" >
						<?php echo JText::_('J2STORE_CLEAR');?>
					</a>
				<?php endif; ?>
			</span>
		</div>
			<div id="j2store-brand-filter-container" class="control-group"  >
				<?php
						$url = 'index.php?option=com_j2store&view=producttags';
					foreach($this->filters['manufacturers'] as $k => $brand):
						$checked ='';
						if(!empty($session_manufacturer_ids) &&  in_array($brand->j2store_manufacturer_id , $session_manufacturer_ids) ){
							$checked ="checked ='checked'";
						}
					?>
					<label class="j2store-product-brand-label">
						<input type="checkbox" class="j2store-brand-checkboxes" name="manufacturer_ids[]"
								id="brand-input-<?php echo $brand->j2store_manufacturer_id ;?>"
									<?php echo $checked;?>
						       	value="<?php echo $brand->j2store_manufacturer_id;?>"
						         />
					       <?php
						         //onclick="document.getElementById('j2store-product-loading').style.display='block';document.getElementById('productsideFilters').submit()"
						       ?>
						<?php echo $this->escape($brand->company);?>

					</label>
				<?php
				endforeach;?>
			</div>
		</div>
		<?php endif;?>
	<?php endif;?>

	<!-- Vendors -->
		<?php if($this->params->get('list_show_vendor_filter', 0) && !empty($this->filters['vendors'])):?>
	<div class="j2store-product-filters j2store-product-vendor-filters">
		<div class="j2store-product-filters-header">
			<h4 class="product-filter-heading"><?php echo JText::_('J2STORE_PRODUCT_FILTER_BY_VENDOR'); ?></h4>
			<?php if(!empty($session_vendor_ids)):?>
				<a href="javascript:void(0);" onclick="resetJ2storeVendorFilter();" >
					<?php echo JText::_('J2STORE_CLEAR');?>
				</a>
			<?php endif; ?>
		</div>
		<div id="j2store-vendor-filter-container" class="control-group">
		<?php foreach($this->filters['vendors'] as $key => $vendor):
				$checked ='';
				if(!empty($session_vendor_ids) && in_array( $vendor->j2store_vendor_id , $session_vendor_ids)){
					$checked ="checked ='checked'";
				}
		?>
			<label class="j2store-product-vendor-label">
				<input type="checkbox" class="j2store-vendor-checkboxes"  id="vendor-input-<?php echo $vendor->j2store_vendor_id ;?>"
				       name="vendor_ids[]"     <?php echo $checked;?>   value="<?php echo $vendor->j2store_vendor_id ;?>"  />
	  				    <!--
	  				    onclick="document.getElementById('j2store-product-loading').style.display='block';document.getElementById('productsideFilters').submit()"
	  				     onchange="jQuery('#j2store-product-loading').show();this.form.submit()" -->
				   	<?php echo $this->escape($vendor->first_name .' '.$vendor->last_name);?>
			</label>
		<?php endforeach;?>
		</div>
	</div>
	<?php endif;?>


	<?php if($this->params->get('list_show_product_filter', 0)):?>
	<!-- Product Filters  -->
	<div class="j2store-product-filters productfilters-list">
			<?php $active_class='';?>

			<?php foreach ($this->filters['productfilters'] as $pf_key => $filtergroup):?>
				<?php $filter_script_id = J2Store::utilities ()->generateId ( $filtergroup['group_name'] ).'_'.$pf_key;?>
				<div class="product-filter-group <?php echo $filter_script_id;?>">
						<h4 class="product-filter-heading"><?php echo $this->escape(JText::_($filtergroup['group_name']));?></h4>
						<span>
							<?php if($this->params->get('list_filter_productfilter_toggle',1)==1):?>
								<span id="pf-filter-icon-minus-<?php echo $filter_script_id;?>"   onclick="getPFFilterToggle('<?php echo $filter_script_id;?>');"><i class="icon-minus"></i></span>
								<span  id="pf-filter-icon-plus-<?php echo $filter_script_id;?>" onclick="getPFFilterToggle('<?php echo $filter_script_id;?>');" style="display:none;" ><i class="icon-plus"></i></span>
							<?php elseif($this->params->get('list_filter_productfilter_toggle',1)==2):?>
								<span  id="pf-filter-icon-plus-<?php echo $filter_script_id;?>"  onclick="getPFFilterToggle('<?php echo $filter_script_id;?>');" ><i class="icon-plus"></i></span>
								<span  id="pf-filter-icon-minus-<?php echo $filter_script_id;?>"   onclick="getPFFilterToggle('<?php echo $filter_script_id;?>');" style="display:none;" ><i class="icon-minus"></i></span>
							<?php endif;?>
						<?php if(!empty($session_productfilter_ids) ):?>
							<a href="javascript:void(0);"
							    data-class="j2store-pfilter-checkboxes-<?php echo $filter_script_id;?>"
							    id="product-filter-group-clear-<?php echo $filter_script_id;?>"
								style="display:none;"
								 onclick="resetJ2storeProductFilter('j2store-pfilter-checkboxes-<?php  echo $filter_script_id;?>');"
								 >
								<?php echo JText::_('J2STORE_CLEAR');?>
							</a>
							<?php endif; ?>
						</span>
				</div>

				<?php if($this->params->get('list_filter_productfilter_toggle',1)==1):?>
				<div id="j2store-pf-filter-<?php echo $filter_script_id;?>" class="control-group j2store-productfilter-list"   <?php echo 'style="display:block;"';?> >
				<?php elseif($this->params->get('list_filter_productfilter_toggle',1)==2):?>
					<div id="j2store-pf-filter-<?php echo $filter_script_id;?>" class="control-group j2store-productfilter-list"   <?php echo 'style="display:none;"';?> >
				<?php else:?>
				<div id="j2store-pf-filter-<?php echo $filter_script_id;?>" class="control-group j2store-productfilter-list">
				<?php endif;?>

					<?php foreach($filtergroup['filters'] as $i =>$filter):
						$checked ='';
						if(!empty($session_productfilter_ids) && in_array( $filter->filter_id ,$session_productfilter_ids)){
							$checked ="checked ='checked'";
						}
					?>
					<label class="j2store-productfilter-label">

						<input class="j2store-pfilter-checkboxes-<?php echo $filter_script_id;?>"
								id="j2store-pfilter-<?php echo $filter_script_id;?>-<?php echo $filter->filter_id ;?>"
								type="checkbox" name="productfilter_ids[]"
								<?php echo $checked;?>
						value="<?php echo $filter->filter_id ;?>"  />
						<!-- onclick="document.getElementById('j2store-product-loading').style.display='block';document.getElementById('productsideFilters').submit()" -->
							<?php echo $this->escape(JText::_($filter->filter_name)); ?>
					</label>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
	</div>
	<?php endif;?>

	<?php echo J2Html::hidden('option','com_j2store');?>
	<?php echo J2Html::hidden('view','producttags');?>
	<?php echo J2Html::hidden('task','browse');?>
	<?php echo J2Html::hidden('Itemid', JFactory::getApplication()->input->getUint('Itemid'));?>
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<script type="text/javascript">
/**
 * Method to Submit the form when search Btn clicked
 */
jQuery("#filterProductsBtn").on('click',function(){
	jQuery("#j2store-product-loading").show();
	jQuery("#productsideFilters").submit();
}) ;

jQuery('document').ready(function (){
	<?php foreach ($this->filters['productfilters'] as $pf_key => $filtergroup):?>
	<?php $filter_script_id = J2Store::utilities ()->generateId ( $filtergroup['group_name'] ).'_'.$pf_key;?>
	<?php foreach($filtergroup['filters'] as $i =>$filter):?>
	var size = jQuery('.j2store-pfilter-checkboxes-<?php echo $filter_script_id;?>:checked').length;
		if(size > 0){
			console.log(size);
			jQuery('#product-filter-group-clear-<?php echo $filter_script_id;?>').show();
			jQuery('#j2store-pf-filter-<?php echo $filter_script_id;?>').show();
			jQuery('#pf-filter-icon-plus-<?php echo $filter_script_id;?>').hide();
			jQuery('#pf-filter-icon-minus-<?php echo $filter_script_id;?>').show();
		}
	<?php endforeach;?>
	<?php endforeach;?>
});
</script>
<?php if($this->params->get('list_show_filter_price', 0) && isset($this->filters['pricefilters']) && count($this->filters['pricefilters'])): ?>
    <script type="text/javascript">
        //assign the values for price filters
        var min_value = jQuery( "#min_price" ).html();
        var max_value = jQuery( "#max_price" ).html();
        var format_value = <?php echo $currency_value;?>;
        function formatCurrency(format_amount) {
            if(format_amount < 0) {
                format_amount = Math.abs(format_amount);
            }

            var decimal_place = '<?php echo $decimal_place;?>';
            if(decimal_place == 0){
                format_amount = format_amount+".";
            }

            if(format_amount == ''){
                format_amount = 0.0;
            }

            format_amount = parseFloat(format_amount);
            format_amount = format_amount.toFixed(decimal_place);
            format_amount = format_amount.toString();
            var replace_string = "$1<?php echo $thousand;?>";
            format_amount = format_amount.replace(/(\d)(?=(\d{3})+\.)/g, replace_string).toString();
            format_amount = format_amount.substring(0,format_amount.length);

            //format_amount = format_amount.toFloat();

            return format_amount;
        }

        jQuery( "#max_price_display" ).html(formatCurrency(max_value*format_value));
        jQuery( "#min_price_display" ).html(formatCurrency(min_value*format_value));

        (function($) {
            $( "#j2store-slider-range" ).slider({
                range: true,
                min: <?php echo $min_price;?>,
                max: <?php echo $max_price;?>,
                values: [ min_value,max_value],
                slide: function( event, ui ) {
                    $( "#amount1" ).val( '<?php if($currency_position == 'pre') echo $currency;?>' + ui.values[ 0 ] + ' <?php if($currency_position == 'post') echo $currency;?>  - <?php if($currency_position == 'pre') echo $currency;?>' + ui.values[ 1 ] + ' <?php if($currency_position == 'post') echo $currency;?>' );
                    $( "#min_price" ).html(ui.values[ 0 ]);
                    $( "#max_price" ).html(  ui.values[ 1 ] );

                    $( "#min_price_input" ).attr('value', ui.values[ 0 ]);
                    $( "#max_price_input" ).attr('value',  ui.values[ 1 ] );

                    var min_format = ui.values[ 0 ]*format_value;
                    var max_format = ui.values[ 1 ]*format_value;
                    $( "#min_price_display" ).html(formatCurrency(min_format));
                    $( "#max_price_display" ).html(formatCurrency(max_format));
                }
            });

        })(j2store.jQuery);


    </script>
<?php endif;?>

