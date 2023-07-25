<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/

defined('_JEXEC') or die;
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$platform->loadExtra('behavior.formvalidator');
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<div class="j2store j2store-fields">
    <form name="adminForm" id="adminForm" method="post"
          class="form-validate" enctype="multipart/form-data"
          action="index.php">
        <div class="<?php echo $row_class ?>">
            <div class="<?php echo $col_class ?>8">
                <fieldset>
                    <legend><?php echo JText::_('J2STORE_ADD_CUSTOM_FIELD');?></legend>
                    <table class="table table-bordered">
                        <tr>
                            <td class="key"><label><?php echo JText::_('J2STORE_CUSTOM_FIELDS_NAME');?></label></td>
                            <td><?php echo J2Html::text('data[field][field_name]' ,$this->item->field_name,array('class'=>'input','id'=>'field_name'));?></td>
                        </tr>
                        <tr>
                            <td class="key"><?php echo JText::_( 'J2STORE_CUSTOM_FIELDS_TABLE' ); ?></td>
                            <td><?php echo $this->item->field_table ?>
                                <?php echo J2html::hidden('data[field][field_table]', $this->item->field_table);?>
                            </td>
                        </tr>
                        <tr class="columnname">
                            <td class="key"><?php echo JText::_( 'J2STORE_CUSTOM_FIELDS_COLUMN' ); ?></td>
                            <td>
                                <?php if(empty($this->item->j2store_customfield_id)): ?>
                                    <?php echo J2Html::text('data[field][field_namekey]' ,$this->item->field_namekey,array('class'=>'input','id'=>'field_namekey'));?>
                                <?php else: ?>
                                    <?php echo $this->item->field_namekey; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="key"><?php echo JText::_( 'J2STORE_CUSTOM_FIELD_TYPE' ); ?></td>
                            <td>
                                <?php
                                if(!empty($this->field->field_type) && $this->field->field_type=='customtext'){
                                    $this->fieldtype->addJS();
                                    echo $this->field->field_type.'<input type="hidden" id="fieldtype" name="data[field][field_type]" value="'.$this->field->field_type.'" />';
                                }else{
                                    echo $this->fieldtype->display('data[field][field_type]',@$this->field->field_type,@$this->field->field_table);
                                }?>
                            </td>
                        </tr>
                        <tr class="required">
                            <td class="key"><?php echo JText::_( 'J2STORE_CUSTOM_FIELDS_REQUIRED' ); ?></td>

                            <td><?php
                                echo J2Html::select()->clearState()
                                    ->type('genericlist')
                                    ->name('data[field][field_required]')
                                    ->value($this->item->field_required)
                                    ->setPlaceholders(
                                        array(
                                            '0' => JText::_('J2STORE_NO'),
                                            '1' => JText::_('J2STORE_YES')
                                        ))
                                    ->getHtml();
                                ?></td>
                        </tr>
                        <tr class="required">
                            <td class="key"><?php echo JText::_( 'J2STORE_CUSTOM_FIELD_ERROR' ); ?></td>
                            <td><?php echo J2Html::text('field_options[errormessage]',@$this->escape($this->item->field_options['errormessage'],array('class'=>'input','id'=>'errormessage') ));?></td>
                        </tr>
                        <tr class="default">
                            <td class="key"><?php echo JText::_( 'J2STORE_CUSTOM_FIELD_DEFAULT' ); ?></td>
                            <td><?php echo $this->fieldClass->display(@$this->field,@$this->field->field_default,'data[field][field_default]',false,'',true,$this->allFields); ?></td>
                        </tr>
                        <tr class="multivalues">
                            <td class="key" valign="top">
                                <?php echo JText::_( 'J2STORE_CUSTOM_FIELD_VALUES' ); ?>
                            </td>
                            <td>
                                <table id="j2store_field_values_table" class="table table-striped table-hover">
                                    <tbody id="tablevalues">
                                    <tr>
                                        <td colspan="3">
                                            <a onclick="addLine();return false;" href='#' title="<?php echo $this->escape(JText::_('J2STORE_CUSTOM_FIELD_ADDVALUE')); ?>"><?php echo JText::_('J2STORE_CUSTOM_FIELD_ADDVALUE'); ?></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><?php echo JText::_('J2STORE_CUSTOM_FIELD_VALUE')?></td>
                                        <td><?php echo JText::_('J2STORE_CUSTOM_FIELD_TITLE'); ?></td>
                                        <td><?php echo JText::_('J2STORE_CUSTOM_FIELD_DISABLED'); ?></td>
                                    </tr>
                                    <?php
                                    if(!empty($this->field->field_value) && is_array($this->field->field_value) AND $this->field->field_type!='zone'){
                                        foreach($this->field->field_value as $title => $value){
                                            $no_selected = 'selected="selected"';
                                            $yes_selected = '';
                                            if((int)$value->disabled){
                                                $no_selected = '';
                                                $yes_selected = 'selected="selected"';
                                            }
                                            ?>
                                            <tr>
                                                <td><input type="text" name="field_values[title][]" value="<?php echo $this->escape($title); ?>" /></td>
                                                <td><input type="text" name="field_values[value][]" value="<?php echo $this->escape($value->value); ?>" /></td>
                                                <td><select name="field_values[disabled][]" class="inputbox">
                                                        <option <?php echo $no_selected; ?> value="0"><?php echo JText::_('J2STORE_NO'); ?></option>
                                                        <option <?php echo $yes_selected; ?> value="1"><?php echo JText::_('J2STORE_YES'); ?></option>
                                                    </select></td>
                                            </tr>
                                        <?php } }?>
                                    <tr>
                                        <td><?php echo J2Html::text('field_values[title][]' ,'',array('class' =>'input'));?></td>
                                        <td><?php echo J2Html::text('field_values[value][]' ,'',array('class' =>'input'));?></td>
                                        <td><?php echo J2Html::select()->clearState()
                                                ->type('genericlist')
                                                ->name('field_values[disabled][]')
                                                ->value(0)
                                                ->setPlaceholders(
                                                    array(
                                                        '0' => JText::_('J2STORE_NO'),
                                                        '1' => JText::_('J2STORE_YES')
                                                    ))
                                                ->getHtml();
                                            ?>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr class="filtering">
                            <td class="key"><?php echo JText::_( 'J2STORE_CUSTOM_FIELD_INPUT_FILTERING' ); ?></td>
                            <td>
                                <?php  $input_filtering  =  (isset($this->item->field_options['filtering']) ? (int)$this->item->field_options['filtering'] : ""); ?>
                                <?php echo JHtml::_('select.booleanlist', "field_options[filtering]", array(),  $input_filtering); ?>
                            </td>
                        </tr>
                        <tr class="maxlength">
                            <td class="key"><?php echo JText::_( 'J2STORE_CUSTOM_FIELD_MAXLENGTH' ); ?></td>
                            <td>
                                <?php $maxlength =  (isset($this->item->field_options['maxlength']) ? (int)$this->item->field_options['maxlength'] : ""); ?>
                                <?php echo J2Html::text('field_options[maxlength]',$maxlength,array('id' =>'maxlength'));?>
                            </td>
                        </tr>

                        <tr class="place_holder">
                            <td class="key"><?php echo JText::_( 'J2STORE_CUSTOM_FIELD_PLACEHOLDER' ); ?></td>
                            <td>
                                <?php $placeholder =  (isset($this->item->field_options['placeholder']) ? $this->item->field_options['placeholder'] : ""); ?>
                                <?php echo J2Html::text('field_options[placeholder]',$placeholder,array('id' =>'placeholder'));?>
                            </td>
                        </tr>
                        <tr class="size">
                            <td class="key"><?php echo JText::_( 'J2STORE_CUSTOM_FIELD_SIZE' ); ?></td>
                            <td><?php echo J2Html::text('field_options[size]',@$this->item->field_options['size'],array('id' =>'size'));?></td>
                        </tr>
                        <tr class="rows">
                            <td class="key"><?php echo JText::_( 'J2STORE_CUSTOM_FIELD_ROWS' ); ?></td>
                            <td><?php echo J2Html::text('field_options[size]',$this->item->field_options['size'],array('id' =>'size'));?>
                            </td>
                        </tr>

                        <tr class="cols">
                            <td class="key">
                                <?php echo JText::_( 'J2STORE_CUSTOM_FIELD_COLUMNS' ); ?>
                            </td>
                            <td>
                                <input type="text"  size="10" name="field_options[cols]" id="cols" class="inputbox" value="<?php echo $this->escape(@$this->field->field_options['cols']); ?>"/>
                            </td>
                        </tr>
                        <tr class="zone">
                            <td class="key"><?php echo JText::_( 'J2STORE_CUSTOM_FIELD_ZONE' ); ?></td>
                            <td><?php echo $this->zoneType->display("field_options[zone_type]",@$this->field->field_options['zone_type'],true);?></td>
                        </tr>

                        <tr class="format">
                            <td class="key"><?php echo JText::_( 'J2STORE_CUSTOM_FIELD_FORMAT' ); ?></td>
                            <td><input type="text" id="format" name="field_options[format]" value="<?php echo $this->escape(@$this->field->field_options['format']); ?>"/></td>
                        </tr>
                        <tr class="customtext">
                            <td class="key">
                                <?php echo JText::_( 'J2STORE_CUSTOM_TEXT' ); ?>
                            </td>
                            <td><textarea cols="50" rows="10" name="fieldcustomtext"><?php echo @$this->field->field_options['customtext']; ?></textarea></td>
                        </tr>

                        <tr class="readonly">
                            <td class="key">
                                <?php echo JText::_( 'J2STORE_CUSTOM_FIELD_READONLY' ); ?>
                            </td>
                            <td>
                                <?php // echo JHTML::_('select.booleanlist', "field_options[readonly]" , ''); ?>
                                <?php echo JHTML::_('select.booleanlist', "field_options[readonly]" , '',@$this->field->field_options['readonly']); ?>
                            </td>
                        </tr>
                    </table>
                </fieldset>
            </div>
            <div class="<?php echo $col_class ?>4">
                <fieldset class="field-status">
                    <legend><?php echo JText::_('J2STORE_STATUS')?></legend>
                    <table class="table table-bordered">
                        <tr>
                            <td class="key">
                                <label for="state">
                                    <?php echo JText::_('J2STORE_PUBLISH');?>
                                </label>
                            </td>
                            <td>
                                <?php echo JHTML::_('select.booleanlist', 'data[field][enabled]', '', $this->item->enabled); ?>
                            </td>
                        </tr>
                    </table>
                </fieldset>

                <fieldset class="field-display">
                    <legend><?php echo JText::_('J2STORE_CUSTOM_FIELD_DISPLAY_SETTINGS')?></legend>
                    <span class="muted"> <small><?php echo JText::_('J2STORE_CUSTOM_FIELD_DISPLAY_HELP');?></small></span>
                    <table class="table table-bordered">
                        <tr>
                            <td class="key"><?php echo JText::_( 'J2STORE_STORE_BILLING_LAYOUT_LABEL' ); ?></td>
                            <td><?php

                                echo JHTML::_('select.booleanlist', "data[field][field_display_billing]", '', $this->field->field_display_billing); ?></td>
                        </tr>
                        <tr>
                            <td class="key"><?php echo JText::_( 'J2STORE_STORE_SHIPPING_LAYOUT_LABEL' ); ?></td>
                            <td><?php echo JHTML::_('select.booleanlist', "data[field][field_display_shipping]", '', $this->item->field_display_shipping); ?></td>
                        </tr>

                        <tr>
                            <td class="key"><?php echo JText::_( 'J2STORE_STORE_PAYMENT_LAYOUT_LABEL' ); ?></td>
                            <td><?php echo JHTML::_('select.booleanlist', "data[field][field_display_payment]" , '', $this->item->field_display_payment); ?></td>
                        </tr>
                    </table>
                </fieldset>
                <?php if(!empty($this->field->j2store_customfield_id)) : ?>
                    <fieldset class="adminform">
                        <legend><?php echo JText::_('PREVIEW'); ?></legend>
                        <table class="admintable table">
                            <tr>
                                <td class="key">
                                    <?php $this->fieldClass->suffix='_preview';
                                    echo $this->fieldClass->getFieldName($this->field); ?>
                                </td>
                                <td><?php
                                    $field_options = '';
                                    if($placeholder){
                                        $field_options .= ' placeholder="'.$placeholder.'" ';
                                    }

                                    echo $this->fieldClass->display($this->field,$this->field->field_default, $this->field->field_namekey, false,$field_options,true,$this->allFields); ?></td>
                            </tr>
                        </table>
                    </fieldset>
                <?php endif; ?>


            </div> <!--  end of span -->

        </div> <!-- end of row -->
        <input type="hidden" name="option" value="com_j2store" />
        <input type="hidden" name="view" value="customfields" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="j2store_customfield_id" value="<?php echo $this->item->j2store_customfield_id ?>" />
        <input type="hidden" name="<?php echo JFactory::getSession()->getFormToken();?>" value="1" />
    </form>
</div>
