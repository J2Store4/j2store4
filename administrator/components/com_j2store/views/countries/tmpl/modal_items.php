<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<table id="itemsList" class="table table-striped">
		<thead>
			<tr>
				<th>
					<input type="checkbox" name="checkall-toggle"
					value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
					onclick="Joomla.checkAll(this)" />
				</th>
				<th><?php echo JText::_('J2STORE_COUNTRY_NAME');?></th>
				<th><?php echo JText::_('J2STORE_COUNTRY_CODE2');?></th>
				<th><?php echo JText::_('J2STORE_COUNTRY_CODE3');?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="4"><?php  echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
		<tbody>
			<?php if(isset($this->countries) && !empty($this->countries)):?>
			<?php foreach($this->countries as $i => $item):?>
			<tr>

				<td>
					<?php echo JHTML::_('grid.id', $i, $item->j2store_country_id );?>
				</td>
				<td><?php echo $item->country_name;?></td>
				<td><?php echo $item->country_isocode_2;?></td>
				<td><?php echo $item->country_isocode_3;?></td>
			</tr>
			<?php endforeach;?>

			<?php else:?>
			<tr>
				<td colspan="4">
					<?php echo JText::_('J2STORE_NO_ITEMS_FOUND');?>
				</td>
			</tr>
			<?php endif;?>
		</tbody>
	</table>