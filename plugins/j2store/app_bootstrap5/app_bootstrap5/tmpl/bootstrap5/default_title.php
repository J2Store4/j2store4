<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
?>


<?php if($this->params->get('list_show_title', 1)): ?>
	<h2 class="product-title">
		<?php if($this->params->get('list_link_title', 1)): ?>		
			<a href="<?php echo JRoute::_('index.php?option=com_j2store&view=products&task=view&id='.$this->product->j2store_product_id.'&Itemid='.$this->active_menu->id); ?>"
			title="<?php echo $this->escape($this->product->product_name); ?>" >
		<?php endif; ?>
		
		<?php echo $this->product->product_name; ?>
		<?php if($this->params->get('list_link_title', 1)): ?>
			</a>
		<?php endif; ?>
	</h2>
<?php endif; ?>
