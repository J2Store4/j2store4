<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;?>
<table class="table table-bordered">
				<tr>
					<td><?php echo JText::_('J2STORE_ADDRESS_VENDOR_NAME');?></td>
					<td><?php echo $this->item->first_name.' ' .$this->item->last_name;?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('J2STORE_ADDRESS_LINE1');?></td>
					<td><?php echo $this->item->address_1;?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('J2STORE_ADDRESS_LINE2');?></td>
					<td><?php echo $this->item->address_2;?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('J2STORE_ADDRESS_CITY');?></td>
					<td><?php echo $this->item->city;?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('J2STORE_ADDRESS_STATE');?></td>
					<td><?php echo $this->item->zone_name;?></td>
				</tr>
				<tr>
					<td><?php echo JText::_('J2STORE_ADDRESS_COUNTRY');?></td>
					<td><?php echo $this->item->country_name;?></td>
				</tr>
			</table>
