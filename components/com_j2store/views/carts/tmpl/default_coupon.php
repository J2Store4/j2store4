<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
?>
<?php if($this->params->get('enable_coupon', 0)):?>
   <div class="coupon">
	    <form action="<?php echo JRoute::_('index.php'); ?>" method="post" enctype="multipart/form-data">	 
	    <?php
		$coupon = F0FModel::getTmpInstance ( 'Coupons', 'J2StoreModel' )->get_coupon();
	    ?>
		<input type="text" name="coupon" value="<?php echo $coupon; ?>" />
		<input type="submit" value="<?php echo JText::_('J2STORE_APPLY_COUPON')?>" class="button btn btn-primary" />
		<input type="hidden" name="option" value="com_j2store" />
         <input type="hidden" name="view" value="carts" />
         <input type="hidden" name="task" value="applyCoupon" />	    
	     </form>
	    </div> 
    <?php endif; ?>