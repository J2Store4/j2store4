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
$sidebar = JHtmlSidebar::render();
$this->params = J2Store::config();
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<div class="<?php echo $row_class; ?>">
    <?php if (!empty($sidebar)): ?>
    <div id="j-sidebar-container" class="<?php echo $col_class;?>2">
        <?php echo $sidebar; ?>
    </div>
    <div id="j-main-container" class="<?php echo $col_class;?>10">
        <?php else : ?>
        <div class="j2store">
            <?php endif; ?>
            <form action="index.php" method="post" name="adminForm" id="adminForm">
                <?php echo J2Html::hidden('option', 'com_j2store'); ?>
                <?php echo J2Html::hidden('view', 'inventories'); ?>
                <?php echo J2Html::hidden('task', 'browse', array('id' => 'task')); ?>
                <?php echo J2Html::hidden('boxchecked', '0'); ?>
                <?php echo J2Html::hidden('filter_order', $this->state->filter_order); ?>
                <?php echo J2Html::hidden('filter_order_Dir', $this->state->filter_order_Dir); ?>
                <div class="input-prepend">
                    <span class="add-on"><?php echo JText::_('J2STORE_FILTER_SEARCH'); ?></span>
                    <?php echo J2Html::text('search', $this->state->search, array('id' => 'search', 'class' => 'input j2store-product-filters')); ?>

                    <?php echo J2Html::button('go', JText::_('J2STORE_FILTER_GO'), array('class' => 'btn btn-success', 'onclick' => 'this.form.submit();')); ?>
                    <?php echo J2Html::button('reset', JText::_('J2STORE_FILTER_RESET'), array('id' => 'reset-filter-search', 'class' => 'btn btn-inverse', "onclick" => "jQuery('#search').val('');this.form.submit();")); ?>
                </div>
                <div class="j2store-inventory-list">
                    <!-- Products items -->
                    <?php if (J2Store::isPro()): ?>
                        <?php echo $this->loadTemplate('items'); ?>
                    <?php else: ?>
                        <?php echo J2Html::pro(); ?>
                    <?php endif; ?>
                </div>
                <?php echo JHTML::_('form.token'); ?>
            </form>
            <?php if (!empty($sidebar)): ?>
        </div>
        <?php else: ?>
    </div>
<?php endif; ?>
</div>