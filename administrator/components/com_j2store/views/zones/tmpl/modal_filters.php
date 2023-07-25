<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
//JHTML::_('behavior.modal');
$platform = J2Store::platform();
$platform->loadExtra('behavior.modal');
$row_class = 'row';
$col_class = 'col-md-';
if (version_compare(JVERSION, '3.99.99', 'lt')) {
    $row_class = 'row-fluid';
    $col_class = 'span';
}
?>
<div class="<?php echo $row_class ?>">
	<div class="<?php echo $col_class ?>12">
		<table class="adminlist table table-striped table-condensed">
			<tr>
				<td>
					<?php echo JText::_('J2STORE_COUNTRY');?>
					<?php  echo J2Html::select()->clearState()
							->type('genericlist')
							->name('country_id')
							->value($this->state->country_id)
							->attribs(array('onchange'=>'this.form.submit();'))
							->setPlaceHolders(
									array(''=>JText::_('J2STORE_SELECT_OPTION'))
							)
							->hasOne('Countries')
							->setRelations( array(
									'fields' => array (
											'key' => 'j2store_country_id',
											'name' => array('country_name')
									)
							)
							)->getHtml();
					?>
				</td>
				<td>
					<input class="btn btn-success" type="button" value="<?php echo JText::_( 'J2STORE_IMPORT_COUNTRIES' );?>" onclick="if (document.adminForm.boxchecked.value==0){alert('Please first make a selection from the list');}else{jQuery('#view').attr('value','geozones');jQuery('#task').attr('value','importzone');this.form.submit();}" />
				</td>
				<td>
					<?php echo $this->pagination->getLimitBox();?>
				</td>
			</tr>
		</table>
	</div>
</div>
<script type="text/javascript">
function resetFilter(){
	jQuery("#search").attr('value','');
 	jQuery("adminForm").submit();
}
</script>