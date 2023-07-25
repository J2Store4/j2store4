<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die;
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/shipping.php');
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<?php $state = $vars->state; ?>
<?php $form = $vars->form; ?>
<?php $items = $vars->list;
?>
<div class="j2store">
	<div class="<?php echo $row_class;?>">
		<div class="<?php echo $col_class;?>12">
			<div class="pull-right">
				<div id="toolbar" class="btn-toolbar">
					<button class="btn btn-small btn-success" onclick="submitButton('newMethod','shippingTask')">
						<span class="icon-new icon-white"></span>
						<?php echo JText::_('JNEW');?>
					</button>
					<button class="btn btn-danger" onclick="submitButton('delete','shippingTask');Joomla.isChecked(this.checked);">
						<span class="icon-trash"></span>
						<?php echo JText::_('J2STORE_DELETE');?>
					</button>
			</div>
		</div>
	</div>
</div>
<form action="<?php echo JRoute::_( @$form['action'] )?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<table class="adminlist table table-striped" style="clear: both;">
		<thead>
            <tr>
				<th width="5"><?php echo JText::_( 'J2STORE_NUM' ); ?></th>
				<th width="20">
				<input type="checkbox" name="checkall-toggle"
					value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>"
					onclick="Joomla.checkAll(this)" />
				</th>
				<th  style="text-align:center;" class="title"><?php echo JText::_('J2STORE_SHIPM_ID'); ?></th>
				<th  class="title"><?php echo JText::_('J2STORE_SHIPM_NAME'); ?></th>
                <th style="text-align:center;"><?php echo JText::_('J2STORE_SFR_TAX_CLASS_NAME'); ?></th>
                <th style="text-align:center;"><?php echo JText::_('J2STORE_SHIPM_STATE'); ?></th>
                <th></th>
            </tr>
		</thead>
        <tfoot>
            <tr>
                <td colspan="20">
                    &nbsp;
                </td>
            </tr>
        </tfoot>
        <tbody>
        <?php
			$i = 0; $k=0;
			foreach($items as $item):
				$checked = JHTML::_('grid.id', $i, $item->j2store_shippingmethod_id );
        	?>
            <tr class='row<?php echo $k; ?>'>
				<td align="center">
					<?php echo $i + 1; ?>
				</td>
				<td style="text-align: center;">
					<?php echo $checked; ?>
				</td>
				<td style="text-align: center;">
					<a href="<?php echo $item->link; ?>">
						<?php echo $item->j2store_shippingmethod_id; ?>
					</a>
				</td>
				<td style="text-align: left;">
                    <a href="<?php echo $item->link; ?>">
                        <?php echo JText::_($item->shipping_method_name); ?>
                    </a>
                    <div class="shipping_rates">
                      	<?php
                        $id = JFactory::getApplication()->input->getInt('id', '0');
                        ?>
                        <span style="float: right;">
                        [<?php
                      	  echo J2StorePopup::popup( "index.php?option=com_j2store&view=shipping&task=view&id={$id}&shippingTask=setRates&tmpl=component&sid={$item->j2store_shippingmethod_id}",JText::_('J2STORE_SHIPM_SET_RATES') ); ?>
                      	 ]</span>
                        <?php
                        if ($shipping_method_type = J2StoreShipping::getType($item->shipping_method_type))
                        {
                        	echo "<b>".JText::_('J2STORE_STANDARD_SHIPPING_TYPE')."</b>: ".JText::_($shipping_method_type->title);
                        }
                        if ($item->subtotal_minimum > '0')
                        {
                        	echo "<br/><b>".JText::_('J2STORE_SHIPPING_METHODS_MINIMUM_SUBTOTAL_REQUIRED')."</b>: ".J2Store::currency()->format( $item->subtotal_minimum );
                        }
                        if( $item->subtotal_maximum > '-1' )
                        {
                        	echo "<br/><b>".JText::_('J2STORE_SHIPPING_METHODS_SUBTOTAL_MAX')."</b>: ".J2Store::currency()->format( $item->subtotal_maximum );
                        }
                        ?>
                    </div>
				</td>
				<td style="text-align: center;">
				    <?php echo $item->taxprofile_name; ?>
				</td>
				<td style="text-align: center;">
					<?php if($item->published){
						$img_url = JUri::root(true).'/media/j2store/images/tick.png';
						$value = 0;
					} else {
						$img_url = JUri::root(true).'/media/j2store/images/publish_x.png';
						$value = 1;
					}
					?>
					<a href="#" onclick="j2storePublishMethod(<?php echo $item->j2store_shippingmethod_id; ?>)">
						<img id="smid_<?php echo $item->j2store_shippingmethod_id; ?>" src="<?php echo $img_url; ?>" alt="" />
					</a>
				</td>
				<td>

				</td>
			</tr>
			<?php $i++; $k = (1 - $k); ?>
			<?php endforeach; ?>

			<?php if (!count($items)) : ?>
			<tr>
				<td colspan="10" align="center">
					<?php echo JText::_('J2STORE_NO_ITEMS_FOUND'); ?>
				</td>
			</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<input type="hidden" name="order_change" value="0" />
	<input type="hidden" name="sid" value=" <?php echo $vars->sid; ?>" />
	<input type="hidden" id="shippingTask" name="shippingTask" value="_default" />
	<input type="hidden" name="task" value="view" />
	<input type="hidden" name="boxchecked" value="" />
	<input type="hidden" name="filter_order" value="<?php echo @$state->order; ?>" />
	<input type="hidden" name="filter_direction" value="<?php echo @$state->direction; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>

</form>
</div>
<?php
	$img_url = JUri::root(true).'/media/j2store/images/';
	?>
<script type="text/javascript">
<!--
function j2storePublishMethod(smid) {
	(function($) {
		var jqxhr = $.post(
				"index.php",
				{	option:'com_j2store',
					view:'shippings',
					task:'view',
					shippingTask:'publish',
					smid:smid,
					id:'<?php echo $vars->sid;?>'

				},
				"json"
			)
			.done(function(data) {
				if(data == 1) {
					$('#smid_'+smid).attr('src', '<?php echo $img_url?>/tick.png');
				} else {
					$('#smid_'+smid).attr('src', '<?php echo $img_url?>/publish_x.png');
				}

			})
			.fail(function() {})
			.always(function() {});

	})(j2store.jQuery);
}

submitButton=function(task){
	(function($) {
		$("#shippingTask").attr('value',task);
	})(j2store.jQuery);
	Joomla.submitbutton('view');
}

//-->
</script>
