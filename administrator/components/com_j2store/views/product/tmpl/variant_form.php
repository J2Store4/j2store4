<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once JPATH_ADMINISTRATOR.'/components/com_j2store/library/popup.php';


//lengths
$this->lengths = J2Html::select()->clearState()
->type('genericlist')
->name($this->form_prefix.'[length_class_id]')
->value($this->item->length_class_id)
->setPlaceHolders(array(''=>JText::_('J2STORE_SELECT_OPTION')))
->hasOne('Lengths')
->setRelations(
		array (
				'fields' => array (
						'key'=>'j2store_length_id',
						'name'=>'length_title'
				)
		)
)->getHtml();

//weights

$this->weights = J2Html::select()->clearState()
->type('genericlist')
->name($this->form_prefix.'[weight_class_id]')
->value($this->item->weight_class_id)
->setPlaceHolders(array(''=>JText::_('J2STORE_SELECT_OPTION')))
->hasOne('Weights')
->setRelations(
		array (
				'fields' => array (
						'key'=>'j2store_weight_id',
						'name'=>'weight_title'
				)
		)
)->getHtml();

//backorder
$this->allow_backorder = J2Html::select()->clearState()
->type('genericlist')
->name($this->form_prefix.'[allow_backorder]')
->value($this->item->allow_backorder)
->setPlaceHolders(
		array('0' => JText::_('COM_J2STORE_DO_NOT_ALLOW_BACKORDER'),
				'1' => JText::_('COM_J2STORE_DO_ALLOW_BACKORDER'),
				'2' => JText::_('COM_J2STORE_ALLOW_BUT_NOTIFY_CUSTOMER')
		))
		->getHtml();

$this->availability =J2Html::select()->clearState()
->type('genericlist')
->name($this->form_prefix.'[availability]')
->value($this->item->availability)
->default(1)
->setPlaceHolders(
		array('0' => JText::_('COM_J2STORE_PRODUCT_OUT_OF_STOCK') ,
				'1'=> JText::_('COM_J2STORE_PRODUCT_IN_STOCK') ,
		)
)
->getHtml();


?>
 <form class="form-horizontal form-validate" id="adminForm" name="adminForm" method="post" action="index.php">
 	<h4><?php  echo JText::_('J2STORE_PRODUCT_NAME');?></h4>
<div class="j2store-bs">
			<div class="tabbable  tabs-left">
				<ul class="nav nav-tabs" data-tabs="tabs">
					<li class="active">
				    	<a href="#generalTab" data-toggle="tab">
				  			<?php echo JText::_('J2STORE_PRODUCT_TAB_GENERAL'); ?>
				   		 </a>
				     </li>
				     <li class="">
				     	<a href="#priceTab" data-toggle="tab">
				    		<?php echo JText::_('J2STORE_PRODUCT_TAB_PRICE'); ?>
				    	</a>
				     </li>
				     <li class="">
				     	<a href="#inventoryTab" data-toggle="tab">
				    		 <?php echo JText::_('J2STORE_PRODUCT_TAB_INVENTORY'); ?>
					     </a>
				     </li>
				     <li class="">
				     	<a href="#shippingTab" data-toggle="tab">
				     		<?php echo JText::_('J2STORE_PRODUCT_TAB_SHIPPING'); ?>
				     	</a>
				      </li>
				   </ul>
				  <div class="tab-content">
				  	<div class="tab-pane fade in active" id="generalTab">
				      	<?php echo $this->loadTemplate('general');?>
				    </div>
				    <div class="tab-pane fade" id="priceTab">
				    	 <?php echo $this->loadTemplate('pricing');?>
				    </div>
				    <div class="tab-pane fade" id="inventoryTab">
				    	<?php echo $this->loadTemplate('inventory');?>
				     </div>
				    <div class="tab-pane fade" id="shippingTab">
				      	<?php echo $this->loadTemplate('shipping');?>
				    </div>
				  </div>
			</div>

			<button class="btn btn-success btn-large" onclick="document.getElementById('task').value='savevariant'; document.adminForm.submit();">
			<?php echo JText::_('JAPPLY'); ?>
		</button>
	</div>


		<?php echo  J2Html::hidden('option','com_j2store');?>
		<?php echo  J2Html::hidden('view','products');?>
		<?php echo  J2Html::hidden('task','',array('id'=>'task'));?>
		<?php echo  J2Html::hidden('variant_id', $this->item->j2store_variant_id,array('id'=>'j2store_variant_id'));?>
			<?php echo JHTML::_( 'form.token' ); ?>
</form>
