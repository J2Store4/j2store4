<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access
defined('_JEXEC') or die;
require_once JPATH_ADMINISTRATOR . '/components/com_j2store/library/popup.php';
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$variant_list = isset($vars->product->variants) ? $vars->product->variants : new stdClass();
$this->weights = isset($vars->product->weights) ? $vars->product->weights : new stdClass();
$this->lengths = isset($vars->product->lengths) ? $vars->product->lengths : new stdClass();
$this->variant_pagination = isset($vars->product->variant_pagination) ? $vars->product->variant_pagination : new stdClass();
$but_class = 'btn-sm';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $but_class = '';
}
?>
<?php if (isset($vars->reinitialize) && $vars->reinitialize): ?>
    <script src="<?php echo JURI::root(true) . '/media/media/js/mediafield.min.js'; ?>"></script>
    <script>
        (function ($) {
            $(document).ready(function () {
                SqueezeBox.initialize({});
                SqueezeBox.assign($('a.modal'), {
                    parse: 'rel'
                });
            });
        })(jQuery);

    </script>
<?php endif; ?>
    <div class="bs-example j2store-advancedvariants-settings">
        <div class="panel-group" id="accordion">
            <?php if (isset($variant_list)): ?>
            <?php $this->i = 0; ?>
            <?php $this->canChange = 1; ?>
            <?php foreach ($variant_list

            as $variant): ?>
            <?php $this->variant = $variant;
            if ($variant->is_master == 1) {
                continue;
            }
            $prefix = $vars->form_prefix . '[variable][' . $this->variant->j2store_variant_id . ']';

            $param_data = $platform->getRegistry($variant->params);
            $variant_main_image = $param_data->get('variant_main_image', '');
            $is_main_as_thum = $param_data->get('is_main_as_thum', 0);
            ?>
            <?php // echo $this->loadTemplate('advancedvariantoptions'); ?>
            <div class="panel panel-default j2store-panel-default"
                 data-variant-id="<?php echo $this->variant->j2store_variant_id; ?>">
                <div class="panel-heading">
                    <h4 class="panel-title">
                        <div class="pull-right">
                            <?php if ($this->variant->isdefault_variant): ?>
                                <a id="default-variant-<?php echo $this->variant->j2store_variant_id; ?>"
                                   class="btn btn-micro hasTooltip <?php echo $but_class; ?>" title=""
                                   onclick="return listVariableItemTask(<?php echo $this->variant->j2store_variant_id; ?>,'unsetDefault',<?php echo $this->variant->product_id; ?>)"
                                   href="javascript:void(0);" data-original-title="UnSet default">
                                    <i class="icon-featured"></i>
                                </a>
                            <?php else: ?>
                                <a id="default-variant-<?php echo $this->variant->j2store_variant_id; ?>"
                                   class="btn btn-micro hasTooltip <?php echo $but_class; ?> " title=""
                                   onclick="return listVariableItemTask(<?php echo $this->variant->j2store_variant_id; ?>,'setDefault',<?php echo $this->variant->product_id; ?>)"
                                   href="javascript:void(0);" data-original-title="Set default">
                                    <i class="icon-unfeatured"></i>
                                </a>
                            <?php endif; ?>

                            <a class="btn btn-danger btn-small"
                               onclick="deleteVariant(<?php echo $this->variant->j2store_variant_id; ?>)"
                               href="javascript:void(0);">
                                <i class="icon icon-trash"></i>
                            </a>

                        </div>
                        #<?php echo $this->variant->j2store_variant_id; ?>--
                        <a data-toggle="collapse" data-parent="#accordion"
                           href="#collapse<?php echo $this->variant->j2store_variant_id; ?>">
                            <?php echo J2Store::product()->getVariantNamesByCSV($this->variant->variant_name); ?>
                            <i
                                    id="icon-downarrow-<?php echo $this->variant->j2store_variant_id; ?>"
                                    class="icon-arrow-down"
                                    onclick="jQuery('#icon-uparrow-<?php echo $this->variant->j2store_variant_id; ?>').toggle('click');jQuery('#icon-downarrow-<?php echo $this->variant->j2store_variant_id; ?>').toggle('click');"
                                    data-toggle-tag="icon-uparrow-<?php echo $this->variant->j2store_variant_id; ?>"></i>
                            <i
                                    id="icon-uparrow-<?php echo $this->variant->j2store_variant_id; ?>"
                                    onclick="jQuery('#icon-downarrow-<?php echo $this->variant->j2store_variant_id; ?>').toggle('click');jQuery('#icon-uparrow-<?php echo $this->variant->j2store_variant_id; ?>').toggle('click');"
                                    class="icon-arrow-up"
                                    data-toggle-tag="icon-downarrow-<?php echo $this->variant->j2store_variant_id; ?>"
                                    style="display:none;"
                            >
                            </i>
                        </a>


                    </h4>

                </div>
                <div id="collapse<?php echo $this->variant->j2store_variant_id; ?>"
                     class="panel-collapse collapse">
                    <div class="panel-body">
                        <div class="row-fluid">
                            <div class="span2">
                                <div class="j2store-product-general">
                                    <div class="control-group">
                                        <?php echo J2Html::hidden($prefix . '[j2store_variant_id]', $this->variant->j2store_variant_id, array('class' => 'input-small', 'id' => 'variant_' . $this->variant->j2store_variant_id)); ?>
                                        <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_SKU'), 'sku', array('class' => 'control-label')); ?>
                                        <?php echo J2Html::text($prefix . '[sku]', $this->variant->sku, array('class' => 'input-small', 'id' => 'sku_' . $this->variant->j2store_variant_id)); ?>
                                    </div>
                                    <div class="control-group">
                                        <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_UPC'), 'upc', array('class' => 'control-label')); ?>
                                        <?php echo J2Html::text($prefix . '[upc]', $this->variant->upc, array('class' => 'input-small', 'id' => 'upc_' . $this->variant->j2store_variant_id)); ?>
                                    </div>
                                </div>
                                <div class="control-group">
                                    <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_REGULAR_PRICE'), 'price', array('class' => 'control-label')); ?>
                                    <?php echo J2Html::price($prefix . '[price]', $this->variant->price, array('class' => 'input-small', 'id' => 'price_' . $this->variant->j2store_variant_id)); ?>
                                </div>

                                <div class="control-group">
                                    <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_SET_ADVANCED_PRICING'), 'sale_price', array('class' => 'control-label')); ?>
                                    <?php $url = "index.php?option=com_j2store&view=products&task=setproductprice&variant_id=" . $this->variant->j2store_variant_id . "&layout=productpricing&tmpl=component"; ?>
                                    <?php echo J2StorePopup::popup($url, JText::_("J2STORE_PRODUCT_SET_PRICES"), array('class' => 'btn btn-success btn-small')); ?>
                                </div>
                                <div class="control-group">
                                    <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_PRICING_CALCULATOR'), 'pricing_calculator', array('class' => 'control-label input-small')); ?>
                                    <?php
                                    //pricing options
                                    echo J2Html::select()->clearState()
                                        ->type('genericlist')
                                        ->name($prefix . '[pricing_calculator]')
                                        ->value($this->variant->pricing_calculator)
                                        ->attribs(array('id' => 'pricing_calculator_' . $this->variant->j2store_variant_id, 'class' => 'input-small'))
                                        ->setPlaceHolders(J2Store::product()->getPricingCalculators())
                                        ->getHtml();
                                    ?>
                                </div>
                                <?php echo J2Store::plugin()->eventWithHtml('AfterDisplayVariableProductForm', array(&$this->variant, $prefix)); ?>

                            </div>
                            <div class="span6">
                                <div class="control-group">
                                    <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_ENABLE_SHIPPING'), 'shipping', array('class' => 'control-label')); ?>
                                    <?php //  echo J2Html::radio($prefix.'[shipping]', $this->variant->shipping,array('class'=>'controls' ,'id'=> 'shipping-'.$this->variant->j2store_variant_id)); ?>
                                    <?php
                                    //pricing options
                                    echo J2Html::select()->clearState()
                                        ->type('genericlist')
                                        ->name($prefix . '[shipping]')
                                        ->value($this->variant->shipping)
                                        ->attribs(array('id' => 'shipping_' . $this->variant->j2store_variant_id, 'class' => 'input-small'))
                                        ->setPlaceHolders(array(1 => JText::_('J2STORE_YES'), 0 => JText::_('J2STORE_NO')))
                                        ->getHtml();
                                    ?>
                                </div>
                                <div class="control-group">
                                    <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_DIMENSIONS'), 'dimensions', array('class' => 'control-label')); ?>
                                    <?php echo J2Html::text($prefix . '[length]', $this->variant->length, array('class' => 'input-mini')); ?>
                                    <?php echo J2Html::text($prefix . '[width]', $this->variant->width, array('class' => 'input-mini')); ?>
                                    <?php echo J2Html::text($prefix . '[height]', $this->variant->height, array('class' => 'input-mini')); ?>
                                </div>
                                <div class="control-group">
                                    <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_LENGTH_CLASS'), 'length_class', array('class' => 'control-label')); ?>
                                    <?php // echo $this->lengths ;
                                    $default_length = empty($this->variant->length_class_id) ? J2Store::config()->get('config_length_class_id') : $this->variant->length_class_id;
                                    echo J2Html::select()->clearState()
                                        ->type('genericlist')
                                        ->name($prefix . '[length_class_id]')
                                        ->value($default_length)
                                        ->attribs(array('id' => 'length_class_' . $this->variant->j2store_variant_id, 'class' => 'input-small'))
                                        ->setPlaceHolders($this->lengths)
                                        ->getHtml();
                                    ?>
                                </div>
                                <div class="control-group form-inline">
                                    <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_WEIGHT'), 'weight', array('class' => '')); ?>
                                    <?php echo J2Html::text($prefix . '[weight]', $this->variant->weight, array('class' => 'input-small')); ?>
                                    <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_WEIGHT_CLASS'), 'weight_class'); ?>
                                    <?php
                                    $default_weight = empty($this->variant->weight_class_id) ? J2Store::config()->get('config_weight_class_id') : $this->variant->weight_class_id;
                                    echo J2Html::select()->clearState()
                                        ->type('genericlist')
                                        ->name($prefix . '[weight_class_id]')
                                        ->value($default_weight)
                                        ->attribs(array('id' => 'weight_class_' . $this->variant->j2store_variant_id, 'class' => 'input-small'))
                                        ->setPlaceHolders($this->weights)
                                        ->getHtml();
                                    ?>
                                </div>
                                <div class="control-group">
                                    <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_MAIN_IMAGE'), 'main_image', array('class' => 'control-label')); ?>
                                    <?php echo J2Html::media($prefix . '[params][variant_main_image]', $variant_main_image, array('id' => 'variant_main_image' . $this->variant->j2store_variant_id, 'image_id' => 'input-variant-main-image' . $this->variant->j2store_variant_id)); ?>
                                    <input id="variant_thum_<?php echo $this->variant->j2store_variant_id; ?>"
                                           type="checkbox"
                                           name="<?php echo $prefix . '[params][is_main_as_thum]'; ?>"
                                        <?php echo (isset($is_main_as_thum) && $is_main_as_thum) ? 'checked="checked"' : ''; ?>
                                           value="1"/>
                                    <?php echo JText::_('J2STORE_PRODUCT_IS_MAIN_IMAGE_AS_THUM'); ?>
                                </div>
                            </div>
                            <div class="span4">
                                <?php if (J2Store::isPro() == 1) : ?>
                                    <div class="j2store-product-general">
                                        <div class="control-group form-inline">
                                            <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_MANAGE_STOCK'), 'manage_stock', array('class' => 'control-label')); ?>
                                            <?php // echo J2Html::radioBooleanList($prefix.'[manage_stock]',$this->variant->manage_stock,array('hide_label'=>true ,'id' => 'manage_stock_'.$this->variant->j2store_variant_id));?>

                                            <?php
                                            //pricing options
                                            echo J2Html::select()->clearState()
                                                ->type('genericlist')
                                                ->name($prefix . '[manage_stock]')
                                                ->value($this->variant->manage_stock)
                                                ->attribs(array('id' => 'manage_stock_' . $this->variant->j2store_variant_id, 'class' => 'input-small'))
                                                ->setPlaceHolders(array(0 => JText::_('J2STORE_NO'), 1 => JText::_('J2STORE_YES')))
                                                ->getHtml();
                                            ?>

                                        </div>
                                        <div class="control-group">
                                            <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_QUANTITY'), 'quantity', array('class' => 'control-label'));
                                            //this gets saved in the productquantities table with the variant_id as the FK
                                            ?>
                                            <?php echo J2Html::hidden($prefix . '[quantity][j2store_productquantity_id]', $this->variant->j2store_productquantity_id, array('class' => 'input', 'id' => 'productquantity_' . $this->variant->j2store_variant_id)); ?>
                                            <?php echo J2Html::text($prefix . '[quantity][quantity]', $this->variant->quantity, array('class' => 'input', 'id' => 'quantity_' . $this->variant->j2store_variant_id)); ?>
                                        </div>

                                        <div class="control-group">
                                            <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_ALLOW_BACK_ORDERS'), 'allow_backorder', array('class' => 'control-label')); ?>
                                            <?php
                                            //three select options: Do not allow, allow, but notify customer, allow
                                            // Radio Btn Displaying
                                            echo J2Html::select()->clearState()
                                                ->type('genericlist')
                                                ->name($prefix . '[allow_backorder]')
                                                ->attribs(array('id' => 'allowbackorder_' . $this->variant->j2store_variant_id))
                                                ->value($this->variant->allow_backorder)
                                                ->setPlaceHolders(
                                                    array('0' => JText::_('COM_J2STORE_DO_NOT_ALLOW_BACKORDER'),
                                                        '1' => JText::_('COM_J2STORE_DO_ALLOW_BACKORDER'),
                                                        '2' => JText::_('COM_J2STORE_ALLOW_BUT_NOTIFY_CUSTOMER')
                                                    ))
                                                ->getHtml(); ?>
                                        </div>

                                        <div class="control-group">
                                            <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_STOCK_STATUS'), 'availability', array('class' => 'control-label')); ?>
                                            <?php //two select options: In Stock, Out of stock ?>
                                            <?php
                                            echo J2Html::select()->clearState()
                                                ->type('genericlist')
                                                ->name($prefix . '[availability]')
                                                ->value($this->variant->availability)
                                                ->setPlaceHolders(
                                                    array('0' => JText::_('COM_J2STORE_PRODUCT_OUT_OF_STOCK'),
                                                        '1' => JText::_('COM_J2STORE_PRODUCT_IN_STOCK'))
                                                )
                                                ->getHtml();
                                            ?>
                                        </div>
                                        <div class="control-group">
                                            <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_NOTIFY_QUANTITY'), 'notify_qty', array('class' => 'control-label')); ?>

                                            <?php
                                            $attribs = (isset($this->variant->use_store_config_notify_qty) && !empty($this->variant->use_store_config_notify_qty)) ? array('id' => 'notify_qty_' . $this->variant->j2store_variant_id, 'disabled' => 'disabled') : array('id' => 'notify_qty_' . $this->variant->j2store_variant_id);
                                            echo J2Html::text($prefix . '[notify_qty]', $this->variant->notify_qty, $attribs); ?>
                                            <div class="qty_restriction">
                                                <label class="control-label">
                                                    <input id="variant_config_notify_qty_<?php echo $this->variant->j2store_variant_id; ?>"
                                                           type="checkbox"
                                                           name="<?php echo $prefix . '[use_store_config_notify_qty]'; ?>"
                                                           class="storeconfig"
                                                        <?php echo (isset($this->variant->use_store_config_notify_qty) && $this->variant->use_store_config_notify_qty) ? 'checked="checked"' : ''; ?> />
                                                    <?php echo JText::_('J2STORE_PRODUCT_USE_STORE_CONFIGURATION'); ?>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="control-group">
                                            <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_QUANTITY_RESTRICTION'), 'quantity_restriction', array('class' => 'control-label')); ?>
                                            <?php // echo J2Html::radio($prefix.'[quantity_restriction]', $this->variant->quantity_restriction, array('class'=>'controls')); ?>
                                            <?php
                                            //pricing options
                                            echo J2Html::select()->clearState()
                                                ->type('genericlist')
                                                ->name($prefix . '[quantity_restriction]')
                                                ->value($this->variant->quantity_restriction)
                                                ->attribs(array('id' => 'quantity_restriction_' . $this->variant->j2store_variant_id, 'class' => 'input-small'))
                                                ->setPlaceHolders(array(1 => JText::_('J2STORE_YES'), 0 => JText::_('J2STORE_NO')))
                                                ->getHtml();
                                            ?>
                                        </div>

                                        <div class="control-group form-inline">
                                            <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_MAX_SALE_QUANTITY'), 'max_sale_qty', array('class' => 'control-label')); ?>
                                            <?php
                                            $attribs = (isset($this->variant->use_store_config_max_sale_qty) && !empty($this->variant->use_store_config_max_sale_qty)) ? array('id' => 'max_sale_qty_' . $this->variant->j2store_variant_id, 'disabled' => 'disabled') : array('id' => 'max_sale_qty_' . $this->variant->j2store_variant_id);
                                            echo J2Html::text($prefix . '[max_sale_qty]', $this->variant->max_sale_qty, $attribs); ?>
                                            <div class="store_config_max_sale_qty">
                                                <label class="control-label">
                                                    <input id="store_config_max_sale_qty_<?php echo $this->variant->j2store_variant_id; ?>"
                                                           type="checkbox"
                                                           name="<?php echo $prefix . '[use_store_config_max_sale_qty]'; ?>"
                                                           class="storeconfig"
                                                        <?php echo isset($this->variant->use_store_config_max_sale_qty) && !empty($this->variant->use_store_config_max_sale_qty) ? 'checked="checked"' : ''; ?> />

                                                    <?php echo JText::_('J2STORE_PRODUCT_USE_STORE_CONFIGURATION'); ?>
                                                </label>
                                            </div>
                                        </div>

                                        <div class="control-group form-inline">
                                            <?php echo J2Html::label(JText::_('J2STORE_PRODUCT_MIN_SALE_QUANTITY'), 'min_sale_qty', array('class' => 'control-label')); ?>
                                            <?php
                                            $attribs = (isset($this->variant->use_store_config_min_sale_qty) && !empty($this->variant->use_store_config_min_sale_qty)) ? array('id' => 'min_sale_qty', 'disabled' => 'disabled') : array('id' => 'min_sale_qty_' . $this->variant->j2store_variant_id);
                                            echo J2Html::text($prefix . '[min_sale_qty]', $this->variant->min_sale_qty, $attribs); ?>
                                            <div class="store_config_min_sale_qty">
                                                <label class="control-label">
                                                    <input id="store_config_min_sale_qty_<?php echo $this->variant->j2store_variant_id; ?>"
                                                           type="checkbox"
                                                           name="<?php echo $prefix . '[use_store_config_min_sale_qty]'; ?>"
                                                           class="storeconfig"
                                                        <?php echo isset($this->variant->use_store_config_min_sale_qty) && !empty($this->variant->use_store_config_min_sale_qty) ? 'checked="checked"' : ''; ?> />
                                                    <?php echo JText::_('J2STORE_PRODUCT_USE_STORE_CONFIGURATION'); ?>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <script type="text/javascript">
                                        (function ($) {
                                            $("#variant_config_notify_qty_<?php echo $this->variant->j2store_variant_id;?>").click(function () {
                                                //$(this).attr('value',0);
                                                if (this.checked == true) {
                                                    $(this).prop('checked', true);
                                                } else {
                                                    $(this).removeAttr('checked');
                                                }
                                                $('#notify_qty_<?php echo $this->variant->j2store_variant_id;?>').attr('disabled', this.checked);
                                            });

                                            $("#store_config_max_sale_qty_<?php echo $this->variant->j2store_variant_id;?>").click(function () {
                                                if (this.checked == true) {
                                                    //	$(this).attr('value',1);
                                                } else {
                                                    $(this).removeAttr('checked');
                                                }

                                                $('#max_sale_qty_<?php echo $this->variant->j2store_variant_id;?>').attr('disabled', this.checked);
                                            });

                                            $("#store_config_min_sale_qty_<?php echo $this->variant->j2store_variant_id;?>").click(function () {
                                                if (this.checked == true) {
                                                    //	$(this).attr('value',1);
                                                } else {
                                                    $(this).removeAttr('checked');
                                                }
                                                $('#min_sale_qty_<?php echo $this->variant->j2store_variant_id;?>').attr('disabled', this.checked);
                                            });


                                        })(j2store.jQuery);
                                    </script>
                                <?php else: ?>
                                    <div class="well">
                                        <p class="lead"> <?php echo JText::_('J2STORE_PRODUCT_MANAGE_STOCK'); ?>  </p>
                                        <?php echo J2Html::pro(); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        function deleteVariant(variant_id) {
            (function ($) {
                var delete_var_data = {
                    option: 'com_j2store',
                    view: 'apps',
                    task: 'view',
                    id: '<?php echo $vars->extension_id;?>'
                    appTask: 'deletevariant',
                    variant_id: variant_id
                };
                $.ajax({
                    url: '<?php echo JRoute::_('index.php');?>',
                    data: delete_var_data,
                    beforeSend: function () {
                        $("#deleteVariant-" + variant_id).attr('value', '<?php echo JText::_('J2STORE_DELETING')?>');
                    },
                    success: function (json) {
                        if (json) {
                            window.location.reload();
                        }
                    }
                });
            })(j2store.jQuery);
        }
    </script>
    <?php $this->i++; ?>
<?php endforeach; ?>
<?php else: ?>
    <?php echo JText::_('J2STORE_NO_RESULTS_FOUND'); ?>
<?php endif; ?>