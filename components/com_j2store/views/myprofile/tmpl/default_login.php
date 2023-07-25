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
defined('_JEXEC') or die('Restricted access');
$platform = J2Store::platform();
$return_url = $platform->getMyprofileUrl(array(),false,true);
//$return_url = trim(JUri::root(),'/').$platform->getMyprofileUrl();
//echo "<pre>";print_r(base64_decode('aW5kZXgucGhwP29wdGlvbj1jb21fajJzdG9yZSZ2aWV3PW15cHJvZmlsZQ=='));
//echo "<pre>";print_r(base64_decode('aHR0cDovL2xvY2FsaG9zdDo4MzAwL2luZGV4LnBocC9lbi9teXByb2ZpbGU='));exit;
//$return_url = JRoute::_( "index.php?option=com_j2store&view=myprofile" );
//$return_url = "index.php?option=com_j2store&view=myprofile";
$guest_action_url = $platform->getMyprofileUrl(array('task' => 'guestentry'));
//JRoute::_( "index.php?option=com_j2store&view=myprofile&task=guestentry" );
$platform->addScript('j2store-jquery-validate','/media/j2store/js/jquery.validate.min.js');
//$document =JFactory::getDocument();
//$document->addScript(JURI::root(true).'/media/j2store/js/jquery.validate.min.js');
$params = J2Store::config();
// get j2Store Params to determine which bootstrap version we're using - Waseem Sadiq (waseem@bulletprooftemplates.com)
$J2gridRow = ($params->get('bootstrap_version', 2) == 2) ? 'row-fluid' : 'row';
$J2gridCol = ($params->get('bootstrap_version', 2) == 2) ? 'span' : 'col-md-';
?>
<script type="text/javascript">
			<!--
			if(typeof(j2store) == 'undefined') {
				var j2store = {};
			}

			if(typeof(j2store.jQuery) == 'undefined') {
				j2store.jQuery = jQuery.noConflict();
			}
			-->
 </script>

<div class="j2store">
	<div class="<?php echo $J2gridRow; ?>">

		<?php if($params->get('show_login_form', 1)): ?>
		<script type="text/javascript">
			<!--
			j2store.jQuery(document).ready(function(){
				j2store.jQuery('#j2storeOrderLoginForm').validate();
			});
			-->
		 </script>
		<div class="<?php echo $J2gridCol; ?>5">
			<h3>
				<?php echo JText::_('J2STORE_LOGIN'); ?>
			</h3>
			<!-- LOGIN FORM -->
			<?php if (JPluginHelper::isEnabled('authentication', 'openid')) :
			$lang->load( 'plg_authentication_openid', JPATH_ADMINISTRATOR );
			$langScript =   'var JLanguage = {};'.
					' JLanguage.WHAT_IS_OPENID = \''.JText::_( 'WHAT_IS_OPENID' ).'\';'.
					' JLanguage.LOGIN_WITH_OPENID = \''.JText::_( 'LOGIN_WITH_OPENID' ).'\';'.
					' JLanguage.NORMAL_LOGIN = \''.JText::_( 'NORMAL_LOGIN' ).'\';'.
					' var modlogin = 1;';
