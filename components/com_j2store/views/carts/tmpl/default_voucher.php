<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
?>
<?php if($this->params->get('enable_voucher', 0)):?>
<div class="voucher">
	    <form action="<?php echo JRoute::_('index.php'); ?>" method="post" enctype="multipart/form-data">
	    <?php
		$voucher = F0FModel::getTmpInstance ( 'Vouchers', 'J2StoreModel' )->get_voucher();
	    ?>
		<input type="text" name="voucher" value="<?php echo $voucher; ?>" />
		<input type="submit" value="<?php echo JText::_('J2STORE_APPLY_VOUCHER')?>" class="button btn btn-primary" />
		<input type="hidden" name="option" value="com_j2store" />
         <input type="hidden" name="view" value="carts" />
         <input type="hidden" name="task" value="applyVoucher" />	    
	     </form>
	  </div>   
    <?php endif; ?>