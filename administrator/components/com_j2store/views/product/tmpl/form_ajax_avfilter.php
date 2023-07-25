<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="row-fluid">
    <div class="span7">
        <div class="alert alert-info alert-block">
            <strong><?php echo JText::_('J2STORE_NOTE'); ?></strong> <?php echo JText::_('J2STORE_FEATURE_AVAILABLE_IN_J2STORE_PRODUCT_LAYOUTS'); ?>
        </div>
        <div class="control-group">
            <h3><?php echo JText::_('COM_J2STORE_TITLE_FILTERGROUPS'); ?></h3>
            <table id="product_filters_table"
                   class="adminlist table table-striped table-bordered j2store">
                <thead>
                    <tr>
                        <th><?php echo JText::_('J2STORE_PRODUCT_FILTER_VALUE');?></th>
                        <th><?php echo JText::_('J2STORE_REMOVE');?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if(isset($this->product_filters) && count($this->product_filters)): ?>
                    <?php foreach($this->product_filters as $group_id=>$filters):?>
                        <tr>
                            <td colspan="2"><h4><?php echo $this->escape($filters['group_name']); ?></h4></td>
                        </tr>
                        <?php foreach($filters['filters'] as $filter):
                            ?>
                            <tr
                                id="product_filter_current_option_<?php echo $filter->filter_id;?>">
                                <td class="addedFilter">
                                    <?php echo $this->escape($filter->filter_name) ;?>
                                </td>
                                <td><span class="filterRemove"
                                          onclick="removeFilter(<?php echo $filter->filter_id; ?>, <?php echo $this->item->j2store_product_id; ?>);">x</span>
                                    <input type="hidden" value="<?php echo $filter->filter_id;?>"
                                           name="<?php echo $this->form_prefix.'[productfilter_ids]' ;?>[]" />
                                </td>
                            </tr>
                        <?php endforeach;?>
                    <?php endforeach;?>
                <?php endif;?>
                <tr class="j2store_a_filter">
                    <td colspan="2">
                        <?php echo JText::_('J2STORE_SEARCH_AND_PRODUCT_FILTERS');?>
                        <?php echo J2Html::text('productfilter' ,'' ,array('id' =>'J2StoreproductFilter'));?>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="span5">&nbsp;</div>
</div>