//			$document = JFactory::getDocument();
//			$document->addScriptDeclaration( $langScript );
			$platform->addInlineScript($langScript);

			JHTML::_('script', 'openid.js');
        				endif; ?>

			<form
				action="<?php echo JRoute::_('index.php', true); ?>"
				method="post" name="login" id="j2storeOrderLoginForm">
                <input type="hidden" name="task" value="user.login">
				<label for="username" class="j2storeUserName"><?php echo JText::_('J2STORE_USERNAME'); ?>


					<input type="text" name="username" class="inputbox required"
					alt="username"
					title="<?php echo JText::_('J2STORE_LOGIN_FORM_ENTER_USERNAME');?>" />
				</label> <label for="password" class="j2storePassword"><?php echo JText::_('J2STORE_PASSWORD'); ?>
					<input type="password" name="password" class="inputbox"
					alt="password"
					title="<?php echo JText::_('J2STORE_LOGIN_FORM_ENTER_PASSWORD');?>" />
				</label>
				<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>

				<label for="remember"> <input type="checkbox" name="remember"
					class="inputbox" value="yes" /> <?php echo JText::_('J2STORE_REMEMBER_ME'); ?>
				</label>
				<?php endif; ?>
				<div class="clr"></div>
				<input type="submit" name="submit"
					class="j2store_checkout_button btn btn-primary"
					value="<?php echo JText::_('J2STORE_LOGIN') ?>" />
				<ul class="loginLinks">
					<li><?php // TODO Can we do this in a lightbox or something? Why does the user have to leave? ?>
						<a
						href="<?php echo JRoute::_( 'index.php?option=com_users&view=reset' ); ?>">
							<?php echo JText::_('J2STORE_FORGOT_YOUR_PASSWORD'); ?>
					</a>
					</li>
					<li><?php // TODO Can we do this in a lightbox or something? Why does the user have to leave? ?>
						<a
						href="<?php echo JRoute::_( 'index.php?option=com_users&view=remind' ); ?>">
							<?php echo JText::_('J2STORE_FORGOT_YOUR_USERNAME'); ?>
					</a>
					</li>
                    <?php $usersConfig = JComponentHelper::getParams('com_users'); ?>
                    <?php if ($usersConfig->get('allowUserRegistration')) : ?>
                        <li>
                            <a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
                                <?php echo JText::_('J2STORE_LOGIN_REGISTER'); ?></a>
                        </li>
                    <?php endif; ?>
				</ul>
				<input type="hidden" name="option" value="com_users" /> <input
					type="hidden" name="task" value="user.login" /> <input
					type="hidden" name="return"
					value="<?php echo base64_encode( $return_url ); ?>" />
				<?php echo JHTML::_( 'form.token' ); ?>
			</form>
		</div>
		<?php endif; ?>
		<?php if ($params->get('allow_guest_checkout')) : ?>
		<script type="text/javascript">
			<!--
			j2store.jQuery(document).ready(function(){
				j2store.jQuery('#j2storeOrderGuestForm').validate();
			});
			-->
		 </script>
		<div class="<?php echo $J2gridCol; ?>6">
			<h3>
				<?php echo JText::_('J2STORE_ORDER_GUEST_VIEW'); ?>
			</h3>
			<small><?php echo JText::_('J2STORE_ORDER_GUEST_VIEW_DESC'); ?> </small>
			<!-- Registration form -->
			<form action="<?php echo $guest_action_url;?>" method="post"
				class="adminform" name="adminForm" id="j2storeOrderGuestForm">

				<div class="j2store_register_fields">
					<label for="email"> <?php echo JText::_( 'J2STORE_ORDER_EMAIL' ); ?>
						*
					</label><input name="email" id="email" class="required email"
						type="text"
						title="<?php echo JText::_('J2STORE_VALIDATION_ENTER_VALID_EMAIL'); ?>" />
				</div>

				<div class="j2store_register_fields">
					<label for="order_token"> <?php echo JText::_( 'J2STORE_ORDER_TOKEN' ); ?>*
					</label> <input name="order_token" id="order_token"
						class="required" type="text"
						title="<?php echo JText::_('J2STORE_VALIDATION_ENTER_VALID_TOKEN'); ?>" />
				</div>
				<div class="j2store_register_fields">

					<input type="submit" name="submit"
						class="j2store_checkout_button btn btn-primary"
						value="<?php echo JText::_('J2STORE_VIEW') ?>" />
				</div>
				<?php echo JHTML::_( 'form.token' ); ?>
			</form>
		</div>
		<?php endif; ?>
		<?php echo J2Store::plugin()->eventWithHtml('MyProfileLogin', array($this)); ?>
	</div>
</div>
