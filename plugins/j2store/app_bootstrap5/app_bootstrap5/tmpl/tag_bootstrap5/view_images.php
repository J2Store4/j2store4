<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
$image_path = JUri::root();
$main_image="";
$main_image_width = $this->params->get('item_product_main_image_width', '200');
$additional_image_width = $this->params->get('item_product_additional_image_width', '100');
$platform = J2Store::platform();
?>
<?php if($this->params->get('item_show_product_main_image', 1) && !empty($this->product->main_image)):?>
    <div class="j2store-mainimage">
        <?php $main_image = $platform->getImagePath($this->product->main_image);?>
        <?php if(!empty($main_image)):?>
            <?php $class= $this->params->get('item_enable_image_zoom', 1) ? 'zoom': 'nozoom'; ?>

            <span class="<?php echo $class; ?>" id="j2store-item-main-image-<?php echo $this->product->j2store_product_id; ?>">
		  	 <img alt="<?php echo (!empty($this->product->main_image_alt)) ? $this->escape($this->product->main_image_alt) : $this->escape($this->product->product_name); ?>"
                  title="<?php echo $this->escape($this->product->product_name) ;?>"
                  class="j2store-product-main-image j2store-img-responsive"
                  src="<?php echo $main_image;?>"
                  width="<?php echo intval($main_image_width); ?>"
             />
		  	 </span>
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
            <?php echo J2Store::product()->displayImage($this->product,array('type'=>'ViewMain','params' => $this->params,'alt'=>$this->escape($this->product->main_image_alt))); ?>
        <?php endif; ?>
    </div>

<?php endif; ?>

<?php if( $this->params->get('item_show_product_additional_image', 1) && isset($this->product->additional_images) && !empty($this->product->additional_images)):?>
    <?php
    $additional_images = json_decode($this->product->additional_images);
    $additional_images = array_filter((array)$additional_images);
    if(count($additional_images)) :
        $additional_images_alt = json_decode($this->product->additional_images_alt,true);
        ?>
        <div class="j2store-product-additional-images">

            <ul class="additional-image-list">
                <?php
                foreach($additional_images as $key => $image):?>
                    <?php
                    $image = $platform->getImagePath($image);
                    if(!empty($image)):
                        $image_src = $image;
                        ?>
                        <li>
                            <img onmouseover="setMainPreview('addimage-<?php echo $this->product->j2store_product_id; ?>-<?php echo $key;?>', <?php echo $this->product->j2store_product_id; ?>, <?php echo $this->params->get('item_enable_image_zoom', 1); ?>, 'inner')"
                                 onclick="setMainPreview('addimage-<?php echo $this->product->j2store_product_id; ?>-<?php echo $key;?>', <?php echo $this->product->j2store_product_id; ?>, <?php echo $this->params->get('item_enable_image_zoom', 1); ?>, 'inner')"
                                 id="addimage-<?php echo $this->product->j2store_product_id; ?>-<?php echo $key;?>"
                                 class="j2store-item-additionalimage-preview j2store-img-responsive"
                                 src="<?php echo $image_src;?>"
                                 alt="<?php echo (isset($additional_images_alt[$key]) && !empty($additional_images_alt[$key])) ? $this->escape($additional_images_alt[$key]) : $this->escape($this->product->product_name); ?>"
                                 title="<?php echo $this->escape($this->product->product_name) ;?>"
                                 width="<?php echo intval( $additional_image_width); ?>"
                            />
                        </li>
                    <?php elseif(!empty($image)):?>
                        <?php echo J2Store::product()->displayImage($this->product,array('type'=>'ViewAdditional','params' => $this->params,'key'=>$key,'image' => $image, 'alt' =>(isset($additional_images_alt[$key]) && !empty($additional_images_alt[$key])) ? $this->escape($additional_images_alt[$key]) : $this->escape($this->product->product_name))); ?>
                    <?php endif;?>
                <?php endforeach;?>
                <?php if(!empty($main_image)):?>
                    <li>
                        <img onmouseover="setMainPreview('additial-main-image-<?php echo $this->product->j2store_product_id; ?>', <?php echo $this->product->j2store_product_id; ?>, <?php echo $this->params->get('item_enable_image_zoom', 1); ?>, 'inner')"
                             onclick="setMainPreview('additial-main-image-<?php echo $this->product->j2store_product_id; ?>', <?php echo $this->product->j2store_product_id; ?>, <?php echo $this->params->get('item_enable_image_zoom', 1); ?>, 'inner')"
                             id="additial-main-image-<?php echo $this->product->j2store_product_id; ?>"
                             alt="<?php echo (!empty($this->product->main_image_alt)) ? $this->escape($this->product->main_image_alt) : $this->escape($this->product->product_name); ?>"
                             title="<?php echo $this->escape($this->product->product_name);?>"
                             class="j2store-item-additionalimage-preview j2store-img-responsive additional-mainimage"
                             src="<?php echo $main_image;?>"
                             width="<?php echo intval($additional_image_width); ?>"
                        />
                    </li>
                <?php elseif (!empty($this->product->main_image)):?>
                    <?php echo J2Store::product()->displayImage($this->product,array('type'=>'AdditionalMain','params' => $this->params,'alt'=>$this->escape($this->product->main_image_alt))); ?>
                <?php endif;?>
            </ul>
        </div>
    <?php endif;?>
<?php endif;?>

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