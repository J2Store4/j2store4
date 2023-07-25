<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
//JHTML::_('behavior.modal');
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
?>
<div class="j2store">
	<h1><?php echo JText::_('J2STORE_ZONES')?></h1>
  <form action="index.php" class="form-horizontal" method="post" name="adminForm" id="adminForm">
				<?php echo J2Html::hidden('option','com_j2store');?>
				<?php echo J2Html::hidden('view','zones',array('id'=>'view'));?>
				<?php echo J2Html::hidden('geozone_id',$this->geozone_id);?>
				<?php echo J2Html::hidden('task','elements',array('id'=>'task'));?>
				<?php echo J2Html::hidden('boxchecked','0');?>
				<?php echo J2Html::hidden('filter_order','');?>
				<?php echo J2Html::hidden('filter_order_Dir','');?>
				<?php echo JHTML::_('form.token'); ?>
				<div class="j2store-zone-filters">
					<!-- general Filters -->
					<?php echo $this->loadTemplate('filters');?>

				</div>
				<div class="j2store-zone-list">
					<?php echo $this->loadTemplate('items');?>
				</div>
	</form>
</div>


