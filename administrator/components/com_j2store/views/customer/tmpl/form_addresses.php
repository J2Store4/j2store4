<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
?>

			<div class="well">
					<!-- Customer's  Billing Address Details  -->
					<address class="pull-left">
						<strong>
							<?php echo $this->item->first_name.' '.$this->item->last_name;?>
						</strong><br />
						<?php echo $this->item->address_1;?>
						<?php echo $this->item->city.' '.$this->item->zip;?>
						<?php echo $this->item->zone_name;?>
						<?php echo $this->item->country_name;?>
						<?php echo $this->item->phone_1;?>
					</address>

					<!--  Delete Options  for Billing Address -->
					<span class="pull-right">
						<a class="btn btn-danger" href="<?php echo JRoute::_('index.php?option=com_j2store&view=customer&task=delete&id='.$this->item->j2store_address_id);?>">
							<?php echo JText::_('J2STORE_DELETE');?>
						</a>
					</span>
			</div>