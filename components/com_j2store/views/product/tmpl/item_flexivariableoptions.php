<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
$options = $this->product->options;
$variant_name = array();
if(isset($this->product->variant->variant_name) && $this->product->variant->variant_name){
    $variant_name = explode(',',$this->product->variant->variant_name);
}
?>
<?php if ($options) { ?>

    <div class="options" id="variable-options-<?php echo $this->product->j2store_product_id?>" >
        <?php foreach ($options as $option_key => $option) { ?>
            <?php
            $default_option_value_id = '';
            foreach ($option['optionvalue'] as $o_value){
                if(isset($variant_name[$option_key]) && isset($o_value['product_optionvalue_id']) && $variant_name[$option_key] == $o_value['product_optionvalue_id']){
                    $default_option_value_id = $o_value['optionvalue_id'];
                    break;
                }
            }
            ?>
            <?php
            //$option['option_value'] = F0FModel::getTmpInstance('Optionvalues','J2StoreModel')->option_id($option['option_id'])->getList();
            $default_option_value_name = '';
            $option_count = 0;
            ?>
            <?php echo J2Store::plugin()->eventWithHtml('BeforeDisplaySingleProductOption', array($this->product, &$option)); ?>

            <?php //var_dump($option); ?>
            <?php if ($option['type'] == 'select') { ?>
                <!-- select -->
                <div id="option-<?php echo $option['productoption_id']; ?>" class="option">
                    <?php if ($option['required']) { ?>
                        <span class="required">*</span>
                    <?php } ?>
                    <b><?php echo $this->escape(JText::_($option['option_name'])); ?>:</b><br />
                    <select name="product_option[<?php echo $option['productoption_id']; ?>]"
                            onChange="doFlexiAjaxPrice(
                            <?php echo $this->product->j2store_product_id?>,
                                    '#option-<?php echo $option["productoption_id"]; ?>'
                                    )"
                    >
                        <option value="*" >
                            <?php echo stripslashes($this->escape(JText::_('J2STORE_CHOOSE')));?>
                        </option>
                        <?php foreach ($option['option_value'] as $option_value) { ?>
                            <?php $checked = ''; if($default_option_value_id == $option_value->j2store_optionvalue_id) $checked = 'selected="selected"'; ?>
                            <?php //$checked = ''; if($default_option_value_name == $option_value->optionvalue_name) $checked = 'selected="selected"'; ?>
                            <option <?php echo $checked; ?> value="<?php echo $option_value->j2store_optionvalue_id; ?>">
                                <?php echo stripslashes($this->escape(JText::_($option_value->optionvalue_name)));?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <br />
            <?php } ?>

            <?php if ($option['type'] == 'radio') { ?>

                <!-- radio -->
                <div id="option-<?php echo $option['productoption_id']; ?>" class="option">
                    <?php if ($option['required']) { ?>
                        <span class="required">*</span>
                    <?php } ?>
                    <b><?php echo $this->escape(JText::_($option['option_name'])); ?>:</b><br />
                    <?php foreach ($option['option_value'] as $option_value) { ?>
                        <?php $checked = ''; if($default_option_value_id == $option_value->j2store_optionvalue_id) $checked = 'checked="checked"'; ?>
                        <?php //$checked = ''; if(isset($option_value->j2store_optionvalue_id) && isset($variant_name[$option_key]) && $option_value->j2store_optionvalue_id == $variant_name[$option_key]) $checked = 'checked="checked"'; ?>
                        <?php //$checked = ''; if($default_option_value_name == $option_value->optionvalue_name) $checked = 'checked="checked"'; ?>
                        <input <?php echo $checked; ?> type="radio" name="product_option[<?php echo $option['productoption_id']; ?>]"
                                                       autocomplete="off"
                                                       onClick="doFlexiAjaxPrice(
                                                       <?php echo $this->product->j2store_product_id?>,
                                                               '#option-<?php echo $option["productoption_id"]; ?>'
                                                               );"
                                                       value="<?php echo $option_value->j2store_optionvalue_id; ?>" id="option-value-<?php echo $option_value->j2store_optionvalue_id; ?>"
                                                       data-product_id="<?php echo $this->product->j2store_product_id?>"  />
                        <?php
                        if(
                            $this->params->get('image_for_product_options', 0) &&
                            isset($option_value->optionvalue_image) &&
                            !empty($option_value->optionvalue_image)
                        ):
                            ?>
                            <img
                                    class="optionvalue-image-<?php echo $option['productoption_id']; ?>-<?php echo $option_value->j2store_optionvalue_id; ?>"
                                    src="<?php echo JUri::root(true).'/'.$option_value->optionvalue_image; ?>" />
                        <?php endif; ?>
                        <label for="option-value-<?php echo $option_value->j2store_optionvalue_id; ?>"  >
                            <?php echo stripslashes($this->escape(JText::_($option_value->optionvalue_name))); ?>
                        </label>
                        <br />
                    <?php } ?>
                </div>
                <br />
            <?php } ?>
            <?php echo J2Store::plugin()->eventWithHtml('AfterDisplaySingleProductOption', array($this->product, $option)); ?>
        <?php } ?>
    </div>
<?php } ?>
