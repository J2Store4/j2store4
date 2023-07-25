<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="alert alert-block alert-info">
	<?php echo JText::_('J2STORE_APP_TAB_HELP')?>
</div>	
<?php echo J2Store::plugin()->eventWithHtml('AfterDisplayProductForm', array($this, $this->item)); ?>