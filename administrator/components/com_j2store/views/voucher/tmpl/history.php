<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

defined('_JEXEC') or die;
// load tooltip behavior
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
$sidebar = JHtmlSidebar::render();
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>

<?php if(!empty( $sidebar )): ?>
    <div class = "<?php echo $row_class ?>">
	<div id="j-sidebar-container" class="<?php echo $col_class ?>2">
      <?php echo $sidebar ; ?>
   </div>
	<div id="j-main-container" class="<?php echo $col_class ?>10">
        <?php else : ?>
        <div id="j-main-container">
<?php endif;?>
	<h3><?php echo JText::_('J2STORE_VOUCHER_HISTORY'); ?> : <?php echo $this->voucher->voucher_code?></h3>



<table class="table table-bordered table-striped">

	<thead>
		<tr>
			<th><?php echo JText::_('J2STORE_INVOICE')?></th>
			<th><?php echo JText::_('J2STORE_ORDER_ID')?></th>
			<th><?php echo JText::_('J2STORE_CUSTOMER')?></th>
			<th><?php echo JText::_('J2STORE_AMOUNT')?></th>
			<th><?php echo JText::_('J2STORE_DATE')?></th>
		</tr>

	</thead>
	<tbody>
	<?php if(count($this->vouchers)): ?>
		<?php foreach($this->vouchers as $item): ?>
			<?php
				$link = 'index.php?option=com_j2store&view=order&id='.$item->order->j2store_order_id;
			?>
			<tr>
				<td>
				<a href="<?php echo $link; ?>" target="_blank">
					<?php echo $item->order->getInvoiceNumber(); ?>
				</a>
				</td>
				<td>
					<a href="<?php echo $link; ?>" target="_blank">
						<?php echo $item->order_id; ?>
					</a>
				</td>
				<td><?php echo $item->order->user_email; ?></td>
				<td><?php echo $item->discount_amount; ?></td>
				<td><?php echo JHtml::_('date', $item->order->created_on, $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'))); ?></td>
			</tr>
		<?php endforeach;?>
	<?php else:?>
		<?php echo JText::_('J2STORE_NO_RESULTS_FOUND');?>
	<?php endif;?>
	</tbody>
</table>

</div>
    </div>