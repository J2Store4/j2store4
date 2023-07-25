<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();
$order_state_save_link = JRoute::_('index.php?option=com_j2store&view=orders&task=orderstatesave');

$this->order_state =J2Html::select()
	->type('genericlist')
	->name('order_state_id')
	->value($this->item->order_state_id)
	->idTag("order_state_id_".$this->item->j2store_order_id)
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

	<div class="panel-body">
		<div class="control-group">
			<?php echo  J2Html::label(JText::_('J2STORE_ORDER_STATUS'),'order_status',array('class'=>'control-label'));?>
			<?php echo $this->order_state; ?>
		</div>

		 <div class="control-group">
			<label class="control-label">
				<input type="checkbox" name="notify_customer" value="1" />
					<?php echo JText::_('J2STORE_NOTIFY_CUSTOMER');?>
			</label>

			<label class="control-label">
				<input type="checkbox" name="reduce_stock" value="1" />
					<?php echo JText::_('J2STORE_REDUCE_STOCK');?>
			</label>
			<label class="control-label">
				<input type="checkbox" name="increase_stock" value="1" />
					<?php echo JText::_('J2STORE_INCREASE_STOCK');?>
			</label>

			<?php if($this->order->has_downloadable_item()): ?>
				<label class="control-label">
					<input type="checkbox" name="grant_download_access" value="1" />
					<?php echo JText::_('J2STORE_GRANT_DOWNLOAD_PERMISSION');?>
				</label>

				<label class="control-label">
					<input type="checkbox" name="reset_download_expiry" value="1" />
					<?php echo JText::_('J2STORE_RESET_DOWNLOAD_EXPIRY');?>
				</label>

                <label class="control-label">
                    <input type="checkbox" name="reset_download_limit" value="1" />
                    <?php echo JText::_('J2STORE_RESET_DOWNLOAD_LIMIT');?>
                </label>
			<?php endif;?>
		</div>

		<div class="control-group">
			<input class="btn btn-large btn-success" type="submit" onclick="jQuery('#task').attr('value','saveOrderstatus');"
				value="<?php echo JText::_('J2STORE_ORDER_STATUS_SAVE'); ?>" />
		</div>

	</div>