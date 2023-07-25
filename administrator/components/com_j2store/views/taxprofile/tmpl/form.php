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
$this->loadHelper('select');
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$platform->loadExtra('behavior.formvalidator');
?>
<div class="j2store">
    <div class="j2storehelp alert alert-info">
        <?php echo JText::_('J2STORE_TAXPROFILES_HELP_TEXT');?>
    </div>

    <form action="index.php" method="post" name="adminForm" id="adminForm" class="form-validate">
        <input type="hidden" name="option" value="com_j2store">
        <input type="hidden" name="view" value="taxprofile">
        <input type="hidden" name="task" value="">
        <input type="hidden"  name="j2store_taxprofile_id" value="<?php echo $this->item->j2store_taxprofile_id	; ?>">
        <input type="hidden" name="<?php echo JFactory::getSession()->getFormToken();?>" value="1" />

        <div id="taxprofile_edit">
            <fieldset class="fieldset">
                <legend>
                    <?php echo JText::_('J2STORE_TAXPROFILE'); ?>
                </legend>
                <table>
                    <tr>
                        <td><?php echo JText::_('J2STORE_TAXPROFILE_NAME'); ?></td>
                        <td>
                            <input type="text" required="" class="input-xlarge" value="<?php echo $this->item->taxprofile_name; ?>" id="taxprofile_name" name="taxprofile_name">
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo JText::_('J2STORE_ENABLED')?></td>
                        <td><?php echo J2StoreHelperSelect::publish('enabled',$this->item->enabled);?></td>
                    </tr>

                </table>
                <fieldset>
                    <legend>
                        <?php echo JText::_('COM_J2STORE_TITLE_TAXRATES'); ?>
                        <small><?php echo JText::_('J2STORE_TAXPROFILE_TAXRATE_MAP_HELP'); ?></small>
                    </legend>
                </fieldset>
        </div>

        <table id="taxprofile_rule_table" class="table table-stripped table-bordered">
            <h4><?php echo JText::_('J2STORE_TAX_RULES');?></h4>
            <thead>
            <tr>
                <th><?php echo JText::_('J2STORE_NUM'); ?>
                </th>
                <th colspan="1"><?php echo JText::_('J2STORE_TAXPROFILE_TAXRATE'); ?>
                </th>
                <th><?php echo JText::_('J2STORE_TAXPROFILE_ADDRESS'); ?>
                </th>
                <th></th>
            </tr>
            </thead>
            <?php $taxrule_row = 0;?>
            <?php if(isset($this->item->taxrules) && count($this->item->taxrules)): ?>
                <?php foreach ($this->item->taxrules as  $i => $taxrule):
                    ?>
                    <tbody id="tax-to-taxrule-row<?php echo $taxrule_row; ?>">

                    <tr>
                        <td><?php echo $i+1; ?></td>
                        <td class="left">
                            <select name="tax-to-taxrule-row[<?php echo $taxrule_row; ?>][taxrate_id]">
                                <?php foreach ($this->item->taxrates as $taxrate) : ?>
                                    <?php  if ($taxrate->j2store_taxrate_id == $taxrule->taxrate_id) : ?>
                                        <option value="<?php echo $taxrate->j2store_taxrate_id; ?>" selected="selected"><?php echo $taxrate->taxrate_name; ?></option>
                                    <?php else: ?>
                                        <option value="<?php echo $taxrate->j2store_taxrate_id; ?>"><?php echo $taxrate->taxrate_name; ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td class="select">
                            <select name="tax-to-taxrule-row[<?php echo $taxrule_row; ?>][address]">
                                <?php  if ($taxrule->address == 'billing') { ?>
                                    <option value="billing" selected="selected"><?php echo JText::_('J2STORE_BILLING_ADDRESS'); ?></option>
                                <?php } else { ?>
                                    <option value="billing"><?php echo JText::_('J2STORE_BILLING_ADDRESS'); ?></option>
                                <?php } ?>
                                <?php  if ($taxrule->address == 'shipping') { ?>
                                    <option value="shipping" selected="selected"><?php echo JText::_('J2STORE_SHIPPING_ADDRESS'); ?></option>
                                <?php } else { ?>
                                    <option value="shipping"><?php echo JText::_('J2STORE_SHIPPING_ADDRESS'); ?></option>
                                <?php } ?>
                                <!--
	                  <?php  if ($taxrule->address == 'store') { ?>
	                  <option value="store" selected="selected"><?php echo JText::_('J2STORE_STORE_ADDRESS'); ?></option>
	                  <?php } else { ?>
	                  <option value="store"><?php echo JText::_('J2STORE_STORE_ADDRESS'); ?></option>
	                  <?php } ?>
	                   -->
                            </select>
                        </td>
                        <input type="hidden" name ="tax-to-taxrule-row[<?php echo $taxrule_row; ?>][j2store_taxrule_id]" value="<?php echo $taxrule->j2store_taxrule_id; ?>"/>
                        <td class="left"><a onclick="j2storeRemoveTax(<?php echo $taxrule->j2store_taxrule_id; ?>, <?php echo $taxrule_row; ?>);" class="button"><?php echo JText::_('J2STORE_REMOVE'); ?></a></td>
                    </tr>

                    <?php $taxrule_row++; ?>
                <?php endforeach; ?>
                </tbody>
            <?php endif; ?>
            <tfoot>
            <tr>
                <td colspan="3"></td>
                <td><a class="btn btn-primary" onclick="j2storeAddTaxProfile();" class="button"><?php echo JText::_('J2STORE_ADD'); ?></a></td>
            </tr>
            </tfoot>
        </table>
