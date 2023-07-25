<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();
?>
<?php if(isset($this->shipping->ordershipping_type) && !empty($this->shipping->ordershipping_name)): ?>

<table class="table table-striped">
	<tr>
		<td><?php echo JText::_('J2STORE_SHIPPING_PLUGIN_NAME'); ?>	:</td>
		<td><?php echo JText::_($this->shipping->ordershipping_name); ?></td>
	</tr>
</table>
<?php endif;?>
<?php echo J2Store::plugin()->eventWithHtml('AdminOrderAfterShippingInformation', array($this)); ?>
