<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
?>
<style>

.j2store-confirm-change {
	margin-top:100px;
    -moz-border-radius: 0 0 8px 8px;
    -webkit-border-radius: 0 0 8px 8px;
    border-radius: 0 0 8px 8px;
    border-width: 0 8px 8px 8px;
    border:1px solid #000000;
}

.j2store-confirm-change .modal-header{
 	border:1px solid #faa732;
	background-color:#faa732;
}
</style>
<div class="j2store-modal">
		<div class="j2store-confirm-change" style="display: none;" id="j2storeConfirmChange" >
            <h3><?php echo JText::_('J2STORE_WARNING');?></h3>
            <hr/>
            <div class="alert alert-warning">
                <span class="ui-icon ui-icon-info"></span>
                <?php echo JText::_('J2STORE_PRODUCT_TYPE_CHANGE_WARNING_MSG');?>
            </div>
            <div class="message-footer">
                <button type="button" id="closeTypeBtn" class="btn btn-inverse" ><?php echo JText::_('J2STORE_CLOSE');?></button>
                <?php J2Html::text('product_id', $this->item->j2store_product_id ,array('id'=>'product_id'));?>
                <button type="button" id="changeTypeBtn" class="btn btn-warning"><?php echo JText::_('J2STORE_CONTINUE');?></button>
            </div>

	</div>
</div>