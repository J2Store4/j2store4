<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
$image_path = JUri::root();
$image_type = $this->params->get('list_image_type', 'thumbnail');
$main_image = "";
$platform = J2Store::platform();
?>
<?php if ($this->params->get('list_show_image', 1)): ?>
    <div class="j2store-product-images">
        <?php if ($image_type == 'thumbimage'): ?>
            <div class="j2store-thumbnail-image">
                <?php $thumb_image = $platform->getImagePath($this->product->thumb_image) ;?>
                <?php if (!empty($thumb_image)): ?>
                    <?php if ($this->params->get('list_image_link_to_product', 1)): ?>
                        <a href="<?php echo $this->product->product_link; ?>">
                    <?php endif; ?>
                    <img alt="<?php echo (!empty($this->product->thumb_image_alt)) ? $this->escape($this->product->thumb_image_alt) : $this->escape($this->product->product_name); ?>"
                         title="<?php echo $this->escape($this->product->product_name); ?>"
                         class="j2store-img-responsive j2store-product-thumb-image-<?php echo $this->product->j2store_product_id; ?>"
                         src="<?php echo  $thumb_image; ?>"
                         width="<?php echo (int)$this->params->get('list_image_thumbnail_width', '200'); ?>"
                    />
                    <?php if ($this->params->get('list_image_link_to_product', 1)): ?>
                        </a>
                    <?php endif; ?>
                <?php elseif (!empty($this->product->thumb_image)): ?>
                    <?php echo J2Store::product()->displayImage($this->product, array('type' => 'Thumb', 'params' => $this->params,'alt'=>$this->escape($this->product->thumb_image_alt))); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($image_type == 'mainimage'): ?>
            <div class="j2store-mainimage">
                <?php $main_image = $platform->getImagePath($this->product->main_image);?>
                <?php if (!empty($main_image)): ?>
                    <?php if ($this->params->get('list_image_link_to_product', 1)): ?>
                        <a href="<?php echo $this->product->product_link; ?>">
                    <?php endif; ?>
                    <img alt="<?php echo (!empty($this->product->main_image_alt)) ? $this->escape($this->product->main_image_alt) : $this->escape($this->product->product_name); ?>"
                         title="<?php echo $this->escape($this->product->product_name); ?>"
                         class="j2store-img-responsive j2store-product-main-image-<?php echo $this->product->j2store_product_id; ?>"
                         src="<?php echo   $main_image; ?>"
                         width="<?php echo (int)$this->params->get('list_image_thumbnail_width', '200'); ?>"
                    />
                    <?php if ($this->params->get('list_image_link_to_product', 1)): ?>
                        </a>
                    <?php endif; ?>
                <?php elseif (!empty($this->product->main_image)): ?>
                    <?php echo J2Store::product()->displayImage($this->product, array('type' => 'Main', 'params' => $this->params,'alt'=> $this->escape($this->product->main_image_alt))); ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>