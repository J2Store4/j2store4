<?php
/*
 * --------------------------------------------------------------------------------
   Weblogicx India  - J2Store - SagePay plugin - form integration
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

<?php echo JText::_($vars->display_name); ?>
<br />
<?php echo JText::_($vars->onbeforepayment_text); ?>

<form action='<?php echo $vars->post_url; ?>' method='post'>
	<input type='hidden' name='VPSProtocol' value='3.00' />
	<input type='hidden' name='TxType' value='PAYMENT' />
	<input type="hidden" name="Vendor" value="<?php echo $vars->vendor_name; ?>" />
	<input type='hidden' name='Crypt' value='<?php echo $vars->crypt; ?>' />


	 <input type="submit" class="btn btn-primary button" value="<?php echo JText::_($vars->button_text); ?>" />
</form>