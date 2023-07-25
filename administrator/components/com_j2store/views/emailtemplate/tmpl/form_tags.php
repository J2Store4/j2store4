<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<table class="table table-bordered">
    <fieldset>
        <legend>
            <h4>
                <?php echo JText::_('J2STORE_EMAILTEMPLATE_CUSTOM_FIELD_BILLING_TAGS')?>
            </h4>
        </legend>
        <div class="alert alert-block alert-info">
            <?php echo JText::_('J2STORE_EMAILTEMPLATE_CUSTOM_FIELD_BILLING_TAGS_HELP');?>
        </div>

        <tr>
            <td><code>[CUSTOM_BILLING_FIELD:FIELDNAME]</code></td>
            <td><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_CUSTOM_FIELD')?></td>
        </tr>

</table>
<table class="table table-bordered">
    <fieldset>
        <legend>
            <h4>
                <?php echo JText::_('J2STORE_EMAILTEMPLATE_CUSTOM_FIELD_SHIPPING_TAGS')?>
            </h4>
        </legend>
        <div class="alert alert-block alert-info">
            <?php echo JText::_('J2STORE_EMAILTEMPLATE_CUSTOM_FIELD_SHIPPING_TAGS_HELP');?>
        </div>

        <tr>
            <td><code>[CUSTOM_SHIPPING_FIELD:FIELDNAME]</code></td>
            <td><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_CUSTOM_FIELD')?></td>
        </tr>
    </fieldset>
</table>
<table class="table table-bordered">
    <fieldset>
        <legend>
            <h4>
                <?php echo JText::_('J2STORE_EMAILTEMPLATE_CUSTOM_FIELD_PAYMENT_TAGS')?>
            </h4>
        </legend>
        <div class="alert alert-block alert-info">
            <?php echo JText::_('J2STORE_EMAILTEMPLATE_CUSTOM_FIELD_PAYMENT_TAGS_HELP');?>
        </div>
        <tr>
            <td><code>[CUSTOM_PAYMENT_FIELD:FIELDNAME]</code></td>
            <td><?php echo JText::_('J2STORE_EMAILTEMPLATE_TAG_CUSTOM_FIELD')?></td>
        </tr>
    </fieldset>
</table>
