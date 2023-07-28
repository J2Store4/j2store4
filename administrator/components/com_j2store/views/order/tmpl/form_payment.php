<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();

$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<div class="payment-information">
	<table class="table table-bordered">
		<tr>
			<td><?php echo JText::_('J2STORE_ORDER_PAYMENT_TYPE'); ?>	:</td>
			<td><?php echo JText::_($this->item->orderpayment_type); ?></td>
		</tr>
		<?php if(!empty($this->item->transaction_id)): ?>
		<tr>
			<td><?php echo JText::_('J2STORE_ORDER_TRANSACTION_ID'); ?></td>
			<td><?php echo $this->item->transaction_id; ?></td>
		</tr>
		<?php endif; ?>
		<tr>
			<td><?php echo JText::_('J2STORE_ORDER_TRANSACTION_LOG'); ?></td>
			<td>  <!-- Button to trigger modal -->
   				 <a data-fancybox data-src="#myTransaction" type="button" class="btn btn-success" ><?php echo JText::_('J2STORE_VIEW');?></a>
  			</td>
		</tr>
		<tr>
			<td colspan="2">

				<?php $pay_html =  trim(J2Store::getSelectableBase()->getFormattedCustomFields($this->orderinfo, 'customfields', 'payment'));?>
				<?php if($pay_html ):?>
					<div class="center">
						<strong><?php echo JText::_('J2STORE_PAYMENT_ADDRESS');?></strong>
						<?php echo J2StorePopup::popupAdvanced("index.php?option=com_j2store&view=orders&task=setOrderinfo&order_id=".$this->item->order_id."&address_type=payment&layout=address&tmpl=component",'',array('class'=>'fa fa-pencil','refresh'=>true,'id'=>'fancybox','width'=>700,'height'=>600));?>
                    </div>
					<?php echo $pay_html; ?>
				<?php endif;?>

			</td>
		</tr>

</table>
<?php echo J2Store::plugin()->eventWithHtml('AdminOrderAfterPaymentInformation', array($this)); ?>
</div>

<!-- Transaction log modal window -->
    <div id="myTransaction" style="display:none;">
            <h3 >
                <?php echo JText::_('J2STORE_TRANSACTION_LOG_HEADER'); ?>
                &nbsp;
                <?php echo $this->item->order_id; ?>
            </h3>
            <div class="j2store"  >
                <div class="<?php echo $row_class ?>">
                    <div class="<?php echo $col_class ?>12">
                        <div class="alert alert-info">
                            <?php echo JText::_('J2STORE_TRANSACTION_LOG_HELP_MSG');?>
                        </div>
                        <ul>
                            <li><?php echo JText::_('J2STORE_ORDER_TRANSACTION_STATUS'); ?>
                                <div class="alert alert-warning">
                                    <small><?php echo JText::_('J2STORE_ORDER_TRANSACTION_STATUS_HELP_MSG'); ?>
                                    </small>
                                </div>
                                <p>
                                    <?php echo JText::_($this->item->transaction_status); ?>
                                </p>
                            </li>
                            <li><?php echo JText::_('J2STORE_ORDER_TRANSACTION_DETAILS'); ?> <br />
                                <div class="alert alert-warning">
                                    <small><?php echo JText::_('J2STORE_ORDER_TRANSACTION_DETAILS_HELP_MSG'); ?>
                                    </small>
                                </div>
                                <p>
                                    <?php echo JText::_($this->item->transaction_details); ?>
                                </p>
                            </li>

                        </ul>
                    </div>
                 </div>
        </div>
    </div>
