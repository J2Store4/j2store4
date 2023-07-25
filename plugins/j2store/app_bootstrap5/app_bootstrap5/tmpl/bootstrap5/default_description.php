<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
?>

<?php if($this->params->get('list_show_short_desc', 1)): ?>
	<div class="product-short-description"><?php echo $this->product->product_short_desc; ?></div>
<?php endif; ?>

<?php if($this->params->get('list_show_long_desc', 0)): ?>
	<div class="product-long-description"><?php echo $this->product->product_long_desc; ?></div>
<?php endif; ?>
