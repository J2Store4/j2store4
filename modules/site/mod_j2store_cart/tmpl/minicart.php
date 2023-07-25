<?php
/*------------------------------------------------------------------------
# mod_j2store_cart - J2 Store Cart
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/

// no direct access
defined('_JEXEC') or die('Restricted access');
$app = JFactory::getApplication();
require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
J2Store::strapper()->addFontAwesome();
J2Store::utilities()->nocache();
$ajax = $app->getUserState('mod_j2store_mini_cart.isAjax');
$hide = false;
if($params->get('check_empty',0) && $list['product_count'] < 1) {
$hide = true;
}
?>
	<?php if(!$ajax): ?>
		<div class="j2store_cart_module_<?php echo $module->id; ?>">
	<?php endif; ?>
		<?php if(!$hide): ?>
			<div class="j2store-minicart-button">
				<span class="cart-item-info">
					<a class="link" href="<?php echo J2Store::platform()->getCartUrl();?>">
						<i class="<?php echo $params->get('minicart_cart_icon_class', 'fa fa-shopping-cart'); ?>"></i>
						<span class="cart-item-count"><?php echo $list['product_count']; ?></span>
					</a>
				</span>
			</div>

		<?php endif; ?>
			<?php if(!$ajax):?>
				</div>
			<?php else: ?>
				<?php $app->setUserState('mod_j2store_mini_cart.isAjax', 0); ?>
			<?php endif; ?>
