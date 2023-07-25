<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal','a.modal');
//JHTML::_('behavior.modal', 'a.modal');
?>

			<div class="control-group well">
					<!-- Customer's  Billing Address Details  -->
					<address class="pull-left">
						<strong>
                            <?php echo $this->item->company;?> <br/>
							<?php echo $this->item->first_name.' '.$this->item->last_name;?>
						</strong>
						<?php echo $this->item->address_1;?>
						<?php echo $this->item->city.' '.$this->item->zip;?>
						<?php echo $this->item->zone_name;?>
						<?php echo $this->item->country_name;?>
						<?php echo $this->item->phone_1;?>

						<?php if(isset( $this->table_fields ) && $this->table_fields):?>
								<?php $field_present = 0; ?>
							<?php foreach ($this->table_fields as $field):?>

								<?php if($field->field_core == 0):?>
									<?php $name_key = $field->field_namekey; ?>
									<?php if(isset( $this->item->$name_key ) && !empty($this->item->$name_key)):?>
										<?php $field_present += 1; ?>
										<?php if($field_present == 1): ?>
										<br /><strong><?php echo JText::_('J2STORE_CUSTOM_FIELDS');?></strong><br />
										<?php endif; ?>
										<?php echo $field->field_name.' :'; ?>
										<?php echo $this->item->$name_key;?>
									<?php endif;?>
								<?php endif;?>
							<?php endforeach;?>
						<?php endif;?>
					</address>

					<!--  Delete Options  for Billing Address -->
					<span class="pull-right">
						<?php echo J2StorePopup::popupAdvanced("index.php?option=com_j2store&view=customer&task=editAddress&id=".$this->item->j2store_address_id."&tmpl=component",JText::_('J2STORE_EDIT'),array('class'=>'btn btn-primary','refresh'=>true,'id'=>'fancybox','width'=>700,'height'=>600));?>
						<a class="btn btn-danger" href="<?php echo JRoute::_('index.php?option=com_j2store&view=customer&task=delete&id='.$this->item->j2store_address_id);?>">
							<?php echo JText::_('J2STORE_DELETE');?>
						</a>
					</span>
			</div>