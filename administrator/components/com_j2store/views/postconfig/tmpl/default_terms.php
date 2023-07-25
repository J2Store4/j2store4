<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
	<?php if(J2Store::isPro() != 1): ?>
	<h3><?php echo JText::_('J2STORE_POSTCONFIG_LBL_MANDATORYINFO') ?></h3>

	<label for="acceptlicense" class="postsetup-main" id="acceptlicense"> <input
		type="checkbox" name="acceptlicense"
		<?php if($this->params->get('acceptlicense')): ?> checked="checked" <?php endif; ?> />
		<?php echo JText::_('J2STORE_POSTCONFIG_LBL_ACCEPTLICENSE')?>
	</label> </br>
	<div class="postsetup-desc"><?php echo JText::_('J2STORE_POSTCONFIG_DESC_ACCEPTLICENSE');?></div>
	<br /> <label for="acceptsupport" class="postsetup-main" id="acceptsupport"> <input
		type="checkbox"  name="acceptsupport"
		<?php if($this->params->get('acceptsupport')): ?> checked="checked" <?php endif; ?> />
		<?php echo JText::_('J2STORE_POSTCONFIG_LBL_ACCEPTSUPPORT')?>
	</label> </br>
	<?php endif;?>