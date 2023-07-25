<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
$currency = J2Store::currency();
$doc = JFactory::getDocument();
J2Store::strapper()->addFontAwesome();
?>

<?php if(isset($this->orders) && count($this->orders)) : ?>
    <?php echo J2Store::plugin()->eventWithHtml('BeforeMyProfileOrderDisplay',array($this->orders));?>
    <form action="<?php echo JRoute::_('index.php');?>" method="post" id="adminForm" name="adminForm" enctype="multipart/form-data">
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th><?php echo JText::_('J2STORE_ORDER_DATE');?></th>
                <th><?php echo JText::_('J2STORE_INVOICE_NO');?></th>
                <th><?php echo JText::_('J2STORE_ORDER_AMOUNT');?></th>
                <th><?php echo JText::_('J2STORE_ORDER_STATUS');?></th>
                <th><?php echo JText::_('J2STORE_ACTIONS');?></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($this->orders as $item):?>
                <?php
                $order = F0FTable::getInstance('Order', 'J2StoreTable')->getClone();
                $order->load(array('order_id'=>$item->order_id));

                ?>
                <tr>
                    <td>
                        <?php echo JHtml::date($item->created_on, J2Store::config()->get('date_format', JText::_('DATE_FORMAT_LC1')), false);?>
                    </td>
                    <td><?php echo $item->invoice; ?></td>
                    <td><?php echo $currency->format($order->get_formatted_grandtotal()); ?></td>
                    <td>
                        <?php if(isset($item->orderstatus_name) && !empty($item->orderstatus_name)) : ?>
                            <label class="label <?php echo $item->orderstatus_cssclass;?>">
                                <?php echo JText::_($item->orderstatus_name);?>
                            </label>
                        <?php else: //legacy compatibility ?>
                            <label class="label">
                                <?php echo JText::_($item->order_state);?>
                            </label>
                        <?php endif; ?>
                    </td>
                    <td>
				<span class="j2store-order-action-icons">
					<span class="j2store-order-view">
					<?php $viewUrl = J2Store::platform()->getMyprofileUrl(array('task' => 'vieworder','tmpl' => 'component','order_id' => $item->order_id));
                    //JRoute::_('index.php?option=com_j2store&view=myprofile&task=vieworder&tmpl=component&order_id='.$item->order_id); ?>
                    <?php echo J2StorePopup::popup($viewUrl, '', array('class'=>'fa fa-list-alt'));?>
					</span>

					<span class="j2store-order-print">
					<?php
                    $printUrl = J2Store::platform()->getMyprofileUrl(array('task' => 'printOrder', 'tmpl' => 'component','order_id' => $item->order_id));
                    //JRoute::_('index.php?option=com_j2store&view=myprofile&task=printOrder&tmpl=component&order_id='.$item->order_id);
                    echo J2StorePopup::popup($printUrl, '', array('class'=>'fa fa-print'));
                    ?>
					</span>

                    <!-- display plugin event actions -->
 					<?php if(isset($item->after_display_order)):
                        echo $item->after_display_order;
                    endif;?>
				</span>
                    </td>
                </tr>
            <?php endforeach;?>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="5">
                    <?php echo $this->order_pagination->getListFooter(); ?>
                </td>
            </tr>
            </tfoot>
        </table>
        <input name="option" value="com_j2store" type="hidden"/>
        <input name="view" value="myprofile" type="hidden"/>
    </form>
    <?php echo J2Store::plugin()->eventWithHtml('AfterMyProfileOrderDisplay',array($this->orders));?>
<?php endif; ?>