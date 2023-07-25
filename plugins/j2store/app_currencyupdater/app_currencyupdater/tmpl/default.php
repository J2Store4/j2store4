<?php
/**
 * --------------------------------------------------------------------------------
 * App Plugin - Currency Updater
 * --------------------------------------------------------------------------------
 * @package     Joomla  3.x
 * @subpackage  J2 Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c) 2017 J2Store . All rights reserved.
 * @license     GNU/GPL v3 or latest
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
$platform = J2Store::platform();
$platform->loadExtra('behavior.framework');
$platform->loadExtra('behavior.modal');
$platform->loadExtra('behavior.tooltip');
$platform->loadExtra('behavior.multiselect');
$platform->loadExtra('dropdown.init');
$platform->loadExtra('formbehavior.chosen', 'select');
JHtml::_('script', 'media/j2store/js/j2store.js', false, false);
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';}
?>

<script type="text/javascript">
    Joomla.submitbutton = function(pressbutton) {
        if(pressbutton === 'save' || pressbutton === 'apply') {
            document.adminForm.task ='view';
            document.getElementById('appTask').value = pressbutton;
            Joomla.submitform('view');
        }else {
            document.adminForm.task = pressbutton;
            Joomla.submitform(pressbutton);;
        }
    }
</script>

<div class="j2store-configuration">
    <form action="<?php echo $vars->action; ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validate">
        <?php echo J2Html::hidden('option','com_j2store');?>
        <?php echo J2Html::hidden('view','apps');?>
        <?php echo J2Html::hidden('app_id',$vars->id);?>
        <?php echo J2Html::hidden('id',$vars->id);?>
        <?php echo J2Html::hidden('appTask', '', array('id'=>'appTask'));?>
        <?php echo J2Html::hidden('task', 'view', array('id'=>'task'));?>

        <?php echo JHtml::_('form.token'); ?>
        <?php
        $fieldsets = $vars->form->getFieldsets();
        $shortcode = $vars->form->getValue('text');
        $tab_count = 0;

        foreach ($fieldsets as $key => $attr)
        {

            if ( $tab_count == 0 )
            {
                echo JHtml::_('bootstrap.startTabSet', 'configuration', array('active' => $attr->name));
            }
            echo JHtml::_('bootstrap.addTab', 'configuration', $attr->name, JText::_($attr->label, true));
            ?>
            <?php  if(J2Store::isPro() != 1 && isset($attr->ispro) && $attr->ispro ==1 ) : ?>
            <?php echo J2Html::pro(); ?>
        <?php else: ?>

            <div class="<?php echo $row_class; ?>">
                <div class="<?php echo  $col_class; ?>12">
                    <?php
                    $layout = '';
                    $style = '';
                    $fields = $vars->form->getFieldset($attr->name);
                    foreach ($fields as $key => $field)
                    {
                        $pro = $field->getAttribute('pro');
                        ?>
                        <div class="control-group <?php echo $layout; ?>" <?php echo $style; ?>>
                            <div class="control-label"><?php echo $field->label; ?></div>
                            <?php if(J2Store::isPro() != 1 && $pro ==1 ): ?>
                                <?php echo J2Html::pro(); ?>
                            <?php else: ?>
                            <div class="controls"><?php echo $field->input; ?>
                                <br />
                                <small class="muted"><?php echo JText::_($field->description); ?></small>
                                <?php endif; ?>

                            </div>
                        </div>
                        <?php
                    }
                    ?>
                    <div id="exchangerate_div" class="<?php echo $row_class; ?>" style="display: none;">
                        <div class="<?php echo $col_class; ?>6 control-group ">
                            <div class="control-label"><label > <?php echo JText::_('J2STORE_EXCHANGERATE_API_KEY'); ?> </label></div>
                            <div class="controls"><input type="text" name="params[exchangerate_api_key]" id="params_exchangerate_api_key" value="<?php echo $this->params->get ( 'exchangerate_api_key', '') ?>"> </div>
                        </div>
                        <div class="alert alert-block alert-info span6">
                            <strong>Exchangerate Free Api key allow 1500 Request per/month.
                                <br>you can generate the API key using this link : <a href="https://app.currencyapi.com/api-keys">https://app.currencyapi.com/api-keys</a></strong>
                        </div>
                    </div>
                    <div id="currencyapi_div"  class="control-group "  style="display:none;">
                        <div class="<?php echo $col_class; ?>6 control-group ">
                            <div class="control-label"><label > <?php echo JText::_('J2STORE_CURRENCY_API_KEY'); ?> </label></div>
                            <div class="controls"><input type="text" name="params[currencyapi_key]" id="params_currencyapi_key" value="<?php  echo $this->params->get ( 'currencyapi_key', '') ?>"> </div>
                        </div>
                        <div class="alert alert-block alert-info <?php echo $col_class; ?>6 control-group ">
                            <strong> currency Free Api key allow 300 Request per/month
                                <br>you can generate the API key using this link : <a href="https://app.exchangerate-api.com/keys">https://app.exchangerate-api.com/keys</a></strong>
                            </strong>
                        </div>
                    </div>
                    <div id="exchangerate_host_div"  class="<?php echo $row_class; ?>"  style="display:none;">
                        <div class="alert alert-block alert-info <?php echo $col_class; ?>6">
                            <strong> Exchangerate host method doesn't need a API key </strong>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
            <?php
            echo JHtml::_('bootstrap.endTab');
            $tab_count++;
        }
        ?>
    </form>
</div>
<script>
    (function($) {
        var selectedValue = $('#params_currency_converter_api_type').val();
        $('#exchangerate_div').css('display', selectedValue == 'exchangerate_api' ? 'block' : 'none');
        $('#currencyapi_div').css('display', selectedValue == 'currencyapi' ? 'block' : 'none');
        $('#exchangerate_host_div').css('display', selectedValue == 'exchangerate_host' ? 'block' : 'none');
        $('#params_currency_converter_api_type').on('change', function() {
            var selectedValue = $(this).val();
            $('#exchangerate_div').css('display', selectedValue == 'exchangerate_api' ? 'block' : 'none');
            $('#currencyapi_div').css('display', selectedValue == 'currencyapi' ? 'block' : 'none');
            $('#exchangerate_host_div').css('display', selectedValue == 'exchangerate_host' ? 'block' : 'none');
        });
    })(j2store.jQuery);
</script>