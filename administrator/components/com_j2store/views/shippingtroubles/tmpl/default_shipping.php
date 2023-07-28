<?php
/**
 * -------------------------------------------------------------------------------
 * @package    J2Store
 * @author      Alagesan, J2Store <support@j2store.org>
 * @copyright   Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license    GNU GPL v3 or later
 * @link        http://j2store.org
 * --------------------------------------------------------------------------------
 *
 * */
defined('_JEXEC') or die;
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
    <div id="j-sidebar-container" class="<?php echo $col_class ?>2">
        <?php echo $sidebar; ?>
    </div>
    <div id="j-main-container" class="<?php echo $col_class ?>10">
        <?php else : ?>
        <div class="j2store">
            <?php endif; ?>
            <h2>
                <?php echo JText::_("J2STORE_SHIPPING_METHOD_VALIDATE"); ?>
            </h2>
            <?php if ($this->shipping_available): ?>
                <div class="<?php echo $col_class ?>12">
                    <div class="tabbable tabs-left <?php echo $row_class ?>" >
                        <ul class="nav nav-tabs flex-column <?php echo $col_class ?>3" id="nav-tab" role="tablist">
                            <?php if (isset($this->shipping_messages) && !empty($this->shipping_messages)): ?>
                                <?php $count = 0; ?>
                                <?php foreach ($this->shipping_messages as $shipping_message): ?>
                                    <?php $name = $shipping_message['name'] ?>
                                    <?php $message = $shipping_message['message']; ?>
                                    <?php foreach ($shipping_message as $shipping_messages): ?>
                                        <?php if (empty($shipping_messages['shipping_name'])): ?>
                                         <?php if (version_compare(JVERSION, '3.99.99', 'lt')) : ?>
                                            <li class="<?php echo $count == 0 ? "active" : ""; ?>">
                                                <a href="#<?php echo str_replace(" ", "_", trim($name)); ?>"
                                                   data-toggle="tab" role="tab">                         <?php echo JText::_($name); ?>
                                                </a>
                                            </li>
                                            <?php else: ?>
                                                <li class="item">
                                                    <a class="nav-link <?php echo $count == 0 ? "active" : ""; ?>" href="#<?php echo str_replace(" ", "_", trim($name)); ?>"
                                                       data-bs-toggle="tab">                         <?php echo JText::_($name); ?>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php $count++; ?>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                        <div class="tab-content <?php echo $col_class ?>9">
                            <?php if (isset($this->shipping_messages) && !empty($this->shipping_messages)): ?>
                                <?php $count_tab = 0; ?>
                                <?php foreach ($this->shipping_messages as $shipping_message): ?>
                                    <?php $name = isset($shipping_message['name']) && empty($shipping_message['name']) ? $shipping_message['name'] :''; ?>
                                    <?php $ship_value = isset($shipping_message['value']) && empty($shipping_message['value']) ? $shipping_message['value'] :'';  ?>
                                    <?php foreach ($shipping_message as $shipping_messages): ?>
                                        <?php if (empty($shipping_messages['shipping_name'])): ?>
                                            <?php if (version_compare(JVERSION, '3.99.99', 'lt')) : ?>
                                            <div class="tab-pane  <?php echo $count_tab == 0 ? "active" : ""; ?>"  id="<?php echo str_replace(" ", "_", trim($name)); ?>">
                                            <table class="table table-striped table-bordered">
                                            <tbody>
                                            <?php else: ?>
                                            <div class="tab-pane fade <?php echo $count_tab == 0 ? "show active" : ""; ?>"  role="tabpanel" id="<?php echo str_replace(" ", "_", trim($name)); ?>">
                                                <table class="table table-striped table-bordered">
                                                    <tbody>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?php foreach ($shipping_messages as $key => $value): ?>
                                                <?php if (!empty($value['name'])) : ?>
                                                    <tr>
                                                        <td><?php echo $value['name']; ?></td>
                                                        <td><?php echo $value['value']; ?></td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <?php $count_tab++; ?>
                                    <?php endforeach; ?>
                                    </tbody>
                                    </table>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger"><?php echo JText::sprintf('J2STORE_SHIPPING_TROUBLESHOOT_NOTE_MESSAGE', 'index.php?option=com_j2store&view=shippings', J2Store::buildHelpLink('support/user-guide/standard-shipping.html', 'shipping')); ?></div>
            <?php endif; ?>
            <div class="<?php echo $col_class ?>9 center">
                <a class="fa fa-arrow-right btn btn-large btn-success "
                   href="<?php echo JRoute::_('index.php?option=com_j2store&view=shippingtroubles&layout=default_shipping_product'); ?>">
                    <?php echo 'Next'; ?>
                </a>
            </div>
            <?php if (!empty($sidebar)): ?>
        </div>
        <?php else: ?>
    </div>
<?php endif; ?>
</div>

<style type="text/css">

    #j-main-container .nav-tabs > li.active > a,
    #j-main-container .nav-tabs > li.active > a:hover,
    #j-main-container .nav-tabs > li.active > a:focus {
        border: 0;
        color: #fff;
        background: url("<?php echo JURI::root();?>media/j2store/images/arrow_white.png") center right no-repeat #999;
    }
</style>
