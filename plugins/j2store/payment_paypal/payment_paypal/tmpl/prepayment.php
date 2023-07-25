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

<form action='<?php echo $vars->post_url; ?>' method='post'>

    <!--USER INFO-->
    <input type='hidden' name='first_name' value='<?php echo substr(html_entity_decode($vars->first_name, ENT_QUOTES, 'UTF-8'), 0,32); ?>' />
    <input type='hidden' name='last_name' value='<?php echo substr(html_entity_decode($vars->last_name, ENT_QUOTES, 'UTF-8'), 0,32); ?>' />
    <input type='hidden' name='email' value='<?php echo $vars->email; ?>' />

    <!--SHIPPING ADDRESS PROVIDED-->
    <input type='hidden' name='address1' value='<?php echo substr(html_entity_decode($vars->address_1, ENT_QUOTES, 'UTF-8'), 0,100); ?>' />
    <input type='hidden' name='address2' value='<?php echo substr(html_entity_decode($vars->address_2, ENT_QUOTES, 'UTF-8'), 0,100); ?>' />
    <input type='hidden' name='city' value='<?php echo substr(html_entity_decode($vars->city, ENT_QUOTES, 'UTF-8'), 0,40); ?>' />
    <input type='hidden' name='country' value='<?php echo $vars->country; ?>' />
    <input type='hidden' name='state' value='<?php echo html_entity_decode($vars->region, ENT_QUOTES, 'UTF-8'); ?>' />
    <input type='hidden' name='zip' value='<?php echo substr(html_entity_decode($vars->postal_code, ENT_QUOTES, 'UTF-8'), 0,32); ?>' />

    <!-- IPN-PDT  ONLY -->
    <input type='hidden' name='custom' value='<?php echo substr($vars->order_id.'|'.$vars->cart_session_id,0,256); ?>'>
    <input type='hidden' name='invoice' value='<?php echo substr($vars->invoice,0,126); ?>' />

    <!--CART INFO ITEMISED-->
    <?php
    $i =1;
    foreach ($vars->products as $product):
        ?>
        <input type='hidden' name='amount_<?php echo $i;?>' value='<?php echo $product['price']; ?>' />
        <input type='hidden' name='item_name_<?php echo $i;?>' value='<?php echo substr($this->clean_title($product['name']), 0,126);?>' />
        <input type='hidden' name='item_number_<?php echo $i;?>' value='<?php echo isset($product['number']) ? substr($product['number'], 0,126) : ''; ?>' />
        <input type='hidden' name='quantity_<?php echo $i;?>' value='<?php echo $product['quantity']; ?>' />

        <?php if(isset($product['options']) && count($product['options'])): ?>
        <?php $j=0; ?>
        <?php foreach ($product['options'] as $option): ?>
            <?php if($j>=7){
                break; // more than 7 options not supported in paypal
            } ?>
            <input type="hidden" name="on<?php echo $j; ?>_<?php echo $i; ?>" value="<?php echo substr($option['name'], 0,63); ?>" />
            <input type="hidden" name="os<?php echo $j; ?>_<?php echo $i; ?>" value="<?php echo substr($option['value'], 0,63); ?>" />
            <?php $j++; ?>
        <?php endforeach; ?>
    <?php endif; ?>
        <?php $i++; ?>
    <?php endforeach; ?>

    <?php if(isset($vars->tax_cart) && $vars->tax_cart > 0) :?>
        <input type='hidden' name='tax_cart' value='<?php echo $vars->tax_cart; ?>' />
    <?php endif; ?>
    <?php if(isset($vars->discount_amount_cart)): ?>
        <input type='hidden' name='discount_amount_cart' value='<?php echo $vars->discount_amount_cart;?>' />
    <?php endif; ?>


    <!--PAYPAL VARIABLES-->
    <input type='hidden' name='cmd' value='_cart' />
    <input type='hidden' name='rm' value='2' />
    <input type="hidden" name="business" value="<?php echo $vars->merchant_email; ?>" />
    <input type='hidden' name='return' value='<?php echo substr($vars->return_url,0,1023); ?>' />
    <input type='hidden' name='cancel_return' value='<?php echo substr($vars->cancel_url,0,1023);//JRoute::_( $vars->cancel_url ); ?>' />
    <input type="hidden" name="notify_url" value="<?php echo $vars->notify_url; ?>" />
    <input type='hidden' name='currency_code' value='<?php echo trim($vars->currency_code); ?>' />
    <input type='hidden' name='no_note' value='1' />
    <input type='hidden' name='bn' value='J2Store_SP' />
    <input type='hidden' name='upload' value='1' />
    <input type='hidden' name='charset' value='utf-8' />

    <!-- payment screen style variables -->
    <?php if($cbt = $this->_getParam('cbt','')): ?>
        <input type="hidden" name="cbt" value="<?php echo substr($cbt, 0,60); ?>" />
    <?php endif; ?>
    <?php if($cpp_header_image = $this->_getParam('cpp_header_image','')): ?>
        <input type="hidden" name="cpp_header_image" value="<?php echo $cpp_header_image;?>" />
    <?php endif; ?>
    <?php if($image_url = $this->_getParam('image_url','')): ?>
        <input type="hidden" name="image_url" value="<?php echo substr($image_url, 0,1023);?>" />
    <?php endif; ?>
    <?php if($cpp_headerback_color = $this->_getParam('cpp_headerback_color','')): ?>
        <input type="hidden" name="cpp_headerback_color" value="<?php echo substr($cpp_headerback_color, 0,6);?>" />
    <?php endif; ?>
    <?php if($cpp_headerborder_color = $this->_getParam('cpp_headerborder_color','')): ?>
        <input type="hidden" name="cpp_headerborder_color" value="<?php echo substr($cpp_headerborder_color, 0,6);?>" />
    <?php endif; ?>

    <input type="submit" class="btn btn-primary button" value="<?php echo JText::_($vars->button_text); ?>" />
</form>