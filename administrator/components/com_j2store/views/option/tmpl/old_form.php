<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/

defined('_JEXEC') or die;
JHtml::_('behavior.modal');
jimport('joomla.filesystem.file');
$this->loadHelper('select');

$config = JFactory::getConfig();
$asset_id = $config->get('asset_id');

$optionvalues=array();
if(isset($this->optionvalues))
{
 $optionvalues = $this->optionvalues;
}
?>
<style>
#option-value  .option-images{
width:100px;
}

</style>
<div class="j2store">
<form class="form-horizontal form-validate" id="adminForm" name="adminForm" method="post" action="index.php">
	<input type="hidden" name="option" value="com_j2store">
	<input type="hidden" name="view" value="option">
	<input type="hidden" name="task" value="">
	<input type="hidden" id="option_id" name="j2store_option_id" value="<?php echo $this->item->j2store_option_id; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<fieldset>
	<legend><?php echo JText::_('J2STORE_OPTION_DETAILS'); ?> </legend>
	<table class="admintable">
		<tr>
			<td width="100" align="right" class="key">
				<label for="option_unique_name">
					<?php echo JText::_( 'J2STORE_OPTION_UNIQUE_NAME' ); ?>:
				</label>
			</td>
			<td>
				<input type="text" name="option_unique_name" id="option_unique_name" class="required" value="<?php echo $this->item->option_unique_name;?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="option_name">
					<?php echo JText::_( 'J2STORE_OPTION_DISPLAY_NAME' ); ?>:
				</label>
			</td>
			<td>
				<input type="text" name="option_name" id="option_name" class="required" value="<?php echo $this->item->option_name;?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="type">
					<?php echo JText::_( 'J2STORE_OPTION_TYPE' ); ?>:
				</label>
			</td>
			<td>
				<?php echo J2StoreHelperSelect::getOptionTypesList('type', 'option-type', $this->item); ?>
			</td>
		</tr>

		<tr>
			<td valign="top" align="right" class="key">
				<?php echo JText::_( 'J2STORE_OPTION_STATE' ); ?>:
			</td>
			<td>
				<?php echo J2StoreHelperSelect::publish('enabled',$this->item->enabled); ?>
			</td>
		</tr>
	</table>

</fieldset>
<fieldset id="option-value">
	<div class="row-fluid">
		<div class="span9">
			<legend><h3><?php echo JText::_('J2STORE_OV_ADD_NEW_OPTION_VALUES');?></h3></legend>
				<table class="list table table-bordered table-stripped">
		          <thead>
		            <tr>
		              <td class="left"><span class="required">*</span> <?php echo JText::_('J2STORE_OPTION_VALUE_NAME'); ?></td>
		              <td><span><?php echo JText::_('J2STORE_OPTION_VALUE_IMAGE');?></span></td>
		              <td class="right"><?php echo JText::_('JGRID_HEADING_ORDERING'); ?></td>
		              <td><?php echo JText::_('J2STORE_REMOVE'); ?></td>
		            </tr>
		          </thead>
		          <?php $option_value_row = 0; ?>
		          <?php if(isset($this->item->optionvalues) && !empty($this->item->optionvalues)):?>
		          <?php foreach($this->item->optionvalues as $option_value):?>

		          <tbody id="option-value-row<?php echo $option_value_row; ?>">
		            <tr>
		              <td class="left">

		              	<input type="hidden"  name="option_value[<?php echo $option_value_row; ?>][j2store_optionvalue_id]" value="<?php echo $option_value->j2store_optionvalue_id	; ?>" />
		                <input type="text" class="input-small" name="option_value[<?php echo $option_value_row; ?>][optionvalue_name]" value="<?php echo isset($option_value->optionvalue_name) ? JText::_($option_value->optionvalue_name): ''; ?>" />
		                <br />
		               </td>
		               <td class="right">
		               <div class="input-prepend input-append">

		               <?php if(JFile::exists(JPATH_SITE.'/'.$option_value->optionvalue_image)):?>
		               	<?php $optionvalueimage = JUri::root().'/'.$option_value->optionvalue_image;?>
		               	<?php elseif(JFile::exists(JPATH_SITE.'/media/j2store/images/common/no_image-100x100.jpg')):?>
						<?php $optionvalueimage = JUri::root().'media/j2store/images/common/no_image-100x100.jpg';?>
		               	<?php endif;?>
		               	<?php if(isset($optionvalueimage) && !empty($optionvalueimage)):?>
							<img class="option-images" id="optimage-jform_optionvalue_image_<?php echo $option_value->j2store_optionvalue_id;?>"
								 src="<?php echo $optionvalueimage ?>" alt="" />
						<?php endif;?>
								<div class="input-prepend input-append">
							<input  onchange="previewImage(this.value,<?php echo $option_value->j2store_optionvalue_id;?>)"
									id="jform_optionvalue_image_<?php echo $option_value->j2store_optionvalue_id;?>" class="input-mini" value="<?php echo $option_value->optionvalue_image; ?>"
									type="text" readonly="readonly"   name="option_value[<?php echo $option_value_row; ?>][optionvalue_image]" />
							<a class="modal btn btn-default " rel="{handler: 'iframe', size: {x: 800, y: 500}}"
								href="index.php?option=com_media&view=images&tmpl=component&asset=<?php echo $asset_id;?>&author=<?php echo JFactory::getUser()->id;?>&fieldid=jform_optionvalue_image_<?php echo $option_value->j2store_optionvalue_id;?>&folder="
								title="<?php echo JText::_('PLG_J2STORE_EXTRAIMAGES_SELECT');?>">
								<?php echo JText::_('J2STORE_IMAGE_SELECT');?>
					 		</a>
					 		<a class="btn hasTooltip btn btn-inverse" onclick="removeImage('jform_optionvalue_image_<?php echo $option_value->j2store_optionvalue_id;?>')"  href="#" title=""  data-original-title="<?php echo JText::_('J2STORE_IMAGE_CLEAR');?>">
								<i class="icon-remove"></i>
					 		</a>
						 </div>
		               </td>
		              <td class="right"><input class="input-small" type="text" name="option_value[<?php echo $option_value_row; ?>][ordering]" value="<?php echo $option_value->ordering; ?>" size="1" /></td>
		              <td class="left"><a class="btn btn-danger btn-mini" onclick="DeleteOptionValue(<?php echo $option_value->j2store_optionvalue_id	; ?>,<?php echo $option_value_row; ?>)" class="button"><?php echo JText::_('J2STORE_REMOVE'); ?></a></td>
		            </tr>
		          </tbody>
		          <?php $option_value_row++; ?>
		          <?php endforeach; ?>
		          <?php endif;?>
		          <tfoot>
		            <tr>
		              <td  colspan="4"><a  href="javascript:void(0)" onclick="j2storeAddOptionValue();" class="btn btn-primary pull-right"><?php echo JText::_('J2STORE_OPTION_VALUE_ADD'); ?></a></td>
		            </tr>
		          </tfoot>
		        </table>
	        </div>
	        <div class="span3">
	        <legend><h3><?php echo JText::_('J2STORE_OPTIONVALUE_ADD_NEW_OPTION_VALUES_HELP');?></h3></legend>
	        	<div class="alert alert-info">
	        		<h4 class="alert-heading"><?php echo JText::_('J2STORE_OPTION_VALUE_IMAGE');?></h4>
	        		<p><?php echo JText::_('J2STORE_OPTION_VALUE_IMAGE_HELP');?></p>
	        	</div>
	        </div>
       </div>
