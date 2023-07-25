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
$platform->loadExtra('behavior.formvalidator');
$version = 'old';
$document = JFactory::getDocument();
if(version_compare(JVERSION,'3.99.99','ge')){
    $version = 'current';
    $user_modal_url='index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;required=0';
    $document->getWebAssetManager()
        ->useScript('webcomponent.field-user');
} elseif(version_compare(JVERSION,'3.6.1','ge')){
	$version = 'new';
	$user_modal_url = "index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;required=0&amp;field={field-user-id}&amp;ismoo=0&amp;excluded=WyIiXQ==";
}elseif(version_compare(JVERSION,'3.5.0','ge')){
	$user_modal_url = "index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;required=0&amp;field={field-user-id}&amp;excluded=WyIiXQ==";
	$version = 'new';
}
if($version == 'new'){
	$document->addScript(JUri::root(true).'/media/jui/js/fielduser.min.js');
}
?>
<div class="order-general-information">
	<div class="info-body">
		<div class="control-group">
			<?php echo J2Html::label(JText::_('J2STORE_ORDER_DATE') ,'created-on',array('class'=>'control-label')); ?>
			<div class="controls">
				<?php echo JHtml::calendar($this->order->created_on, $this->form_prefix.'[created_on]','order-created-on','%d-%m-%Y', array('class'=>'input-small'));?>
			</div>
		</div>
		<?php if($this->order->invoice_prefix && $this->order->invoice_number):?>
		<div class="control-group">
			<?php echo J2Html::label(JText::_('J2STORE_INVOICE') ,'invoice-prefix',array('class'=>'control-label')); ?>
			<div class="controls">
				<?php echo $this->order->invoice_prefix;?><?php echo $this->order->invoice_number;?>
			</div>
		</div>
		<?php endif;?>
		<div class="control-group">
			<?php echo J2Html::label(JText::_('J2STORE_ORDER_ID') ,'order_id',array('class'=>'control-label')); ?>
			<div class="controls">
				<?php echo $this->order->order_id;?>
			</div>
		</div>
		<div class="control-group">
			<?php echo J2Html::label(JText::_('J2STORE_ORDER_EMAIL') ,$this->form_prefix.'[customer_email]',array('class'=>'control-label')); ?>
			<div class="controls">
				<div class="input-append">
					<?php
					$user_name = '';
					if($this->order->user_id){
						$user_name=J2Html::getUserNameById($this->order->user_id);
					}
					?>
					<?php if($version == 'old'): ?>

						<input type="text"  class="input-small"  name="<?php echo $this->form_prefix.'[user_name]';?>" value="<?php echo $user_name;?>" id="jform_user_id_name" readonly="true" aria-invalid="false" />
						<input type="hidden" onchange="j2storeGetAddress()" name="<?php echo $this->form_prefix.'[user_id]';?>" value="<?php echo $this->order->user_id;?>" id="jform_user_id" class="j2store-order-filters"  readonly="true"/>

						<?php $url ='index.php?option=com_users&view=users&layout=modal&tmpl=component&field=jform_user_id';?>
						<?php echo J2StorePopup::popup($url,'<i class="icon icon-user"></i>', array('class'=>'btn btn-primary modal_jform_created_by'));?>

					<?php elseif($version == 'new'): ?>

						<div data-button-select=".button-select" data-input-name=".field-user-input-name" data-input=".field-user-input" data-modal-height="400px" data-modal-width="100%" data-modal=".modal" data-url="<?php echo $user_modal_url;?>" class="field-user-wrapper">
							<div class="input-append">
								<input type="text" class="field-user-input-name " name="<?php echo $this->form_prefix.'[user_name]';?>" readonly="" placeholder="Select a User." value="<?php echo $user_name;?>" id="jform_created_by">
								<a title="Select User" class="btn btn-primary button-select"><span class="icon-user"></span></a>
								<div class="modal hide fade" tabindex="-1" id="userModal_jform_created_by">
									<div class="modal-header">
										<button data-dismiss="modal" class="close" type="button">×</button>
										<h3>Select User</h3>
									</div>
									<div class="modal-body">
									</div>
									<div class="modal-footer">
										<button data-dismiss="modal" class="btn">Cancel</button></div>
								</div>
							</div>
							<input type="hidden" data-onchange="" class="field-user-input " value="<?php echo $this->order->user_id;?>" name="<?php echo $this->form_prefix.'[user_id]';?>" id="jform_created_by_id">
						</div>
                    <?php elseif($version == 'current'): ?>
                        <div class="controls">
                            <joomla-field-user class="field-user-wrapper" url="<?php echo $user_modal_url;?>" modal=".modal" modal-width="100%" modal-height="400px" input=".field-user-input" input-name=".field-user-input-name" button-select=".button-select">
                                <div class="input-group">
                                    <input type="text" class="field-user-input-name " name="<?php echo $this->form_prefix.'[user_name]';?>" readonly="" placeholder="Select a User." value="<?php echo $user_name;?>" id="jform_created_by">
                                    <button type="button" class="btn btn-primary button-select" title="Select User">
                                        <span class="icon-user icon-white" aria-hidden="true"></span>
                                        <span class="visually-hidden">Select User</span>
                                    </button>
                                </div>
                                <input type="hidden" data-onchange="" class="field-user-input " value="<?php echo $this->order->user_id;?>" name="<?php echo $this->form_prefix.'[user_id]';?>" id="jform_created_by_id">
                                <div id="userModal_jform_created_by" role="dialog" tabindex="-1" class="joomla-modal modal fade" data-url="<?php echo $user_modal_url;?>" data-iframe="<iframe class=&quot;iframe&quot; src=&quot;index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;required=0&amp;field=jform_created_by&quot; name=&quot;Select User&quot; title=&quot;Select User&quot; height=&quot;100%&quot; width=&quot;100%&quot;></iframe>">
                                    <div class="modal-dialog modal-lg jviewport-width80">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h3 class="modal-title">Select User</h3>
                                                <button type="button" class="btn-close novalidate" data-bs-dismiss="modal" aria-label="Close">
                                                </button>
                                            </div>
                                            <div class="modal-body jviewport-height60">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button></div>
                                        </div>
                                    </div>
                                </div>
                            </joomla-field-user>
                        </div>

                    <?php endif;?>

				</div>
			</div>
		</div>
		<?php if(!empty($this->order->j2store_order_id) && $this->order->user_id <= 0):?>
			<div class="alert alert-info">
				<?php echo JText::_('J2STORE_EDIT_GUEST_ORDER_NOTE');?>
				<?php if(isset($this->order->user_email) && $this->order->user_email):?>
					<?php echo JText::sprintf('J2STORE_EDIT_GUEST_ORDER_USER_EMAIL_NOTE',$this->order->user_email);?>
				<?php endif;?>
			</div>
		<?php endif;?>
		<div class="control-group">
			<?php echo J2Html::label(JText::_('J2STORE_CUSTOMER_CHECKOUT_LANGUAGE'), 'order-language',array('class'=>'control-label'));?>
			<div class="controls">
				<?php   echo J2Html::select()->clearState()
						->type('genericlist')
						->name($this->form_prefix.'[customer_language]')
						->attribs(array('class'=>'input-small','style'=>'width: 220px;'))
						->value($this->order->customer_language)
						->setPlaceHolders($this->languages)
						->getHtml(); ?>
			</div>
		</div>
        <div class="alert alert-info"><?php echo JText::_('J2STORE_EDIT_ORDER_STATUS_NOTE');?></div>
        <div class="control-group">
            <div><?php echo  J2Html::label(JText::_('J2STORE_ORDER_STATUS'),'order_status',array('class'=>'control-label'));?></div>
			<div class="controls">
				<?php echo $this->order_status;?>
				<input type="hidden" name="<?php echo $this->form_prefix.'[order_state_id]';?>" value="<?php echo (isset($this->order->order_state_id) && !empty($this->order->order_state_id)) ? $this->order->order_state_id : 5;?>"/>
			</div>
		</div>							
		<div class="control-group">
			<?php echo  J2Html::label(JText::_('J2STORE_CUSTOMER_NOTE'),'customer_note',array('class'=>'control-label'));?>
			<div class="controls">
				<textarea name="<?php echo $this->form_prefix.'[customer_note]' ;?>"><?php echo $this->order->customer_note;?></textarea>
			</div>
		</div>
		<div>
		<input type="hidden" name="<?php echo $this->form_prefix.'[update_history]';?>" value="<?php echo $this->update_history;?>"/>
		</div>
	</div>
</div>

<script type="text/javascript">
function jSelectUser_jform_user_id(id, title) {
	var old_id = document.getElementById('jform_user_id').value;
	document.getElementById('jform_user_id').value = id;
	document.getElementById('jform_user_id_name').value = title;
	document.getElementById('jform_user_id').className = document.getElementById('jform_user_id').className.replace();
	SqueezeBox.close();
};
</script>
