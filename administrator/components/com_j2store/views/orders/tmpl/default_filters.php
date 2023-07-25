<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$secondary_button = 'btn btn-dark';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
$secondary_button = 'btn btn-inverse';
}
?>
<table class="adminlist table table-striped table-condensed">
    <tr>
        <td>
            <?php echo J2Html::button('reset', JText::_('J2STORE_FILTER_RESET_ALL'), array('id' => 'reset-filter', 'class' => $secondary_button)); ?>
        </td>
        <td colspan="2">
            <?php if (($this->state->since) || ($this->state->until) || ($this->state->paykey) || ($this->state->moneysum) || ($this->state->toinvoice) || ($this->state->coupon_code) || ($this->state->user_id)) : ?>
                <input id="hideBtnAdvancedControl" class="<?php echo $secondary_button ?>" type="button"
                       onclick="jQuery('#advanced-search-controls').toggle('click');jQuery(this).toggle('click');jQuery('#showBtnAdvancedControl').toggle('click');"
                       value="<?php echo JText::_('J2STORE_HIDE_FILTER_ADVANCED') ?>"/>
                <input id="showBtnAdvancedControl" class="btn btn-success" type="button"
                       onclick="jQuery('#advanced-search-controls').toggle('click');jQuery(this).toggle('click');jQuery('#hideBtnAdvancedControl').toggle('click');"
                       value="<?php echo JText::_('J2STORE_SHOW_FILTER_ADVANCED') ?>" style="display:none;"/>
            <?php else: ?>
                <input id="hideBtnAdvancedControl" class="<?php echo $secondary_button ?>" type="button"
                       onclick="jQuery('#advanced-search-controls').toggle('click');jQuery(this).toggle('click');jQuery('#showBtnAdvancedControl').toggle('click');"
                       value="<?php echo JText::_('J2STORE_HIDE_FILTER_ADVANCED') ?>" style="display:none;"/>
                <input id="showBtnAdvancedControl" class="btn btn-success" type="button"
                       onclick="jQuery('#advanced-search-controls').toggle('click');jQuery(this).toggle('click');jQuery('#hideBtnAdvancedControl').toggle('click');"
                       value="<?php echo JText::_('J2STORE_SHOW_FILTER_ADVANCED') ?>"/>
            <?php endif; ?>
        </td>
    </tr>
    <tr>
        <td align="left" width="100%"><?php echo JText::_('J2STORE_FILTER_SEARCH'); ?>:
            <?php $search = htmlspecialchars($this->state->search); ?>
            <?php echo J2Html::text('search', $search, array('id' => 'search', 'class' => 'input j2store-order-filters')); ?>
            <?php echo J2Html::button('go', JText::_('J2STORE_FILTER_GO'), array('class' => 'btn btn-success', 'onclick' => 'this.form.submit();')); ?>
            <?php echo J2Html::button('reset', JText::_('J2STORE_FILTER_RESET'), array('id' => 'reset-filter-search', 'class' => $secondary_button)); ?>
        </td>

        <td nowrap="nowrap">
            <?php echo JText::_('J2STORE_FILTER_ORDER_STATUS'); ?>:
            <?php
            echo J2Html::select()
                ->type('genericlist')
                ->name('orderstate')
                ->value($this->state->orderstate)
                ->attribs(array('onchange' => 'this.form.submit();', 'class' => 'input j2store-order-filters'))
                ->setPlaceHolders(array('' => JText::_('J2STORE_SELECT_OPTION')))
                ->hasOne('Orderstatuses')
                ->ordering('ordering')
                ->setRelations(
                    array(
                        'fields' => array
                        (
                            'key' => 'j2store_orderstatus_id',
                            'name' => 'orderstatus_name'
                        )
                    )
                )->getHtml();
            ?>
        </td>
        <td><?php echo $this->pagination->getLimitBox(); ?></td>
    </tr>
</table>