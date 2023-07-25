<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
jimport( 'joomla.html.html.jgrid' );

?>

<td><?php echo J2Store::product()->getVariantNamesByCSV($this->variant->variant_name); ?></td>
<td><?php echo $this->variant->sku; ?></td>
<td><?php echo J2store::currency()->format($this->variant->price); ?></td>
<td><?php echo (isset($this->variant->shipping) && ($this->variant->shipping)) ? JText::_('J2STORE_YES') : JText::_('J2STORE_NO'); ?></td>
<td><?php echo $this->variant->quantity;?></td>
<td>
<?php if( $this->variant->isdefault_variant):?>
	<a id="default-variant-<?php echo $this->variant->j2store_variant_id;?>" class="btn btn-micro hasTooltip" title="" onclick="return listVariableItemTask(<?php echo $this->variant->j2store_variant_id;?>,'unsetDefault',<?php echo $this->variant->product_id;?>)" href="javascript:void(0);" data-original-title="UnSet default">
		<i class="icon-featured"></i>
	</a>
<?php else:?>
	<a id="default-variant-<?php echo $this->variant->j2store_variant_id;?>" class="btn btn-micro hasTooltip" title="" onclick="return listVariableItemTask(<?php echo $this->variant->j2store_variant_id;?>,'setDefault',<?php echo $this->variant->product_id;?>)" href="javascript:void(0);" data-original-title="Set default">
		<i class="icon-unfeatured"></i>
	</a>
<?php endif;?>

</td>
<td>
<?php
$base_path = rtrim(JUri::root(),'/').'/administrator';
echo J2StorePopup::popup(
    $base_path."/index.php?option=com_j2store&view=products&task=setvariant&variant_id=".$this->variant->j2store_variant_id."&layout=variant_form&tmpl=component",
		JText::_( "J2STORE_EDIT" ),
		array('class'=>'btn btn-success')
	);
?>
</td>