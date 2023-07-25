<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();
//$this->prefix = 'jform[order]';
?>
<div class="panel panel-default order-history">
	<div class="panel-heading">
		<h4><?php echo JText::_('J2STORE_ORDER_HISTORY');?></h4>
	</div>
<div class="panel-body">	
<table class="table table-striped table-condensed table-bordered">
	<thead>
		<tr>
			<th><?php echo JText::_("J2STORE_ORDER_DATE"); ?></th>
			<th><?php echo JText::_("J2STORE_ORDER_COMMENT"); ?></th>
			<th><?php echo JText::_("J2STORE_ORDER_STATUS"); ?></th>

		</tr>
	</thead>
	<?php foreach($this->orderhistory as $history):?>
	<tr>
		<td>
			<?php echo JHTML::_('date', $history->created_on, $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'))); ?>
		</td>
		<td>
			<?php echo JText::_($history->comment);?>
		</td>
		<td>
			<?php 	echo J2Html::getOrderStatusHtml($history->order_state_id);?>
		</td>
	</tr>
	<?php endforeach;?>

</table>
</div>
</div>