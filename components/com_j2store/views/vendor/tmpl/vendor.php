<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
// get j2Store Params to determine which bootstrap version we're using - Waseem Sadiq (waseem@bulletprooftemplates.com)
$J2gridRow = ($this->params->get('bootstrap_version', 2) == 2) ? 'row-fluid' : 'row';
$J2gridCol = ($this->params->get('bootstrap_version', 2) == 2) ? 'span' : 'col-md-';

?>
<div class="j2store">
	<div class="<?php echo $J2gridRow; ?>">
		<div class="<?php echo $J2gridCol; ?>12">
			<h3><?php echo JText::_('J2STORE_VENDOR_DETAIL');?></h3>
			<div class="tabbable">
				<ul class="nav nav-tabs" data-tabs="tabs">
					<li class="active">
				    	<a href="#j2store-vendor-profile-tab" data-toggle="tab">
				  			<?php echo JText::_('J2STORE_VENDOR_TAB_PROFILE'); ?>

				   		 </a>
				     </li>
				     <li class="">
				     	<a href="#j2store-vendor-products-tab" data-toggle="tab">
				    		 <?php echo JText::_('J2STORE_PRODUCTS'); ?>
					     </a>
				     </li>
				     <li class="">
				     	<a href="#j2store-vendor-orders-tab" data-toggle="tab">
				    		<?php echo JText::_('J2STORE_ORDERS'); ?>
				    	</a>
				     </li>
				   </ul>
				  <div class="tab-content">
				  	<div class="tab-pane fade in active" id="j2store-vendor-profile-tab">
						<?php echo $this->loadTemplate('profile');?>
				    </div>
				    <div class="tab-pane fade" id="j2store-vendor-products-tab">
						<?php echo $this->loadTemplate('products');?>
				     </div>
				      <div class="tab-pane fade" id="j2store-vendor-orders-tab">
						<?php echo $this->loadTemplate('orders');?>
				      </div>
				  </div>
			</div>
		</div>
	</div>
</div>