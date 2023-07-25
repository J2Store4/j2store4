<?php
/*
 * --------------------------------------------------------------------------------
   Weblogicx India  - J2Store - Paypal standard plugin
 * --------------------------------------------------------------------------------
 * @package		Joomla! 2.5x
 * @subpackage	J2Store
 * @author    	Weblogicx India http://www.weblogicxindia.com
 * @copyright	Copyright (c) 2010 - 2015 Weblogicx India Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link		http://weblogicxindia.com
 * --------------------------------------------------------------------------------
*/

defined('_JEXEC') or die('Restricted access'); ?>
<?php 
       $image = $this->params->get('display_image', '');         	 
?>
         <?php if(!empty($image)): ?>
         	<span class="j2store-payment-image">
				<img class="payment-plugin-image payment_paypal" src="<?php echo JUri::root().JPath::clean($image); ?>" />
			</span>
		<?php endif; ?>
		
<?php echo JText::_($vars->display_name); ?>
<br />
<?php echo JText::_($vars->onbeforepayment_text); ?>
<?php
if($vars->post_url != ''){
	?>
	<form action='<?php echo $vars->post_url; ?>' method='post'>
        <?php
        $button_label = $vars->button_text;
        if($vars->is_card_update) {
            $button_label = $vars->card_update_button_text;
        }
        ?>
        <input type="submit" class="btn btn-primary button" value="<?php echo JText::_($button_label); ?>" />
	</form>
<?php
} else {
	echo JText::_('J2STORE_PAYMENT_PAYPALSUBSCRIPTION_SOMETHING_WENT_WRONG_IN_CREATING_TOKEN');
	if(isset($vars->errorMessage)){
		echo "<br>";
		echo $vars->errorMessage;
	}
}
?>
