<?php
/*------------------------------------------------------------------------
# com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
$platform = J2Store::platform();
// get j2Store Params to determine which bootstrap version we're using - Waseem Sadiq (waseem@bulletprooftemplates.com)
$J2gridRow = ($this->params->get('bootstrap_version', 2) == 2) ? 'row-fluid' : 'row';
$J2gridCol = ($this->params->get('bootstrap_version', 2) == 2) ? 'span' : 'col-md-';
?>
<script type="text/javascript">
	<!--
	function j2storeGetPaymentForm(element, container) {
		var url = '<?php echo JRoute::_('index.php'); ?>';
		var data = 'option=com_j2store&view=checkout&task=getPaymentForm&tmpl=component&payment_element='+ element;
		j2storeDoTask(url, container, document.adminForm, '', data);
	}
	//-->
</script>
<?php echo J2Store::plugin()->eventWithHtml('BeforeDisplayShippingPayment',array($this->order)); ?>

<!-- SHIPPING METHOD -->
<?php if($this->showShipping):?>
	<div class="j2store-shipping" id="shippingcost-pane">
		<div id="onCheckoutShipping_wrapper">
			<?php echo $this->shipping_method_form;?>
		</div>
	</div>
<?php endif;?>

<!-- SHIPPING METHOD END -->


<?php if($this->showPayment): ?>
	<div id='onCheckoutPayment_wrapper'>
		<h3>
			<?php echo JText::_('J2STORE_SELECT_A_PAYMENT_METHOD'); ?>
		</h3>
		<?php if ($this->plugins): ?>

			<?php foreach ($this->plugins as $plugin): ?>

				<?php
				$params= $platform->getRegistry($plugin->params);
				$image = $params->get('display_image', '');
				?>
				<?php echo J2Store::plugin()->eventWithHtml('BeforeDisplayPaymentMethod',array($plugin->element, $this->order)); ?>
				<label class="payment-plugin-image-label <?php echo $plugin->element; ?>" >
					<input value="<?php echo $plugin->element; ?>" class="payment_plugin"
					       name="payment_plugin" type="radio"
					       onclick="j2storeGetPaymentForm('<?php echo $plugin->element; ?>', 'payment_form_div');"
						<?php echo (!empty($plugin->checked)) ? "checked" : ""; ?>
						   title="<?php echo JText::_('J2STORE_SELECT_A_PAYMENT_METHOD'); ?>" />

					<?php if(!empty($image)): ?>
						<img class="payment-plugin-image <?php echo $plugin->element; ?>" src="<?php echo JUri::root().JPath::clean($image); ?>" />
					<?php endif; ?>
					<?php
					$title = $params->get('display_name', '');
					if(!empty($title)) {
						echo JText::_($title);
					} else {
						echo JText::_($plugin->name );
					}
					?>
				</label>

				<?php echo J2Store::plugin()->eventWithHtml('AfterDisplayPaymentMethod',array($plugin->element, $this->order)); ?>
				<?php echo J2Store::plugin()->eventWithHtml('CheckoutShippingPayment', array($this->order)); ?>

			<?php endforeach; ?>
		<?php endif; ?>

	</div>
	<div class="j2error"></div>
	<div id='payment_form_div' style="padding-top: 10px;">
		<?php
		if (!empty($this->payment_form_div))
		{
			echo $this->payment_form_div;
		}
		?>

	</div>
<?php endif; ?>


<?php
//custom fields
$html = $this->storeProfile->get('store_payment_layout');

//first find all the checkout fields
preg_match_all("^\[(.*?)\]^",$html,$checkoutFields, PREG_PATTERN_ORDER);

$allFields = $this->fields;
?>
<?php foreach ($this->fields as $fieldName => $oneExtraField): ?>
	<?php
	$onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';
	//echo $this->fieldsClass->display($oneExtraField,@$this->address->$fieldName,$fieldName,false);
	if(property_exists($this->address, $fieldName)) {
        $placeholder =  (isset($oneExtraField->field_options['placeholder']) ? $oneExtraField->field_options['placeholder'] : "");
        $field_options = '';
        if($placeholder){
            $field_options .= ' placeholder="'.$placeholder.'" ';
        }
		$html = str_replace('['.$fieldName.']',$this->fieldsClass->getFormattedDisplay($oneExtraField,$this->address->$fieldName, $fieldName,false, $field_options, $test = false, $allFields, $allValues = null),$html);
	}
	?>
<?php endforeach; ?>

<?php
//check for unprocessed fields. If the user forgot to add the fields to the checkout layout in store profile, we probably have some.
$unprocessedFields = array();
foreach($this->fields as $fieldName => $oneExtraField) {
	if(!in_array($fieldName, $checkoutFields[1])) {
		$unprocessedFields[$fieldName] = $oneExtraField;
	}
}

//now we have unprocessed fields. remove any other square brackets found.
preg_match_all("^\[(.*?)\]^",$html,$removeFields, PREG_PATTERN_ORDER);
foreach($removeFields[1] as $fieldName) {
    if(!empty($fieldName)){
        $html = str_replace('['.$fieldName.']', '', $html);
    }
}

?>

<?php echo $html; ?>


<?php if(count($unprocessedFields)): ?>
	<div class="<?php echo $J2gridRow;?>">
		<div class="<?php echo $J2gridCol;?>12">
			<?php $uhtml = '';?>
			<?php foreach ($unprocessedFields as $fieldName => $oneExtraField): ?>
				<?php
				$onWhat='onchange'; if($oneExtraField->field_type=='radio') $onWhat='onclick';
				//echo $this->fieldsClass->display($oneExtraField,@$this->address->$fieldName,$fieldName,false);
				if(property_exists($this->address, $fieldName)) {
                    $placeholder =  (isset($oneExtraField->field_options['placeholder']) ? $oneExtraField->field_options['placeholder'] : "");
                    $field_options = '';
                    if($placeholder){
                        $field_options .= ' placeholder="'.$placeholder.'" ';
                    }
					$uhtml .= $this->fieldsClass->getFormattedDisplay($oneExtraField,$this->address->$fieldName, $fieldName,false, $field_options, $test = false, $allFields, $allValues = null);
					$uhtml .='<br />';
				}
				?>
			<?php endforeach; ?>
			<?php echo $uhtml; ?>
		</div>
	</div>
<?php endif; ?>

<?php if($this->params->get('show_customer_note', 1)): ?>
	<div class="customer-note">
		<h3>
			<?php echo JText::_('J2STORE_CUSTOMER_NOTE'); ?>
		</h3>
		<textarea name="customer_note" rows="3" cols="40"></textarea>
	</div>

<?php endif; ?>

<?php if($this->params->get('show_terms', 1)):?>
	<?php $tos = $this->params->get('termsid', ''); ?>
	<div id="checkbox_tos">
		<?php if($this->params->get('terms_display_type', 'link') =='checkbox' ):?>
			<input type="checkbox" class="required" name="tos_check"
			       title="<?php echo JText::_('J2STORE_AGREE_TO_TERMS_VALIDATION'); ?>" />
			<label for="tos_check" id="tos_check">

				<?php echo JText::_('J2STORE_TERMS_AND_CONDITIONS_AGREE_TO'); ?>

				<?php if(!empty($tos)): ?>
                    <a data-fancybox data-src="#j2store-tos-modal" data-touch="false" href="javascript:;" >
                        <?php echo JText::_('J2STORE_TERMS_AND_CONDITIONS'); ?>
                    </a>

				<?php else: ?>
					<?php echo JText::_('J2STORE_TERMS_AND_CONDITIONS'); ?>
				<?php endif; ?>

			</label>
			<div class="j2error"></div>

		<?php else: ?>

			<?php echo JText::_('J2STORE_TERMS_AND_CONDITION_PRETEXT'); ?>

			<?php if(!empty($tos)): ?>
                <a data-fancybox data-src="#j2store-tos-modal" data-touch="false" href="javascript:;" >
                    <?php echo JText::_('J2STORE_TERMS_AND_CONDITIONS'); ?>
                </a>
			<?php else: ?>
				<?php echo JText::_('J2STORE_TERMS_AND_CONDITIONS'); ?>
			<?php endif; ?>
		<?php endif;?>
        <?php if(!empty($tos)): ?>
            <div id="j2store-tos-modal"  style="display:none;">
                <?php if(is_numeric($tos)): ?>
                    <p><?php echo J2Store::article()->display($tos); ?></p>
                <?php endif;?>
            </div>
        <?php endif;?>
	</div>
<?php endif; ?>

<?php /****** To get App term  html ********/?>
<?php echo J2Store::plugin()->eventWithHtml('AfterDisplayShippingPayment',array($this->order)); ?>

<div class="buttons">
	<div class="left">
		<input type="button"
		       value="<?php echo JText::_('J2STORE_CHECKOUT_CONTINUE'); ?>"
		       id="button-payment-method" class="button btn btn-primary" />
	</div>
</div>
<input type="hidden" name="task"
       value="shipping_payment_method_validate" />
<input type="hidden" name="option" value="com_j2store" />
<input type="hidden" name="view" value="checkout" />
