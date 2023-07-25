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
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$platform->loadExtra('behavior.formvalidator');
jimport('joomla.filesystem.file');
$this->loadHelper('select');
$row_class = 'row';
$col_class = 'col-md-';
$alert_html = '<joomla-alert type="danger" close-text="Close" dismiss="true" role="alert" style="animation-name: joomla-alert-fade-in;"><div class="alert-heading"><span class="error"></span><span class="visually-hidden">Error</span></div><div class="alert-wrapper"><div class="alert-message" >'.JText::_('J2STORE_INVALID_INPUT_FIELD').'</div></div></joomla-alert>' ;
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
    $alert_html = '<div class="alert alert-error alert-danger">'.JText::_('J2STORE_INVALID_INPUT_FIELD').'<button type="button" class="close" data-dismiss="alert">×</button></div>' ;
}
$config = JFactory::getConfig();
$asset_id = $config->get('asset_id');

$optionvalues=array();
if(isset($this->optionvalues))
{
 $optionvalues = $this->optionvalues;
}

?>
<script  type="text/javascript">
    Joomla.submitbutton = function(pressbutton) {
        var form = document.adminForm;
        if (pressbutton == 'cancel') {
            document.adminForm.task.value = pressbutton;
            form.submit();
        }else{
            if (document.formvalidator.isValid(form)) {
                document.adminForm.task.value = pressbutton;
                form.submit();
            }
            else {
                let msg = [];
                msg.push('<?php echo $alert_html; ?>');
                document.getElementById('system-message-container').innerHTML =  msg.join('\n') ;
            }
        }
    }
</script>
<style>
#option-value  .option-images{
width:100px;
}

</style>
<div class="<?php echo $row_class; ?>">
    <div class="<?php echo $col_class ?>12">
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
				<input type="text" name="option_unique_name" id="option_unique_name" class="required" value="<?php echo htmlentities($this->item->option_unique_name);?>" />
			</td>
		</tr>
		<tr>
			<td width="100" align="right" class="key">
				<label for="option_name">
					<?php echo JText::_( 'J2STORE_OPTION_DISPLAY_NAME' ); ?>:
				</label>
			</td>
			<td>
				<input type="text" name="option_name" id="option_name" class="required" value="<?php echo htmlentities($this->item->option_name);?>" />
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
		<?php
		if($this->item->type == 'text'){
			?>
			<style>
				#place_holder{
					display: table-row;
				}
			</style>
		<?php
		}else{
			?>
			<style>
				#place_holder{
					display: none;
				}
			</style>
		<?php
		}
        $this->item->option_params = $platform->getRegistry($this->item->option_params);
		?>
		<tr id="place_holder" >
			<td width="100" align="right" class="key">
				<label for="place_holder">
					<?php echo JText::_( 'J2STORE_OPTION_PLACEHOLDER' ); ?>:
				</label>
			</td>
			<td>
				<?php echo J2Html::text('option_params[place_holder]', $this->item->option_params->get('place_holder', '' )); ?>
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

		<?php

		if($this->item->type == 'date' || $this->item->type == 'datetime'  ):
			?>


		<tr>
			<td>
				<?php echo JText::_( 'J2STORE_DATE_HIDE_PAST_DATES' ); ?>
			</td>
			<td>
				<?php echo J2Html::radio('option_params[hide_pastdates]', $this->item->option_params->get('hide_pastdates')); ?>
			</td>
		</tr>

		<tr>
			<td>
				<?php echo JText::_( 'J2STORE_CONF_DATE_FORMAT_LABEL' ); ?>
			</td>
			<td>
				<?php echo J2Html::text('option_params[date_format]', $this->item->option_params->get('date_format', 'yy-mm-dd')); ?>
			</td>
		</tr>
		
		<?php if($this->item->type == 'datetime'): ?>
		
		<tr>
			<td>
				<?php echo JText::_( 'J2STORE_CONF_TIME_FORMAT_LABEL' ); ?>
			</td>
			<td>
				<?php echo J2Html::text('option_params[time_format]', $this->item->option_params->get('time_format', 'HH:mm' )); ?>
			</td>
		</tr>
		
		
		<?php endif; ?>
		
		<?php endif;?>
	</table>

</fieldset>
<fieldset id="option-value">
	<div class="<?php echo $row_class; ?>">
		<div class="<?php echo $col_class ?>9">
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
		                <input type="text" class="input-small required"   name="option_value[<?php echo $option_value_row; ?>][optionvalue_name]" value="<?php echo isset($option_value->optionvalue_name) ? htmlentities($option_value->optionvalue_name): ''; ?>" />
		                <br />
		               </td>
		               <td class="right">
		               <div class="input-prepend input-append">
                           <?php echo J2Html::media('option_value['.$option_value_row.'][optionvalue_image]', $option_value->optionvalue_image, array('id' => 'jform_optionvalue_image_'.$option_value->j2store_optionvalue_id, 'image_id' => 'input-optionvalue-image-'.$option_value->j2store_optionvalue_id, 'no_hide' => '')); ?>
						 </div>
		               </td>
                        <td class="right"><input class="input-small required" type="text" name="option_value[<?php echo $option_value_row; ?>][ordering]" value="<?php echo (!empty($option_value->ordering) ? $option_value->ordering: 0); ?>" size="1" /></td>
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
	        <div class="<?php echo $col_class; ?>3">
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
</script>
    <?php if(version_compare(JVERSION, '3.99.99', 'lt')):?>
    <script>
window.addEvent('domready', function() {

SqueezeBox.initialize({});
SqueezeBox.assign($$('a.modal-button'), {
	parse: 'rel'
});

});
    </script>
<?php endif; ?>




<script type="text/javascript">
var thumb_image = "<?php echo JUri::root().'media/j2store/images/common/no_image-100x100.jpg'?>";
var vhref = "<?php echo "index.php?option=com_media&view=images&tmpl=component&asset=".$asset_id."&author=".JFactory::getUser()->id."&fieldid=jform_main_image_";?>";
(function($) {

$('select[name=\'type\']').bind('change', function() {
	if (this.value == 'select' || this.value == 'radio' || this.value == 'checkbox' || this.value == 'image') {
		$('#option-value').show();
		$('#place_holder').hide();
	} else if(this.value == 'text') {
		$('#option-value').hide();
		$('#place_holder').show();
	}else{
		$('#option-value').hide();
		$('#place_holder').hide();
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
	html += '<input type="text" class="input-small required"  name="option_value[' + option_value_row + '][optionvalue_name]" value="" /> <br />';
	html += '  </td>';
	html += '<td class="right">';
	html += '<input type="hidden" name="option_value[' + option_value_row + '][optionvalue_image]" value="" />';
	html += '<span class="text text-info"><?php echo addslashes(JText::_('J2STORE_OPTIONVALUE_INSERT_IMAGE_HELP'));?></span>';
        html += '    <td class="right"><input class="input-small" type="text" name="option_value[' + option_value_row + '][ordering]" value="0" size="1" /></td>';
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
        </div>
    </div>

