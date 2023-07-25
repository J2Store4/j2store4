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
?>
<div class="j2store">
<?php if(!isset($this->error)): ?>
<!--    ORDER SUMMARY   -->
	<?php if(isset($this->order)): ?>
		<div class="j2storeOrderSummary">
			<?php echo $this->loadAnyTemplate('site:com_j2store/checkout/default_cartsummary'); ?>
		</div>
	<?php endif; ?>

	<?php echo J2Store::plugin()->eventWithHtml('BeforeCheckoutConfirm', array($this)); ?>
	
	<?php if(isset($this->plugin_html)): ?>
			<!--    PAYMENT METHOD   -->
		<h3>
			<?php echo JText::_("J2STORE_PAYMENT_METHOD"); ?>
		</h3>
	
		<div class="payment">
			<?php echo $this->plugin_html; ?>
		</div>
	<?php endif; ?>

	<?php if(isset($this->free_redirect) && strlen($this->free_redirect) > 5): ?>
	<form action="<?php echo J2Store::platform()->getCheckoutUrl(array('task' => 'confirmPayment')); ?>" method="post" >
	<input type="submit" class="btn btn-primary" value="<?php echo JText::_('J2STORE_PLACE_ORDER'); ?>" />
	
	<input type="hidden" name="option" value="com_j2store" />
	<input type="hidden" name="view" value="checkout" />
	<input type="hidden" name="task" value="confirmPayment" />
	</form>
	<?php endif;?>
<?php else: ?>
	<?php echo $this->error; ?>
<?php endif; ?>
<?php echo J2Store::plugin()->eventWithHtml('AfterCheckoutConfirm', array($this)); ?>
</div>