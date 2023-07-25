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

<?php if($this->params->get('item_show_sdesc', 1)): ?>
	<div class="product-sdesc">
		<?php echo $this->product->product_short_desc; ?>
	</div>
<?php endif; ?>

