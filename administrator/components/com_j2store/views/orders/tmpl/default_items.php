<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
?>

	<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th width="1%"><?php echo JText::_( 'J2STORE_NUM' ); ?>
				</th>
				<th width="2%"><input type="checkbox" name="checkall-toggle"
					value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
					onclick="Joomla.checkAll(this)" />
				</th>
				<th width="5%" class="title"><?php  echo JHTML::_('grid.sort',  'J2STORE_INVOICE_NO', 'invoice',$this->state->filter_order_Dir,$this->state->filter_order ); ?>
				</th>
				<th width="5%" class="title"><?php  echo JHTML::_('grid.sort',  'J2STORE_ORDER_ID', 'order_id', $this->state->filter_order_Dir,$this->state->filter_order  ); ?>
				</th>
				<th width="7%"><?php  echo JHTML::_('grid.sort',  'J2STORE_ORDER_DATE', 'created_on',$this->state->filter_order_Dir,$this->state->filter_order  ); ?>
				</th>
				<th width="20%" class="title"><?php echo JHTML::_('grid.sort',  'J2STORE_CUSTOMER', 'billing_first_name',$this->state->filter_order_Dir,$this->state->filter_order  ); ?>
				</th>
				<th width="7%"><?php   echo JHTML::_('grid.sort',  'J2STORE_ORDER_AMOUNT', 'order_total',$this->state->filter_order_Dir,$this->state->filter_order ); ?>
				</th>
				<th width="15%"><?php  echo JHTML::_('grid.sort',  'J2STORE_ORDER_PAYMENT_TYPE', 'orderpayment_type', $this->state->filter_order_Dir,$this->state->filter_order ); ?>
				</th>
					<th width="10%"><?php  echo JText::_('J2STORE_ORDER_STATUS' ); ?>
				</th>
				<?php echo J2Store::plugin ()->eventWithHtml ( 'AdminOrderListTab', array($this->state))?>
				<th></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="10"><?php  echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php
					if($this->items && !empty($this->items)):
				foreach($this->items as $i=> $row):
				$link 	= JRoute::_( 'index.php?option=com_j2store&view=order&id='.$row->j2store_order_id  );
				$checked = JHTML::_('grid.id', $i, $row->j2store_order_id );
				$order = F0FTable::getInstance('Order', 'J2StoreTable');
				$order->load(array('order_id'=>$row->order_id));
				?>
				<tr>
				<td><?php echo $this->pagination->getRowOffset( $i ); ?>
				</td>
					<td><?php echo $checked; ?>
				</td>
				<td>
					<span class="editlinktip hasTip"
						title="<?php echo JText::_( 'J2STORE_ORDER_VIEW' );?>::<?php echo $this->escape($row->order_id); ?>">
						<a href="<?php echo $link ?>"><?php echo $this->escape($row->invoice); ?></a>
					</span>
				</td>

				<td><span class="editlinktip hasTip"
					title="<?php echo JText::_( 'J2STORE_ORDER_VIEW');?>::<?php echo $this->escape($row->order_id); ?>">
						<a href="<?php echo $link ?>"> <?php echo $this->escape($row->order_id); ?>
					</a>
				</span>
				</td>

 				<td><?php  echo JHTML::_('date',$row->created_on, $this->params->get('date_format', JText::_('DATE_FORMAT_LC1'))); ?></td>
				<td align="center">
				<?php  echo $row->billing_first_name .' '.$row->billing_last_name; ?>
				<br />
				<small>
					<?php echo $row->user_email;?>
				</small>
				<br />
				<?php if($row->user_id == 0): ?>
				<label class="label label-warning"><?php echo JText::_('J2STORE_GUEST')?></label>
				<?php endif;?>
				<br />
					<?php if($row->discount_code):?>
					<?php echo JText::_('J2STORE_COUPON_CODE');?>:<?php echo $row->discount_code;?>
					<?php endif;?>

				</td>

				<td align="center"><?php echo $this->currency->format( $order->get_formatted_grandtotal(), $row->currency_code, $row->currency_value ); ?>
				</td>
				<td align="center"><?php echo JText::_($row->orderpayment_type); ?>
				</td>
				<td align="center">
					<p align="center">
						<span class="label <?php echo $row->orderstatus_cssclass;?> order-state-label">
						<?php echo JText::_($row->orderstatus_name); ?>
						</span>
					</p>
						<?php echo JText::_("J2STORE_CHANGE_ORDER_STATUS"); ?>
						<?php $attr = array("class"=>"input-small" , "id"=>"order_state_id_".$row->j2store_order_id);?>
						<?php
						echo J2Html::select()->clearState()
						->type('genericlist')
						->name('order_state_id')
						->value($row->order_state_id)
						->idTag('order_state_id_'.$row->j2store_order_id)
						->attribs($attr)
						->setPlaceHolders(array(''=>JText::_('J2STORE_SELECT_OPTION')))
						->hasOne('Orderstatuses')
                        ->ordering('ordering')
						->setRelations(
								array (
										'fields' => array
										(
												'key'=>'j2store_orderstatus_id',
												'name'=>'orderstatus_name'
										)
								)
						)->getHtml();

						?>
						<label>
						<input type="checkbox" name="notify_customer" id="notify_customer_<?php echo $row->j2store_order_id;?>" value="1" />
							<?php echo JText::_('J2STORE_NOTIFY_CUSTOMER');?>
						</label>
						<input type="hidden" name="return" value="orders" />
						<input class="btn btn-primary" id="order-list-save_<?php echo $row->j2store_order_id;?>" type="button" onclick="submitOrderState('<?php echo $row->j2store_order_id; ?>','<?php echo $row->order_id; ?>')"
							value="<?php echo JText::_('J2STORE_ORDER_STATUS_SAVE'); ?>" />
				</td>
					<?php echo J2Store::plugin ()->eventWithHtml ( 'AdminOrderListTabContent', array($row))?>
				<td>
					<div class="order-list-print">
				<?php
					$url = JRoute::_( "index.php?option=com_j2store&view=orders&task=printOrder&tmpl=component&order_id=".$row->order_id);
					echo J2StorePopup::popup($url, JText::_( "J2STORE_PRINT_INVOICE" ), array('class'=>'fa fa-print btn btn-small btn-primary'));
				?>
				</div>

				</td>
				<td>
					<a href="<?php echo "index.php?option=com_j2store&view=orders&task=createOrder&oid=".$row->j2store_order_id?>" >
						<i class="icon icon-pencil"></i>
					</a>
				</td>
				</tr>
				<?php endforeach;?>
				<?php else:?>
				<tr>
					<td colspan="10">
						<?php echo JText::_('J2STORE_NO_RESULTS_FOUND');?>
					</td>
				</tr>
				<?php endif;?>
			</tbody>

		</table>