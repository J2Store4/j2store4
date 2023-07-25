<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="j2store-product j2store-product-<?php echo $this->product->j2store_product_id; ?> product-<?php echo $this->product->j2store_product_id; ?> <?php echo $this->product->product_type; ?> default">
	<?php if(isset($this->sublayout) && !empty($this->sublayout)): ?>
		<?php echo $this->loadTemplate($this->sublayout); ?>
	<?php else: ?>	
		<?php echo $this->loadTemplate($this->product->product_type); ?>
	<?php endif;?>
	<?php echo J2Store::plugin ()->eventWithHtml ( 'AfterProductDisplay', array($this->product,$this) )?>
</div>