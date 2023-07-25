<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
$image_counter = 0;
$joomla_version = 0;
//because JVERSION value 3.5.0-beta
// so we have to substring
$version =  substr(JVERSION, 0,5);
$media_path = JPATH_SITE."/media/media/js/mediafield.min.js";
if(JFile::exists ( $media_path )){
    J2Store::platform()->addScript('j2store-mediafield','/media/media/js/mediafield.min.js');
	//JFactory::getDocument()->addScript(JUri::root(true).'/media/media/js/mediafield.min.js');
    $joomla_version = 1;
}
if(version_compare(JVERSION,'3.99.99','ge')){
    $joomla_version = 4;
}elseif (version_compare($version, '3.5.0', 'ge') && version_compare($version, '3.6.3', 'lt') || $joomla_version === 1){
	$joomla_version = 1;
}else{
	$joomla_version = 0;
}
?>

<div class="j2store-product-images">
    <div class="row-fluid">
        <div class="span12">
            <div class="control-group">
                <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_THUMB_IMAGE'), 'thumb_image', array('control-label')); ?>
                <?php echo J2Html::media($this->form_prefix . '[thumb_image]', $this->item->thumb_image, array('id' => 'thumb_image', 'image_id' => 'input-thumb-image', 'no_hide' => '')); ?>
            </div>
            <div class="control-group">
                <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_THUMB_IMAGE_ALT_TEXT'), 'thumb_image_alt', array('control-label')); ?>
                <?php echo J2Html::text($this->form_prefix . '[thumb_image_alt]', $this->item->thumb_image_alt, array('id' => 'thumb_image_alt')); ?>
            </div>
            <div class="control-group">
                <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_MAIN_IMAGE'), 'main_image', array('control-label')); ?>
                <?php echo J2Html::media($this->form_prefix . '[main_image]', $this->item->main_image, array('id' => 'main_image', 'image_id' => 'input-main-image', 'no_hide' => '')); ?>
                <?php echo J2Html::hidden($this->form_prefix . '[j2store_productimage_id]', $this->item->j2store_productimage_id); ?>
            </div>
            <div class="control-group">
                <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_MAIN_IMAGE_ALT_TEXT'), 'main_image_alt', array('control-label')); ?>
                <?php echo J2Html::text($this->form_prefix . '[main_image_alt]', $this->item->main_image_alt, array('id' => 'main_image_alt')); ?>
            </div>
            <table id="additionalImages" class="table table-bordered table-striped table-condensed">
                <thead>
                <tr>
                    <td colspan="3">
                        <div class="pull-right">
                            <input type="button" id="addImagBtn" class="btn btn-success"
                                   value="<?php echo JText::_('J2STORE_PRODUCT_ADDITIONAL_IMAGES_ADD') ?>"/>
                        </div>
                    </td>
                </tr>
                </thead>
                <tr>
                    <th>
                        <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_ADDITIONAL_IMAGE'), 'additioanl_image_label'); ?>
                    </th>
                    <th>
                        <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_ADDITIONAL_IMAGE_ALT_TEXT'), 'additioanl_image_label'); ?>
                    </th>
                    <th>
                        <?php echo JText::_('J2STORE_DELETE'); ?>
                    </th>
                </tr>
                <?php
                if (isset($this->item->additional_images) && !empty($this->item->additional_images)):?>
                    <?php
                    $add_image = json_decode($this->item->additional_images);
                    $add_image_alt = json_decode($this->item->additional_images_alt,true);
                    ?>
                <?php endif;
                if (isset($add_image) && !empty($add_image)):
                    foreach ($add_image as $key => $img):?>
                        <tbody class="tr-additional-image" id="additional-image-<?php echo $key; ?>">
                        <tr>
                            <td colspan="1">
                                <?php echo J2Html::media($this->form_prefix . '[additional_images][' . $key . ']', $img, array('id' => 'additional_image_' . $key, 'class' => 'image-input', 'image_id' => 'input-additional-image-' . $key, 'no_hide' => '')); ?>
                            </td>
                            <td>
                                <?php echo J2Html::text($this->form_prefix . '[additional_images_alt][' . $key . ']', isset($add_image_alt[$key])?$add_image_alt[$key]:'', array('id' => 'additional_image_alt_' . $key)); ?>
                            </td>
                            <td>
                                <input type="button" onclick="deleteImageRow(this)" class="btn btn-success"
                                       value="<?php echo JText::_('J2STORE_DELETE') ?>"/>
                            </td>
                        </tr>
                        </tbody>
                        <?php
                        if ($key >= $image_counter)
                        {
                            $image_counter = $key;
                        }
                        $image_counter++;
                        ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tbody class="tr-additional-image" id="additional-image-0">
                    <tr>
                        <td colspan="1">
                            <?php echo J2Html::media($this->form_prefix . '[additional_images][0]', '', array('id' => 'additional_image_0', 'class' => 'image-input', 'image_id' => 'input-additional-image-0', 'no_hide' => '')); ?>
                        </td>
                        <td>
                            <?php echo J2Html::text($this->form_prefix . '[additional_images_alt][0]', '', array('id' => 'additional_image_alt_0')); ?>
                        </td>
                        <td><input type="button" onclick="deleteImageRow(this)" class="btn btn-success"
                                   value="<?php echo JText::_('J2STORE_DELETE') ?>"/></td>
                    </tr>
                    </tbody>
                <?php endif; ?>
                <!-- DO NOT DELETE - START - HTML needed for the add additional image script -->
                <input type="hidden" id="additional_image_counter" name="additional_image_counter"
                       value="<?php echo $image_counter; ?>"/>
                <tbody class="tr-additional-image" id="additional-image-template" style="display: none;">
                <tr>
                    <td colspan="1">
                        <?php echo J2Html::media('additional_image_tmpl', '', array('id' => 'additional_image_', 'class' => 'image-input', 'image_id' => 'input-additional-image-', 'no_hide' => '')); ?>
                    </td>
                    <td>
                        <?php echo J2Html::text('additional_images_alt_tmpl', '', array('id' => 'additional_image_alt_', 'class' => 'image-alt-text')); ?>
                    </td>
                    <td><input type="button" onclick="deleteImageRow(this)" class="btn btn-success"
                               value="<?php echo JText::_('J2STORE_DELETE') ?>"/></td>
                </tr>
                </tbody>
                <!-- DO NOT DELETE - END - HTML needed for the additional image script -->
            </table>

        </div>
    </div>

    <div class="alert alert-info">
        <h4><?php echo JText::_('J2STORE_QUICK_HELP'); ?></h4>
        <h5><?php echo JText::_('J2STORE_FEATURE_AVAILABLE_IN_J2STORE_PRODUCT_LAYOUTS_AND_ARTICLES'); ?></h5>
        <p><?php echo JText::_('J2STORE_PRODUCT_IMAGES_HELP_TEXT'); ?></p>
    </div>
