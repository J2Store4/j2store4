<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 *
 * Bootstrap 2 layout of product detail
 */
// No direct access
defined('_JEXEC') or die;
$columns = $this->params->get('related_product_columns', 3);
$total = count($this->cross_sells); $counter = 0;
// get j2Store Params to determine which bootstrap version we're using - Waseem Sadiq (waseem@bulletprooftemplates.com)
$J2gridRow = ($this->params->get('bootstrap_version', 2) == 2) ? 'row-fluid' : 'row';
$J2gridCol = ($this->params->get('bootstrap_version', 2) == 2) ? 'span' : 'col-md-';
?>

<div class="<?php echo $J2gridRow; ?> product-crosssells-container">
	<div class="<?php echo $J2gridCol; ?>12">
		<h3><?php echo JText::_('J2STORE_RELATED_PRODUCTS_CROSS_SELLS'); ?></h3>

				<?php foreach($this->cross_sells as $cross_sell_product):?>
					

					<?php $rowcount = ((int) $counter % (int) $columns) + 1; ?>
					<?php if ($rowcount == 1) : ?>
						<?php $row = $counter / $columns; ?>
						<div class="cross-sell-product-row <?php echo 'row-'.$row; ?> <?php echo $J2gridRow; ?>">
					<?php endif;?>

					<div class="<?php echo $J2gridCol.round((12 / $columns));?> crosssell-product product-<?php echo $cross_sell_product->j2store_product_id;?> <?php echo $cross_sell_product->params->get('product_css_class','');?>">

						<?php
							$thumb_image = '';
							if(isset($cross_sell_product->thumb_image) && $cross_sell_product->thumb_image){
	      					$thumb_image = $cross_sell_product->thumb_image;
	      					}


	      				?>
		   				<?php if(isset($thumb_image) &&  JFile::exists(JPATH::clean(JPATH_SITE.'/'.$thumb_image))):?>
		   				<span class="cross-sell-product-image">
		   					<a href="<?php echo $cross_sell_product->product_view_url; ?>">
		   						<img alt="<?php echo $this->escape($cross_sell_product->product_name) ;?>" class="j2store-product-thumb-image-<?php echo $cross_sell_product->j2store_product_id; ?>"  src="<?php echo JUri::root().JPath::clean($thumb_image);?>" />
		   					</a>
		   				</span>
					   	<?php endif; ?>

						<h3 class="cross-sell-product-title">
							<a href="<?php echo $cross_sell_product->product_view_url; ?>">
								<?php echo $this->escape($cross_sell_product->product_name); ?>
							</a>
						</h3>
                        <?php if( J2Store::product()->canShowprice($this->params) ): ?>
                            <?php
                            $this->product = $cross_sell_product;
                            echo $this->loadAnyTemplate('site:com_j2store/product/item_price');
                            ?>
                        <?php endif; ?>
					<?php if( J2Store::product()->canShowCart($this->params) ): ?>
						<?php
							$this->singleton_product = $cross_sell_product;
							$this->singleton_params = $this->params;						
							echo $this->loadAnyTemplate('site:com_j2store/product/cart');
						?>
						<?php endif; ?>
					</div>
				<?php $counter++; ?>
				<?php if (($rowcount == $columns) or ($counter == $total)) : ?>
					</div>
				<?php endif; ?>

			<?php endforeach;?>
	</div>
</div>