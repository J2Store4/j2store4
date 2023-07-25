<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
?>
<table  class="table table-striped table-bordered">
	<thead>
		<tr>
			<td><?php echo JText::_('J2STORE_PRODUCT_NAME');?></td>
			<td><?php echo JText::_('J2STORE_PRODUCT_TYPE')?></td>
			<td><?php echo JText::_('J2STORE_PRODUCT_TYPE')?></td>
			<td><?php echo JText::_('J2STORE_EDIT')?></td>
		</tr>
	</thead>
	<tbody>
		<?php foreach($this->item->products as $product):?>
		<tr>
			<td><?php echo $product->product_name;?></td>
			<td><?php echo $product->product_type;?></td>
			<td><?php echo $product->product_type;?></td>
			<td>
				<a class="btn btn-primary" href="<?php echo JRoute::_('index.php?option=com_content&task=article.edit&a_id='.$product->product_source_id);?>" >
					<?php echo JText::_('J2STORE_EDIT')?>
				</a>
			</td>
		</tr>
	<?php endforeach;?>
	</tbody>
</table>
