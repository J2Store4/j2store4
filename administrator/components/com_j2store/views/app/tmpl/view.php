<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$row = $this->item;?>
	<?php $results = J2Store::plugin()->eventWithHtml('GetAppView', array($row)); ?>
	<h3><?php echo JText::_($row->name); ?></h3>
	<?php echo $results; ?>