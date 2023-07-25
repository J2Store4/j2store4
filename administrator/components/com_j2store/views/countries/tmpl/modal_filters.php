<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<table class="adminlist table table-striped table-condensed">
			<tr>
			<td align="left" width="100%"><?php echo JText::_( 'J2STORE_FILTER_SEARCH' ); ?>:
				<?php echo $search = htmlspecialchars(@$this->state->search);?>
				<?php echo  J2Html::text('country_name',$search,array('id'=>'search' ,'class'=>'input j2store-zone-filters'));?>
				<?php echo  J2Html::button('go',JText::_( 'J2STORE_FILTER_GO' ) ,array('class'=>'btn btn-success' ,'onclick'=>'this.form.submit();'));?>
				<?php echo J2Html::button('reset',JText::_( 'J2STORE_FILTER_RESET' ),array('id'=>'filter-reset','class'=>'btn btn-inverse','onclick'=>'resetFilter()'));?>
			</td>
			<td><?php echo $this->pagination->getLimitBox();?></td>
			<td>
			<input class="btn btn-success" type="button" value="<?php echo JText::_( 'J2STORE_IMPORT_COUNTRIES' );?>" onclick="if (document.adminForm.boxchecked.value==0){alert('Please first make a selection from the list');}else{jQuery('#view').attr('value','geozones');jQuery('#task').attr('value','importcountry');this.form.submit();}" />
			</td>
		</tr>
</table>
<script type="text/javascript">
function resetFilter(){
	jQuery("#search").attr('value','');
 	jQuery("adminForm").submit();
}

</script>