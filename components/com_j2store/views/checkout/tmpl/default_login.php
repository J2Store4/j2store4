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
// get j2Store Params to determine which bootstrap version we're using - Waseem Sadiq (waseem@bulletprooftemplates.com)
$J2gridRow = ($this->params->get('bootstrap_version', 2) == 2) ? 'row-fluid' : 'row';
$J2gridCol = ($this->params->get('bootstrap_version', 2) == 2) ? 'span' : 'col-md-';
?>
<div class="<?php echo $J2gridRow;?>">
  <?php if($this->params->get('allow_registration', 1) || $this->params->get('allow_guest_checkout', 0)): ?>
    <div class="<?php echo $J2gridCol;?>6 left">
      <h2><?php echo JText::_('J2STORE_CHECKOUT_NEW_CUSTOMER'); ?></h2>
      <p><?php echo JText::_('J2STORE_CHECKOUT_OPTIONS'); ?></p>
      <!-- registration -->
      <?php if($this->params->get('allow_registration', 1)): ?>
        <label for="register">
          <?php if ($this->account == 'register') { ?>
            <input type="radio" name="account" value="register" id="register" checked="checked" />
          <?php } else { ?>
            <input type="radio" name="account" value="register" id="register" />
          <?php } ?>
          <b><?php echo JText::_('J2STORE_CHECKOUT_REGISTER'); ?></b></label>
        <br />
      <?php endif; ?>

      <!-- guest -->
      <?php if ($this->params->get('allow_guest_checkout', 0)) : ?>
        <label for="guest">
          <?php if ($this->account == 'guest') { ?>
            <input type="radio" name="account" value="guest" id="guest" checked="checked" />
          <?php } else { ?>
            <input type="radio" name="account" value="guest" id="guest" />
          <?php } ?>
          <b><?php echo JText::_('J2STORE_CHECKOUT_GUEST'); ?></b></label>
        <br />
      <?php endif; ?>
      <br />
      <?php if($this->params->get('allow_registration', 1)): ?>
        <p><?php echo JText::_('J2STORE_CHECKOUT_REGISTER_ACCOUNT_HELP_TEXT'); ?></p>
      <?php endif; ?>

        <button type="button" id="button-account" class="button btn btn-primary" ><?php echo JText::_('J2STORE_CHECKOUT_CONTINUE'); ?></button>
      <br />
    </div>
  <?php endif; ?>
  <?php if($this->params->get('show_login_form', 1)): ?>
  <div id="login" class="<?php echo $J2gridCol;?>6 right">
    <h2><?php echo JText::_('J2STORE_CHECKOUT_RETURNING_CUSTOMER'); ?></h2>
    <b><?php echo JText::_('J2STORE_CHECKOUT_USERNAME'); ?></b><br />
    <input type="text" name="email" value=""  onkeypress="return loginKeyPress(event);"/>
    <br />
    <br />
    <b><?php echo JText::_('J2STORE_CHECKOUT_PASSWORD'); ?></b><br />
    <input type="password" name="password" value="" onkeypress="return loginKeyPress(event);" />
    <br />
      <?php echo J2Store::plugin()->eventWithHtml('BeforeCheckoutLoginButton', array($this)); ?>
    <button type="button" id="button-login" class="button btn btn-primary" ><?php echo JText::_('J2STORE_CHECKOUT_LOGIN'); ?></button><br />
    <input type="hidden" name="task" value="login_validate" />
    <input type="hidden" name="option" value="com_j2store" />
    <input type="hidden" name="view" value="checkout" />
    <br />
    <?php
    $forgot_pass_link = JRoute::_('index.php?option=com_users&view=reset');
    ?>
    <a href="<?php echo $forgot_pass_link;?>" target="_blank"><?php echo JText::_('J2STORE_FORGOT_YOUR_PASSWORD'); ?></a>
  </div>
</div>
<?php endif; ?>
<?php echo J2Store::plugin()->eventWithHtml('CheckoutLogin', array($this)); ?>
<input type="hidden" name="option" value="com_j2store" />
<input type="hidden" name="view" value="checkout" />