</fieldset>
</form>

<script>

  function removeImage(id){
	  var no_preview = "<?php echo JUri::root().'media/j2store/images/common/no_image-100x100.jpg'?>";
		jQuery("#"+id).val("");
		jQuery("#optimage-"+id).attr('src',no_preview);
		jQuery('html, body').animate({
			scrollTop: jQuery("#"+id).offset().top
	      });
	}

function previewImage(value,id) {

	value='<?php echo JUri::root();?>'+value;
	jQuery("#optimage-"+id).attr('src',value);

}


function jInsertFieldValue(value, id) {

    var old_id = document.id(id).value;
if (old_id != id) {
	var elem = document.id(id)
	elem.value = value;
	elem.fireEvent("change");
	previewImage(value,id);
}

}

window.addEvent('domready', function() {

SqueezeBox.initialize({});
SqueezeBox.assign($$('a.modal-button'), {
	parse: 'rel'
});

});



</script>

<script type="text/javascript">
var thumb_image = "<?php echo JUri::root().'media/j2store/images/common/no_image-100x100.jpg'?>";
var vhref = "<?php echo "index.php?option=com_media&view=images&tmpl=component&asset=".$asset_id."&author=".JFactory::getUser()->id."&fieldid=jform_main_image_";?>";
(function($) {

$('select[name=\'type\']').bind('change', function() {
	if (this.value == 'select' || this.value == 'radio' || this.value == 'checkbox' || this.value == 'image') {
		$('#option-value').show();
	} else {
		$('#option-value').hide();
	}
});
$('select[name=\'type\']').trigger('change');
})(j2store.jQuery);
var option_value_row = <?php echo $option_value_row; ?>;

function j2storeAddOptionValue(){
	(function($) {
	html  = '<tbody id="option-value-row' + option_value_row + '">';
	html += '  <tr>';
    html += '    <td class="left"><input  type="hidden" name="option_value[' + option_value_row + '][j2store_optionvalue_id]" value="" />';
	html += '<input type="text" class="input-small" name="option_value[' + option_value_row + '][optionvalue_name]" value="" /> <br />';
	html += '  </td>';
	html += '<td class="right">';
	html += '<input type="hidden" name="option_value[' + option_value_row + '][optionvalue_image]" value="" />';
	html += '<span class="text text-info"><?php echo addslashes(JText::_('J2STORE_OPTIONVALUE_INSERT_IMAGE_HELP'));?></span>';
	html += '    <td class="right"><input class="input-small" type="text" name="option_value[' + option_value_row + '][ordering]" value="" size="1" /></td>';
	html += '    <td class="left"><a onclick="j2store.jQuery(\'#option-value-row' + option_value_row + '\').remove();" class="button"><?php echo JText::_('J2STORE_REMOVE'); ?></a></td>';
	html += '  </tr>';
    html += '</tbody>';

	$('#option-value tfoot').before(html);

	option_value_row++;
	})(j2store.jQuery);
}

function DeleteOptionValue(optionvalue_id , option_value_row){
	(function($) {
				$.ajax({
	            url :'index.php?option=com_j2store&view=option&task=deleteoptionvalue&optionvalue_id='+optionvalue_id,
				type: 'post',
				dataType:'json',
				success: function(json){
					if(json){
						$("#system-message-container").html(json['html']);
						if(json['success']){
							$('#option-value-row'+option_value_row).remove();
						}
					}
				}

		});
	})(j2store.jQuery);
}

</script>


