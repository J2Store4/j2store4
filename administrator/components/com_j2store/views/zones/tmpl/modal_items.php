<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>

<table class="table table-striped table-bordered">
		<thead>
			<tr>
				<th>
					<input type="checkbox" name="checkall-toggle"
					value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
					onclick="Joomla.checkAll(this)" />
				</th>
				<th>
					<?php echo JText::_('J2STORE_ZONE_NAME');?>
				</th>
				<th>
					<?php echo JText::_('J2STORE_COUNTRY_NAME');?>
				</th>
				<th>
					<?php echo JText::_('J2STORE_ZONE_CODE');?>
				</th>

			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="4">
					<?php  echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<?php if(isset($this->zones) && !empty($this->zones)):?>
		<tbody>
		<?php foreach($this->zones as $i => $item):?>
		<tr>
			<td>
				<?php echo JHTML::_('grid.id', $i, $item->j2store_zone_id ); ;?>
			</td>
			<td>
				<?php echo $item->zone_name;?>
			</td>
			<td>
				<?php echo F0FModel::getTmpInstance('Countries','J2StoreModel')->getItem($item->country_id)->country_name;?>
			</td>
			<td>
				<?php echo $item->zone_code;?>
			</td>
		</tr>
		<?php endforeach;?>
		<?php else:?>
			<tr>
				<td colspan="4">
					<?php echo JText::_('J2STORE_NO_ITEMS');?>
				</td>
			</tr>
		<?php endif;?>
	</table>