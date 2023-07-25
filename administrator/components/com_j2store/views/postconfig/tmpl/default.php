<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
$platform = J2Store::platform();
$platform->loadExtra('behavior.framework');
$platform->loadExtra('behavior.combobox');
$platform->loadExtra('formbehavior.chosen','select');

$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
$script = <<<JS

;// This comment is intentionally put here to prevent badly written plugins from causing a Javascript error
// due to missing trailing semicolon and/or newline in their code.
  if(typeof(j2store) == 'undefined') {
	var j2store = {};
}
if(typeof(j2store.jQuery) == 'undefined') {
	j2store.jQuery = jQuery.noConflict();
}
(function($){
	$(document).ready(function(){
		$('#j2store-postconfig-apply').click(function(e){
		$.ajax({
				url : 'index.php?option=com_j2store&view=postconfig&task=saveConfig',
				type: 'post',
				data: $('#j2store-postconfig-form input[type=\'text\'], #j2store-postconfig-form input[type=\'checkbox\']:checked, #j2store-postconfig-form input[type=\'radio\']:checked, #j2store-postconfig-form input[type=\'hidden\'], #j2store-postconfig-form select, #j2store-postconfig-form textarea'),
  				dataType: 'json',
				cache: false,
  				beforeSend: function() {
  					$('#j2store-postconfig-apply').attr('disabled', true);
  					$('#j2store-postconfig-apply').after('<span class="wait">&nbsp;<img src="media/j2store/images/loading.gif" alt="" /></span>');
  				}
			})
			.done(function(json) {
		
				$('.warning, .j2error').remove();
  					if (json['redirect']) {
  						location = json['redirect'];
  					} else if (json['error']) {
  						$.each( json['error'], function( key, value ) {
  							if (value) {
  								$('#j2store-postconfig-form #'+key).after('<br class="j2error" /><span class="j2error">' + value + '</span>');
  							}
  						});

  					}
		
			})
			.always(function() {
  					$('#j2store-postconfig-apply').attr('disabled', false);
  					$('.wait').remove();
  			});
		});		
	})
})(j2store.jQuery);

JS;
JFactory::getDocument()->addScriptDeclaration($script);
?>

<form action="index.php" method="post" name="adminForm" id="j2store-postconfig-form"
	class="form-horizontal">
	<input type="hidden" name="option" value="com_j2store" /> <input
		type="hidden" name="view" value="postconfig" /> <input type="hidden"
		name="task" id="task" value="saveConfig" /> <input type="hidden"
		name="<?php echo JFactory::getSession()->getFormToken()?>" value="1" />

	<div class="hero-unit">
		<h1><?php echo JText::_('J2STORE_CONGRATULATIONS')?></h1>
		<p class="lead"><?php echo JText::_('J2STORE_POSTCONFIG_WELCOME_MESSAGE'); ?>
		<p class="text-info"><?php echo JText::_('J2STORE_POSTCONFIG_WHATTHIS'); ?></p>
	</div>
	
	<div class="<?php echo $row_class;?>">
		<div class="<?php echo $col_class;?>4">
			<h3><?php echo JText::_('J2STORE_BASIC_SETTINGS'); ?></h3>
			<?php echo $this->loadTemplate('basic'); ?>
		</div> <!-- end of span. Basic settings -->
		
		<div class="<?php echo $col_class;?>4">
			<h3><?php echo JText::_('J2STORE_ADVANCED_SETTINGS')?></h3>
			<?php echo $this->loadTemplate('advanced'); ?>
		</div>
	
		<div class="<?php echo $col_class;?>4">
			<h3><?php echo JText::_('J2STORE_INTEGRITY_CHECK')?></h3>
			<?php echo $this->loadTemplate('integrity'); ?>
		</div>
			
	</div> <!--  end of row -->
	<div class="<?php echo $row_class;?>">
		<div class="<?php echo $col_class;?>12">
			<?php echo $this->loadTemplate('terms'); ?>
		</div> 
	</div>	

	<button id="j2store-postconfig-apply" class="btn btn-success btn-large"
		onclick="return false;"><?php echo JText::_('J2STORE_SAVE_AND_PROCEED');?></button>
</form>