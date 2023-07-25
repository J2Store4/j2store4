<?php
/**
 * @package   J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license   GNU GPL v3 or later
 */
// No direct access
defined('_JEXEC') or die;
?>
<?php if (isset($this->filters) && count($this->filters)): ?>
    <div class="j2store-product-specifications">

        <?php foreach ($this->filters as $group_id => $rows): ?>
            <h4 class="filter-group-name"><?php echo $this->escape(JText::_($rows['group_name'])); ?></h4>
            <table class="table table-striped">
                <?php foreach ($rows['filters'] as $filter): ?>
                    <tr>
                        <td>
                            <?php echo $this->escape(JText::_($filter->filter_name)); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endforeach; ?>

    </div>
<?php endif; ?>
<table class="table table-striped">
    <tr>
        <td><?php echo JText::_('J2STORE_PRODUCT_DIMENSIONS'); ?></td>
        <td>
            <span class="product-dimensions">
            <?php if (isset($this->product->variant) && !empty($this->product->variant)): ?>
                <?php if ($this->product->variant->length && $this->product->variant->height && $this->product->variant->width): ?>
                    <?php echo round($this->product->variant->length, 2); ?>
                    x <?php echo round($this->product->variant->width, 2); ?>
                    x <?php echo round($this->product->variant->height, 2); ?>
                    <?php echo $this->product->variant->length_title; ?>
                <?php endif; ?>
            <?php else: ?>
                <?php echo JText::_('J2STORE_EMPTY_DASHES'); ?>
            <?php endif; ?>
            </span>
        </td>
    </tr>
    <tr>
        <td>
            <?php echo JText::_('J2STORE_PRODUCT_WEIGHT'); ?>
        </td>
        <td>
            <span class="product-weight">
            <?php if (isset($this->product->variant) && !empty($this->product->variant)): ?>
                <?php if ($this->product->variant->weight): ?>
                    <?php echo round($this->product->variant->weight, 2); ?>
                    <?php echo $this->product->variant->weight_title; ?>
                <?php endif; ?>
            <?php else: ?>
                <?php echo JText::_('J2STORE_EMPTY_DASHES'); ?>
            <?php endif; ?>
            </span>
        </td>
    </tr>
</table>