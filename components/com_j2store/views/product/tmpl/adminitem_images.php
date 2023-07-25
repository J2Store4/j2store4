<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
$image_path = JUri::root();
$platform = j2store::platform();

?>
<div class="j2store-product-images j2store-product-images-<?php echo $this->product->j2store_product_id; ?>">

	<?php if($this->params->get('show_thumbnail_image', 0)):?>
	<div class="j2store-thumbnail-image">
		   <?php
	      $main_image="";
	      	$thumb_image = $platform->getImagePath($this->product->thumb_image) ;
	      ?>
		   <?php if(!empty($thumb_image)):?>

		   <?php if($this->params->get('category_link_image_to_product',0) == 1):?>
                   <a href="<?php echo $this->product->product_link; ?>">
               <?php endif; ?>
               <img alt="<?php echo (!empty($this->product->thumb_image_alt)) ? $this->escape($this->product->thumb_image_alt) : $this->escape($this->product->product_name); ?>"
                    title="<?php echo $this->escape($this->product->product_name); ?>"
                    class="j2store-img-responsive j2store-product-thumb-image-<?php echo $this->product->j2store_product_id; ?>"
                    src="<?php echo $thumb_image ; ?>"
                    width="<?php echo (int)$this->params->get('list_image_thumbnail_width', '200'); ?>"/>
               <?php if ($this->params->get('list_image_link_to_product', 1)): ?>
                   </a>
		   <?php endif;?>
		   <?php elseif(!empty($this->product->thumb_image)):?>
			   <?php echo J2Store::product()->displayImage($this->product,array('type'=>'ItemThumb','params' => $this->params, 'alt' => $this->escape($this->product->thumb_image_alt))); ?>
		   <?php endif; ?>
	</div>
	 <?php endif; ?>

	<?php if($this->params->get('show_main_image', 0)):?>
	<div class="j2store-mainimage">
		   <?php
	      $main_image="";
	      	$main_image = $platform->getImagePath($this->product->main_image);
	      ?>
		   <?php if(!empty( $main_image )):?>
		   <?php $class= $this->params->get('item_enable_image_zoom', 1) ? 'zoom': 'nozoom'; ?>

 			<?php if($this->params->get('category_link_image_to_product',0) == 1):?>
               <a href="<?php echo $this->product->product_link; ?>">
                   <?php endif; ?>
                   <img alt="<?php echo (!empty($this->product->main_image_alt)) ? $this->escape($this->product->main_image_alt) : $this->escape($this->product->product_name); ?>"
                        title="<?php echo $this->escape($this->product->product_name); ?>"
                        class="j2store-img-responsive j2store-product-main-image-<?php echo $this->product->j2store_product_id; ?>"
                        src="<?php echo $image_path . $main_image; ?>"
                        width="<?php echo (int)$this->params->get('list_image_thumbnail_width', '200'); ?>"/>
                   <?php if ($this->params->get('list_image_link_to_product', 1)): ?>
               </a>
		   	<?php endif;?>
			   <script type="text/javascript">
				   var main_image="<?php echo $image_path.$main_image ;?>";
				   j2store.jQuery(document).ready(function(){
					   var enable_zoom = <?php echo $this->params->get('item_enable_image_zoom', 1);?>;
					   if(enable_zoom){
						   j2store.jQuery('#j2store-item-main-image-<?php echo $this->product->j2store_product_id; ?>').zoom();
					   }
				   });
			   </script>
		   <?php elseif(!empty($this->product->main_image)):?>
			   <?php echo J2Store::product()->displayImage($this->product,array('type'=>'ItemMain','params' => $this->params, 'alt'=>$this->product->main_image_alt)); ?>
		   <?php endif; ?>
	</div>
	 <?php endif; ?>

	 <?php if($this->params->get('show_additional_image') && isset($this->product->additional_images) && !empty($this->product->additional_images)):?>
	 	<?php
	 		$additional_images = json_decode($this->product->additional_images);
	 		$additional_images  = array_filter((array)$additional_images);
	 		if($additional_images):
	 	?>
			<div class="j2store-product-additional-images">

				<ul class="additional-image-list">
					<?php
						$additional_images = json_decode($this->product->additional_images);
						if(isset($additional_images) && count($additional_images)):
                            $additional_images_alt = json_decode($this->product->additional_images_alt,true);
						foreach($additional_images as $key => $image):?>
						<?php
						if(JFile::exists(JPATH_SITE.'/'.$image)):
							$image_src = $image_path.$image;
							 	?>
						<li>
							<img onmouseover="setMainPreview('addimage-<?php echo $this->product->j2store_product_id; ?>-<?php echo $key;?>', <?php echo $this->product->j2store_product_id; ?>, <?php echo $this->params->get('item_enable_image_zoom', 1); ?>, 'inner')"
								 onclick="setMainPreview('addimage-<?php echo $this->product->j2store_product_id; ?>-<?php echo $key;?>', <?php echo $this->product->j2store_product_id; ?>, <?php echo $this->params->get('item_enable_image_zoom', 1); ?>, 'inner')"
								 id="addimage-<?php echo $this->product->j2store_product_id; ?>-<?php echo $key;?>"
								 class="j2store-item-additionalimage-preview j2store-img-responsive"
								 src="<?php echo $image_src;?>"
                                 alt="<?php echo (isset($additional_images_alt[$key]) && !empty($additional_images_alt[$key])) ? $this->escape($additional_images_alt[$key]) : $this->escape($this->product->product_name); ?>"
								 title="<?php echo $this->escape($this->product->product_name) ;?>"
								 />
						</li>
							<?php elseif(!empty($image)):?>
							<?php echo J2Store::product()->displayImage($this->product,array('type'=>'ViewAdditional','params' => $this->params,'key'=>$key,'image' => $image,'alt'=>(isset($additional_images_alt[$key]) && !empty($additional_images_alt[$key])) ? $additional_images_alt[$key] : $this->product->product_name)); ?>
					<?php endif;?>
					<?php endforeach;?>
						<?php if($main_image &&  JFile::exists(JPATH_SITE.'/'.$main_image)):?>
						<li>
						 <img onmouseover="setMainPreview('additial-main-image-<?php echo $this->product->j2store_product_id; ?>', <?php echo $this->product->j2store_product_id; ?>, <?php echo $this->params->get('item_enable_image_zoom', 1); ?>, 'inner')"
								  onclick="setMainPreview('additial-main-image-<?php echo $this->product->j2store_product_id; ?>', <?php echo $this->product->j2store_product_id; ?>, <?php echo $this->params->get('item_enable_image_zoom', 1); ?>, 'inner')"
								  id="additial-main-image-<?php echo $this->product->j2store_product_id; ?>"
                                    alt="<?php echo (!empty($this->product->main_image_alt)) ? $this->escape($this->product->main_image_alt) : $this->escape($this->product->product_name); ?>"
							 	  class="j2store-item-additionalimage-preview j2store-img-responsive additional-mainimage"
							 	  src="<?php echo $image_path.$main_image;?>"
							  		title="<?php echo $this->escape($this->product->product_name) ;?>"
							 	/>
						</li>
						<?php elseif (!empty($this->product->main_image)):?>
							<?php echo J2Store::product()->displayImage($this->product,array('type'=>'AdditionalMain','params' => $this->params,'alt'=>$this->escape($this->product->main_image_alt))); ?>
						<?php endif;?>
					<?php endif;?>
				</ul>
			</div>
			<?php endif;?>
		<?php endif;?>
</div>
<?php if ($this->params->get('item_enable_image_zoom', 1)) : ?>
	<script>
		j2store.jQuery(document).ready(function(){
			j2store.jQuery( 'body' ).on( 'after_doAjaxFilter', function( e, product, response ){
				j2store.jQuery('img.zoomImg').remove();
				j2store.jQuery('#j2store-item-main-image-<?php echo $this->product->j2store_product_id; ?>').zoom();
			});
		});
	</script>
<?php endif; ?>