</div>
<script type="text/javascript">

    function deleteImageRow(element) {
        (function ($) {
            var tbody = $(element).closest('.tr-additional-image');

            if ($(".tr-additional-image").length == 2) {
                // reset the last item
                var image_div = jQuery("#additional-image-template");
                addAdditionalImage(image_div, 0, '<?php echo $joomla_version;?>');
                jQuery("#additional-image-0").addClass('hide');
            }
            tbody.remove();
        })(j2store.jQuery);
    }

    var counter = <?php echo $image_counter;?>;

    jQuery("#addImagBtn").click(function () {
        counter = jQuery("#additional_image_counter").val();
        counter++;
        (function ($) {
            var image_div = jQuery("#additional-image-template");
            addAdditionalImage(image_div, counter, '<?php echo $joomla_version;?>');
        })(j2store.jQuery);
        jQuery("#additional_image_counter").val(counter);
    })

    function addAdditionalImage(image_div, counter, joomla_version) {
        (function ($) {
            //increament the
            var clone = image_div.clone();
            clone.attr('id', 'additional-image-' + counter);
            //need to change the input name
            clone.find('.j2store-media-slider-image-preview').each(function () {
                $(this).attr('src', '<?php echo JUri::root() . 'media/j2store/images/common/no_image-100x100.jpg'; ?>');
                if ($('#input-additional-image-' + counter).html() == '') {
                    $(this).attr("id", 'input-additional-image-' + counter);
                }
            });
            clone.find(':text').each(function () {
                var is_alt_text = $(this).hasClass('image-alt-text');
                var input_name = (is_alt_text) ? 'additional_images_alt' : 'additional_images';
                $(this).attr("name", "<?php echo $this->form_prefix ?>[" + input_name + "][" + counter + "]");
                $(this).attr("value", '');
                $(this).attr("id", 'jform_image_additional_image_' + counter);
                $(this).attr("image_id", 'input-additional-image-' + counter);
                if (joomla_version == 1 || joomla_version == 4) {
                    $(this).attr("onchange", 'previewImage(this,jform_image_additional_image_' + counter + ')');
                }
            });
            clone.removeClass('hide');
            //remove joomla 3.5
            if (joomla_version == 0) {
                clone.find('.modal').each(function () {
                    $(this).attr('href', 'index.php?option=com_media&view=images&tmpl=component&asset=1&author=673&fieldid=jform_image_additional_image_' + counter + '&folder=');
                });
            } else if (joomla_version == 1) {
                //for joomla 3.5
                clone.append('<script src="<?php echo JUri::root(true) . '/media/media/js/mediafield.min.js'?>" type="text\/javascript"><\/script>');
            }
            //to chang label id
            var new_html = image_div.before(clone);
            //now it is placed just of the image div so remove the element
            var processed_html = clone.remove();
            //get the newly added tbody and insert after the additional-image-0
            $(processed_html).insertAfter($('#additionalImages tbody:last-child'));
            $(processed_html).show();
            // intialize squeeze box again for edit button to work
            // no need in joomla 3.5
            if (joomla_version == 0) {
                //window.parent.SqueezeBox.initialize({});
                //window.parent.SqueezeBox.assign($('a.modal'), {
                //	parse: 'rel'
                //});
                SqueezeBox.initialize({});
                SqueezeBox.assign($('#additional-image-' + counter + ' a.modal'), {
                    parse: 'rel'
                });
            }
        })(j2store.jQuery);
    }
</script>