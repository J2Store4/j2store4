<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 * This file is for email.
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();
$order = $this->order;
$platform = J2Store::platform();
$items = $this->order->getItems();
$currency = J2Store::currency();
$this->taxes = $order->getOrderTaxrates();
$colspan = '2';
if(empty($order->customer_language) || $order->customer_language == '*' || $order->customer_language == ''){
    $language = JFactory::getLanguage();

}else{
    $conf = JFactory::getConfig();
    $debug = $conf->get('debug_lang');
    $language = JLanguage::getInstance($order->customer_language, $debug);
    $language->load('com_j2store');
}
?>
<div style="page-break-inside: avoid;">
    <h3><?php echo $language->_('J2STORE_ORDER_SUMMARY')?></h3>
    <table style="border-collapse: collapse;" class="emailtemplate-table table table-bordered" width="100%" border="0" cellspacing="0" cellpadding="0">
        <thead>
        <tr>
            <th style="font-family: 'Arial';line-height: 1.35em;padding: 7px 9px 9px;border: 1px solid #ccc;"><?php echo $language->_('J2STORE_CART_LINE_ITEM'); ?></th>
            <th style="font-family: 'Arial';line-height: 1.35em;padding: 7px 9px 9px;border: 1px solid #ccc;"><?php echo $language->_('J2STORE_CART_LINE_ITEM_QUANTITY'); ?></th>
            <?php if(isset($this->taxes) && count($this->taxes) && $this->params->get('show_item_tax', 0)): ?>
                <?php $colspan = '3'; ?>
                <th style="font-family: 'Arial';line-height: 1.35em;padding: 7px 9px 9px;border: 1px solid #ccc;"><?php echo $language->_('J2STORE_CART_LINE_ITEM_TAX'); ?></th>
            <?php endif; ?>
            <th style="font-family: 'Arial';line-height: 1.35em;padding: 7px 9px 9px;border: 1px solid #ccc;"><?php echo $language->_('J2STORE_CART_LINE_ITEM_TOTAL'); ?></th>
        </tr>
        </thead>

        <tbody>
        <?php foreach ($items as $item): ?>
            <?php
            $item->params = $platform->getRegistry($item->orderitem_params);
            $thumb_image = $item->params->get('thumb_image', '');
            $back_order_text = $item->params->get('back_order_item', '');
            ?>
            <tr valign="top">
                <td style="font-family: 'Arial';line-height: 1.35em;padding: 7px 9px 9px;border: 1px solid #ccc;">
                    <?php if($this->params->get('show_thumb_email', 0) && !empty($thumb_image)): ?>
                        <span class="cart-thumb-image">
								<?php if(JFile::exists(JPATH_SITE.'/'.$thumb_image)): ?>
                                    <img src="<?php echo JUri::root(true). '/'.$thumb_image; ?>" >
                                <?php endif;?>
							</span>
                    <?php endif; ?>

                    <?php echo $this->order->get_formatted_lineitem_name($item,$this->email_receiver);?>

                    <?php if($this->params->get('show_price_field', 1)): ?>

                        <span class="cart-product-unit-price">
								<span class="cart-item-title"><?php echo $language->_('J2STORE_CART_LINE_ITEM_UNIT_PRICE'); ?></span>
								<span class="cart-item-value">
									<?php echo $currency->format($this->order->get_formatted_order_lineitem_price($item, $this->params->get('checkout_price_display_options', 1)), $this->order->currency_code, $this->order->currency_value);?>
								</span>
							</span>
                    <?php endif; ?>

                    <?php if($this->params->get('show_sku', 1) && !empty($item->orderitem_sku)): ?>
                        <br />
                        <span class="cart-product-sku">
								<span class="cart-item-title"><?php echo $language->_('J2STORE_CART_LINE_ITEM_SKU'); ?></span>
								<span class="cart-item-value"><?php echo $item->orderitem_sku; ?></span>
							</span>

                    <?php endif; ?>
                    <?php if($back_order_text):?>
                        <br />
                        <span class="label label-inverse"><?php echo $language->_($back_order_text);?></span>
                    <?php endif;?>
                    <?php echo J2Store::plugin()->eventWithHtml('AfterDisplayLineItemTitleInOrder', array($item, $this->order, $this->params));?>
                </td>
                <td class="cart-line-quantity" style="font-family: 'Arial';line-height: 1.35em;padding: 7px 9px 9px;border: 1px solid #ccc;text-align: center"><?php echo $item->orderitem_quantity; ?></td>
                <?php if(isset($this->taxes) && count($this->taxes) && $this->params->get('show_item_tax', 0)): ?>
                    <td style="font-family: 'Arial';line-height: 1.35em;padding: 7px 9px 9px;border: 1px solid #ccc;"><?php 	echo $currency->format($item->orderitem_tax);	?></td>
                <?php endif; ?>
                <td class="cart-line-subtotal" style="font-family: 'Arial';line-height: 1.35em;padding: 7px 9px 9px;border: 1px solid #ccc;text-align: right;">
                    <?php echo $currency->format($this->order->get_formatted_lineitem_total($item, $this->params->get('checkout_price_display_options', 1)), $this->order->currency_code, $this->order->currency_value ); ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot class="emailtemplate-table-footer" style="text-align: right;">
        <?php if($totals = $this->order->get_formatted_order_totals()): ?>
            <?php foreach($totals as $total): ?>
                <tr valign="top">
                    <th class="totals-heading" style="font-family: 'Arial';line-height: 1.35em;padding: 7px 9px 9px;border: 1px solid #ccc;text-align: right;" scope="row" colspan="<?php echo $colspan; ?>"> <?php echo $total['label']; ?></th>
                    <td style="font-family: 'Arial';line-height: 1.35em;padding: 7px 9px 9px;border: 1px solid #ccc;text-align: right;"><?php echo $total['value']; ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tfoot>
    </table>
</div>