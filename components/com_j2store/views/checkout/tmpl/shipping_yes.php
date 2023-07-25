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

defined('_JEXEC') or die('Restricted access');

$shipping_rates_text = JText::_('J2STORE_GETTING_SHIPPING_RATES');
$shipping_selected_text = '';
?>
<?php if(count($this->rates)): ?>
<h3><?php echo JText::_('J2STORE_CHECKOUT_SELECT_A_SHIPPING_METHOD');?></h3>
<input type="hidden" id="shippingrequired" name="shippingrequired" value="1" />
<?php

        foreach ($this->rates as $rate)
        {
            $checked = "";
            if(!empty($this->default_rate)) {
            	if ( $this->default_rate['name'] == $rate['name'] )
            	{
            		$checked = "checked";
            	}
            }
			$select_text = "";
			if(isset( $rate['select_text'] )){
				$select_text = $rate['select_text'];
			}

	        $css_id = $rate['element']."_".JFilterOutput::stringURLSafe($rate['name']);

			$shipping_selected_text .= "<div class='shipping_element ".$css_id."_select_text' style='display:none;'>".JText::_ ( $select_text )."</div>"
            ?>
            <input id="shipping_<?php echo $css_id; ?>" name="shipping_plugin" rel="<?php echo $rate['name']; ?>" type="radio" value="<?php echo $rate['element'] ?>" onClick="j2storeSetShippingRate('<?php echo $rate['name']; ?>','<?php echo $rate['price']; ?>',<?php echo $rate['tax']; ?>,<?php echo $rate['extra']; ?>, '<?php echo $rate['code']; ?>', true, '<?php echo $rate['element'];?>', '<?php echo $css_id; ?>' );" <?php echo $checked; ?> />
            <label for="shipping_<?php echo $css_id; ?>" onClick="j2storeSetShippingRate('<?php echo $rate['name']; ?>','<?php echo $rate['price']; ?>',<?php echo $rate['tax']; ?>,<?php echo $rate['extra']; ?>, '<?php echo $rate['code']; ?>', true, '<?php echo $rate['element'];?>', '<?php echo $css_id; ?>' );"><?php echo $rate['name']; ?> ( <?php echo $this->currency->format( $rate['total']); ?> )</label><br />
            <?php
        }
?>
<?php endif;?>
<?php $setval = false;?>
<?php if(count($this->rates)==1 && ($this->rates['0']['name'] == $this->default_rate['name'])) $setval= true;?>
<input type="hidden" name="shipping_price" id="shipping_price" value="<?php echo $setval ? $this->rates['0']['price'] : "";?>" />
<input type="hidden" name="shipping_tax" id="shipping_tax" value="<?php echo $setval ? $this->rates['0']['tax'] : "";?>" />
<input type="hidden" name="shipping_name" id="shipping_name" value="<?php echo $setval ? $this->rates['0']['name'] : "";?>" />
<input type="hidden" name="shipping_code" id="shipping_code" value="<?php echo $setval ? $this->rates['0']['code'] : "";?>" />
<input type="hidden" name="shipping_extra" id="shipping_extra" value="<?php echo $setval ? $this->rates['0']['extra'] : "";?>" />

<div id='shipping_form_div' style="padding-top: 10px;"></div>
<div id='shipping_error_div' style="padding-top: 10px;"></div>
<?php
echo $shipping_selected_text;
if (!empty($this->default_rate) ) :
	$default_rate = $this->default_rate;
    $default_css_id = $default_rate['element']."_".JFilterOutput::stringURLSafe($default_rate['name']);
?>
<script type="text/javascript">
(function($) {
	$(document).ready(function(){
		j2storeSetShippingRate('<?php echo $default_rate['name']; ?>','<?php echo $default_rate['price']; ?>',<?php echo $default_rate['tax']; ?>,<?php echo $default_rate['extra']; ?>, '<?php echo $default_rate['code']; ?>', true,'<?php echo $default_rate['element'];?>', '<?php echo $default_css_id; ?>' );
});
})(j2store.jQuery);
</script>
<?php endif; ?>