<?php
/*------------------------------------------------------------------------
# mod_j2store_cart - J2 Store Cart
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/


// no direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
J2Store::utilities()->nocache();

require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/strapper.php');
J2StoreStrapper::addJS();
$action = JRoute::_('index.php');

?>
<script type="text/javascript">
<!--
if(typeof(j2store) == 'undefined') {
	var j2store = {};
}
if(typeof(j2store.jQuery) == 'undefined') {
	j2store.jQuery = jQuery.noConflict();
}

//-->
</script>

<style type="text/css">
#j2store_currency {
background: <?php echo $background_color; ?>;
color: <?php echo $text_color; ?>;
}

#j2store_currency a {
color: <?php echo $link_color; ?>;
}

#j2store_currency a.active {
color: <?php echo $active_link_color; ?>;
}


#j2store_currency a:hover {
color: <?php echo $link_hover_color; ?>;
}

</style>

<?php if (count($currencies) > 1) : ?>
<div class="j2store <?php echo $moduleclass_sfx ?>" >
<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
  <div id="j2store_currency">
    <?php foreach ($currencies as $currency) : ?>

	    <?php if ($currency->currency_code == $currency_code) : ?>
		    <a class="active" title="<?php echo $currency->currency_title; ?>"><b><?php echo $currency->currency_symbol; ?></b></a>
	    <?php else: ?>
	    	<a title="<?php echo $currency->currency_title; ?>" onclick="j2store.jQuery('input[name=\'currency_code\']').attr('value', '<?php echo $currency->currency_code; ?>'); j2store.jQuery(this).parent().parent().submit();"><?php echo $currency->currency_symbol; ?></a>
	    <?php endif; ?>

    <?php endforeach; ?>
    <input type="hidden" name="currency_code" value="" />
    <input type="hidden" name="option" value="com_j2store" />
    <input type="hidden" name="view" value="carts" />
    <input type="hidden" name="task" value="setcurrency" />
    <input type="hidden" name="redirect" value="<?php echo base64_encode( JUri::getInstance()->toString()); ?>" />
  </div>
</form>
</div>
<?php endif; ?>
