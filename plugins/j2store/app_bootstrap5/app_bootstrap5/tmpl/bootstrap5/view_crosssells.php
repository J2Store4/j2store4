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
$columns = $this->params->get('item_related_product_columns', 3);
$total = count($this->cross_sells); $counter = 0;
$cross_image_width = $this->params->get('item_product_cross_image_width', '100');
$platform = J2Store::platform();
?>

<div class="row product-crosssells-container">
	<div class="col-sm-12">
		<h3><?php echo JText::_('J2STORE_RELATED_PRODUCTS_CROSS_SELLS'); ?></h3>

				<?php foreach($this->cross_sells as $cross_sell_product):?>
					<?php
						$cross_sell_product->product_link = $platform->getProductUrl(array('task' => 'view', 'id' => $cross_sell_product->j2store_product_id));
                            //JRoute::_('index.php?option=com_j2store&view=products&task=view&id='.$cross_sell_product->j2store_product_id);
						if(!empty($cross_sell_product->addtocart_text)) {
							$cart_text = JText::_($cross_sell_product->addtocart_text);
						} else {
							$cart_text = JText::_('J2STORE_ADD_TO_CART');
						}
                        $cross_product_name = $this->escape($cross_sell_product->product_name);
					?>

					<?php $rowcount = ((int) $counter % (int) $columns) + 1; ?>
					<?php if ($rowcount == 1) : ?>
						<?php $row = $counter / $columns; ?>
						<div class="crosssell-product-row <?php echo 'row-'.$row; ?> row">
					<?php endif;?>
                    <?php $cross_sell_css=''; ?>
                    <?php if(!in_array($cross_sell_product->product_type,array('variable','flexivariable'))): ?>
                        <?php  $cross_sell_css = $cross_sell_product->params->get('product_css_class','');?>
                    <?php endif; ?>
                    <div class="col-sm-<?php echo round((12 / $columns));?> upsell-product product-<?php echo $cross_sell_product->j2store_product_id;?> <?php echo isset($cross_sell_css) ? $cross_sell_css:''; ?>  ">
						<span class="cross-sell-product-image">
                            <?php
                                $thumb_image = '';
                                if(isset($cross_sell_product->thumb_image) && $cross_sell_product->thumb_image){
                                    $thumb_image =$platform->getImagePath($cross_sell_product->thumb_image);
                                }
                            ?>
                            <?php if(isset($thumb_image) &&  !empty($thumb_image)):?>
                            <a href="<?php echo $cross_sell_product->product_link; ?>">
		   					    <img title="<?php echo $cross_product_name ;?>"
                                     alt="<?php echo $cross_product_name ;?>"
                                     class="j2store-product-thumb-image-<?php echo $cross_sell_product->j2store_product_id; ?>"
                                     src="<?php echo $thumb_image;?>"
                                     width="<?php echo intval($cross_image_width);?>"/>
                            </a>
                        <?php endif; ?>

						</span>
						<h3 class="cross-sell-product-title">
							<a href="<?php echo $cross_sell_product->product_link; ?>">
								<?php echo $cross_product_name; ?>
							</a>
						</h3>

                        <?php if( J2Store::product()->canShowprice($this->params) ): ?>
                            <?php
                            $this->singleton_product = $cross_sell_product;
                            $this->singleton_params = $this->params;
                            echo $this->loadAnyTemplate('site:com_j2store/products/price');
                            ?>
                        <?php endif; ?>

						<?php if(J2Store::product()->canShowCart($this->params)): ?>
						<?php if(count($cross_sell_product->options) || $cross_sell_product->product_type == 'variable'): ?>
							<a class="<?php echo $this->params->get('choosebtn_class', 'btn btn-success'); ?>"
								href="<?php echo $cross_sell_product->product_link; ?>">
								<?php echo JText::_('J2STORE_CART_CHOOSE_OPTIONS'); ?>
							</a>
						<?php else: ?>
						<?php
							$this->singleton_product = $cross_sell_product;
							$this->singleton_params = $this->params;
							$this->singleton_cartext = $this->escape($cart_text);
							echo $this->loadAnyTemplate('site:com_j2store/products/cart');
						?>
						<?php endif; ?>
						<?php endif; ?>
					</div>
				<?php $counter++; ?>
				<?php if (($rowcount == $columns) or ($counter == $total)) : ?>
					</div>
				<?php endif; ?>

			<?php endforeach;?>
	</div>
  </div>