<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
?>

<div class="product-files">
	<div class="control-group">
		<?php echo J2Html::label(JText::_('J2STORE_SET_PRODUCT_FILES') ,'product_files_option',array('class'=>'control-label')); ?>
		<div class="controls">
			<?php
            $base_path = rtrim(JUri::root(),'/').'/administrator';
            echo J2StorePopup::popup($base_path."/index.php?option=com_j2store&view=products&task=setproductfiles&product_id=".$this->item->j2store_product_id."&layout=productfiles&tmpl=component", JText::_( "J2STORE_PRODUCT_SET_FILES" ), array('class'=>'btn btn-success'));?>
		</div>
	</div>
	<div class="control-group">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_FILE_DOWNLOAD_LIMIT') ,'product_files_option',array('class'=>'control-label')); ?>
		<div class="controls">
			<?php echo J2Html::text($this->form_prefix.'[params][download_limit]', $this->item->params->get('download_limit'));?>
		</div>
	</div>

	<div class="control-group">
		<?php echo J2Html::label(JText::_('J2STORE_PRODUCT_FILE_DOWNLOAD_EXPIRY') ,'product_files_option',array('class'=>'control-label')); ?>
		<div class="controls">
			<?php echo J2Html::text($this->form_prefix.'[params][download_expiry]', $this->item->params->get('download_expiry') ,array('id'=>'expiry_date'));?>
		</div>
	</div>
</div>