</div>
</form>

<script type="text/javascript"><!--
    var taxrule_row = <?php echo $taxrule_row; ?>;

    function j2storeAddTaxProfile() {
        (function($) {
            html  = '<tbody id="tax-to-taxrule-row'+taxrule_row +'">';
            html += '  <tr>';
            html +='<td></td>';
            html += '    <td class="left"><select name="tax-to-taxrule-row[' + taxrule_row + '][taxrate_id]">';
            <?php foreach ($this->item->taxrates as $taxrate) : ?>
            html += '      <option value="<?php echo $taxrate->j2store_taxrate_id; ?>"><?php echo addslashes($taxrate->taxrate_name); ?></option>';
            <?php endforeach ?>
            html += '    </select></td>';
            html += '    <td class="left"><select name="tax-to-taxrule-row[' + taxrule_row + '][address]">';
            html += '      <option value="billing"><?php echo JText::_('J2STORE_BILLING_ADDRESS'); ?></option>';
            html += '      <option value="shipping"><?php echo JText::_('J2STORE_SHIPPING_ADDRESS'); ?></option>';
            //  html += '      <option value="store"><?php echo JText::_('J2STORE_STORE_ADDRESS'); ?></option>';
            html += '    </select></td>';
            html += '<input type="hidden" name="tax-to-taxrule-row['+ taxrule_row + '][j2store_taxrule_id]" value="" /></td>';
            html += '    <td class="left"><a onclick="j2store.jQuery(\'#tax-to-taxrule-row' + taxrule_row + '\').remove();" class="button"><?php echo JText::_('J2STORE_REMOVE'); ?></a></td>';
            html += '  </tr>';
            html += '</tbody>';

            $('#taxprofile_rule_table > tfoot').before(html);

            taxrule_row++;

        })(j2store.jQuery);
    }

    function j2storeRemoveTax(taxrule_id, taxrule_row) {
        (function($) {
            $('.j2storealert').remove();
            $.ajax({
                method:'post',
                url:'index.php?option=com_j2store&view=taxprofile&task=deleteTaxRule',
                data:{'taxrule_id':taxrule_id},
                dataType:'json'
            }).done(function(response) {
                if(response.success) {
                    $('#tax-to-taxrule-row'+taxrule_row).remove();
                    $('#taxprofile_rule_table').before('<div class="j2storealert alert alert-block">'+response.success+'</div>');
                } else {
                    $('#taxprofile_rule_table').before('<div class="j2storealert alert alert-block">'+response.error+'</div>');
                }
            });
        })(j2store.jQuery);
    }
    //--></script>
</div>
