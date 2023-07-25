<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 * 
 * Bootstrap 2 layout of product detail
 */
// No direct access
defined('_JEXEC') or die;
?>

<?php if($this->params->get('item_show_title', 1)): ?>
	<h<?php echo $this->params->get('item_title_headertag', '2'); ?> class="product-title">
		<?php echo $this->escape($this->product->product_name); ?>
	</h<?php echo $this->params->get('item_title_headertag', '2'); ?>>
<?php endif; ?>

