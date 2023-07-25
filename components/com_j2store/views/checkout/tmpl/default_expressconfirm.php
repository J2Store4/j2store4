<?php
/*------------------------------------------------------------------------
# com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Ramesh Elamathi - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
?>

<?php if(isset($this->order)): ?>
		<div class="j2storeOrderSummary">
			<?php echo $this->loadAnyTemplate('site:com_j2store/checkout/default_cartsummary'); ?>
		</div>
	<?php endif; ?>
<?php if(isset($this->ec_html)): ?>
			<!--    Express Payment   -->		
	<?php echo $this->ec_html; ?>		
<?php endif;?>
